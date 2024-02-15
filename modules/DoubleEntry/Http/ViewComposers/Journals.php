<?php

namespace Modules\DoubleEntry\Http\ViewComposers;

use App\Traits\Modules;
use Illuminate\View\View;
use Modules\DoubleEntry\View\Components\Journals as Component;

class Journals
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

        $type = $view->getData()['type'];


        if ($type == 'journal') {
            return;
        }

        $mapping = [
            'income' => 'transaction',
            'expense' => 'transaction',
            'journal' => 'transaction',
            'credit_note_refund' => 'transaction',
            'debit_note_refund' => 'transaction',
            'invoice' => 'document',
            'bill' => 'document',
            'credit-note' => 'document',
            'debit-note' => 'document',
        ];

        if (!array_key_exists($type, $mapping)) {
            return;
        }

        $referenceDocument = $view->getData()[$mapping[$type]];

        if (!$referenceDocument->de_ledger) {
            return;
        }

        $journals = new Component($referenceDocument); 

        $section = 'get_paid_end';

        if ($type == 'bill') {
            $section = 'make_paid_end';
        }

        if ($mapping[$type] == 'transaction') {
            $section = 'row_create_end';
        }

        $view->getFactory()->startPush($section, $journals->render()->with($journals->data()));
    }
}
