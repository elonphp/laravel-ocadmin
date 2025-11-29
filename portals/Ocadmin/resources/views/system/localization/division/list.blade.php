<form id="form-division" method="post" data-oc-toggle="ajax" data-oc-load="{{ $action }}" data-oc-target="#division-list">
    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th class="text-center" style="width: 1px;">
                        <input type="checkbox" onclick="$('input[name*=\'selected\']').prop('checked', $(this).prop('checked'));" class="form-check-input">
                    </th>
                    <th>
                        <a href="{{ route('ocadmin.system.localization.division.list', array_merge(request()->all(), ['sort' => 'name', 'order' => request('order') === 'asc' && request('sort') === 'name' ? 'desc' : 'asc'])) }}" @class([request('order', 'asc') => request('sort') === 'name'])>
                            區域名稱
                        </a>
                    </th>
                    <th>
                        <a href="{{ route('ocadmin.system.localization.division.list', array_merge(request()->all(), ['sort' => 'code', 'order' => request('order') === 'asc' && request('sort') === 'code' ? 'desc' : 'asc'])) }}" @class([request('order', 'asc') => request('sort') === 'code'])>
                            代碼
                        </a>
                    </th>
                    <th>
                        <a href="{{ route('ocadmin.system.localization.division.list', array_merge(request()->all(), ['sort' => 'level', 'order' => request('order') === 'asc' && request('sort') === 'level' ? 'desc' : 'asc'])) }}" @class([request('order', 'asc') => request('sort') === 'level'])>
                            層級
                        </a>
                    </th>
                    <th>
                        <a href="{{ route('ocadmin.system.localization.division.list', array_merge(request()->all(), ['sort' => 'sort_order', 'order' => request('order') === 'asc' && request('sort') === 'sort_order' ? 'desc' : 'asc'])) }}" @class([request('order', 'asc') => request('sort', 'sort_order') === 'sort_order'])>
                            排序
                        </a>
                    </th>
                    <th>所屬國家</th>
                    <th class="text-end">操作</th>
                </tr>
            </thead>
            <tbody>
                @forelse($divisions as $division)
                <tr @class(['table-active opacity-50' => !$division->is_active])>
                    <td class="text-center">
                        <input type="checkbox" name="selected[]" value="{{ $division->id }}" class="form-check-input">
                    </td>
                    <td>{{ $division->native_name }}</td>
                    <td>{{ $division->code }}</td>
                    <td>{{ $division->level }}</td>
                    <td>{{ $division->sort_order }}</td>
                    <td>{{ $division->country->name ?? '-' }}</td>
                    <td class="text-end">
                        <a href="{{ route('ocadmin.system.localization.division.edit', $division) }}" data-bs-toggle="tooltip" title="編輯" class="btn btn-primary"><i class="fa-solid fa-pencil"></i></a>
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
        <div class="col-sm-6 text-start">{{ $divisions->links('ocadmin::pagination.default') }}</div>
        <div class="col-sm-6 text-end">顯示 {{ $divisions->firstItem() ?? 0 }} 到 {{ $divisions->lastItem() ?? 0 }}，共 {{ $divisions->total() }} 筆</div>
    </div>
</form>
