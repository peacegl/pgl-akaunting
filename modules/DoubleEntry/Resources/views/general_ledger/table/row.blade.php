<x-table.tr>
    <x-table.td class="w-2/12 col-w-2 py-2 text-left text-alignment-left text-black-400" override="class">
        <x-date date="{{ $row['issued_at'] }}" />
    </x-table.td>
    @if(!is_null($row['link']) && ! isset($print))
        <x-table.td class="w-4/12 col-w-4 py-2 text-left text-alignment-center text-black-400" override="class">
            <x-link href="{{ $row['link'] }}" class="text-sm sm:mt-0 sm:mb-0 leading-4" override="class">
                <x-link.hover color="to-black-400">
                    {{ $row['transaction'] }}
                </x-link.hover>
            </x-link>
        </x-table.td>
    @else
        <x-table.td class="w-4/12 col-w-4 py-2 text-left text-alignment-center text-black-400" override="class">
            {{ $row['transaction'] }}
        </x-table.td>
    @endif
    <x-table.td class="w-2/12 col-w-2 py-2 ltr:text-right text-alignment-center rtl:text-left text-black-400 text-xs" override="class">
        {{ !empty($row['debit']) ? money($row['debit'], setting('default.currency'), true) : '' }}
    </x-table.td>
    <x-table.td class="w-2/12 col-w-2 py-2 ltr:text-right text-alignment-center rtl:text-left text-black-400 text-xs" override="class">
        {{ !empty($row['credit']) ? money($row['credit'], setting('default.currency'), true) : '' }}
    </x-table.td>
    <x-table.td class="w-2/12 col-w-2 py-2 ltr:text-right rtl:text-left text-alignment-right text-black-400 text-xs" override="class">
        {{ money($row['balance'], setting('default.currency'), true) }}
    </x-table.td>
</x-table.tr>