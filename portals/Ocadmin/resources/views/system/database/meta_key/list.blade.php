<form id="form-meta-key" method="post" data-oc-toggle="ajax" data-oc-load="{{ $action }}" data-oc-target="#meta-key-list">
    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th class="text-center" style="width: 1px;">
                        <input type="checkbox" onclick="$('input[name*=\'selected\']').prop('checked', $(this).prop('checked'));" class="form-check-input">
                    </th>
                    <th style="width: 80px;">
                        <a href="{{ route('lang.ocadmin.system.database.meta_key.list', array_merge(request()->all(), ['sort' => 'id', 'order' => request('order') === 'asc' && request('sort') === 'id' ? 'desc' : 'asc'])) }}" @class([request('order', 'asc') => request('sort', 'id') === 'id'])>
                            ID
                        </a>
                    </th>
                    <th>
                        <a href="{{ route('lang.ocadmin.system.database.meta_key.list', array_merge(request()->all(), ['sort' => 'name', 'order' => request('order') === 'asc' && request('sort') === 'name' ? 'desc' : 'asc'])) }}" @class([request('order', 'asc') => request('sort') === 'name'])>
                            欄位名稱
                        </a>
                    </th>
                    <th>
                        <a href="{{ route('lang.ocadmin.system.database.meta_key.list', array_merge(request()->all(), ['sort' => 'table_name', 'order' => request('order') === 'asc' && request('sort') === 'table_name' ? 'desc' : 'asc'])) }}" @class([request('order', 'asc') => request('sort') === 'table_name'])>
                            所屬資料表
                        </a>
                    </th>
                    <th>
                        <a href="{{ route('lang.ocadmin.system.database.meta_key.list', array_merge(request()->all(), ['sort' => 'description', 'order' => request('order') === 'asc' && request('sort') === 'description' ? 'desc' : 'asc'])) }}" @class([request('order', 'asc') => request('sort') === 'description'])>
                            欄位說明
                        </a>
                    </th>
                    <th class="text-end">操作</th>
                </tr>
            </thead>
            <tbody>
                @forelse($metaKeys as $metaKey)
                <tr>
                    <td class="text-center">
                        <input type="checkbox" name="selected[]" value="{{ $metaKey->id }}" class="form-check-input">
                    </td>
                    <td>{{ $metaKey->id }}</td>
                    <td><code>{{ $metaKey->name }}</code></td>
                    <td>
                        @if($metaKey->table_name)
                            <span class="badge bg-info">{{ $metaKey->table_name }}</span>
                        @else
                            <span class="badge bg-secondary">共用</span>
                        @endif
                    </td>
                    <td>{{ $metaKey->description }}</td>
                    <td class="text-end">
                        <a href="{{ route('lang.ocadmin.system.database.meta_key.edit', $metaKey) }}" data-bs-toggle="tooltip" title="編輯" class="btn btn-primary"><i class="fa-solid fa-pencil"></i></a>
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
        <div class="col-sm-6 text-start">{{ $metaKeys->links('ocadmin::pagination.default') }}</div>
        <div class="col-sm-6 text-end">顯示 {{ $metaKeys->firstItem() ?? 0 }} 到 {{ $metaKeys->lastItem() ?? 0 }}，共 {{ $metaKeys->total() }} 筆</div>
    </div>
</form>
