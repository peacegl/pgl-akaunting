<x-layouts.admin>
    <x-slot name="title">{{ trans_choice('double-entry::general.manual_journals', 2) }}</x-slot>

    <x-slot name="favorite"
        title="{{ trans_choice('double-entry::general.manual_journals', 2) }}"
        icon="balance"
        route="double-entry.journal-entry.index"
    ></x-slot>

    <x-slot name="buttons">
        @can('create-double-entry-journal-entry')
            <x-link href="{{ route('double-entry.journal-entry.create') }}" kind="primary">
                {{ trans('general.title.new', ['type' => trans_choice('double-entry::general.manual_journals', 1)]) }}
            </x-link>
        @endcan
    </x-slot>

    <x-slot name="moreButtons">
        <x-dropdown id="dropdown-more-actions">
            <x-slot name="trigger">
                <span class="material-icons pointer-events-none">more_horiz</span>
            </x-slot>

            @can('create-double-entry-journal-entry')
                <x-dropdown.link href="{{ route('import.create', ['double-entry', 'journal-entry']) }}">
                    {{ trans('import.import') }}
                </x-dropdown.link>
            @endcan

            <x-dropdown.link href="{{ route('double-entry.journal-entry.export', request()->input()) }}">
                {{ trans('general.export') }}
            </x-dropdown.link>
        </x-dropdown>
    </x-slot>

    <x-slot name="content">
        @if ($journals->count() || request()->get('search', false))
            <x-index.container>
                <x-index.search
                    search-string="Modules\DoubleEntry\Models\Journal"
                    bulk-action="Modules\DoubleEntry\BulkActions\JournalEntry"
                />

                <x-table>
                    <x-table.thead>
                        <x-table.tr>
                            <x-table.th kind="bulkaction">
                                <x-index.bulkaction.all />
                            </x-table.th>

                            <x-table.th class="w-6/12 sm:w-3/12">
                                <x-slot name="first">
                                    <x-sortablelink column="paid_at" title="{{ trans('general.date') }}" />
                                </x-slot>
                                <x-slot name="second">
                                    {{ trans_choice('general.numbers', 1) }}
                                </x-slot>
                            </x-table.th>

                            <x-table.th class="w-4/12" hidden-mobile>
                                {{ trans('general.description') }}
                            </x-table.th>

                            <x-table.th class="w-3/12" hidden-mobile>
                                {{ trans('general.reference') }}
                            </x-table.th>

                            <x-table.th class="w-6/12 sm:w-2/12" kind="amount">
                                <x-sortablelink column="amount" title="{{ trans('general.amount') }}" />
                            </x-table.th>
                        </x-table.tr>
                    </x-table.thead>

                    <x-table.tbody>
                        @foreach($journals as $item)
                            <x-table.tr href="{{ route('double-entry.journal-entry.show', $item->id) }}">
                                <x-table.td kind="bulkaction">
                                    <x-index.bulkaction.single id="{{ $item->id }}" name="journal_{{ $item->id }}" />
                                </x-table.td>

                                <x-table.td class="w-6/12 sm:w-3/12">
                                    <x-slot name="first" class="font-bold truncate" override="class">
                                        <x-date date="{{ $item->paid_at }}" />
                                    </x-slot>
                                    <x-slot name="second">
                                        <x-link 
                                            href="{{ route('double-entry.journal-entry.show', $item->id) }}" 
                                            class="text-sm sm:mt-0 sm:mb-0 leading-4" 
                                            override="class"
                                        >
                                            <x-link.hover color="to-black-400">
                                                {{ $item->journal_number }}
                                            </x-link.hover>
                                        </x-link>
                                    </x-slot>
                                </x-table.td>
                                
                                <x-table.td class="w-4/12 truncate" hidden-mobile>
                                    {{ $item->description }}
                                </x-table.td>
                                
                                <x-table.td class="w-3/12 relative" hidden-mobile>
                                    <div class="{{ empty($item->reference) ? 'mt-4' : '' }}">
                                        {{ $item->reference }}
                                    </div>
                                </x-table.td>
                                
                                <x-table.td class="w-6/12 sm:w-2/12" kind="amount">
                                    <x-money :amount="$item->amount" :currency="$item->currency_code" convert />
                                </x-table.td>

                                <x-table.td kind="action">
                                    <x-table.actions :model="$item" />
                                </x-table.td>
                            </x-table.tr>
                        @endforeach
                    </x-table.tbody>
                </x-table>

                <x-pagination :items="$journals" />
            </x-index.container>
        @else
            <x-empty-page
                group="double-entry"
                page="journal-entry"
                image-empty-page="{{ asset('modules/DoubleEntry/Resources/assets/img/manual-journal.png') }}"
                :title="trans_choice('double-entry::general.manual_journals', 2)"
                :create-button-title="trans_choice('double-entry::general.manual_journals', 1)"
                :import-button-title="trans_choice('double-entry::general.manual_journals', 1)"
                url-docs-path="https://akaunting.com/docs/app-manual/accounting/double-entry"
                permission-create="create-double-entry-journal-entry"
            />
        @endif
    </x-slot>

    <x-script alias="double-entry" file="journal-entries" />
</x-layouts.admin>
