<form id="form-user" method="post" data-oc-toggle="ajax" data-oc-load="{{ $action }}" data-oc-target="#user-list">
    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th class="text-center" style="width: 1px;">
                        <input type="checkbox" onclick="$('input[name*=\'selected\']').prop('checked', $(this).prop('checked'));" class="form-check-input">
                    </th>
                    <th>
                        <a href="{{ route('lang.ocadmin.system.access.user.list', array_merge(request()->all(), ['sort' => 'username', 'order' => request('order') === 'asc' && request('sort') === 'username' ? 'desc' : 'asc'])) }}" @class([request('order', 'asc') => request('sort') === 'username'])>
                            {{ $lang->column_username }}
                        </a>
                    </th>
                    <th>
                        <a href="{{ route('lang.ocadmin.system.access.user.list', array_merge(request()->all(), ['sort' => 'email', 'order' => request('order') === 'asc' && request('sort') === 'email' ? 'desc' : 'asc'])) }}" @class([request('order', 'asc') => request('sort') === 'email'])>
                            {{ $lang->column_email }}
                        </a>
                    </th>
                    <th>
                        <a href="{{ route('lang.ocadmin.system.access.user.list', array_merge(request()->all(), ['sort' => 'name', 'order' => request('order') === 'asc' && request('sort') === 'name' ? 'desc' : 'asc'])) }}" @class([request('order', 'asc') => request('sort') === 'name'])>
                            {{ $lang->column_name }}
                        </a>
                    </th>
                    <th>{{ $lang->column_roles }}</th>
                    <th class="text-end">{{ $lang->column_action }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr @class(['table-active opacity-50' => !$user->is_active])>
                    <td class="text-center">
                        <input type="checkbox" name="selected[]" value="{{ $user->id }}" class="form-check-input">
                    </td>
                    <td>{{ $user->username }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->name }}</td>
                    <td>
                        @foreach($user->roles as $role)
                            @if($role->name === 'staff')
                                <span class="badge bg-primary">{{ $role->title ?: $role->name }}</span>
                            @else
                                <span class="badge bg-secondary">{{ $role->title ?: $role->name }}</span>
                            @endif
                        @endforeach
                    </td>
                    <td class="text-end">
                        <a href="{{ route('lang.ocadmin.system.access.user.edit', $user->id) }}" data-bs-toggle="tooltip" title="{{ $lang->button_edit }}" class="btn btn-primary"><i class="fa-solid fa-pencil"></i></a>
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
        <div class="col-sm-6 text-start">{{ $users->links('ocadmin::pagination.default') }}</div>
        <div class="col-sm-6 text-end">{{ sprintf($lang->text_pagination, $users->firstItem() ?? 0, $users->lastItem() ?? 0, $users->total()) }}</div>
    </div>
</form>
