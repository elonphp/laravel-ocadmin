<form id="form-term" method="post" data-oc-toggle="ajax" data-oc-load="{{ $action }}" data-oc-target="#term-list">
    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th class="text-center" style="width: 1px;">
                        <input type="checkbox" onclick="$('input[name*=\'selected\']').prop('checked', $(this).prop('checked'));" class="form-check-input">
                    </th>
                    <th style="width: 80px;">
                        <a href="{{ route('lang.ocadmin.system.taxonomy.term.list', array_merge(request()->all(), ['sort' => 'id', 'order' => request('order') === 'asc' && request('sort') === 'id' ? 'desc' : 'asc'])) }}" @class([request('order', 'asc') => request('sort') === 'id'])>
                            ID
                        </a>
                    </th>
                    @if(!$currentTaxonomyId)
                    <th>分類法</th>
                    @endif
                    <th>
                        <a href="{{ route('lang.ocadmin.system.taxonomy.term.list', array_merge(request()->all(), ['sort' => 'code', 'order' => request('order') === 'asc' && request('sort') === 'code' ? 'desc' : 'asc'])) }}" @class([request('order', 'asc') => request('sort') === 'code'])>
                            代碼
                        </a>
                    </th>
                    <th>名稱</th>
                    <th>父層</th>
                    <th class="text-center" style="width: 80px;">
                        <a href="{{ route('lang.ocadmin.system.taxonomy.term.list', array_merge(request()->all(), ['sort' => 'sort_order', 'order' => request('order') === 'asc' && request('sort', 'sort_order') === 'sort_order' ? 'desc' : 'asc'])) }}" @class([request('order', 'asc') => request('sort', 'sort_order') === 'sort_order'])>
                            排序
                        </a>
                    </th>
                    <th class="text-center" style="width: 80px;">狀態</th>
                    <th class="text-end" style="width: 100px;">操作</th>
                </tr>
            </thead>
            <tbody>
                @forelse($terms as $term)
                <tr>
                    <td class="text-center">
                        <input type="checkbox" name="selected[]" value="{{ $term->id }}" class="form-check-input">
                    </td>
                    <td>{{ $term->id }}</td>
                    @if(!$currentTaxonomyId)
                    <td>
                        <a href="{{ route('lang.ocadmin.system.taxonomy.term.index', ['filter_taxonomy_id' => $term->taxonomy_id]) }}">
                            {{ $term->taxonomy?->name }}
                        </a>
                    </td>
                    @endif
                    <td><code>{{ $term->code }}</code></td>
                    <td>
                        @if($term->parent)
                            <span class="text-muted">{{ $term->parent->name }} &raquo;</span>
                        @endif
                        {{ $term->name }}
                    </td>
                    <td>
                        @if($term->parent)
                            <span class="badge bg-secondary">{{ $term->parent->name }}</span>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td class="text-center">{{ $term->sort_order }}</td>
                    <td class="text-center">
                        @if($term->is_active)
                            <span class="badge bg-success">啟用</span>
                        @else
                            <span class="badge bg-danger">停用</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <a href="{{ route('lang.ocadmin.system.taxonomy.term.edit', ['id' => $term->id]) }}" data-bs-toggle="tooltip" title="編輯" class="btn btn-primary btn-sm">
                            <i class="fa-solid fa-pencil"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="{{ $currentTaxonomyId ? 7 : 8 }}" class="text-center">暫無資料</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="row">
        <div class="col-sm-6 text-start">{{ $terms->links('ocadmin::pagination.default') }}</div>
        <div class="col-sm-6 text-end">顯示 {{ $terms->firstItem() ?? 0 }} 到 {{ $terms->lastItem() ?? 0 }}，共 {{ $terms->total() }} 筆</div>
    </div>
</form>
