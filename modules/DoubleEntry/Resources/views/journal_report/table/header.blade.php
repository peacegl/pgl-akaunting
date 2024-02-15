<x-table.thead>
    <x-table.tr class="border-b border-purple border-bottom-1">
        <x-table.th class="w-8/12 ltr:text-left rtl:text-right text-alignment-left text-xl text-purple font-semibold" override="class" colspan="3">
            {{ $reference_document->date }} 

            @if (! isset($print))
                <x-link href="{{ $reference_document->link }}" class="text-sm sm:mt-0 sm:mb-0 leading-4" override="class">
                    <x-link.hover color="to-black-400">
                        {{ $reference_document->transaction }}
                    </x-link.hover>
                </x-link>
            @else
                {{ $reference_document->transaction }}
            @endif
        </x-table.th>
    </x-table.tr>
</x-table.thead>