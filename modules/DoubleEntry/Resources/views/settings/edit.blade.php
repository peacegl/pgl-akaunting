<x-layouts.admin>
    <x-slot name="title">{{ trans('double-entry::general.name') }}</x-slot>

    <x-slot name="content">
        <x-form.container>
            <x-form id="double-entry-setting" method="POST" route="double-entry.settings.update">
                <x-form.section>
                    <x-slot name="head">
                        <x-form.section.head title="{{ trans('double-entry::general.default_type', ['type' => trans_choice('double-entry::general.chart_of_accounts', 2)]) }}" description="" />
                    </x-slot>

                    <x-slot name="body">
                        <x-form.group.select group name="accounts_receivable" label="{{ trans('double-entry::general.accounts.receivable') }}" :options="$account_options" :selected="setting('double-entry.accounts_receivable')" />

                        <x-form.group.select group name="accounts_payable" label="{{ trans('double-entry::general.accounts.payable') }}" :options="$account_options" :selected="setting('double-entry.accounts_payable')" />

                        <x-form.group.select group name="accounts_sales" label="{{ trans('double-entry::general.accounts.sales') }}" :options="$account_options" :selected="setting('double-entry.accounts_sales')" />

                        <x-form.group.select group name="accounts_expenses" label="{{ trans('double-entry::general.accounts.expenses') }}" :options="$account_options" :selected="setting('double-entry.accounts_expenses')" />

                        <x-form.group.select group name="accounts_sales_discount" label="{{ trans('double-entry::general.accounts.sales_discount') }}" :options="$account_options" :selected="setting('double-entry.accounts_sales_discount')" />

                        <x-form.group.select group name="accounts_purchase_discount" label="{{ trans('double-entry::general.accounts.purchase_discount') }}" :options="$account_options" :selected="setting('double-entry.accounts_purchase_discount')" />

                        <x-form.group.select group name="accounts_owners_contribution" label="{{ trans('double-entry::general.accounts.owners_contribution') }}" :options="$account_options" :selected="setting('double-entry.accounts_owners_contribution')" />

                        @if ($is_payroll)
                            <x-form.group.select group name="accounts_payroll" label="{{ trans('double-entry::general.accounts.payroll') }}" :options="$account_options" :selected="setting('double-entry.accounts_payroll')" />
                        @endif
                    </x-slot>
                </x-form.section>

                <x-form.section>
                    <x-slot name="head">
                        <x-form.section.head title="{{ trans('double-entry::general.default_type', ['type' => trans_choice('general.types', 2)]) }}" description="" />
                    </x-slot>

                    <x-slot name="body">
                        <x-form.group.select group name="types_bank" label="{{ trans('double-entry::general.bank_cash') }}" :options="$type_options" :selected="setting('double-entry.types_bank', 6)" />

                        <x-form.group.select group name="types_tax" label="{{ trans_choice('general.taxes', 1) }}" :options="$type_options" :selected="setting('double-entry.types_tax', 17)" />
                    </x-slot>
                </x-form.section>

                <x-form.section>
                    <x-slot name="head">
                        <x-form.section.head title="{{ trans_choice('double-entry::general.manual_journals', 1) . ' ' . trans_choice('double-entry::general.entries', 1) }}" description="" />
                    </x-slot>

                    <x-slot name="body">
                        <x-form.group.text name="journal_number_prefix" label="{{ trans('settings.invoice.prefix') }}" :value="old('journal_number_prefix', setting('double-entry.journal.number_prefix', 'MJE-'))" />

                        <x-form.group.text name="journal_number_digit" label="{{ trans('settings.invoice.digit') }}" :value="old('journal_number_digit', setting('double-entry.journal.number_digit', '5'))" />

                        <x-form.group.text name="journal_number_next" label="{{ trans('settings.invoice.next') }}" :value="old('journal_number_next', setting('double-entry.journal.number_next', '1'))" />
                    </x-slot>
                </x-form.section>

                @can('update-double-entry-settings')
                    <x-form.section>
                        <x-slot name="foot">
                            <x-form.buttons :cancel="url()->previous()" />
                        </x-slot>
                    </x-form.section>
                @endcan
            </x-form>
        </x-form.container>
    </x-slot>

    <x-script alias="double-entry" file="double-entry-settings" />
</x-layouts.admin>
