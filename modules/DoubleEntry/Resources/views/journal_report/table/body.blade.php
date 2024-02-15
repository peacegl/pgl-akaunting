<tbody>
    @foreach($reference_document->ledgers as $ledger)
        @include($class->views['detail.table.row'])
    @endforeach
</tbody>
