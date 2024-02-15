<x-table.thead>
    <x-table.tr class="border-b border-purple border-bottom-1">
        <x-table.th class="w-10/12 ltr:text-left rtl:text-right text-xl text-purple font-bold pt-8 text-alignment-left" override="class">
            <div class="flex items-center">
                {{ trans($de_class->name) }}

                @if (! isset($print))
                    <button type="button" class="flex items-center mt-1 leading-none align-text-top" onClick="toggleSub('class-{{ $de_class->id }}', event)">
                        <span class="material-icons transform transition-all text-lg leading-none">navigate_next</span>
                    </button> 
                @endif
            </div>
        </x-table.th>
        <x-table.th class="w-2/12 ltr:text-right rtl:text-left text-lg font-bold pt-8 text-alignment-right" override="class">
            <x-money :amount="$de_class->total" :currency="default_currency()" convert />
        </x-table.th>
    </x-table.tr>
</x-table.thead>