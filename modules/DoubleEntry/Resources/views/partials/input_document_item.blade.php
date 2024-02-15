<el-popover
    popper-class="p-0 h-0"
    placement="bottom"
    width="300"
    trigger="click">
    <div class="bg-white rounded-lg shadow-lg p-4">
        <div class="items-center mb-2">
            {{ trans('double-entry::general.document.detail', ['class' => Str::lower(trans($document_type_class)), 'type' => Str::lower(trans_choice($document_type_name, 2))]) }}
        </div>
        <div class="items-center">
            <x-form.group.select
                :name="$input_account_name"
                :label="$input_account_text"
                icon="university"
                :options="$de_accounts"
                :selected="$input_account_selected"
                data-item='de_account_id'
                v-model='row.de_account_id'
                visible-change='onBindingItemField(index, "de_account_id")'
                model='this.item_accounts[index] !== undefined ? this.item_accounts[index].toString() : this.item_default_accounts[row.item_id] !== undefined ? this.item_default_accounts[row.item_id].toString() : ""'
                group
            />
        </div>
    </div>

    <x-button
        type="button"
        class="relative absolute -top-2 flex items-center text-right border-0 p-0 pr-4 text-xs text-purple"
        slot="reference"
        override="class"
    >
        <span class="border-b border-transparent transition-all hover:border-purple">
            {{ trans('double-entry::general.edit_account', ['type' => trans($document_type_class)]) }}
        </span>
    </x-button>
</el-popover>
