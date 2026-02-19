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
                <th>
                    <a href="{{ $sort_code }}" @class([$order => $sort === 'code'])>{{ $lang->column_code }}</a>
                </th>
                <th>{{ $lang->column_company }}</th>
                <th>{{ $lang->column_parent }}</th>
                <th>{{ $lang->column_is_active }}</th>
                <th>
                    <a href="{{ $sort_sort_order }}" @class([$order => $sort === 'sort_order'])>{{ $lang->column_sort_order }}</a>
                </th>
                <th class="text-end">{{ $lang->column_action }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($departments as $department)
            <tr>
                <td class="text-center">
                    <input type="checkbox" name="selected[]" value="{{ $department->id }}" class="form-check-input">
                </td>
                <td>{{ $department->name }}</td>
                <td>{{ $department->code ?: '-' }}</td>
                <td>{{ $department->company?->name ?: '-' }}</td>
                <td>{{ $department->parent?->name ?: '-' }}</td>
                <td>
                    @if($department->is_active)
                        <span class="badge bg-success">{{ $lang->text_active }}</span>
                    @else
                        <span class="badge bg-secondary">{{ $lang->text_inactive }}</span>
                    @endif
                </td>
                <td>{{ $department->sort_order }}</td>
                <td class="text-end">
                    <a href="{{ route('lang.ocadmin.hrm.department.edit', $department) }}" data-bs-toggle="tooltip" title="{{ $lang->button_edit }}" class="btn btn-primary"><i class="bi bi-pencil"></i></a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center">{{ $lang->text_no_data }}</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="row">
    <div class="col-sm-6 text-start">{!! $pagination !!}</div>
    <div class="col-sm-6 text-end">{!! sprintf($lang->text_showing, $departments->firstItem() ?? 0, $departments->lastItem() ?? 0, $departments->total()) !!}</div>
</div>
