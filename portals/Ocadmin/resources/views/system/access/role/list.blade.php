<form id="form-role" method="post" data-oc-toggle="ajax" data-oc-load="{{ $action }}" data-oc-target="#role-list">
    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th class="text-center" style="width: 1px;">
                        <input type="checkbox" onclick="$('input[name*=\'selected\']').prop('checked', $(this).prop('checked'));" class="form-check-input">
                    </th>
                    <th>
                        <a href="{{ route('lang.ocadmin.system.access.role.list', array_merge(request()->all(), ['sort' => 'name', 'order' => request('order') === 'asc' && request('sort', 'name') === 'name' ? 'desc' : 'asc'])) }}" @class([request('order', 'asc') => request('sort', 'name') === 'name'])>
                            {{ $lang->column_name }}
                        </a>
                    </th>
                    <th>
                        <a href="{{ route('lang.ocadmin.system.access.role.list', array_merge(request()->all(), ['sort' => 'title', 'order' => request('order') === 'asc' && request('sort') === 'title' ? 'desc' : 'asc'])) }}" @class([request('order', 'asc') => request('sort') === 'title'])>
                            {{ $lang->column_title }}
                        </a>
                    </th>
                    <th>{{ $lang->column_permissions_count }}</th>
                    <th>{{ $lang->column_guard_name }}</th>
                    <th class="text-end">{{ $lang->column_action }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($roles as $role)
                <tr>
                    <td class="text-center">
                        <input type="checkbox" name="selected[]" value="{{ $role->id }}" class="form-check-input">
                    </td>
                    <td>{{ $role->name }}</td>
                    <td>{{ $role->title }}</td>
                    <td>
                        <span class="badge bg-info">{{ $role->permissions_count }}</span>
                    </td>
                    <td>{{ $role->guard_name }}</td>
                    <td class="text-end">
                        <a href="{{ route('lang.ocadmin.system.access.role.edit', $role) }}" data-bs-toggle="tooltip" title="{{ $lang->button_edit }}" class="btn btn-primary"><i class="fa-solid fa-pencil"></i></a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center">{{ $lang->text_no_results }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="row">
        <div class="col-sm-6 text-start">{{ $roles->links('ocadmin::pagination.default') }}</div>
        <div class="col-sm-6 text-end">{{ sprintf($lang->text_pagination, $roles->firstItem() ?? 0, $roles->lastItem() ?? 0, $roles->total()) }}</div>
    </div>
</form>
