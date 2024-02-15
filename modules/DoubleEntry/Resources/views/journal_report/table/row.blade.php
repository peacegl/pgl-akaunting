<x-table.tr class=" " override="class">
    <x-table.td class="w-8/12 col-w-8 ltr:text-left rtl:text-right text-alignment-left text-black-400" override="class">
        @if (! isset($print) && $ledger->account->general_ledger_report)
            <x-link href="{{ route('reports.show', [$ledger->account->general_ledger_report->id]) . '?search=de_account_id:' . $ledger->account->id }}" class="text-sm sm:mt-0 sm:mb-0 leading-4" override="class">
                <x-link.hover color="to-black-400">
                    {{ $ledger->account->trans_name }}
                </x-link.hover>
            </x-link>
        @else
            {{ $ledger->account->trans_name }}
        @endif
    </x-table.td>
    <x-table.td class="w-2/12 col-w-2 ltr:text-right rtl:text-left text-alignment-center text-black-400 text-xs" override="class">
        <x-money :amount="(double) $ledger->debit" :currency="default_currency()" convert />
    </x-table.td>
    <x-table.td class="w-2/12 col-w-2 ltr:text-right rtl:text-left text-alignment-right text-black-400 text-xs" override="class">
        <x-money :amount="(double) $ledger->credit" :currency="default_currency()" convert />
    </x-table.td>
</x-table.tr>