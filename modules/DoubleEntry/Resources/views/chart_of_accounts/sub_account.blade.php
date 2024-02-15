@if ($sub_account->sub_accounts)
    @if ($loop->first)
        <x-table.tr
            href="{{ route('double-entry.chart-of-accounts.index') }}"
            class="relative flex items-center border-b hover:bg-gray-100 px-1 group transition-height collapse-sub"
            data-collapse="child-{{ $parent_account->id }}">
            <x-table.td class="w-1/12" hidden-mobile>
            </x-table.td>
            <x-table.td class="w-2/12" hidden-mobile style="padding-left: {{ $tree_level * 25 }}px;">
                <div class="flex items-center">
                    <span class="material-icons transform mr-1 text-lg leading-none">subdirectory_arrow_right</span>
                    {{ $parent_account->code }}
                </div>
            </x-table.td>
            <x-table.td class="w-6/12 sm:w-7/12" style="padding-left: {{ $tree_level * 25 }}px;">
                <x-slot name="first">
                    <x-link href="{{ route('reports.show', [$parent_account->general_ledger_report->id]) . '?search=de_account_id:' . $parent_account->id }}" class="text-sm sm:mt-0 sm:mb-0 leading-4" override="class">
                        <x-link.hover color="to-black-400">
                            {{ $parent_account->trans_name }}
                        </x-link.hover>
                    </x-link>

                    @if (! $parent_account->enabled)
                        <x-index.disable text="{{ trans_choice('double-entry::general.chart_of_accounts', 1) }}" />
                    @endif
                </x-slot>
            </x-table.td>
            <x-table.td class="w-6/12 sm:w-2/12" kind="amount">
                {!! $parent_account->balance_without_subaccounts_colorized !!}
            </x-table.td>
        </x-table.tr>
    @endif
    <x-table.tr
        href="{{ route('double-entry.chart-of-accounts.edit', $sub_account->id) }}"
        class="relative flex items-center border-b hover:bg-gray-100 px-1 group transition-height collapse-sub"
        data-collapse="child-{{ $parent_account->id }}">
        <x-table.td class="w-1/12" hidden-mobile>
            {{ Form::doubleEntryBulkActionGroup($sub_account->id, $sub_account->name, ['v-model' => 'bulk_action.selected_grouped[' . $sub_account->type->declass->id . ']', 'group' => $sub_account->type->declass->id]) }}
        </x-table.td>
        <x-table.td class="w-2/12" hidden-mobile style="padding-left: {{ $tree_level * 25 }}px;">
            <div class="flex items-center">
                @if($sub_account->sub_accounts->count() > 0)
                    <span class="material-icons transform mr-1 text-lg leading-none">subdirectory_arrow_right</span>
                    {{ $sub_account->code }}
                    <button type="button" class="w-4 h-4 flex items-center justify-center mx-2 leading-none align-text-top rounded-lg bg-gray-500 hover:bg-gray-700" node="child-{{ $sub_account->id }}" onClick="toggleSub('child-{{ $sub_account->id }}', event)">
                        <span class="material-icons transform transition-all text-lg leading-none align-middle text-white rotate-90">chevron_right</span>
                    </button>
                @else
                    <span class="material-icons transform mr-1 text-lg leading-none">subdirectory_arrow_right</span>
                    {{ $sub_account->code }}
                @endif
            </div>
        </x-table.td>
        <x-table.td class="w-6/12 sm:w-7/12" style="padding-left: {{ $tree_level * 25 }}px;">
            <x-link href="{{ route('reports.show', [$sub_account->general_ledger_report->id]) . '?search=de_account_id:' . $sub_account->id }}" class="text-sm sm:mt-0 sm:mb-0 leading-4" override="class">
                <x-link.hover color="to-black-400">
                    {{ $sub_account->trans_name }}
                </x-link.hover>
            </x-link>

            @if (! $sub_account->enabled)
                <x-index.disable text="{{ trans_choice('double-entry::general.chart_of_accounts', 1) }}" />
            @endif
        </x-table.td>
        <x-table.td class="w-6/12 sm:w-2/12" kind="amount">
            {!! $sub_account->balance_colorized !!}
        </x-table.td>

        <x-table.td kind="action">
            <x-table.actions :model="$sub_account" />
        </x-table.td>
    </x-table.tr>
    @php
        $parent_account = $sub_account;
        $tree_level++;
    @endphp
    @foreach($sub_account->sub_accounts as $sub_account)
        @php
            $sub_account->load(['type.declass', 'sub_accounts']);
        @endphp
        @include('double-entry::chart_of_accounts.sub_account', ['parent_account' => $parent_account, 'sub_account' => $sub_account, 'tree_level' => $tree_level])
    @endforeach
@endif