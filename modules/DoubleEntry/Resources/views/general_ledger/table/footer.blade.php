<tfoot>
    <x-table.tr>
        <x-table.td class="w-6/12 col-w-6 py-2 ltr:text-left rtl:text-right text-alignment-left text-black-400 font-bold" override="class" colspan="2">
            {{ trans('double-entry::general.totals_balance') }}
        </x-table.td>
        <x-table.td class="w-2/12 col-w-2 py-2 ltr:text-right rtl:text-left text-alignment-center text-black-400 font-medium text-xs" override="class">
            <x-money :amount="$class->footer_totals[$table_key]['debit']" :currency="default_currency()" convert />
        </x-table.td>
        <x-table.td class="w-2/12 col-w-2 py-2 ltr:text-right rtl:text-left text-alignment-center text-black-400 font-medium text-xs" override="class">
            <x-money :amount="$class->footer_totals[$table_key]['credit']" :currency="default_currency()" convert />
        </x-table.td>
        <x-table.td class="w-2/12 col-w-2 py-2 ltr:text-right rtl:text-left text-alignment-right text-black-400 font-medium text-xs" override="class">
            <x-money :amount="$class->footer_totals[$table_key]['balance']" :currency="default_currency()" convert />
        </x-table.td>
    </x-table.tr>
    <x-table.tr class="relative flex items-center px-1 group hover:bg-gray-100" override="class">
        <x-table.td class="w-10/12 col-w-10 pb-8 ltr:text-left rtl:text-right text-alignment-left text-black-400 font-bold" override="class" colspan="4">
            {{ trans('double-entry::general.balance_change') }}
        </x-table.td>
        <x-table.td class="w-2/12 col-w-2 pb-8 ltr:text-right rtl:text-left text-alignment-right text-black-400 font-medium text-xs" override="class">
            <x-money :amount="$class->footer_totals[$table_key]['balance_change']" :currency="default_currency()" convert />
        </x-table.td>
    </x-table.tr>
</tfoot>