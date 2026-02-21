<div class="table-responsive">
    <table class="table table-bordered table-hover">
        <thead>
            <tr>
                <th class="text-center" style="width: 1px;">
                    <input type="checkbox" onclick="$('input[name*=\'selected\']').prop('checked', $(this).prop('checked'));" class="form-check-input">
                </th>
                <th>
                    <a href="{{ $sort_code }}" @class([request('order', 'asc') => request('sort') === 'code'])>
                        代碼
                    </a>
                </th>
                <th>
                    <a href="{{ $sort_name }}" @class([request('order', 'asc') => request('sort') === 'name'])>
                        名稱
                    </a>
                </th>
                <th>說明</th>
                <th class="text-center">詞彙數</th>
                <th class="text-center">
                    <a href="{{ $sort_sort_order }}" @class([request('order', 'asc') => request('sort') === 'sort_order'])>
                        排序
                    </a>
                </th>
                <th class="text-center">狀態</th>
                <th class="text-end">操作</th>
            </tr>
        </thead>
        <tbody>
            @forelse($taxonomies as $taxonomy)
            <tr>
                <td class="text-center">
                    <input type="checkbox" name="selected[]" value="{{ $taxonomy->id }}" class="form-check-input">
                </td>
                <td><code>{{ $taxonomy->code }}</code></td>
                <td>{{ $taxonomy->name }}</td>
                <td>{{ $taxonomy->description ?: '-' }}</td>
                <td class="text-center">
                    <a href="{{ route('lang.ocadmin.config.term.index', ['filter_taxonomy_id' => $taxonomy->id]) }}" class="badge bg-info text-decoration-none">
                        {{ $taxonomy->terms_count }}
                    </a>
                </td>
                <td class="text-center">{{ $taxonomy->sort_order }}</td>
                <td class="text-center">
                    @if($taxonomy->is_active)
                    <span class="badge bg-success">啟用</span>
                    @else
                    <span class="badge bg-secondary">停用</span>
                    @endif
                </td>
                <td class="text-end">
                    <a href="{{ route('lang.ocadmin.config.taxonomy.edit', $taxonomy) }}" data-bs-toggle="tooltip" title="編輯" class="btn btn-primary"><i class="fa-solid fa-pencil"></i></a>
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
    <div class="col-sm-6 text-start">{!! $pagination !!}</div>
    <div class="col-sm-6 text-end">顯示 {{ $taxonomies->firstItem() ?? 0 }} 到 {{ $taxonomies->lastItem() ?? 0 }}，共 {{ $taxonomies->total() }} 筆</div>
</div>
