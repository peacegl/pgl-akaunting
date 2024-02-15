<?php

namespace Modules\DoubleEntry\Listeners;

use Modules\DoubleEntry\Models\AccountItem;

class ItemCloned
{
    /**
     * Handle the event.
     *
     * @param  Event $event
     * @return void
     */
    public function handle($clone, $original)
    {
        $income_account_id = $original->de_income_account()->value('account_id');
        if ($income_account_id) {
            AccountItem::create([
                'company_id'    => company_id(), 
                'item_id'       => $clone->id, 
                'type'          => 'income',
                'account_id'    => $income_account_id
            ]);
        }

        $expense_account_id = $original->de_expense_account()->value('account_id');
        if ($expense_account_id) {
            AccountItem::create([
                'company_id'    => company_id(), 
                'item_id'       => $clone->id, 
                'type'          => 'expense',
                'account_id'    => $expense_account_id
            ]);
        }
    }
}
