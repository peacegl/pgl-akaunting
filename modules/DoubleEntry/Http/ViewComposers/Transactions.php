<?php

namespace Modules\DoubleEntry\Http\ViewComposers;

use App\Models\Banking\Transaction;
use App\Traits\Modules;
use Illuminate\View\View;
use Modules\DoubleEntry\Models\Account;
use Modules\DoubleEntry\View\Components\Accounts;

class Transactions
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

        $section = 'category_id_input_start';

        $selected = null;

        if ($request->routeIs('transactions.edit') ||
            $request->routeIs('modals.documents.document.transactions.edit')) {
            $transaction = $request->route('transaction');

            $ledger = $transaction->de_ledger()
                ->where('entry_type', 'item')
                ->first();

            if (is_null($ledger)) {
                return;
            }

            if (!is_null($ledger)) {
                $selected = $ledger->account->id;
            }
        }

        $code = null;

        $is_income_transaction = $request->routeIs('transactions.create') && $request->input('type') === Transaction::INCOME_TYPE;

        if ($is_income_transaction) {
            $code = setting('double-entry.accounts_sales', 400);
        }

        $is_expense_transaction = $request->routeIs('transactions.create') && $request->input('type') === Transaction::EXPENSE_TYPE;

        if ($is_expense_transaction) {
            $code = setting('double-entry.accounts_expenses', 628);
        }

        $document = $request->route('document');

        $is_income = $request->routeIs('modals.documents.document.transactions.create') && $document && config('type.document.' . $document->type . '.transaction_type') === Transaction::INCOME_TYPE;

        if ($is_income) {
            $code = setting('double-entry.accounts_receivable', 120);
        }

        $is_expense = $request->routeIs('modals.documents.document.transactions.create') && $document && config('type.document.' . $document->type . '.transaction_type') === Transaction::EXPENSE_TYPE;

        if ($is_expense) {
            $code = setting('double-entry.accounts_payable', 200);
        }

        if ($code) {
            $account = Account::code($code)->first();

            if ($account) {
                $selected = $account->id;
            }
        }

        $formGroupClass = 'sm:col-span-3';

        if ($view->getName() === 'modals.documents.payment') {
            $formGroupClass = 'col-span-6';
        }

        $accounts = new Accounts(selected:$selected, formGroupClass:$formGroupClass);

        $key = $accounts->data();

        $content = $accounts->render()->with($key);

        $view->getFactory()->startPush($section, $content);
    }
}
