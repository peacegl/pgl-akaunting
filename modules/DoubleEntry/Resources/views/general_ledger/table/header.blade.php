<x-table.thead>
    <x-table.tr class="border-b border-purple border-bottom-1">
        <x-table.th class="ltr:text-left rtl:text-right text-xl text-purple font-bold pt-8 text-alignment-left" override="class" colspan="5">
            {{ $table_name }}
        </x-table.th>
    </x-table.tr>
    <x-table.tr class="relative flex items-center px-1 group border-b hover:bg-gray-100 text-alignment-left" override="class">
        <x-table.th class="w-10/12 ltr:text-left rtl:text-right text-black-400 font-bold py-2" override="class" colspan="4">
            {{ trans('accounts.opening_balance') }}
        </x-table.th>
        <x-table.th class="w-2/12 ltr:text-right rtl:text-left text-black-400 font-medium text-xs py-2 text-alignment-right" override="class">
            <x-money :amount="$class->opening_balances[$table_key]" :currency="default_currency()" convert />
        </x-table.th>
    </x-table.tr>
</x-table.thead>