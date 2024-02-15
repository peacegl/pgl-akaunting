<x-layouts.admin>
    <x-slot name="title">
        {{ trans('general.title.new', ['type' => trans_choice('double-entry::general.manual_journals', 1)]) }}
    </x-slot>

    <x-slot name="favorite"
        title="{{ trans('general.title.new', ['type' => trans_choice('double-entry::general.manual_journals', 1)]) }}"
        icon="balance"
        route="double-entry.journal-entry.create"
    ></x-slot>

    <x-slot name="content">
        <x-form.container>
            <x-form
                id="journal-entry"
                route="double-entry.journal-entry.store">
                <x-form.section>
                    <x-slot name="head">
                        <x-form.section.head title="{{ trans('general.general') }}" description="{{ trans('double-entry::general.form_description.manual_journal.general') }}" />
                    </x-slot>

                    <x-slot name="body">
                        <x-form.group.date
                            name="paid_at"
                            :label="trans('general.date')"
                            :show-date-format="company_date_format()"
                            date-format="Y-m-d"
                            :value="Date::now()->toDateString()"
                        />

                        <x-form.group.select
                            name="currency_code"
                            :label="trans_choice('general.currencies', 1)"
                            :options="$currencies"
                            :selected="$currency->code"
                            change="onChangeCurrency" />

                        <x-form.input.hidden name="currency_rate" :value="$currency->rate" />

                        <x-form.group.textarea name="description" :label="trans('general.description')" />
                    </x-slot>
                </x-form.section>

                <x-form.section>
                    <x-slot name="head">
                        <x-form.section.head title="{{ trans_choice('double-entry::general.lines', 2) }}" description="{{ trans('double-entry::general.form_description.manual_journal.lines') }}" />
                    </x-slot>
                    <x-slot name="body">
                        <div class="sm:col-span-6 overflow-x-scroll large-overflow-unset">
                            <div class="small-table-width">
                                <x-table class="flex flex-col divide-y divide-gray-200">
                                    <x-table.thead>
                                        <x-table.tr>
                                            <x-table.th class="w-3/12">
                                                {{ trans_choice('general.accounts', 1) }}
                                            </x-table.th>
                
                                            <x-table.th class="w-4/12">
                                                {{ trans_choice('general.notes', 1) }}
                                            </x-table.th>
                
                                            <x-table.th class="w-2/12">
                                                {{ trans_choice('double-entry::general.debits', 1) }}
                                            </x-table.th>
                
                                            <x-table.th class="w-2/12">
                                                {{ trans_choice('double-entry::general.credits', 1) }}
                                            </x-table.th>
                
                                            <x-table.th class="w-1/12"></x-table.th>
                                        </x-table.tr>
                                    </x-table.thead>
                
                                    <x-table.tbody>
                                        @include('double-entry::journal_entry.item')
                
                                        <x-table.tr class="flex">
                                            <x-table.td class="w-full" override="class">
                                                <x-button
                                                    type="button"
                                                    class="w-full h-10 flex items-center justify-center border-b text-purple font-medium disabled:bg-gray-200 hover:bg-gray-100"
                                                    override="class"
                                                    v-on:click="onAddItem">
                                                    <span class="material-icons-outlined text-base font-bold ltr:mr-1 rtl:ml-1">add</span>
                                                    {{ trans('general.form.add', ['field' => trans_choice('double-entry::general.lines', 1)]) }}
                                                </x-button>
                                            </x-table.td>
                                        </x-table.tr>
                
                                        <x-table.tr class="flex items-center mt-2">
                                            <x-table.td class="w-8/12 ltr:text-right rtl:text-left">
                                                {{ trans('invoices.sub_total') }}
                                            </x-table.td>
                
                                            <x-table.td class="w-2/12 ltr:text-right rtl:text-left truncate">
                                                <x-form.group.money
                                                    name="sub_total_debit"
                                                    value="0.00"
                                                    v-model="sub_total.debit_formatted"
                                                    form-group-class="hidden"
                                                    :currency="$currency"
                                                    disabled
                                                    masked />
                                                <span v-html="sub_total.debit_formatted"></span>
                                            </x-table.td>
                
                                            <x-table.td class="w-2/12 ltr:text-right rtl:text-left truncate">
                                                <x-form.group.money
                                                    name="sub_total_credit"
                                                    value="0.00"
                                                    v-model="sub_total.credit_formatted"
                                                    form-group-class="hidden"
                                                    :currency="$currency"
                                                    disabled
                                                    masked />
                                                <span v-html="sub_total.credit_formatted"></span>
                                            </x-table.td>
                                        </x-table.tr>
                
                                        <x-table.tr class="flex items-center mt-2">
                                            <x-table.td class="w-8/12 ltr:text-right rtl:text-left">
                                                {{ trans('invoices.total') }}
                                            </x-table.td>
                
                                            <x-table.td class="w-2/12 ltr:text-right rtl:text-left truncate" v-bind:style="{ background: color.debit}">
                                                <x-form.group.money
                                                    name="grand_total_debit"
                                                    value="0.00"
                                                    v-model="grand_total.debit_formatted"
                                                    form-group-class="hidden"
                                                    :currency="$currency"
                                                    disabled
                                                    masked />
                                                <span v-html="grand_total.debit_formatted"></span>
                                            </x-table.td>
                
                                            <x-table.td class="w-2/12 ltr:text-right rtl:text-left truncate" v-bind:style="{ background: color.credit}">
                                                <x-form.group.money
                                                    name="grand_total_credit"
                                                    value="0.00"
                                                    v-model="grand_total.credit_formatted"
                                                    form-group-class="hidden"
                                                    :currency="$currency"
                                                    disabled
                                                    masked />
                                                <span v-html="grand_total.credit_formatted"></span>
                                            </x-table.td>
                                        </x-table.tr>
                                    </x-table.tbody>
                                </x-table>
                            </div>
                        </div>
                    </x-slot>
                </x-form.section>

                <x-form.section>
                    <x-slot name="head">
                        <x-form.section.head title="{{ trans_choice('general.others', 1) }}" description="{{ trans('double-entry::general.form_description.manual_journal.other') }}" />
                    </x-slot>

                    <x-slot name="body">
                        <x-form.group.text name="journal_number" :label="trans_choice('general.numbers', 1)" :value="$journal_number" />

                        <x-form.group.select name="basis" :label="trans('general.basis')" :options="$basis" :selected="trans('general.accrual')" />

                        <x-form.group.text name="reference" :label="trans('general.reference')" not-required />

                        <x-form.group.attachment />
                    </x-slot>
                </x-form.section>

                <x-form.section>
                    <x-slot name="foot">
                        <div class="sm:col-span-6 flex items-center justify-end mt-8">
                            <x-form.buttons cancel-route="double-entry.journal-entry.index" save-disabled="form.loading || journal_button" />
                        </div>
                    </x-slot>
                </x-form.section>

                <x-form.input.hidden name="currency_code" :value="$currency->code" />
            </x-form>
        </x-form.container>
    </x-slot>

    <x-script alias="double-entry" file="journal-entries" />
</x-layouts.admin>
