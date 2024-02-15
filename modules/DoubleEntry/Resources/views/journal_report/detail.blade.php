@include($class->views['detail.content.header'])

@foreach($class->reference_documents as $reference_document)
    @include($class->views['detail.table'])
@endforeach

@include($class->views['detail.content.footer'])
