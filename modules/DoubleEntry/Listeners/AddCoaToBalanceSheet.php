<?php

namespace Modules\DoubleEntry\Listeners;

use App\Abstracts\Listeners\Report as Listener;
use App\Events\Report\FilterShowing;
use App\Traits\DateTime;
use Modules\DoubleEntry\Models\Account as Coa;
use Modules\DoubleEntry\Traits\Journal;

class AddCoaToBalanceSheet extends Listener
{
    use DateTime, Journal;

    public $classes = [
        'Modules\DoubleEntry\Reports\BalanceSheet',
    ];

    /**
     * Handle filter showing event.
     *
     * @param \App\Events\Report\FilterShowing $event
     * @return void
     */
    public function handleFilterShowing(FilterShowing $event)
    {
        if ($this->skipThisClass($event)) {
            return;
        }

        $de_accounts = Coa::pluck('name', 'id')->transform(function ($name) {
            return is_array(trans($name)) ? $name : trans($name);
        })->sort()->all();

        $event->class->filters['de_accounts'] = $de_accounts;
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
}
