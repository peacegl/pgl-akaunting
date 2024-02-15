@if($class->footer_totals[$table_key]['debit'] > 0 || $class->footer_totals[$table_key]['credit'] > 0)
    <x-table.thead>
        <x-table.tr class="border-b border-purple border-bottom-1">
            <x-table.th class="w-10/12 col-w-12 ltr:text-left rtl:text-right text-xl text-purple font-bold pt-4 text-alignment-left" override="class" colspan="{{ count($class->dates) + 2 }}">
                {{ $table_name }}
            </x-table.th>
            <x-table.th class="w-2/12" override="class">
            </x-table.th>
        </x-table.tr>
    </x-table.thead>
@endif
