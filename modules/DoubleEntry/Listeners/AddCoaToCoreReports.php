<?php

namespace Modules\DoubleEntry\Listeners;

use App\Abstracts\Listeners\Report as Listener;
use App\Events\Report\FilterApplying;
use App\Events\Report\FilterShowing;
use App\Events\Report\GroupApplying;
use App\Events\Report\GroupShowing;
use App\Events\Report\RowsShowing;
use App\Models\Document\Document;
use App\Models\Document\DocumentTotal;
use App\Reports\ExpenseSummary;
use App\Reports\IncomeExpenseSummary;
use App\Reports\ProfitLoss;
use App\Traits\Currencies;
use App\Utilities\Date;
use Modules\DoubleEntry\Models\Account;
use Modules\DoubleEntry\Traits\Accounts;
use Throwable;

class AddCoaToCoreReports extends Listener
{
    use Accounts, Currencies;

    protected $classes = [
        'App\Reports\IncomeSummary',
        'App\Reports\ExpenseSummary',
        'App\Reports\IncomeExpenseSummary',
        'App\Reports\ProfitLoss',
    ];

    /**
     * Handle filter showing event.
     *
     * @param  $event
     * @return void
     */
    public function handleFilterShowing(FilterShowing $event)
    {
        if ($this->skipRowsShowing($event, 'de_account')) {
            return;
        }

        unset($event->class->filters['categories']);

        $types = match(get_class($event->class)) {
            'App\Reports\IncomeSummary' => [13, 14, 15],
            'App\Reports\ExpenseSummary' => [11, 12],
        default=> [],
        };

        if (empty($types)) {
            return;
        }

        $accounts = Account::inType($types)
            ->pluck('name', 'id')
            ->transform(function ($name) {
                return is_array(trans($name)) ? $name : trans($name);
            })
            ->sort()
            ->all();

        $event->class->filters['de_accounts'] = $accounts;
        $event->class->filters['names']['de_accounts'] = trans_choice('double-entry::general.chart_of_accounts', 1);
        $event->class->filters['operators']['de_accounts'] = [
            'equal' => true,
            'not_equal' => false,
            'range' => false,
        ];
    }

    /**
     * Handle filter applying event.
     *
     * @param  $event
     * @return void
     */
    public function handleFilterApplying(FilterApplying $event)
    {
        if ($this->skipRowsShowing($event, 'de_account')) {
            return;
        }

        $de_account_id = $this->getSearchStringValue('de_account_id');

        if (empty($de_account_id)) {
            return;
        }

        try {
            $event->model->where(function ($query) use ($de_account_id) {
                return $query->whereHas('de_ledger', function ($query) use ($de_account_id) {
                    $query->where('account_id', $de_account_id);
                })->orWhereHas('items.de_ledger', function ($query) use ($de_account_id) {
                    $query->where('account_id', $de_account_id);
                })->orWhereHas('item_taxes.de_ledger', function ($query) use ($de_account_id) {
                    $query->where('account_id', $de_account_id);
                })->orWhereHas('totals.de_ledger', function ($query) use ($de_account_id) {
                    $query->where('account_id', $de_account_id);
                });
            });
        } catch (Throwable $th) {
            return;
        }
    }

    /**
     * Handle group showing event.
     *
     * @param  $event
     * @return void
     */
    public function handleGroupShowing(GroupShowing $event)
    {
        if ($this->skipThisClass($event)) {
            return;
        }

        $event->class->groups['de_account'] = trans_choice('double-entry::general.chart_of_accounts', 1);
    }

    /**
     * Handle group applying event.
     *
     * @param  $event
     * @return void
     */
    public function handleGroupApplying(GroupApplying $event)
    {
        if ($this->skipRowsShowing($event, 'de_account')) {
            return;
        }

        switch ($event->model->getTable()) {
            case 'documents':
                $items = $event->model->items()->get()->merge($event->model->totals()->code('discount')->get());
                $event->model->type = $event->model->type == 'bill' ? 'expense' : 'income';

                break;
            case 'transactions' && !is_null($event->model->document_id):
                $items = $event->model->document->items()->get()->merge($event->model->document->totals()->code('discount')->get());

                break;
            case 'transactions' && is_null($event->model->document_id):
            case 'double_entry_journals':
                $items = collect([$event->model]);

                break;
            default:
                $items = collect([]);

                break;
        }

        if ($items->isEmpty()) {
            return;
        }

        $items->each(function ($item) use (&$event) {
            $item->type = $event->model->type;

            if ($item instanceof DocumentTotal) {
                $item->type = $item->type == Document::BILL_TYPE ? 'expense' : 'income';
            }

            $item->table = $item->type;
        });

        $filter = $this->getSearchStringValue('de_account_id');

        foreach ($items as $item) {
            $model = $item->de_ledger();

            if (!empty($filter)) {
                $model->where('account_id', $filter);
            }

            $ledgers = $model->with('account.type.declass')->get();

            if ($ledgers->isEmpty()) {
                continue;
            }

            foreach ($ledgers as $ledger) {
                if (!empty($event->model->parent_id) && isset($event->model->issued_at)) {
                    $ledger->issued_at = $event->model->issued_at->toDateTimeString();
                }

                if (!empty($event->model->parent_id) && isset($event->model->paid_at)) {
                    $ledger->issued_at = $event->model->paid_at->toDateTimeString();
                }

                $this->setTotals($event, $ledger, $item->type, $item->table);
            }
        }
    }

