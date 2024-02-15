<!-- if it HAS NOT subcategories -->
@php
    $name = $class->row_names[$table_key][$id]['name'];
    $name = is_array(trans($name)) ? $name : trans($name)
@endphp
@if (is_null($node))
    @php
        $rows = $class->row_values[$table_key][$id];
    @endphp
    
    @if ($row_total = array_sum($rows))
        @if (isset($parent_id))
            <tr class="collapse-sub" data-collapse="child-{{ $parent_id }}">
                <td class="{{ $class->column_name_width }} col-w-8 py-2 text-left text-alignment-left text-black-400 pl-{{ $tree_level * 10 }} print-report-padding">
                    @if (! isset($print) && $class->row_names[$table_key][$id]['route'])
                        <x-link href="{{ $class->row_names[$table_key][$id]['route'] }}" class="text-sm sm:mt-0 sm:mb-0 leading-4" override="class">
                            <x-link.hover color="to-black-400">
                                {{ $name }}
                            </x-link.hover>
                        </x-link>
                    @else
                        {{ $name }}
                    @endif
                </td>
        @else
            <tr>
                <td class="{{ $class->column_name_width }} col-w-8 py-2 text-left text-alignment-left text-black-400">
                    @if (! isset($print) && $class->row_names[$table_key][$id]['route'])
                        <x-link href="{{ $class->row_names[$table_key][$id]['route'] }}" class="text-sm sm:mt-0 sm:mb-0 leading-4" override="class">
                            <x-link.hover color="to-black-400">
                                {{ $name }}
                            </x-link.hover>
                        </x-link>
                    @else
                        {{ $name }}
                    @endif
                </td>
        @endif

        @foreach($rows as $row)
            <td class="{{ $class->column_value_width }} col-w-2 py-2 ltr:text-right rtl:text-left text-alignment-center text-black-400 text-xs">{{ $class->has_money ? money($row, setting('default.currency'), true) : $row }}</td>
        @endforeach
    </tr>
    @endif
@endif

<!-- if it HAS subcategories -->
@if (is_array($node))
    <!-- parent part -->
    @php
        $parent_row_values = $class->row_values[$table_key][$id];

        array_walk_recursive($node, function ($value, $key) use ($class, $table_key, $id, &$parent_row_values) {
            if ($key == $id) {
                return;
            }

            foreach($class->row_values[$table_key][$key] as $date => $amount) {
                $parent_row_values[$date] += $amount;
            }
        });
    @endphp

    @if ($row_total = array_sum($parent_row_values))
        @if (isset($parent_id))
            <tr class="collapse-sub" data-collapse="child-{{ $parent_id }}">
                <td class="{{ $class->column_name_width }} col-w-8 py-2 text-left text-alignment-left text-black-400 pl-{{ $tree_level * 10 }} print-report-padding">
        @else
            <tr>
                <td class="{{ $class->column_name_width }} col-w-8 py-2 text-left text-alignment-left text-black-400">
        @endif

        @if (array_sum($parent_row_values) != array_sum($class->row_values[$table_key][$id]) && ! isset($print))
            <button type="button" class="align-text-top" node="child-{{ $id }}" onClick="toggleSub('child-{{ $id }}', event)">
                <span class="material-icons transform transition-all rotate-90 text-lg leading-none">navigate_next</span>
            </button>
        @endif

        @if (! isset($print) && $class->row_names[$table_key][$id]['route'])
            <x-link href="{{ $class->row_names[$table_key][$id]['route'] }}" class="text-sm sm:mt-0 sm:mb-0 leading-4" override="class">
                <x-link.hover color="to-black-400">
                    {{ $name }}
                </x-link.hover>
            </x-link>
        @else
            {{ $name }}
        @endif

        </td>
        @foreach($parent_row_values as $row)
            <td class="{{ $class->column_value_width }} col-w-2 py-2 ltr:text-right rtl:text-left text-alignment-center text-black-400 text-xs">{{ $class->has_money ? money($row, setting('default.currency'), true) : $row }}</td>
        @endforeach
    </tr>
    @endif

    <!-- no categories part -->
    @php $rows = $class->row_values[$table_key][$id]; @endphp
    @if (($row_total = array_sum($rows)) && array_sum($parent_row_values) != array_sum($rows))
    <tr class="collapse-sub" data-collapse="child-{{ $id }}">
        <td class="{{ $class->column_name_width }} col-w-8 py-2 text-left text-alignment-left text-black-400 pl-{{ ($tree_level + 1) * 10 }} print-report-padding">{!! $class->row_names[$table_key][$id]['name'] !!}</td>
        @foreach($rows as $row)
            <td class="{{ $class->column_value_width }} col-w-2 py-2 ltr:text-right rtl:text-left text-alignment-center text-black-400 text-xs">{{ $class->has_money ? money($row, setting('default.currency'), true) : $row }}</td>
        @endforeach
    </tr>
    @endif

    <!-- subcategories part -->
    @php
        $parent_id = $id;
        $tree_level++;
    @endphp

    @foreach($node as $id => $node)
        @if ($parent_id != $id)
            @include($class->views['detail.table.row'], ['parent_id' => $parent_id, 'tree_level' => $tree_level])
        @endif
    @endforeach
@endif
