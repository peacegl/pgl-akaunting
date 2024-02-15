<?php

namespace Modules\DoubleEntry\Reports;

use App\Abstracts\Report;
use Illuminate\Support\Str;
use Modules\DoubleEntry\Models\Account;

class GeneralLedger extends Report
{
    public $default_name = 'double-entry::general.general_ledger';

    public $category = 'general.accounting';

    public $icon = 'balance';

    public $opening_balances = [];

    public function getGrandTotal()
    {
        return trans('general.na');
    }

    public function setViews()
    {
        parent::setViews();

        $this->views['detail.content.header'] = 'double-entry::general_ledger.content.header';
        $this->views['detail.table.header'] = 'double-entry::general_ledger.table.header';
        $this->views['detail.table.body'] = 'double-entry::general_ledger.table.body';
        $this->views['detail.table.row'] = 'double-entry::general_ledger.table.row';
        $this->views['detail.table.footer'] = 'double-entry::general_ledger.table.footer';
    }

    public function setTables()
    {
        $query = $this->applyFilters(Account::with('type'));

        $this->tables = $query->get(['code', 'name', 'type_id'])
            ->mapWithKeys(function ($account) {
                $key = Str::lower($account->code . ' - ' . $account->trans_name);

                $value = $account->trans_name . ' (' . trans($account->type->name) . ')';

                return [$key => $value];
            })
            ->all();
    }

    public function setData()
    {
        $query = $this->applyFilters(Account::query());

        $accounts = $query->get();

        foreach ($accounts as $account) {
            $account_key = Str::lower($account->code . ' - ' . $account->trans_name);

            $this->footer_totals[$account_key]['debit'] = 0;
            $this->footer_totals[$account_key]['credit'] = 0;

            $this->opening_balances[$account_key] = $balance = $account->opening_balance;

            foreach ($account->ledgers as $ledger) {
                $ledger->castDebit();
                $ledger->castCredit();

                $balance += $ledger->debit - $ledger->credit;

                $this->row_values[$account_key][] = [
                    'issued_at' => $ledger->issued_at,
                    'transaction' => $ledger->transaction,
                    'link' => $ledger->ledgerable_link,
                    'debit' => $ledger->debit,
                    'credit' => $ledger->credit,
                    'balance' => $balance,
                ];

                $this->footer_totals[$account_key]['debit'] += $ledger->debit;
                $this->footer_totals[$account_key]['credit'] += $ledger->credit;
            }

            $this->footer_totals[$account_key]['balance'] = $balance;
            $this->footer_totals[$account_key]['balance_change'] = $balance - $this->opening_balances[$account_key];
        }
    }

    public function getFields()
    {
        return [];
    }

    public function print()
    {
        $print = true;

        return view($this->views['print'], compact('print'))->with('class', $this);
    }
}
