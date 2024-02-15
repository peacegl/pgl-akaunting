<?php

namespace Modules\DoubleEntry\Widgets;

use App\Abstracts\Widget;
use App\Traits\Currencies;
use Modules\DoubleEntry\Models\Account;

class ExpensesByCoa extends Widget
{
    use Currencies;

    public $default_name = 'double-entry::widgets.expenses_by_coa';

    public $description = 'double-entry::widgets.description.expenses_by_coa';

    public function show()
    {
        Account::inType('12')
            ->with('ledgers')
            ->each(function ($account) {
                $amount = 0;

                $model = $account->ledgers()
                    ->whereNotNull('debit')
                    ->whereHasMorph('ledgerable', [
                        'App\Models\Banking\Transaction',
                    ], function ($query, $type) {
                        if ($type == 'App\Models\Banking\Transaction') {
                            $query->expense();
                        }
                    });

                $this->applyFilters($model, ['date_field' => 'issued_at'])
                    ->each(function ($ledger) use (&$amount) {
                        $amount += $ledger->ledgerable->getAmountConvertedToDefault();
                    });

                $color = sprintf('#%06x', rand(0, 16777100));

                $this->addMoneyToDonut($color, $amount, $account->trans_name);
            });

        $chart = $this->getDonutChart(trans_choice('general.expenses', 2), '100%', 300, 6);

        return $this->view('widgets.donut_chart', compact('chart'));
    }
}
