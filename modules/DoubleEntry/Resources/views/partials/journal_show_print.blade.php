<x-layouts.print>
    <x-slot name="title">
        {{ trans_choice('double-entry::general.manual_journals', 1) }}
    </x-slot>

    <x-slot name="content">
        <x-transactions.show.content
            type="journal"
            :transaction="$journal_entry"
            hide-created
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
</x-layout-print>
