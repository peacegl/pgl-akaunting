<x-table.tbody>
    @foreach($class->row_values[$table_key] as $row)
        @include($class->views['detail.table.row'])
    @endforeach
</x-table.tbody>
