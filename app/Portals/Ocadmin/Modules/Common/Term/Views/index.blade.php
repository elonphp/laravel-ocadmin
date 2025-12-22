@extends('ocadmin::layouts.app')

@section('title', isset($currentTaxonomy) ? $currentTaxonomy->name . ' - 詞彙管理' : '詞彙管理')

@section('content')
<div id="content">
    <div class="page-header">
        <div class="container-fluid">
            <div class="float-end">
                <button type="button" data-bs-toggle="tooltip" title="篩選" onclick="$('#filter-term').toggleClass('d-none');" class="btn btn-light d-lg-none">
                    <i class="fa-solid fa-filter"></i>
                </button>
                <a href="{{ route('lang.ocadmin.system.taxonomy.term.create', $currentTaxonomyId ? ['taxonomy_id' => $currentTaxonomyId] : []) }}" data-bs-toggle="tooltip" title="新增" class="btn btn-primary">
                    <i class="fa-solid fa-plus"></i>
                </a>
                <button type="button" id="button-delete" data-bs-toggle="tooltip" title="刪除" class="btn btn-danger">
                    <i class="fa-regular fa-trash-can"></i>
                </button>
            </div>
            <h1>
                @if(isset($currentTaxonomy))
                    {{ $currentTaxonomy->name }} - 詞彙管理
                @else
                    詞彙管理
                @endif
            </h1>
            @include('ocadmin::layouts.partials.breadcrumb')
        </div>
    </div>

    <div class="container-fluid">
        @if(session('success'))
        <div class="alert alert-success alert-dismissible">
            <i class="fa-solid fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <div class="row">
            {{-- 篩選區塊 - 右側 --}}
            <div id="filter-term" class="col-lg-3 col-md-12 order-lg-last d-none d-lg-block mb-3">
                <div class="card">
                    <div class="card-header"><i class="fa-solid fa-filter"></i> 篩選條件</div>
                    <div class="card-body">
                        <form id="form-filter">
                            <div class="mb-3">
                                <label class="form-label">分類法</label>
                                <select name="filter_taxonomy_id" id="input-taxonomy-id" class="form-select">
                                    <option value="">-- 全部 --</option>
                                    @foreach($taxonomies as $taxonomy)
                                    <option value="{{ $taxonomy->id }}" {{ $currentTaxonomyId == $taxonomy->id ? 'selected' : '' }}>{{ $taxonomy->name }} ({{ $taxonomy->code }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">代碼</label>
                                <input type="text" name="filter_code" value="{{ request('filter_code') }}" placeholder="代碼" id="input-code" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">狀態</label>
                                <select name="filter_is_active" id="input-is-active" class="form-select">
                                    <option value="">-- 全部 --</option>
                                    <option value="1" {{ request('filter_is_active') === '1' ? 'selected' : '' }}>啟用</option>
                                    <option value="0" {{ request('filter_is_active') === '0' ? 'selected' : '' }}>停用</option>
                                </select>
                            </div>
                            <div class="text-end">
                                <button type="reset" id="button-clear" class="btn btn-light"><i class="fa-solid fa-rotate"></i> 重設</button>
                                <button type="button" id="button-filter" class="btn btn-light"><i class="fa-solid fa-filter"></i> 篩選</button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- 快速導航：分類法列表 --}}
                <div class="card mt-3">
                    <div class="card-header"><i class="fa-solid fa-folder-tree"></i> 快速切換分類法</div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <a href="{{ route('lang.ocadmin.system.taxonomy.term.index') }}" class="list-group-item list-group-item-action {{ !$currentTaxonomyId ? 'active' : '' }}">
                                <i class="fa-solid fa-list"></i> 全部詞彙
                            </a>
                            @foreach($taxonomies as $taxonomy)
                            <a href="{{ route('lang.ocadmin.system.taxonomy.term.index', ['filter_taxonomy_id' => $taxonomy->id]) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center {{ $currentTaxonomyId == $taxonomy->id ? 'active' : '' }}">
                                {{ $taxonomy->name }}
                                <span class="badge bg-secondary">{{ $taxonomy->terms->count() }}</span>
                            </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- 列表區塊 - 左側 --}}
            <div class="col-lg-9 col-md-12">
                <div class="card">
                    <div class="card-header">
                        <i class="fa-solid fa-tags"></i>
                        @if(isset($currentTaxonomy))
                            {{ $currentTaxonomy->name }} 詞彙列表
                        @else
                            詞彙列表
                        @endif
                    </div>
                    <div id="term-list" class="card-body">
                        {!! $list !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script type="text/javascript">
$(document).ready(function() {
    // AJAX 分頁和排序
    $('#term-list').on('click', 'thead a, .pagination a', function(e) {
        e.preventDefault();
        var url = $(this).attr('href');
        var displayUrl = url.replace('/list', '');
        window.history.pushState({}, null, displayUrl);
        $('#term-list').load(url);
    });

    // 篩選按鈕
    $('#button-filter').on('click', function() {
        var url = '{{ route('lang.ocadmin.system.taxonomy.term.list') }}?';
        var params = [];

        var filter_taxonomy_id = $('#input-taxonomy-id').val();
        if (filter_taxonomy_id) {
            params.push('filter_taxonomy_id=' + encodeURIComponent(filter_taxonomy_id));
        }

        var filter_code = $('#input-code').val();
        if (filter_code) {
            params.push('filter_code=' + encodeURIComponent(filter_code));
        }

        var filter_is_active = $('#input-is-active').val();
        if (filter_is_active !== '') {
            params.push('filter_is_active=' + encodeURIComponent(filter_is_active));
        }

        url += params.join('&');

        var displayUrl = url.replace('/list', '');
        window.history.pushState({}, null, displayUrl);
        $('#term-list').load(url);
    });

    // 重設按鈕
    $('#button-clear').on('click', function() {
        setTimeout(function() {
            $('#button-filter').click();
        }, 10);
    });

    // 批次刪除
    $('#button-delete').on('click', function() {
        var selected = [];
        $('input[name*=\'selected\']:checked').each(function() {
            selected.push($(this).val());
        });

        if (selected.length === 0) {
            alert('請選擇要刪除的項目');
            return;
        }

        if (confirm('確定要刪除選取的 ' + selected.length + ' 筆資料嗎？')) {
            $.ajax({
                url: '{{ route('lang.ocadmin.system.taxonomy.term.batch-delete') }}',
                type: 'POST',
                data: {
                    selected: selected,
                    _token: '{{ csrf_token() }}'
                },
                dataType: 'json',
                success: function(json) {
                    if (json.success) {
                        $('#button-filter').click();
                    } else {
                        alert(json.message || '刪除失敗');
                    }
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    alert('刪除失敗：' + thrownError);
                }
            });
        }
    });
});
</script>
@endsection
