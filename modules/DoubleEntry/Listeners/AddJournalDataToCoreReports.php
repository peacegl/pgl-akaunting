<?php

namespace Modules\DoubleEntry\Listeners;

use App\Abstracts\Listeners\Report as Listener;
use App\Events\Report\DataLoaded;
use Illuminate\Support\Str;
use Modules\DoubleEntry\Models\DEClass;

class AddJournalDataToCoreReports extends Listener
{
    public $classes = [
        'App\Reports\IncomeSummary',
        'App\Reports\ExpenseSummary',
        'App\Reports\IncomeExpenseSummary',
        'App\Reports\ProfitLoss',
    ];

    /**
     * Handle the event.
     *
     * @param DataLoaded $event
     * @return void
     */
    public function handle(DataLoaded $event)
    {
        if ($this->skipThisClass($event)) {
            return;
        }

        if ($this->skipRowsShowing($event, 'de_account')) {
            return;
        }

        $report = $event->class;

        switch (get_class($report)) {
            case 'App\Reports\IncomeSummary':
                $journal_entries_income = $this->getJournals('income');
                $report->setTotals($journal_entries_income, 'paid_at', false, 'default', false);

                break;
            case 'App\Reports\ExpenseSummary':
                $journal_entries_expense = $this->getJournals('expense');
                $report->setTotals($journal_entries_expense, 'paid_at', false, 'default', false);

                break;
            case 'App\Reports\IncomeExpenseSummary':
            case 'App\Reports\ProfitLoss':
                $journal_entries_income = $this->getJournals('income');
                $report->setTotals($journal_entries_income, 'paid_at', false, 'default', false);

                $journal_entries_expense = $this->getJournals('expense');
                $report->setTotals($journal_entries_expense, 'paid_at', false, 'default', false);

                break;

            default:

                break;
        }

        $this->setNetProfit($report);
    }

    /**
     * Get journals that consist of accounts whose class of them is the expense/income.
     *
     * @return array
     */
    protected function getJournals($type = 'income')
    {
        $journals = [];

        if ($type == 'income') {
            $class_name = $type;
        }

        if ($type == 'expense') {
            $class_name = Str::of($type)->plural();
        }

        $class = DEClass::where('name', 'double-entry::classes.' . $class_name)
            ->with(['accounts'])
            ->first();

        if (!$class) {
            return $journals;
        }

        $accounts = $class->accounts()->enabled()->get();

        foreach ($accounts as $account) {
            $ledgers = $account->ledgers()
                ->where('ledgerable_type', 'Modules\DoubleEntry\Models\Journal')
                ->get();

            foreach ($ledgers as $ledger) {
                $hasJournal = false;
                $journal = $ledger->ledgerable;

                if (!$journal) {
                    continue;
                }

                $journal->type = $type;

                foreach ($journals as $journal_entry) {
                    if ($journal->is($journal_entry)) {
                        $hasJournal = true;
                        break;
                    }
                }

                if (!$hasJournal) {
                    array_push($journals, $journal);
                }
            }
        }

        return $journals;
    }

    public function setNetProfit($report)
    {
        $report->net_profit = [];
        
        foreach ($report->footer_totals as $table => $dates) {
            foreach ($dates as $date => $total) {
                if (!isset($report->net_profit[$date])) {
                    $report->net_profit[$date] = 0;
                }

                if ($table == 'income') {
                    $report->net_profit[$date] += $total;

                    continue;
                }

                $report->net_profit[$date] -= $total;
            }
        }
    }
}
