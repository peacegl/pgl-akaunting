<?php

namespace Modules\DoubleEntry\Reports;

use App\Abstracts\Report;
use Modules\DoubleEntry\Models\Ledger;

class JournalReport extends Report
{
    public $category = 'general.accounting';

    public $icon = 'balance';

    public function getDefaultName()
    {
        if (!empty($this->default_name)) {
            return trans($this->default_name);
        }

        return trans_choice('double-entry::general.manual_journals', 2);
    }

    public function setViews()
    {
        parent::setViews();

        $this->views['header'] = 'double-entry::journal_report.header';
        $this->views['detail'] = 'double-entry::journal_report.detail';
        $this->views['detail.content.header'] = 'double-entry::journal_report.content.header';
        $this->views['detail.table.header'] = 'double-entry::journal_report.table.header';
        $this->views['detail.table.body'] = 'double-entry::journal_report.table.body';
        $this->views['detail.table.row'] = 'double-entry::journal_report.table.row';
        $this->views['detail.table.footer'] = 'double-entry::journal_report.table.footer';
    }

    public function setData()
    {
        $report_at = $this->getSearchStringValue('report_at');
        $basis = $this->getSearchStringValue('basis', 'accrual');
        $contact = $this->getSearchStringValue('contact', '');
        $de_account_id = $this->getSearchStringValue('de_account_id');

        $args = [
            'report_at' => $report_at,
            'basis' => $basis,
            'contact' => $contact,
            'de_account_id' => $de_account_id,
        ];

        $builder = $this->applyFilters(Ledger::with(['ledgerable', 'account']), $args);

        $this->reference_documents = $this->transformData($builder);
    }

    public function getFields()
    {
        return [];
    }

    private function transformData($builder)
    {
        $entries = $builder->get()->asEntries();

        if ($entries->count() === 0) {
            return collect();
        }

        $reference_documents = collect();

        foreach ($entries as $key => $entry) {
            $reference_documents->push($this->transformLedgersDocuments($entry));
        }

        return $reference_documents;
    }

    private function transformLedgersDocuments($ledger_items)
    {
        return (object) [
            'date' => company_date($ledger_items->first()->issued_at),
            'link' => $ledger_items->first()->ledgerable_link,
            'transaction' => $ledger_items->first()->transaction,
            'debit_total' => money($ledger_items->sum('debit'), setting('default.currency'), true),
            'credit_total' => money($ledger_items->sum('credit'), setting('default.currency'), true),
            'ledgers' => $ledger_items,
        ];
    }

    public function print()
    {
        $print = true;

        return view($this->views['print'], compact('print'))->with('class', $this);
    }
}
