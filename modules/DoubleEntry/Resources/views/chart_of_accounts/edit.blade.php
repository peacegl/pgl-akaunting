<x-layouts.admin>
    <x-slot name="title">
        {{ trans('general.title.edit', ['type' => trans_choice('general.accounts', 1)]) }}
    </x-slot>

    <x-slot name="content">
        <x-form.container>
            <x-form
                id="chart-of-account"
                method="PATCH"
                :route="['double-entry.chart-of-accounts.update', $account->id]"
                :model="$account">
                <x-form.section>
                    <x-slot name="head">
                        <x-form.section.head
                            title="{{ trans('general.general') }}"
                            description="{{ trans('double-entry::general.form_description.manual_journal.general') }}"
                        />
                    </x-slot>

                    <x-slot name="body">
                        <x-form.group.text name="name" label="{{ trans('general.name') }}" />

                        <x-form.group.text name="code" :label="trans('general.code')" />

                        <x-form.group.select
                            name="type_id"
                            :label="trans_choice('general.types', 1)"
                            :options="$types"
                            change="updateParentAccounts"
                            v-disabled="{{ in_array($account->type_id, [setting('double-entry.types_bank', 6), setting('double-entry.types_tax', 17)]) ? 'true' : 'false' }}"
                            group
                        />

                        <x-form.group.select
                            name="account_id"
                            :label="trans_choice('double-entry::general.parents', 1) . ' ' . trans_choice('general.accounts', 1)"
                            :options="$accounts[$account->type_id]"
                            dynamicOptions="accountsBasedTypes"
                            sort-options="false" 
                            group
                            not-required
                        />

                        <x-form.group.textarea
                            name="description"
                            :label="trans('general.description')"
                            not-required
                        />

                        <x-form.group.switch name="enabled" :label="trans('general.enabled')" />

                        <x-form.input.hidden name="accounts" value="{{ json_encode($accounts) }}" />

                        <x-form.input.hidden name="parent_account_id" :value="$account->account_id" />
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