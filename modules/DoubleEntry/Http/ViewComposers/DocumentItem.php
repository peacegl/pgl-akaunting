<?php

namespace Modules\DoubleEntry\Http\ViewComposers;

use App\Traits\Modules;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Modules\DoubleEntry\Models\Account;
use Modules\DoubleEntry\Models\AccountItem;
use Modules\DoubleEntry\Models\Ledger;
use Modules\DoubleEntry\Models\Type;
use Modules\DoubleEntry\Traits\Permissions;

class DocumentItem
{
    use Modules, Permissions;

    /**
     * Bind data to the view.
     *
     * @param  View  $view
     * @return void
     */
    public function compose(View $document_item_view)
    {
        $alias = 'double-entry';

        if ($this->moduleIsDisabled($alias)) {
            return;
        }

        $request = request();

        if ($this->isNotValidDocumentType(Str::singular((string) $request->segment(3)))) {
            return;
        }

        $de_accounts = $item_accounts = $item_default_accounts = [];

        $document_type_class = 'double-entry::classes.income';
        $document_type_name = 'general.sales';

        if ($request->segment(2) == 'purchases') {
            $document_type_class = 'double-entry::classes.expenses';
            $document_type_name = 'general.purchases';
        }

        $types = Type::whereHas('declass', function ($query) use ($document_type_class) {
            $query->where('name', $document_type_class);
        })->pluck('id');

        if ($request->segment(2) == 'purchases') {
            $types = $types->merge(Type::where('name', 'double-entry::types.inventory')->pluck('id'));
        }

        if ($request->segment(2) == 'credit-debit-notes') {
            $types = Type::pluck('id');
        }

        Account::inType($types->toArray())
            ->with('type')
            ->enabled()
            ->orderBy('code')
            ->get()
            ->each(function ($account) use (&$de_accounts) {
                $de_accounts[trans($account->type->name)][$account->id] = $account->code . ' - ' . $account->trans_name;
            });

        ksort($de_accounts);

        if ($request->routeIs('invoices.edit') || $request->routeIs('bills.edit')) {
            $document = $request->route(Str::singular((string) $request->segment(3)));

            foreach ($document->items as $item) {
                $account_id = Ledger::record($item->id, 'App\Models\Document\DocumentItem')->value('account_id');

                if (empty($account_id)) {
                    continue;
                }

                $item_accounts[] = $account_id;
            }
        }

        $input_account_name = 'de_account_id';
        $input_account_text = trans_choice('general.accounts', 1);
        $input_account_selected = null;

        $type = 'income';

        if ($request->routeIs('bills.*') || $request->routeIs('credit-debit-notes.debit-notes.*')) {
            $type = 'expense';
        }

        $account_items = AccountItem::where('type', $type)->get();

        foreach ($account_items as $account_item) {
            $item_default_accounts[$account_item->item_id] = $account_item->account_id;
        }

        $section = 'item_custom_fields';
        $view = 'double-entry::partials.input_document_item';

        $data = compact(
            'de_accounts',
            'input_account_name',
            'input_account_text',
            'input_account_selected',
            'document_type_class',
            'document_type_name',
        );

        $content = view($view, $data);

        $document_item_view->getFactory()->startPush($section, $content);

        $section = 'scripts';
        $view = 'double-entry::partials.script';

        $data = compact('item_accounts', 'item_default_accounts');

        $content = view($view, $data);

        $document_item_view->getFactory()->startPush($section, $content);
    }
}
