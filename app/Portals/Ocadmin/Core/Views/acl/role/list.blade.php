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
                <th class="text-center">
                    <a href="{{ $sort_sort_order }}" @class([$order => $sort === 'sort_order'])>{{ $lang->column_sort_order }}</a>
                </th>
                <th class="text-center">{{ $lang->column_is_active }}</th>
                <th class="text-center">{{ $lang->column_guard_name }}</th>
                <th class="text-end">{{ $lang->column_action }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($roles as $role)
            <tr>
                <td class="text-center">
                    <input type="checkbox" name="selected[]" value="{{ $role->id }}" class="form-check-input">
                </td>
                <td><code>{{ $role->name }}</code></td>
                <td>{{ $role->display_name }}</td>
                <td>{{ $role->note ?: '-' }}</td>
                <td class="text-center">{{ $role->sort_order }}</td>
                <td class="text-center">
                    @if($role->is_active)
                    <span class="badge bg-success">{{ $lang->text_enabled }}</span>
                    @else
                    <span class="badge bg-danger">{{ $lang->text_disabled }}</span>
                    @endif
                </td>
                <td class="text-center"><span class="badge bg-secondary">{{ $role->guard_name }}</span></td>
                <td class="text-end">
                    <a href="{{ route('lang.ocadmin.system.role.edit', $role) }}" data-bs-toggle="tooltip" title="{{ $lang->button_edit }}" class="btn btn-primary"><i class="fa-solid fa-pencil"></i></a>
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
    <div class="col-sm-6 text-start">{{ $roles->links() }}</div>
    <div class="col-sm-6 text-end">{!! sprintf($lang->text_showing, $roles->firstItem() ?? 0, $roles->lastItem() ?? 0, $roles->total()) !!}</div>
</div>
