<?php

namespace Modules\DoubleEntry\Observers\Banking;

use App\Abstracts\Observer;
use Modules\DoubleEntry\Models\Ledger;
use Modules\DoubleEntry\Models\Journal;
use App\Models\Banking\Transfer as Model;
use Modules\DoubleEntry\Models\AccountBank;
use Modules\DoubleEntry\Events\Journal\JournalCreated;
use Modules\DoubleEntry\Traits\Journal as JournalTraits;

class Transfer extends Observer
{
    use JournalTraits;

    /**
     * Listen to the saved event.
     *
     * @param  Model  $transfer
     * @return void
     */
    public function saved(Model $transfer)
    {
        $payment = $transfer->expense_transaction;
        $revenue = $transfer->income_transaction;

        $payment_account_id = AccountBank::where('bank_id', $payment->account_id)->pluck('account_id')->first();
        $revenue_account_id = AccountBank::where('bank_id', $revenue->account_id)->pluck('account_id')->first();

        if (empty($payment_account_id) || empty($revenue_account_id)) {
            return;
        }

        // $journal_number = $this->getNextJournalNumber();

        // $journal = Journal::where('company_id', $transfer->company_id)
        //     ->where('reference', 'transfer:' . $transfer->id)
        //     ->where('journal_number', $journal_number)
        //     ->first();

        // if (! $journal) {
        //     $journal = Journal::create([
        //         'company_id' => $transfer->company_id,
        //         'reference' => 'transfer:' . $transfer->id,
        //         'journal_number' => $journal_number,
        //         'amount' => $payment->amount,
        //         'currency_code' => $payment->currency_code,
        //         'currency_rate' => $payment->currency_rate,
        //         'paid_at' => $payment->paid_at,
        //         'description' => $payment->description ?: trans_choice('general.transfers', 1),
        //     ]);

        //     event(new JournalCreated($journal));
        // }

        $l1 = Ledger::updateOrCreate([
            'company_id' => $transfer->company_id,
            'ledgerable_type' => get_class($payment),
            'ledgerable_id' => $payment->id,
            'account_id' => $payment_account_id,
        ], [
            'issued_at' => $payment->paid_at,
            'entry_type' => 'item',
            'credit' => $payment->amount,
            'reference' => 'transfer:' . $transfer->id,
        ]);

        $payment->reference = 'journal-entry-ledger:' . $l1->id;
        $payment->save();

        $l2 = Ledger::updateOrCreate([
            'company_id' => $transfer->company_id,
            'ledgerable_type' => get_class($revenue),
            'ledgerable_id' => $revenue->id,
            'account_id' => $revenue_account_id,
        ], [
            'issued_at' => $revenue->paid_at,
            'entry_type' => 'item',
            'debit' => $revenue->amount,
            'reference' => 'transfer:' . $transfer->id,
        ]);

        $revenue->reference = 'journal-entry-ledger:' . $l2->id;
        $revenue->save();
    }

    /**
     * Listen to the deleted event.
     *
     * @param  Model  $transfer
     * @return void
     */
    public function deleted(Model $transfer)
    {
        $journal = Journal::where('reference', 'transfer:' . $transfer->id)->first();

        if (empty($journal)) {
            Ledger::record($transfer->id, get_class($transfer))->delete();

            return;
        }

        Ledger::record($journal->id, get_class($journal))->delete();

        $journal->delete();
    }
}
