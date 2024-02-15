<?php

namespace Modules\DoubleEntry\Http\ViewComposers;

use App\Traits\Modules;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Modules\DoubleEntry\Models\Account;
use Modules\DoubleEntry\Models\AccountItem;
use Modules\DoubleEntry\Models\Type;
use Modules\DoubleEntry\View\Components\Accounts;

class Items
{
    use Modules;

    /**
     * Bind data to the view.
     *
     * @param  View  $view
     * @return void
     */
    public function compose(View $view)
    {
        if ($this->moduleIsDisabled('double-entry')) {
            return;
        }

        $request = request();

        $section = 'purchase_price_input_end';

        // arrangement for income account
        $name = 'de_income_account_id';
        $label = trans_choice('general.incomes', 1) . ' ' . trans_choice('general.accounts', 1);
        $selected = null;

        if ($request->routeIs('*items.edit')) {
            $item = $request->route(Str::singular((string) $request->segment(3)));

            $account = AccountItem::where([
                'item_id' => $item->id,
                'type' => 'income',
            ])->first();

            if (!is_null($account)) {
                $selected = $account->account_id;
            }
        }

        $options = Account::inType(Type::whereHas('declass', function ($query) {
            $query->where('name', 'double-entry::classes.income');
        })->pluck('id')->toArray())->enabled()->orderBy('code')->get()->transform(function ($item) {
            $item->name = $item->code . ' - ' . $item->trans_name;

            return $item;
        })->pluck('name', 'id');

        $accounts = new Accounts($name, $label, $options, $selected, group: false);

        $key = $accounts->data();

        $content = $accounts->render()->with($key);

        $view->getFactory()->startPush($section, $content);

        // arrangement for expense account
        $name = 'de_expense_account_id';
        $label = trans_choice('general.expenses', 1) . ' ' . trans_choice('general.accounts', 1);
        $selected = null;

        if ($request->routeIs('*items.edit')) {
            $item = $request->route(Str::singular((string) $request->segment(3)));

            $account = AccountItem::where([
                'item_id' => $item->id,
                'type' => 'expense',
            ])->first();

            if (!is_null($account)) {
                $selected = $account->account_id;
            }
        }

        $options = Account::inType(Type::whereHas('declass', function ($query) {
            $query->where('name', 'double-entry::classes.expenses');
        })->pluck('id')->toArray())->enabled()->orderBy('code')->get()->transform(function ($item) {
            $item->name = $item->code . ' - ' . $item->trans_name;

            return $item;
        })->pluck('name', 'id');

        $accounts = new Accounts($name, $label, $options, $selected, group: false);

        $key = $accounts->data();

        $content = $accounts->render()->with($key);

        $view->getFactory()->startPush($section, $content);
    }
}
