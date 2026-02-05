<div class="table-responsive">
    <table class="table table-bordered table-hover">
        <thead>
            <tr>
                <th class="text-center" style="width: 1px;">
                    <input type="checkbox" onclick="$('input[name*=\'selected\']').prop('checked', $(this).prop('checked'));" class="form-check-input">
                </th>
                <th>
                    <a href="{{ route('lang.ocadmin.config.term.index', array_merge(request()->all(), ['sort' => 'taxonomy_id', 'order' => request('order') === 'asc' && request('sort') === 'taxonomy_id' ? 'desc' : 'asc'])) }}" @class([request('order', 'asc') => request('sort') === 'taxonomy_id'])>
                        分類
                    </a>
                </th>
                <th>
                    <a href="{{ route('lang.ocadmin.config.term.index', array_merge(request()->all(), ['sort' => 'code', 'order' => request('order') === 'asc' && request('sort') === 'code' ? 'desc' : 'asc'])) }}" @class([request('order', 'asc') => request('sort') === 'code'])>
                        代碼
                    </a>
                </th>
                <th>
                    <a href="{{ route('lang.ocadmin.config.term.index', array_merge(request()->all(), ['sort' => 'name', 'order' => request('order') === 'asc' && request('sort') === 'name' ? 'desc' : 'asc'])) }}" @class([request('order', 'asc') => request('sort') === 'name'])>
                        名稱
                    </a>
                </th>
                <th>父項目</th>
                <th class="text-center">
                    <a href="{{ route('lang.ocadmin.config.term.index', array_merge(request()->all(), ['sort' => 'sort_order', 'order' => request('order') === 'asc' && request('sort') === 'sort_order' ? 'desc' : 'asc'])) }}" @class([request('order', 'asc') => request('sort') === 'sort_order'])>
                        排序
                    </a>
                </th>
                <th class="text-center">狀態</th>
                <th class="text-end">操作</th>
            </tr>
        </thead>
        <tbody>
            @forelse($terms as $term)
            <tr>
                <td class="text-center">
                    <input type="checkbox" name="selected[]" value="{{ $term->id }}" class="form-check-input">
                </td>
                <td><span class="badge bg-info">{{ $term->taxonomy->name }}</span></td>
                <td><code>{{ $term->code }}</code></td>
                <td>{{ $term->name }}</td>
                <td>{{ $term->parent ? $term->parent->name : '-' }}</td>
                <td class="text-center">{{ $term->sort_order }}</td>
                <td class="text-center">
                    @if($term->is_active)
                    <span class="badge bg-success">啟用</span>
                    @else
                    <span class="badge bg-secondary">停用</span>
                    @endif
                </td>
                <td class="text-end">
                    <a href="{{ route('lang.ocadmin.config.term.edit', $term) }}" data-bs-toggle="tooltip" title="編輯" class="btn btn-primary"><i class="fa-solid fa-pencil"></i></a>
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
    <div class="col-sm-6 text-start">{{ $terms->links() }}</div>
    <div class="col-sm-6 text-end">顯示 {{ $terms->firstItem() ?? 0 }} 到 {{ $terms->lastItem() ?? 0 }}，共 {{ $terms->total() }} 筆</div>
</div>
