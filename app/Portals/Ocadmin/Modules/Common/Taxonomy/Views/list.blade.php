<form id="form-taxonomy" method="post" data-oc-toggle="ajax" data-oc-load="{{ $action }}" data-oc-target="#taxonomy-list">
    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th class="text-center" style="width: 1px;">
                        <input type="checkbox" onclick="$('input[name*=\'selected\']').prop('checked', $(this).prop('checked'));" class="form-check-input">
                    </th>
                    <th style="width: 80px;">
                        <a href="{{ route('lang.ocadmin.system.taxonomy.taxonomy.list', array_merge(request()->all(), ['sort' => 'id', 'order' => request('order') === 'asc' && request('sort') === 'id' ? 'desc' : 'asc'])) }}" @class([request('order', 'asc') => request('sort') === 'id'])>
                            ID
                        </a>
                    </th>
                    <th>
                        <a href="{{ route('lang.ocadmin.system.taxonomy.taxonomy.list', array_merge(request()->all(), ['sort' => 'code', 'order' => request('order') === 'asc' && request('sort') === 'code' ? 'desc' : 'asc'])) }}" @class([request('order', 'asc') => request('sort') === 'code'])>
                            代碼
                        </a>
                    </th>
                    <th>名稱</th>
                    <th class="text-center" style="width: 100px;">詞彙數量</th>
                    <th class="text-center" style="width: 80px;">
                        <a href="{{ route('lang.ocadmin.system.taxonomy.taxonomy.list', array_merge(request()->all(), ['sort' => 'sort_order', 'order' => request('order') === 'asc' && request('sort', 'sort_order') === 'sort_order' ? 'desc' : 'asc'])) }}" @class([request('order', 'asc') => request('sort', 'sort_order') === 'sort_order'])>
                            排序
                        </a>
                    </th>
                    <th class="text-center" style="width: 80px;">狀態</th>
                    <th class="text-end" style="width: 150px;">操作</th>
                </tr>
            </thead>
            <tbody>
                @forelse($taxonomies as $taxonomy)
                <tr>
                    <td class="text-center">
                        <input type="checkbox" name="selected[]" value="{{ $taxonomy->id }}" class="form-check-input">
                    </td>
                    <td>{{ $taxonomy->id }}</td>
                    <td><code>{{ $taxonomy->code }}</code></td>
                    <td>{{ $taxonomy->name }}</td>
                    <td class="text-center">
                        <a href="{{ route('lang.ocadmin.system.taxonomy.term.index', ['filter_taxonomy_id' => $taxonomy->id]) }}" class="badge bg-info text-decoration-none">
                            {{ $taxonomy->terms->count() }} 筆
                        </a>
                    </td>
                    <td class="text-center">{{ $taxonomy->sort_order }}</td>
                    <td class="text-center">
                        @if($taxonomy->is_active)
                            <span class="badge bg-success">啟用</span>
                        @else
                            <span class="badge bg-danger">停用</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <a href="{{ route('lang.ocadmin.system.taxonomy.term.index', ['filter_taxonomy_id' => $taxonomy->id]) }}" data-bs-toggle="tooltip" title="管理詞彙" class="btn btn-info btn-sm">
                            <i class="fa-solid fa-tags"></i>
                        </a>
                        <a href="{{ route('lang.ocadmin.system.taxonomy.taxonomy.edit', ['id' => $taxonomy->id]) }}" data-bs-toggle="tooltip" title="編輯" class="btn btn-primary btn-sm">
                            <i class="fa-solid fa-pencil"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center">暫無資料</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="row">
        <div class="col-sm-6 text-start">{{ $taxonomies->links('ocadmin::pagination.default') }}</div>
        <div class="col-sm-6 text-end">顯示 {{ $taxonomies->firstItem() ?? 0 }} 到 {{ $taxonomies->lastItem() ?? 0 }}，共 {{ $taxonomies->total() }} 筆</div>
    </div>
</form>
