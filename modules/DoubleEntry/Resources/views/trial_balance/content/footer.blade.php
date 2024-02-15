<div class="table-responsive my-8">
    <table class="w-full rp-border-collapse">
        <thead>
            <tr>
                <th class="{{ $class->column_name_width }} col-w-8 text-right text-alignment-right font-medium text-lg">
                    {{ trans_choice('general.totals', 1) }}
                </th>
                <th class="{{ $class->column_value_width }} col-w-2 ltr:text-right rtl:text-left text-alignment-center text-purple font-medium text-lg uppercase">
                    <x-money :amount="$class->content_footer_total['debit']" :currency="default_currency()" convert />
                </th>
                <th class="{{ $class->column_value_width }} col-w-2 ltr:text-right rtl:text-left text-alignment-center text-purple font-medium text-lg uppercase">
                    <x-money :amount="$class->content_footer_total['credit']" :currency="default_currency()" convert />
                </th>
            </tr>
        </thead>
    </table>
</div>
