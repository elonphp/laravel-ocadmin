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
                    <a href="{{ $sort_display_name }}" @class([$order => $sort === 'display_name'])>{{ $lang->column_display_name }}</a>
                </th>
                <th>{{ $lang->column_note }}</th>
                <th class="text-center">{{ $lang->column_guard_name }}</th>
                <th class="text-end">{{ $lang->column_action }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($permissions as $permission)
            <tr>
                <td class="text-center">
                    <input type="checkbox" name="selected[]" value="{{ $permission->id }}" class="form-check-input">
                </td>
                <td><code>{{ $permission->name }}</code></td>
                <td>{{ $permission->display_name }}</td>
                <td>{{ $permission->note ?: '-' }}</td>
                <td class="text-center"><span class="badge bg-secondary">{{ $permission->guard_name }}</span></td>
                <td class="text-end">
                    <a href="{{ route('lang.ocadmin.system.permission.edit', $permission) }}" data-bs-toggle="tooltip" title="{{ $lang->button_edit }}" class="btn btn-primary"><i class="bi bi-pencil"></i></a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center">{{ $lang->text_no_data }}</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="row">
    <div class="col-sm-6 text-start">{!! $pagination !!}</div>
    <div class="col-sm-6 text-end">{!! sprintf($lang->text_showing, $permissions->firstItem() ?? 0, $permissions->lastItem() ?? 0, $permissions->total()) !!}</div>
</div>
