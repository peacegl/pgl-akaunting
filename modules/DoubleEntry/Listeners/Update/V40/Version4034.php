<?php

namespace Modules\DoubleEntry\Listeners\Update\V40;

use App\Abstracts\Listeners\Update as Listener;
use App\Models\Banking\Transaction;
use App\Events\Install\UpdateFinished;
use App\Models\Banking\TransactionTax;
use App\Models\Common\Company;
use App\Traits\Jobs;
use Modules\DoubleEntry\Models\AccountTax;
use Modules\DoubleEntry\Models\Ledger;
use Modules\DoubleEntry\Jobs\Ledger\CreateLedger;
use Modules\DoubleEntry\Traits\Permissions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class Version4034 extends Listener
{
    const ALIAS = 'double-entry';

    const VERSION = '4.0.34';

    use Jobs, Permissions;

    /**
     * Handle the event.
     *
     * @param  $event
     * @return void
     */
    public function handle(UpdateFinished $event)
    {
        if ($this->skipThisUpdate($event)) {
            return;
        }

        $company_ids = DB::table('transaction_taxes')
            ->join('modules', 'modules.company_id', '=', 'transaction_taxes.company_id')
            ->where('modules.alias', 'double-entry')
            ->whereNull('modules.deleted_at')
            ->pluck('modules.company_id')
            ->unique();

        foreach ($company_ids as $company_id) {
            Log::channel('stderr')->info('Updating company: ' . $company_id);

            $company = Company::find($company_id);

            if (! $company instanceof Company) {
                continue;
            }

            $company->makeCurrent();

            $this->updateLedgers();

            Log::channel('stderr')->info('Company updated: ' . $company_id);
        }
    }

    protected function updateLedgers()
    {
        Log::channel('stderr')->info('Updating ledgers...');

        $transaction_taxes = TransactionTax::get();

        foreach ($transaction_taxes as $transaction_tax) {
            $this->createTransactionTaxLedger($transaction_tax);
        }
    }

    public function createTransactionTaxLedger($transaction_tax)
    {
        Log::channel('stderr')->info('Creating transaction tax ledger...');

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

        Log::channel('stderr')->info('Transfer ledgers created...');
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
            $this->isJournal($transaction) || 
            $this->isNotValidTransactionType($transaction->type) || 
            $this->isReconciliation($transaction)
        ) {
            return true;
        }

        return false;
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
}
