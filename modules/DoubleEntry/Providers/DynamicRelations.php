<?php

namespace Modules\DoubleEntry\Providers;

use App\Models\Common\Item;
use App\Models\Document\Document;
use App\Models\Banking\Transaction;
use App\Models\Document\DocumentItem;
use App\Models\Banking\TransactionTax;
use App\Models\Document\DocumentTotal;
use Illuminate\Support\ServiceProvider;
use Modules\DoubleEntry\Models\Journal;
use App\Models\Document\DocumentItemTax;

class DynamicRelations extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        Document::resolveRelationUsing('de_ledger', function ($document) {
            if ($document instanceof \Modules\CreditDebitNotes\Models\CreditNote 
                || $document instanceof \Modules\CreditDebitNotes\Models\DebitNote
                ) {
                return $document->belongsTo('Modules\DoubleEntry\Models\Ledger', 'id', 'ledgerable_id')->where('ledgerable_type', 'App\Models\Document\Document');
            }

            return $document->morphOne('Modules\DoubleEntry\Models\Ledger', 'ledgerable');
        });

        DocumentItem::resolveRelationUsing('de_ledger', function ($documentItem) {
            return $documentItem->morphOne('Modules\DoubleEntry\Models\Ledger', 'ledgerable');
        });

        DocumentItemTax::resolveRelationUsing('de_ledger', function ($documentItemTax) {
            return $documentItemTax->morphOne('Modules\DoubleEntry\Models\Ledger', 'ledgerable');
        });

        DocumentTotal::resolveRelationUsing('de_ledger', function ($documentTotal) {
            return $documentTotal->morphOne('Modules\DoubleEntry\Models\Ledger', 'ledgerable');
        });

        Transaction::resolveRelationUsing('de_ledger', function ($transaction) {
            return $transaction->morphOne('Modules\DoubleEntry\Models\Ledger', 'ledgerable');
        });

        TransactionTax::resolveRelationUsing('de_ledger', function ($transaction) {
            return $transaction->morphOne('Modules\DoubleEntry\Models\Ledger', 'ledgerable');
        });

        Journal::resolveRelationUsing('de_ledger', function ($journal) {
            return $journal->morphOne('Modules\DoubleEntry\Models\Ledger', 'ledgerable');
        });

        Transaction::resolveRelationUsing('ledgers', function ($transaction) {
            return $transaction->morphMany('Modules\DoubleEntry\Models\Ledger', 'ledgerable');
        });

        TransactionTax::resolveRelationUsing('ledgers', function ($transactionTax) {
            return $transactionTax->morphMany('Modules\DoubleEntry\Models\Ledger', 'ledgerable');
        });

        Journal::resolveRelationUsing('ledgers', function ($journal) {
            return $journal->morphMany('Modules\DoubleEntry\Models\Ledger', 'ledgerable');
        });

        Item::resolveRelationUsing('de_income_account', function ($item) {
            return $item->belongsTo('Modules\DoubleEntry\Models\AccountItem', 'id', 'item_id')->where('type', 'income');
        });

        Item::resolveRelationUsing('de_expense_account', function ($item) {
            return $item->belongsTo('Modules\DoubleEntry\Models\AccountItem', 'id', 'item_id')->where('type', 'expense');
        });
    }

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }
}
