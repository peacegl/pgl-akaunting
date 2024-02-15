<?php

namespace Modules\DoubleEntry\Http\ViewComposers;

use App\Models\Banking\Transaction;
use App\Traits\Modules;
use Illuminate\View\View;
use Modules\DoubleEntry\Models\Account;
use Modules\DoubleEntry\Models\Type;
use Modules\DoubleEntry\View\Components\Accounts;

class ReceiptInput
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
        if ($this->moduleIsDisabled('double-entry') || $this->moduleIsDisabled('receipt')) {
            return;
        }

        $request = request();

        $section = 'tax_amount_input_end';

        $selected = null;

        $options = [];

        $receipt = $request->route('receipt');

        $transaction = Transaction::find($receipt->payment_id);

        if (!is_null($transaction)) {
            $ledger = $transaction->de_ledger()
                ->where('entry_type', 'item')
                ->first();

            if (!is_null($ledger)) {
                $selected = $ledger->account->id;
            }
        }

        $types = Type::whereHas('declass', function ($query) {
            $query->where('name', 'double-entry::classes.expenses');
        })
            ->pluck('id')
            ->toArray();

        Account::inType($types)
            ->with('type')
            ->enabled()
            ->orderBy('code')
            ->get()
            ->each(function ($account) use (&$options) {
                $options[trans($account->type->name)][$account->id] = $account->code . ' - ' . $account->trans_name;
            });

        ksort($options);

        $accounts = new Accounts(selected:$selected, options:$options);

        $key = $accounts->data();

        $content = $accounts->render()->with($key);

        $view->getFactory()->startPush($section, $content);
    }
}
