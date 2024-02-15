<tbody data-collapse="class-{{ $de_class->id }}" class="active-collapse">
    @foreach($de_class->types as $type)
        @if (!empty($type->total))
            @include($class->views['detail.table.row'])
        @endif
    @endforeach
</tbody>
