@stack('bulk_action_all_input_start')

    <div class="custom-control custom-checkbox">
        <input type="checkbox"
            id="table-check-all-{{ $attributes['group'] }}"
            class="rounded-sm text-purple border-gray-300 cursor-pointer disabled:bg-gray-200 focus:outline-none focus:ring-transparent"
            v-model="{{ !empty($attributes['v-model']) ? $attributes['v-model'] : 'bulk_action.select_all' }}"
            @click="onSelectAll({{ !empty($attributes['group']) ? $attributes['group'] : undefined }})">
        <label class="custom-control-label" for="table-check-all-{{ $attributes['group'] }}"></label>
    </div>

@stack('bulk_action_all_input_end')
