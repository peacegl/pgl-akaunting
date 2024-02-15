<?php

namespace Modules\DoubleEntry\Listeners;

use App\Abstracts\Listeners\Report as Listener;
use App\Events\Report\FilterApplying;
use App\Events\Report\FilterShowing;
use App\Events\Report\RowsShowing;
use Illuminate\Support\Str;
use Modules\DoubleEntry\Models\Account;
use Modules\DoubleEntry\Traits\Accounts;

class AddCoaToTrialBalance extends Listener
{
    use Accounts;

    public $classes = [
        'Modules\DoubleEntry\Reports\TrialBalance',
    ];

    /**
     * Handle filter showing event.
     *
     * @param  $event
     * @return void
     */
    public function handleFilterShowing(FilterShowing $event)
    {
        if ($this->skipThisClass($event)) {
            return;
        }

        $accounts = Account::has('ledgers')
            ->isNotSubAccount()
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
            'not_equal' => true,
            'range' => false,
        ];

        $event->class->filters['report_at'] = '';
        $event->class->filters['keys']['report_at'] = 'report_at';
        $event->class->filters['names']['report_at'] = trans_choice('general.reports', 1) . ' ' . trans('general.date');
        $event->class->filters['types']['report_at'] = 'date';
        $event->class->filters['operators']['report_at'] = [
            'equal' => true,
            'not_equal' => false,
            'range' => true,
        ];

        $event->class->filters['basis'] = $this->getBasis();
        $event->class->filters['keys']['basis'] = 'basis';
        $event->class->filters['names']['basis'] = trans('general.basis');
        $event->class->filters['defaults']['basis'] = $event->class->getSetting('basis', 'accrual');
    }

    /**
     * Handle filter applying event.
     *
     * @param  $event
     * @return void
     */
    public function handleFilterApplying(FilterApplying $event)
    {
        if ($this->skipThisClass($event)) {
            return;
        }

        $input = request('search', '');

        $value = $this->getSearchStringValue(name:'de_account_id', input:$input);

        $whereNot = Str::contains($input, 'not de_account_id');

        if (!empty($value) && $whereNot) {
            $id = 'not id:' . $value;
        }

        if (!empty($value) && !$whereNot) {
            $id = 'id:' . $value;
        }

        if (isset($id)) {
            $event->model->usingSearchString($id);
        }

        $event->model->has('ledgers');
    }

    /**
     * Handle records showing event.
     *
     * @param  $event
     * @return void
     */
    public function handleRowsShowing(RowsShowing $event)
    {
        if ($this->skipThisClass($event)) {
            return;
        }

        $accounts = Account::with(['declass', 'sub_accounts'])
            ->get(['id', 'account_id', 'type_id', 'name'])
            ->transform(function ($account) {
                $account->name = trans($account->name);

                $class_name = trans($account->declass->name);
                $account->class_name = Str::lower($class_name);

                return $account;
            })
            ->all();

        $this->setRowNamesAndValues($event, $accounts);

        $nodes = $this->getAccountsNodes($accounts);

        $this->setTreeNodes($event, $nodes);
    }

    public function setRowNamesAndValues($event, $accounts)
    {
        foreach ($event->class->tables as $table_key => $table_name) {
            foreach ($accounts as $account) {
                if ($account->class_name != $table_key) {
                    continue;
                }

                $event->class->row_names[$table_key][$account->id] = $account->name;

                $event->class->row_values[$table_key][$account->id]['debit'] = 0;
                $event->class->row_values[$table_key][$account->id]['credit'] = 0;
            }
        }
    }

    public function setTreeNodes($event, $nodes)
    {
        foreach ($event->class->tables as $table_key => $table_name) {
            foreach ($nodes as $id => $node) {
                if (!isset($event->class->row_names[$table_key][$id])) {
                    continue;
                }

                $event->class->row_tree_nodes[$table_key][$id] = $node;
            }
        }
    }
}