    public function setTotals($event, $ledger, $type, $table, $check_type = false)
    {
        $date = $this->getFormattedDate($event, Date::parse($ledger->issued_at));

        if (
            !isset($event->class->row_values[$table][$ledger->account_id])
            || !isset($event->class->row_values[$table][$ledger->account_id][$date])
            || !isset($event->class->footer_totals[$table][$date])
        ) {
            return;
        }

        $amount = !empty($ledger->debit) ? $ledger->debit : $ledger->credit;

        if (empty($amount)) {
            return;
        }

        if (($event->class instanceof ProfitLoss || $event->class instanceof IncomeExpenseSummary) &&
            !str_contains($ledger->account->type->declass->name, $type)) {
            return;
        }

        if (($event->class instanceof ExpenseSummary || $event->class instanceof ProfitLoss || $event->class instanceof IncomeExpenseSummary) &&
            $ledger->account->type->declass->name == 'double-entry::classes.expenses' &&
            $ledger->credit) {
            $amount = $amount * -1;
        }

        if ($ledger->account->type->declass->name == 'double-entry::classes.income' && $ledger->debit) {
            $amount = $amount * -1;
        }

        if (($check_type == false) || ($type == 'income')) {
            $event->class->row_values[$table][$ledger->account_id][$date] += $amount;

            $event->class->footer_totals[$table][$date] += $amount;
        } else {
            $event->class->row_values[$table][$ledger->account_id][$date] -= $amount;

            $event->class->footer_totals[$table][$date] -= $amount;
        }
    }

    /**
     * Handle records showing event.
     *
     * @param  $event
     * @return void
     */
    public function handleRowsShowing(RowsShowing $event)
    {
        if ($this->skipRowsShowing($event, 'de_account')) {
            return;
        }

        $types = match(get_class($event->class)) {
            'App\Reports\IncomeSummary' => [13, 14, 15],
            'App\Reports\ExpenseSummary' => [11, 12],
            'App\Reports\IncomeExpenseSummary' => [11, 12, 13, 14, 15],
            'App\Reports\ProfitLoss' => [11, 12, 13, 14, 15],
        };

        $accounts = Account::inType($types)
            ->with(['sub_accounts'])
            ->orderBy('name')
            ->get(['id', 'account_id', 'name'])
            ->transform(function ($account, $key) {
                $account->name = $account->trans_name;

                return $account;
            })
            ->all();

        // $this->setRowNamesAndValuesForProfitLoss($event, $rows, $de_accounts);

        // $rows = $event->class->filters['de_accounts'];

        $this->setRowNamesAndValues($event, $accounts);

        $nodes = $this->getAccountsNodes($accounts);

        $this->setTreeNodes($event, $nodes);
    }

    public function setRowNamesAndValues($event, $accounts)
    {
        foreach ($event->class->dates as $date) {
            foreach ($event->class->tables as $table_key => $table_name) {
                foreach ($accounts as $account) {
                    $event->class->row_names[$table_key][$account->id] = $account->name;
                    $event->class->row_values[$table_key][$account->id][$date] = 0;
                }
            }
        }
    }

    public function setRowNamesAndValuesForProfitLoss($event, $rows, $de_accounts)
    {
        $type_accounts = [
            'income' => [13, 14, 15],
            'expense' => [11, 12],
        ];

        foreach ($event->class->dates as $date) {
            foreach ($event->class->tables as $table_key => $table_name) {
                foreach ($rows as $id => $name) {
                    $de_account = $de_accounts->where('id', $id)->first();

                    if (!in_array($de_account->table_key, $type_accounts[$table_key])) {
                        continue;
                    }

                    $event->class->row_names[$table_key][$id] = $name;
                    $event->class->row_values[$table_key][$id][$date] = 0;
                }
            }
        }
    }
}
