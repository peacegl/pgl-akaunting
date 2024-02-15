<x-layouts.admin>
    <x-slot name="title">
        {{ trans_choice('double-entry::general.manual_journals', 1) . ': ' . $journal_entry->journal_number }}
    </x-slot>

    <x-slot name="buttons">
        <x-transactions.show.buttons
            type="journal"
            :transaction="$journal_entry"
            text-button-add-new="{{ trans('general.title.new', ['type' => trans_choice('double-entry::general.manual_journals', 1)]) }}"
        />
    </x-slot>

    <x-slot name="moreButtons">
        <x-transactions.show.more-buttons
            type="journal"
            :transaction="$journal_entry"
            hide-divider-1
            hide-divider-2
            hide-divider-3
            hide-button-end
            hide-button-share
            hide-button-email
            hide-button-duplicate
            route-button-print="double-entry.journal-entry.print"
            route-button-pdf="double-entry.journal-entry.pdf"
            route-button-delete="double-entry.journal-entry.destroy"
            :text-delete-modal="trans_choice('double-entry::general.manual_journals', 1)"
        />
    </x-slot>

    <x-slot name="content">
        <x-transactions.show.content
            type="journal"
            :transaction="$journal_entry"
            hide-header-account
            hide-header-category
            hide-header-contact
            hide-account
            hide-category
            hide-contact
            hide-schedule
            hide-children
            hide-payment-methods
            hide-footer-histories
            text-header-account=""
            text-description="{{ trans('general.description') }}"
            text-content-title="{{ trans_choice('double-entry::general.manual_journals', 1) }}"
            transaction-template="double-entry::partials.journal_show_template"
        />
    </x-slot>

    @push('stylesheet')
        <link rel="stylesheet" href="{{ asset('public/css/print.css?v=' . version('short')) }}" type="text/css">
    @endpush

    <x-script alias="double-entry" file="journal-entries" />
</x-layouts.admin>
