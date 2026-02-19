<div class="table-responsive">
    <table class="table table-bordered table-hover">
        <thead>
            <tr>
                <th class="text-center" style="width: 1px;">
                    <input type="checkbox" onclick="$('input[name*=\'selected\']').prop('checked', $(this).prop('checked'));" class="form-check-input">
                </th>
                <th>
                    <a href="{{ $sort_name }}" @class([$order => $sort === 'name'])>{{ $lang->column_name }}</a>
                </th>
                <th>{{ $lang->column_code }}</th>
                <th>{{ $lang->column_type }}</th>
                <th class="text-center">{{ $lang->column_values_count }}</th>
                <th class="text-center">
                    <a href="{{ $sort_sort_order }}" @class([$order => $sort === 'sort_order'])>{{ $lang->column_sort_order }}</a>
                </th>
                <th class="text-end">{{ $lang->column_action }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($options as $option)
            <tr>
                <td class="text-center">
                    <input type="checkbox" name="selected[]" value="{{ $option->id }}" class="form-check-input">
                </td>
                <td>{{ $option->name }}</td>
                <td><code>{{ $option->code }}</code></td>
                <td>{{ $lang->{'text_' . $option->type} ?? $option->type }}</td>
                <td class="text-center">
                    <span class="badge bg-info">{{ $option->option_values_count }}</span>
                </td>
                <td class="text-center">{{ $option->sort_order }}</td>
                <td class="text-end">
                    <a href="{{ route('lang.ocadmin.catalog.option.edit', $option) }}" data-bs-toggle="tooltip" title="{{ $lang->button_edit }}" class="btn btn-primary"><i class="bi bi-pencil"></i></a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center">{{ $lang->text_no_data }}</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="row">
    <div class="col-sm-6 text-start">{!! $pagination !!}</div>
    <div class="col-sm-6 text-end">{!! sprintf($lang->text_showing ?? '顯示 %s 到 %s，共 %s 筆', $options->firstItem() ?? 0, $options->lastItem() ?? 0, $options->total()) !!}</div>
</div>
