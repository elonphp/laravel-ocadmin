<form id="form-account" method="post" data-oc-toggle="ajax" data-oc-load="{{ $action }}" data-oc-target="#account-list">
    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th class="text-center" style="width: 1px;">
                        <input type="checkbox" onclick="$('input[name*=\'selected\']').prop('checked', $(this).prop('checked'));" class="form-check-input">
                    </th>
                    <th>
                        <a href="{{ route('lang.ocadmin.member.user.list', array_merge(request()->all(), ['sort' => 'username', 'order' => request('order') === 'asc' && request('sort') === 'username' ? 'desc' : 'asc'])) }}" @class([request('order', 'asc') => request('sort') === 'username'])>
                            帳號
                        </a>
                    </th>
                    <th>
                        <a href="{{ route('lang.ocadmin.member.user.list', array_merge(request()->all(), ['sort' => 'name', 'order' => request('order') === 'asc' && request('sort') === 'name' ? 'desc' : 'asc'])) }}" @class([request('order', 'asc') => request('sort') === 'name'])>
                            姓名
                        </a>
                    </th>
                    <th>
                        <a href="{{ route('lang.ocadmin.member.user.list', array_merge(request()->all(), ['sort' => 'email', 'order' => request('order') === 'asc' && request('sort') === 'email' ? 'desc' : 'asc'])) }}" @class([request('order', 'asc') => request('sort') === 'email'])>
                            Email
                        </a>
                    </th>
                    <th>
                        <a href="{{ route('lang.ocadmin.member.user.list', array_merge(request()->all(), ['sort' => 'mobile', 'order' => request('order') === 'asc' && request('sort') === 'mobile' ? 'desc' : 'asc'])) }}" @class([request('order', 'asc') => request('sort') === 'mobile'])>
                            手機
                        </a>
                    </th>
                    <th>狀態</th>
                    <th class="text-end">操作</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr @class(['table-active opacity-50' => !$user->is_active])>
                    <td class="text-center">
                        <input type="checkbox" name="selected[]" value="{{ $user->id }}" class="form-check-input">
                    </td>
                    <td>{{ $user->username }}</td>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->mobile }}</td>
                    <td>
                        @if($user->is_active)
                            <span class="badge bg-success">啟用</span>
                        @else
                            <span class="badge bg-secondary">停用</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <a href="{{ route('lang.ocadmin.member.user.edit', $user) }}" data-bs-toggle="tooltip" title="編輯" class="btn btn-primary"><i class="fa-solid fa-pencil"></i></a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center">暫無資料</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="row">
        <div class="col-sm-6 text-start">{{ $users->links('ocadmin::pagination.default') }}</div>
        <div class="col-sm-6 text-end">顯示 {{ $users->firstItem() ?? 0 }} 到 {{ $users->lastItem() ?? 0 }}，共 {{ $users->total() }} 筆</div>
    </div>
</form>
