<div class="table-responsive">
    <table class="table table-bordered table-hover">
        <thead>
            <tr>
                <th class="text-center" style="width: 1px;">
                    <input type="checkbox" onclick="$('input[name*=\'selected\']').prop('checked', $(this).prop('checked'));" class="form-check-input">
                </th>
                <th>
                    <a href="{{ route('lang.ocadmin.system.permission.index', array_merge(request()->all(), ['sort' => 'name', 'order' => request('order') === 'asc' && request('sort', 'name') === 'name' ? 'desc' : 'asc'])) }}" @class([request('order', 'asc') => request('sort', 'name') === 'name'])>
                        權限代碼
                    </a>
                </th>
                <th>顯示名稱</th>
                <th>備註</th>
                <th class="text-center">Guard</th>
                <th class="text-end">操作</th>
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
                    <a href="{{ route('lang.ocadmin.system.permission.edit', $permission) }}" data-bs-toggle="tooltip" title="編輯" class="btn btn-primary"><i class="fa-solid fa-pencil"></i></a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center">暫無資料</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="row">
    <div class="col-sm-6 text-start">{{ $permissions->links() }}</div>
    <div class="col-sm-6 text-end">顯示 {{ $permissions->firstItem() ?? 0 }} 到 {{ $permissions->lastItem() ?? 0 }}，共 {{ $permissions->total() }} 筆</div>
</div>
