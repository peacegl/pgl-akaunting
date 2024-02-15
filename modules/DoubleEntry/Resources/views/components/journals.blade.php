<x-show.accordion type="journals" :open="false">
    <x-slot name="head">
        <x-show.accordion.head
            :title="trans_choice('double-entry::general.journals', 2)"
            :description="trans('double-entry::general.journals_description')"
        />
    </x-slot>

    <x-slot name="body" class="block" override="class">
        <x-table>
            <x-table.thead>
                <x-table.tr>
                    <x-table.th class="w-2/4">
                        {{ trans_choice('general.accounts', 1) }}
                    </x-table.th>

                    <x-table.th class="w-1/4" kind="amount">
                        {{ trans_choice('double-entry::general.debits', 1) }}
                    </x-table.th>

                    <x-table.th class="w-1/4" kind="amount">
                        {{ trans_choice('double-entry::general.credits', 1) }}
                    </x-table.th>
                </x-table.tr>
            </x-table.thead>
            <x-table.tbody>
                @foreach($referenceDocument->ledgers as $ledger)
                    <x-table.tr>
                        <x-table.td class="w-2/4">
                            {{ $ledger->account->trans_name }}
                        </x-table.td>

                        <x-table.td class="w-1/4" kind="amount">
                            <x-money :amount="$ledger->debit ?? 0" :currency="$referenceDocument->currency_code" convert />
                        </x-table.td>

                        <x-table.td class="w-1/4" kind="amount">
                            <x-money :amount="$ledger->credit ?? 0" :currency="$referenceDocument->currency_code" convert />
                        </x-table.td>
                    </x-table.tr>
                @endforeach
            </x-table.tbody>
        </x-table>
    </x-slot>
</x-show.accordion>
