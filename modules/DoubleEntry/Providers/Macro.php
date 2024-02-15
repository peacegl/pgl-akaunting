<?php

namespace Modules\DoubleEntry\Providers;

use App\Models\Banking\Transaction;
use App\Models\Banking\TransactionTax;
use App\Models\Document\Document;
use App\Models\Document\DocumentItem;
use App\Models\Document\DocumentItemTax;
use App\Models\Document\DocumentTotal;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\ServiceProvider as Provider;
use Modules\DoubleEntry\Models\Journal;

class Macro extends Provider
{
    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function boot()
    {
        Collection::macro('asEntries', function () {
            $entries = new Collection();

            foreach ($this as $ledger) {
                $ledgerable = $ledger->ledgerable;

                if ($ledgerable instanceof Journal) {
                    $prefix = 'journal';

                    $value = $ledgerable->ledgers;
                }

                if ($ledgerable instanceof Transaction ||
                    $ledgerable instanceof TransactionTax) {
                    $prefix = 'transaction';

                    $value = $ledgerable->ledgers;
                }

                if ($ledgerable instanceof DocumentItem ||
                    $ledgerable instanceof DocumentItemTax ||
                    $ledgerable instanceof DocumentTotal) {
                    $ledgerable = $ledgerable->document;
                }

                if ($ledgerable instanceof Document) {
                    $prefix = 'document';

                    $value = collect([$ledgerable->de_ledger]);

                    foreach ($ledgerable->items as $item) {
                        if ($ledger = $item->de_ledger) {
                            $value->push($ledger);
                        }
                    }

                    foreach ($ledgerable->item_taxes as $item_tax) {
                        if ($ledger = $item_tax->de_ledger) {
                            $value->push($ledger);
                        }
                    }

                    foreach ($ledgerable->totals as $total) {
                        if ($ledger = $total->de_ledger) {
                            $value->push($ledger);
                        }
                    }
                }

                $key = "{$prefix}_{$ledgerable->id}";

                if ($entries->has($key)) {
                    continue;
                }

                $entries->put($key, $value);
            }

            return $entries;
        });
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
