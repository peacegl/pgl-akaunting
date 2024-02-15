<div class="table-responsive my-8">
    <table class="w-full rp-border-collapse">
        <thead>
            <tr>
                <th class="{{ $class->column_name_width }} col-w-8"></th>
                <th class="{{ $class->column_value_width }} col-w-2 ltr:text-right rtl:text-left text-alignment-center text-purple font-medium text-xs uppercase">
                    {{ trans_choice('double-entry::general.debits', 1) }}
                </th>
                <th class="{{ $class->column_value_width }} col-w-2 ltr:text-right rtl:text-left text-alignment-center text-purple font-medium text-xs uppercase">
                    {{ trans_choice('double-entry::general.credits', 1) }}
                </th>
            </tr>
        </thead>
    </table>
</div>
