<x-layouts.admin>
    <x-slot name="title">{{ trans_choice('double-entry::general.chart_of_accounts', 2) }}</x-slot>

    <x-slot name="favorite"
        title="{{ trans_choice('double-entry::general.chart_of_accounts', 2) }}"
        icon="balance"
        route="double-entry.chart-of-accounts.index"
    ></x-slot>

    <x-slot name="buttons">
        @can('create-double-entry-chart-of-accounts')
            <x-link href="{{ route('double-entry.chart-of-accounts.create') }}" kind="primary">
                {{ trans('general.title.new', ['type' => trans_choice('double-entry::general.chart_of_accounts', 1)]) }}
            </x-link>
        @endcan
    </x-slot>

    <x-slot name="moreButtons">
        <x-dropdown id="dropdown-more-actions">
            <x-slot name="trigger">
                <span class="material-icons pointer-events-none">more_horiz</span>
            </x-slot>

            @can('create-double-entry-chart-of-accounts')
                <x-dropdown.link href="{{ route('import.create', ['double-entry', 'chart-of-accounts']) }}">
                    {{ trans('import.import') }}
                </x-dropdown.link>
            @endcan

            <x-dropdown.link href="{{ route('double-entry.chart-of-accounts.export', request()->input()) }}">
                {{ trans('general.export') }}
            </x-dropdown.link>
        </x-dropdown>
    </x-slot>

    <x-slot name="content">
        <x-index.container>
            <x-index.search
                search-string="Modules\DoubleEntry\Models\Account"
                bulk-action="Modules\DoubleEntry\BulkActions\ChartOfAccounts"
            />

            @foreach($classes as $key => $class)
                @php
                    $accordion_class = 'border-t pb-4 pt-4';

                    if ($key == 0) {
                        $accordion_class = 'pb-4 pt-4';
                    }
                @endphp
                <x-show.accordion type="{{ trans($class->name) }}" :class="$accordion_class" override="class" open >
                    <x-slot name="head">
                        <x-show.accordion.head title="{{ trans($class->name) }}" />
                    </x-slot>
                
                    <x-slot name="body">
                        <x-table>
                            <x-table.thead>
                                <x-table.tr>
                                    <x-table.th class="w-1/12" hidden-mobile>
                                        {{ Form::doubleEntryBulkActionAllGroup(['v-model' => 'bulk_action.select_all[' . $class->id . ']', 'group' => $class->id]) }}
                                    </x-table.th>
                                    <x-table.th class="w-2/12" hidden-mobile>{{ trans('general.code') }}</x-table.th>
                                    <x-table.th class="w-6/12 sm:w-4/12">{{ trans('general.name') }}</x-table.th>
                                    <x-table.th class="w-3/12" hidden-mobile>{{ trans_choice('general.types', 1) }}</x-table.th>
                                    <x-table.th class="w-6/12 sm:w-2/12" kind="amount">{{ trans('general.balance') }}</x-table.th>
                                </x-table.tr>
                            </x-table.thead>
                            <x-table.tbody>
                                @foreach($class->accounts as $account)
                                    <x-table.tr href="{{ route('double-entry.chart-of-accounts.edit', $account->id) }}">
                                        <x-table.td class="w-1/12 ltr:pr-6 rtl:pl-6" hidden-mobile>
                                            {{ Form::doubleEntryBulkActionGroup($account->id, $account->name, ['v-model' => 'bulk_action.selected_grouped[' . $account->declass->id . ']', 'group' => $account->declass->id]) }}
                                        </x-table.td>
                                        <x-table.td class="w-2/12" hidden-mobile>
                                            @if($account->sub_accounts->count() > 0)
                                                <div class="flex items-center">
                                                    {{ $account->code }}
            
                                                    <button type="button" class="w-4 h-4 flex items-center justify-center mx-2 leading-none align-text-top rounded-lg bg-gray-500 hover:bg-gray-700" node="child-{{ $account->id }}" onClick="toggleSub('child-{{ $account->id }}', event)">
                                                        <span class="material-icons transform transition-all text-lg leading-none align-middle text-white rotate-90">chevron_right</span>
                                                    </button>
                                                </div>
                                            @else
                                                {{ $account->code }}
                                            @endif
                                        </x-table.td>
                                        <x-table.td class="w-6/12 sm:w-4/12">
                                            <x-slot name="first">
                                                <x-link href="{{ route('reports.show', [$account->general_ledger_report->id]) . '?search=de_account_id:' . $account->id }}" class="text-sm font-semibold sm:mt-0 sm:mb-0 leading-4" override="class">
                                                    <x-link.hover color="to-black-400">
                                                        {{ $account->trans_name }}
                                                    </x-link.hover>
                                                </x-link>
    
                                                @if (! $account->enabled)
                                                    <x-index.disable text="{{ trans_choice('double-entry::general.chart_of_accounts', 1) }}" />
                                                @endif
                                            </x-slot>
                                        </x-table.td>
                                        <x-table.td class="w-3/12" hidden-mobile>{{ trans($account->type->name) }}</x-table.td>
                                        <x-table.td class="w-6/12 sm:w-2/12" kind="amount">
                                            {!! $account->balance_colorized !!}
                                        </x-table.td>
                                        <x-table.td kind="action">
                                            <x-table.actions :model="$account" />
                                        </x-table.td>
                                    </x-table.tr>
                                    @foreach($account->sub_accounts as $sub_account)
                                        @php
                                            $sub_account->load(['type.declass', 'sub_accounts']);
                                        @endphp
            
                                        @include('double-entry::chart_of_accounts.sub_account', ['parent_account' => $account, 'sub_account' => $sub_account, 'tree_level' => 1])
                                    @endforeach
                                @endforeach
                            </x-table.tbody>
                        </x-table>
                    </x-slot>
                </x-show.accordion>
            @endforeach
        </x-index.container>
    </x-slot>

    <x-script alias="double-entry" file="chart-of-accounts" />
</x-layouts.admin>