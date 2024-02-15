<x-layouts.admin>
    <x-slot name="title">
        {{ trans('general.title.new', ['type' => trans_choice('general.accounts', 1)]) }}
    </x-slot>

    <x-slot name="content">
        <x-form.container>
            <x-form id="chart-of-account" method="POST" route="double-entry.chart-of-accounts.store">
                <x-form.section>
                    <x-slot name="head">
                        <x-form.section.head title="{{ trans('general.general') }}" description="{{ trans('double-entry::general.form_description.chart_of_accounts.general') }}" />
                    </x-slot>

                    <x-slot name="body">
                        <x-form.group.text name="name" :label="trans('general.name')" />

                        <x-form.group.text name="code" :label="trans('general.code')" />

                        <x-form.group.select
                            name="type_id"
                            :label="trans_choice('general.types', 1)"
                            :options="$types"
                            change="updateParentAccounts"
                            group
                        />

                        <x-form.group.select 
                            name="account_id" 
                            label="{{ trans_choice('double-entry::general.parents', 1) . ' ' . trans_choice('general.accounts', 1) }}"
                            dynamicOptions="accountsBasedTypes" 
                            sort-options="false" 
                            :options="[]"
                            group 
                            not-required 
                        />

                        <x-form.group.textarea
                            name="description"
                            :label="trans('general.description')"
                            not-required
                        />

                        <x-form.input.hidden name="accounts" value="{{ json_encode($accounts) }}" />
                        <x-form.input.hidden name="enabled" :value="true" />
                    </x-slot>
                </x-form.section>

                <x-form.section>
                    <x-slot name="foot">
                        <x-form.buttons :cancel="url()->previous()" />
                    </x-slot>
                </x-form.section>
            </x-form>
        </x-form.container>
    </x-slot>

    <x-script alias="double-entry" file="chart-of-accounts" />
</x-layouts.admin>