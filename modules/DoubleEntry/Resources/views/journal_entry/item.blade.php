<x-table.tr 
    class="relative flex items-center px-1 group/actions border-b"
    v-for="(row, index) in form.items"
    v-bind:index="index">
    <x-table.td class="w-3/12">
        <x-form.group.select
            name="account_id"
            :options="$accounts"
            v-model="row.account_id = $event"
            model="row.account_id"
            group />
    </x-table.td>

    <x-table.td class="w-4/12">
        <x-form.group.text
            name="note"
            v-model="row.notes" />
    </x-table.td>

    <x-table.td class="w-2/12">
        <x-form.group.money
            name="debit"
            v-disabled="row.has_credit"
            value="0"
            v-model="row.debit"
            :currency="$currency"
            change="row.debit = $event; onCalculateJournal"
            v-error="form.errors.get('items.' + index + '.debit')" />
    </x-table.td>

    <x-table.td class="w-2/12">
        <x-form.group.money
            name="credit"
            v-disabled="row.has_debit"
            value="0"
            v-model="row.credit"
            :currency="$currency"
            change="row.credit = $event; onCalculateJournal"
            v-error="form.errors.get('items.' + index + '.credit')" />
    </x-table.td>

    <x-table.td class="w-1/12 none-truncate">
        <x-button
            type="button"
            v-on:click="onDeleteItem(index)"
            :title="trans('general.delete')"
            class="px-3 py-1.5 mb-3 sm:mt-2 sm:mb-0 rounded-xl text-sm font-medium leading-6 hover:bg-gray-200 disabled:bg-gray-50"
            override="class"
        >
            <span class="w-full material-icons-outlined text-lg text-gray-300 group-hover:text-gray-500">delete</span>
        </x-button>
    </x-table.td>
</x-table.tr>
