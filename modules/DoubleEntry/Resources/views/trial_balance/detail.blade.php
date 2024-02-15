@include($class->views['detail.content.header'])

@if (!$class->no_records)
    @foreach($class->tables as $table_key => $table_name)
        @include($class->views['detail.table'])
    @endforeach
@else
    <div class="table-responsive my-8">
        {{ trans('general.no_records') }}
    </div>
@endif

@include($class->views['detail.content.footer'])
