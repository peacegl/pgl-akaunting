<?php

namespace Modules\DoubleEntry\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ViewComposer extends ServiceProvider
{
    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function boot()
    {
        View::composer([
            'components.documents.form.line-item'
        ], 'Modules\DoubleEntry\Http\ViewComposers\DocumentItem');

        View::composer([
            'common.items.create',
            'inventory::items.create',
            'common.items.edit',
            'inventory::items.edit',
        ], 'Modules\DoubleEntry\Http\ViewComposers\Items');

        View::composer([
            'banking.transactions.create',
            'banking.transactions.edit',
            'modals.documents.payment',
        ], 'Modules\DoubleEntry\Http\ViewComposers\Transactions');

        View::composer([
            'components.transactions.show.content',
            'components.documents.show.content',
        ], 'Modules\DoubleEntry\Http\ViewComposers\Journals');

        // Integrated Apps
        View::composer([
            'receipt::receipt.edit'
        ], 'Modules\DoubleEntry\Http\ViewComposers\ReceiptInput');
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
