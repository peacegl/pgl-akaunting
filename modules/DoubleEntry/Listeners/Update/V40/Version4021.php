<?php

namespace Modules\DoubleEntry\Listeners\Update\V40;

use App\Abstracts\Listeners\Update as Listener;
use App\Traits\Jobs;
use App\Models\Banking\Transfer;
use App\Events\Install\UpdateFinished;
use App\Models\Common\Company;
use Modules\DoubleEntry\Models\Ledger;
use Modules\DoubleEntry\Models\Journal;
use Modules\DoubleEntry\Models\AccountBank;
use Modules\DoubleEntry\Jobs\Journal\DeleteJournalEntry;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Version4021 extends Listener
{
    const ALIAS = 'double-entry';

    const VERSION = '4.0.21';

    use Jobs;

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

        $company_ids = DB::table('double_entry_journals')
            ->where('reference', 'like', 'transfer:%')
            ->groupBy('company_id')
            ->pluck('company_id');

        foreach ($company_ids as $company_id) {
            Log::channel('stderr')->info('Updating company: ' . $company_id);

            $company = Company::find($company_id);

            if (! $company instanceof Company) {
                continue;
            }

            $company->makeCurrent();

            $this->updateJournal();

            Log::channel('stderr')->info('Company updated: ' . $company_id);
        }
    }

    protected function updateJournal()
    {
        Log::channel('stderr')->info('Updating journal entries...');

        $journals = Journal::where('reference', 'like', 'transfer:%')->get();

        foreach ($journals as $journal) {
            $this->createTransferLedgers($journal);

            Log::channel('stderr')->info('Deleting journal entry: ' . $journal->id);

            $this->dispatch(new DeleteJournalEntry($journal));
        }
    }

    public function createTransferLedgers($journal)
    {
        Log::channel('stderr')->info('Creating transfer ledgers...');

        $reference = explode(':', $journal->reference);

        $transfer = Transfer::find($reference[1]);

        if (! $transfer) {
            return;
        }

        $payment = isset($transfer->expense_transaction) ? $transfer->expense_transaction : null;
        $revenue = isset($transfer->income_transaction) ? $transfer->income_transaction : null;

        if (empty($payment) || empty($revenue)) {
            return;
        }

        $payment_account_id = AccountBank::where('bank_id', $payment->account_id)->pluck('account_id')->first();
        $revenue_account_id = AccountBank::where('bank_id', $revenue->account_id)->pluck('account_id')->first();

        if (empty($payment_account_id) || empty($revenue_account_id)) {
            return;
        }

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

        Log::channel('stderr')->info('Transfer ledgers created...');
    }
}
