@include($class->views['detail.content.header'])

@foreach($class->de_classes as $de_class)
    @include($class->views['detail.table'])
@endforeach

@include($class->views['detail.content.footer'])
