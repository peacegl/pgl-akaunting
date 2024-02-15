<div class="table-responsive my-8">
    <table class="w-full">
        <x-table.thead>
            <x-table.tr>
                <x-table.th class="w-8/12 col-w-8 ltr:text-right rtl:text-left text-purple font-medium text-xs uppercase" override="class"></x-table.th>
                <x-table.th class="w-2/12 col-w-2 ltr:text-right rtl:text-left text-alignment-center text-purple font-medium text-xs uppercase" override="class">
                    {{ trans_choice('double-entry::general.debits', 1) }}
                </x-table.th>
                <x-table.th class="w-2/12 col-w-2 ltr:text-right rtl:text-left text-alignment-right text-purple font-medium text-xs uppercase" override="class">
                    {{ trans_choice('double-entry::general.credits', 1) }}
                </x-table.th>
            </x-table.tr>
        </x-table.thead>
    </table>
</div>
