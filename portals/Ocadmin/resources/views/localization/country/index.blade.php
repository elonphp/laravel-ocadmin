@extends('ocadmin::layouts.app')

@section('title', '國家管理')

@section('content')
<div id="content">
    <div class="page-header">
        <div class="container-fluid">
            <div class="float-end">
                <button type="button" data-bs-toggle="tooltip" title="篩選" onclick="$('#filter-country').toggleClass('d-none');" class="btn btn-light d-lg-none">
                    <i class="fa-solid fa-filter"></i>
                </button>
                <a href="{{ route('ocadmin.localization.country.create') }}" data-bs-toggle="tooltip" title="新增" class="btn btn-primary">
                    <i class="fa-solid fa-plus"></i>
                </a>
                <button type="button" id="button-delete" data-bs-toggle="tooltip" title="刪除" class="btn btn-danger">
                    <i class="fa-regular fa-trash-can"></i>
                </button>
            </div>
            <h1>國家管理</h1>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('ocadmin.dashboard') }}">首頁</a></li>
                <li class="breadcrumb-item"><a href="#">系統管理</a></li>
                <li class="breadcrumb-item"><a href="#">本地化設定</a></li>
                <li class="breadcrumb-item active">國家管理</li>
            </ol>
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
            <div id="filter-country" class="col-lg-3 col-md-12 order-lg-last d-none d-lg-block mb-3">
                <div class="card">
                    <div class="card-header"><i class="fa-solid fa-filter"></i> 篩選條件</div>
                    <div class="card-body">
                        <form id="form-filter">
                            <div class="mb-3">
                                <label class="form-label">國家名稱</label>
                                <input type="text" name="filter_name" value="{{ request('filter_name') }}" placeholder="國家名稱" id="input-name" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">ISO 代碼 (2)</label>
                                <input type="text" name="filter_iso_code_2" value="{{ request('filter_iso_code_2') }}" placeholder="ISO 代碼 (2)" id="input-iso-code-2" class="form-control" maxlength="2">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">狀態</label>
                                <select name="equal_is_active" id="input-is-active" class="form-select">
                                    <option value="*">-- 全部 --</option>
                                    <option value="1" {{ request('equal_is_active') === '1' ? 'selected' : '' }}>啟用</option>
                                    <option value="0" {{ request('equal_is_active') === '0' ? 'selected' : '' }}>停用</option>
                                </select>
                            </div>
                            <div class="text-end">
                                <button type="reset" id="button-clear" class="btn btn-light"><i class="fa-solid fa-rotate"></i> 重設</button>
                                <button type="button" id="button-filter" class="btn btn-light"><i class="fa-solid fa-filter"></i> 篩選</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- 列表區塊 - 左側 --}}
            <div class="col-lg-9 col-md-12">
                <div class="card">
                    <div class="card-header"><i class="fa-solid fa-list"></i> 國家列表</div>
                    <div id="country-list" class="card-body">
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
    $('#country-list').on('click', 'thead a, .pagination a', function(e) {
        e.preventDefault();
        var url = $(this).attr('href');
        // 更新瀏覽器 URL（將 /list 替換為 index 路由）
        var displayUrl = url.replace('/list', '');
        window.history.pushState({}, null, displayUrl);
        // AJAX 載入表格內容
        $('#country-list').load(url);
    });

    // 篩選按鈕
    $('#button-filter').on('click', function() {
        var url = '{{ route('ocadmin.localization.country.list') }}?';
        var params = [];

        var filter_name = $('#input-name').val();
        if (filter_name) {
            params.push('filter_name=' + encodeURIComponent(filter_name));
        }

        var filter_iso_code_2 = $('#input-iso-code-2').val();
        if (filter_iso_code_2) {
            params.push('filter_iso_code_2=' + encodeURIComponent(filter_iso_code_2));
        }

        var equal_is_active = $('#input-is-active').val();
        if (equal_is_active !== '*') {
            params.push('equal_is_active=' + encodeURIComponent(equal_is_active));
        }

        url += params.join('&');

        // 更新瀏覽器 URL（將 /list 替換為 index 路由）
        var displayUrl = url.replace('/list', '');
        window.history.pushState({}, null, displayUrl);
        // AJAX 載入表格內容
        $('#country-list').load(url);
    });

    // 重設按鈕
    $('#button-clear').on('click', function() {
        // 清空表單後觸發篩選
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
                url: '{{ route('ocadmin.localization.country.batch-delete') }}',
                type: 'POST',
                data: {
                    selected: selected,
                    _token: '{{ csrf_token() }}'
                },
                dataType: 'json',
                success: function(json) {
                    if (json.success) {
                        // 刪除成功後重新載入列表
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
