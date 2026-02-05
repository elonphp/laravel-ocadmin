@extends('ocadmin::layouts.app')

@section('title', '分類管理')

@section('content')
<div id="content">
    <div class="page-header">
        <div class="container-fluid">
            <div class="float-end">
                <button type="button" data-bs-toggle="tooltip" title="篩選" onclick="$('#filter-taxonomy').toggleClass('d-none');" class="btn btn-light d-lg-none">
                    <i class="fa-solid fa-filter"></i>
                </button>
                <a href="{{ route('lang.ocadmin.config.taxonomy.create') }}" data-bs-toggle="tooltip" title="新增" class="btn btn-primary">
                    <i class="fa-solid fa-plus"></i>
                </a>
                <button type="button" id="button-delete" data-bs-toggle="tooltip" title="刪除" class="btn btn-danger">
                    <i class="fa-regular fa-trash-can"></i>
                </button>
            </div>
            <h1>分類管理</h1>
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
            {{-- 篩選區塊 --}}
            <div id="filter-taxonomy" class="col-lg-3 col-md-12 order-lg-last d-none d-lg-block mb-3">
                <div class="card">
                    <div class="card-header"><i class="fa-solid fa-filter"></i> 篩選條件</div>
                    <div class="card-body">
                        <form id="form-filter">
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
                                <select name="filter_is_active" id="input-is-active" class="form-select">
                                    <option value="">-- 全部 --</option>
                                    <option value="1" {{ request('filter_is_active') === '1' ? 'selected' : '' }}>啟用</option>
                                    <option value="0" {{ request('filter_is_active') === '0' ? 'selected' : '' }}>停用</option>
                                </select>
                            </div>
                            <div class="text-end">
                                <button type="button" id="button-filter" class="btn btn-light"><i class="fa-solid fa-filter"></i> 篩選</button>
                                <button type="reset" id="button-clear" class="btn btn-light"><i class="fa-solid fa-rotate"></i> 重設</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- 列表區塊 --}}
            <div class="col-lg-9 col-md-12">
                <div class="card">
                    <div class="card-header"><i class="fa-solid fa-list"></i> 分類列表</div>
                    <div id="taxonomy-list" class="card-body">
                        @include('ocadmin::config.taxonomy.list')
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
    $('#taxonomy-list').on('click', 'thead a, .pagination a', function(e) {
        e.preventDefault();
        $('#taxonomy-list').load($(this).attr('href') + ' #taxonomy-list > *');
    });

    $('#button-filter').on('click', function() {
        var url = '{{ route('lang.ocadmin.config.taxonomy.index') }}?';
        var params = [];

        var v = $('#input-code').val();
        if (v) params.push('filter_code=' + encodeURIComponent(v));

        v = $('#input-name').val();
        if (v) params.push('filter_name=' + encodeURIComponent(v));

        v = $('#input-is-active').val();
        if (v !== '') params.push('filter_is_active=' + v);

        url += params.join('&');
        window.history.pushState({}, null, url);
        $('#taxonomy-list').load(url + ' #taxonomy-list > *');
    });

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
                url: '{{ route('lang.ocadmin.config.taxonomy.batch-delete') }}',
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
