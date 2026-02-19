@extends('ocadmin::layouts.app')

@section('title', '詞彙項目')

@section('content')
<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">詞彙項目</h3>
            </div>
            <div class="col-sm-6">
                @include('ocadmin::layouts.partials.breadcrumb')
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="mb-3 text-end">
            <button type="button" data-bs-toggle="tooltip" title="篩選" onclick="$('#filter-term').toggleClass('d-none');" class="btn btn-light d-lg-none">
                <i class="bi bi-funnel"></i>
            </button>
            <a href="{{ route('lang.ocadmin.config.term.create', request()->only('filter_taxonomy_id') ? ['taxonomy_id' => request('filter_taxonomy_id')] : []) }}" data-bs-toggle="tooltip" title="新增" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i>
            </a>
            <button type="button" id="button-delete" data-bs-toggle="tooltip" title="刪除" class="btn btn-danger">
                <i class="bi bi-trash"></i>
            </button>
        </div>

        <div class="row">
            {{-- 篩選區塊 --}}
            <div id="filter-term" class="col-lg-3 col-md-12 order-lg-last d-none d-lg-block mb-3">
                <div class="card">
                    <div class="card-header"><i class="bi bi-funnel"></i> 篩選條件</div>
                    <div class="card-body">
                        <form id="form-filter">
                            <div class="mb-3">
                                <label class="form-label">分類</label>
                                <select name="filter_taxonomy_id" id="input-taxonomy" class="form-select">
                                    <option value="">-- 全部 --</option>
                                    @foreach($taxonomies as $taxonomy)
                                    <option value="{{ $taxonomy->id }}" {{ request('filter_taxonomy_id') == $taxonomy->id ? 'selected' : '' }}>{{ $taxonomy->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">代碼</label>
                                <input type="text" name="filter_code" value="{{ request('filter_code') }}" placeholder="代碼" id="input-code" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">名稱</label>
                                <input type="text" name="filter_name" value="{{ request('filter_name') }}" placeholder="名稱" id="input-name" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">狀態</label>
                                <select name="equal_is_active" id="input-is-active" class="form-select">
                                    <option value="">-- 全部 --</option>
                                    <option value="1" {{ request('equal_is_active', '1') === '1' ? 'selected' : '' }}>啟用</option>
                                    <option value="0" {{ request('equal_is_active') === '0' ? 'selected' : '' }}>停用</option>
                                </select>
                            </div>
                            <div class="text-end">
                                <button type="reset" id="button-reset" class="btn btn-light"><i class="bi bi-arrow-clockwise"></i> 重設</button>
                                <button type="button" id="button-clear" class="btn btn-light"><i class="bi bi-eraser"></i> 清除</button>
                                <button type="button" id="button-filter" class="btn btn-light"><i class="bi bi-funnel"></i> 篩選</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- 列表區塊 --}}
            <div class="col-lg-9 col-md-12">
                <div class="card">
                    <div class="card-header"><i class="bi bi-tags"></i> 詞彙列表</div>
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
    $('#term-list').on('click', 'thead a, .pagination a', function(e) {
        e.preventDefault();
        $('#term-list').load($(this).attr('href'));
    });

    // 篩選
    $('#button-filter').on('click', function() {
        var url = '{{ route('lang.ocadmin.config.term.list') }}?';
        var params = [];

        var v = $('#input-taxonomy').val();
        if (v) params.push('filter_taxonomy_id=' + v);

        v = $('#input-code').val();
        if (v) params.push('filter_code=' + encodeURIComponent(v));

        v = $('#input-name').val();
        if (v) params.push('filter_name=' + encodeURIComponent(v));

        params.push('equal_is_active=' + encodeURIComponent($('#input-is-active').val()));

        url += params.join('&');
        window.history.pushState({}, null, url.replace('/list?', '?'));
        $('#term-list').load(url);
    });

    // 重設
    $('#button-reset').on('click', function() {
        document.getElementById('form-filter').reset();
        $('#button-filter').trigger('click');
    });

    // 清除
    $('#button-clear').on('click', function() {
        $('#form-filter').find('input[type="text"]').val('');
        $('#form-filter').find('select').val('');
        $('#button-filter').trigger('click');
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
                url: '{{ route('lang.ocadmin.config.term.batch-delete') }}',
                type: 'POST',
                data: { selected: selected, _token: '{{ csrf_token() }}' },
                dataType: 'json',
                success: function(json) {
                    if (json.success) {
                        location.reload();
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
