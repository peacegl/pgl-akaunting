<?php

namespace Modules\DoubleEntry\Observers\Banking;

use App\Abstracts\Observer;
use App\Models\Banking\TransactionTax as Model;
use App\Models\Banking\Transaction;
use App\Traits\Jobs;
use App\Traits\Modules;
use Illuminate\Support\Str;
use Modules\DoubleEntry\Jobs\Ledger\DeleteLedger;
use Modules\DoubleEntry\Jobs\Ledger\CreateLedger;
use Modules\DoubleEntry\Models\AccountTax;
use Modules\DoubleEntry\Models\Ledger;
use Modules\DoubleEntry\Traits\Accounts;
use Modules\DoubleEntry\Traits\Permissions;

class TransactionTax extends Observer
{
    use Accounts, Jobs, Permissions, Modules;

    /**
     * Listen to the created event.
     *
     * @param Model $transaction_tax
     * @return void
     */
    public function created(Model $transaction_tax)
    {
        if ($this->skipEvent($transaction_tax->transaction)) {
            return;
        }

        $account_id = AccountTax::where('tax_id', $transaction_tax->tax->id)->pluck('account_id')->first();

        if (is_null($account_id)) {
            return;
        }

        if ($transaction_tax->type == Transaction::INCOME_TYPE) {
            $label = 'credit';

            if ($transaction_tax->tax->type == 'withholding') {
                $label = 'debit';
            }
        }

        if ($transaction_tax->type == Transaction::EXPENSE_TYPE) {
            $label = 'debit';

            if ($transaction_tax->tax->type == 'withholding') {
                $label = 'credit';
            }
        }

        $request = [
            'company_id'        => $transaction_tax->company_id,
            'ledgerable_id'     => $transaction_tax->id,
            'ledgerable_type'   => get_class($transaction_tax),
            'issued_at'         => $transaction_tax->created_at,
            'entry_type'        => 'item',
            'account_id'        => $account_id,
            $label              => $transaction_tax->amount,
        ];

        $request['account_id'] = $account_id;

        $this->dispatch(new CreateLedger($request));

        $transaction_ledger = Ledger::where('ledgerable_id', $transaction_tax->transaction->id)
            ->where('ledgerable_type', get_class($transaction_tax->transaction))
            ->where('entry_type', $request['entry_type'])
            ->first();

        if (is_null($transaction_ledger)) {
            return;
        }

        $transaction_ledger->update([
            $label => $transaction_ledger->$label - $transaction_tax->amount,
        ]);
    }

    /**
     * Listen to the saved event.
     *
     * @param Model $transaction_tax
     * @return void
     */
    public function saved(Model $transaction_tax)
    {
        if ($this->skipEvent($transaction_tax->transaction)) {
            return;
        }
    }

    /**
     * Listen to the deleted event.
     *
     * @param Model $transaction_tax
     * @return void
     */
    public function deleted(Model $transaction_tax)
    {
        if ($this->skipEvent($transaction_tax->transaction)) {
            return;
        }

        foreach ($transaction_tax->ledgers as $ledger) {
            if ($transaction_tax->type == Transaction::INCOME_TYPE) {
                $label = 'credit';
    
                if ($transaction_tax->tax->type == 'withholding') {
                    $label = 'debit';
                }
            }
    
            if ($transaction_tax->type == Transaction::EXPENSE_TYPE) {
                $label = 'debit';
    
                if ($transaction_tax->tax->type == 'withholding') {
                    $label = 'credit';
                }
            }

            $this->dispatch(new DeleteLedger($ledger));

            $transaction_ledger = Ledger::where('ledgerable_id', $transaction_tax->transaction->id)
                ->where('ledgerable_type', get_class($transaction_tax->transaction))
                ->where('entry_type', $transaction_tax->entry_type)
                ->first();

            if (is_null($transaction_ledger)) {
                return;
            }

            $transaction_ledger->update([
                $label => $transaction_ledger->$label + ($ledger->debit ?? $ledger->credit) ,
            ]);
        }
    }

    /**
     * Determine the transaction belongs to a journal or not.
     *
     * @param Model $transaction
     * @return bool
     */
    protected function isJournal($transaction)
    {
        if (empty($transaction->reference)) {
            return false;
        }

        if (!Str::contains($transaction->reference, 'journal-entry-ledger:')) {
            return false;
        }

        return true;
    }

    /**
     * Determine the transaction belongs to a reconciliation.
     * 
     * @param Model $transaction
     * @return bool
     */
    public function isReconciliation(Transaction $transaction)
    {
        return $transaction->isDirty('reconciled');
    }

    /**
     * Determines event will be continued or not.
     *
     * @param Transaction $transaction
     * @return bool
     */
    private function skipEvent(Transaction $transaction)
    {
        if (
            $this->moduleIsDisabled('double-entry')
            || $this->isJournal($transaction)
            || $this->isNotValidTransactionType($transaction->type)
            || $this->isReconciliation($transaction)
        ) {
            return true;
        }

        return false;
    }
}
