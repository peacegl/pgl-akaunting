<?php

namespace Modules\DoubleEntry\Jobs\Journal;

use App\Abstracts\JobShouldQueue;
use App\Models\Setting\Currency;
use Modules\DoubleEntry\Models\Account;
use Modules\DoubleEntry\Traits\Journal as Traits;

class ImportJournalEntry extends JobShouldQueue
{
    use Traits;

    protected $rows;

    public function __construct($rows)
    {
        $this->rows = $rows;

        $this->onQueue('jobs');
    }
        
    public function handle()
    {
        $owner_account = Account::where('code', setting('double-entry.accounts_owners_contribution', 300))->first();

        foreach ($this->rows[0] as $row) {
            if ($row['balance'] == 0) {
                continue;
            }

            $account = Account::where('code', $row['code'])->first();

            if (empty($account)) {
                continue;
            }

            if ($row['balance'] > 0) {
                $items = [
                    [
                        'account_id'    => $account->id,
                        'debit'         => $row['balance'],
                        'credit'        => 0,
                        'has_credit'    => true,
                        'has_debit'     => false,
                    ], 
                    [
                        'account_id'    => $owner_account->id,
                        'debit'         => 0,
                        'credit'        => $row['balance'],
                        'has_credit'    => false,
                        'has_debit'     => true,
                    ]
                ];
            } else {
                $items = [
                    [
                        'account_id'    => $account->id,
                        'debit'         => 0,
                        'credit'        => str_replace('-', '', $row['balance']),
                        'has_credit'    => false,
                        'has_debit'     => true,
                    ], 
                    [
                        'account_id'    => $owner_account->id,
                        'debit'         => str_replace('-', '', $row['balance']),
                        'credit'        => 0,
                        'has_credit'    => true,
                        'has_debit'     => false,
                    ]
                ];
            }

            $currency = Currency::where('code', default_currency())->first();
            $journal_number = $this->getNextJournalNumber();

            $data = [
                'company_id'        => company_id(),
                'journal_number'    => $journal_number,
                'currency_code'     => $currency->code,
                'currency_rate'     => $currency->rate,
                'basis'             => trans('general.accrual'),
                'description'       => trans('double-entry::general.opening_balance'),
                'paid_at'           => date('Y-m-d'),
                'created_from'      => 'import',
                'items'             => $items,
            ];

            $this->dispatch(new CreateJournalEntry($data));
        }
    }
}
