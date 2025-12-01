@extends('ocadmin::layouts.app')

@section('title', '帳號管理')

@section('content')
<div id="content">
    <div class="page-header">
        <div class="container-fluid">
            <div class="float-end">
                <button type="button" data-bs-toggle="tooltip" title="篩選" onclick="$('#filter-account').toggleClass('d-none');" class="btn btn-light d-lg-none">
                    <i class="fa-solid fa-filter"></i>
                </button>
                <a href="{{ route('lang.ocadmin.account.account.create') }}" data-bs-toggle="tooltip" title="新增" class="btn btn-primary">
                    <i class="fa-solid fa-plus"></i>
                </a>
                <button type="button" id="button-delete" data-bs-toggle="tooltip" title="刪除" class="btn btn-danger">
                    <i class="fa-regular fa-trash-can"></i>
                </button>
            </div>
            <h1>帳號管理</h1>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('lang.ocadmin.dashboard') }}">首頁</a></li>
                <li class="breadcrumb-item"><a href="#">帳號管理</a></li>
                <li class="breadcrumb-item active">帳號</li>
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
            <div id="filter-account" class="col-lg-3 col-md-12 order-lg-last d-none d-lg-block mb-3">
                <div class="card">
                    <div class="card-header"><i class="fa-solid fa-filter"></i> 篩選條件</div>
                    <div class="card-body">
                        <form id="form-filter">
                            <div class="mb-3">
                                <label class="form-label">帳號</label>
                                <input type="text" name="filter_username" value="{{ request('filter_username') }}" placeholder="帳號" id="input-username" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">姓名</label>
                                <input type="text" name="filter_name" value="{{ request('filter_name') }}" placeholder="姓名" id="input-name" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="text" name="filter_email" value="{{ request('filter_email') }}" placeholder="Email" id="input-email" class="form-control">
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
                    <div class="card-header"><i class="fa-solid fa-list"></i> 帳號列表</div>
                    <div id="account-list" class="card-body">
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
    $('#account-list').on('click', 'thead a, .pagination a', function(e) {
        e.preventDefault();
        var url = $(this).attr('href');
        var displayUrl = url.replace('/list', '');
        window.history.pushState({}, null, displayUrl);
        $('#account-list').load(url);
    });

    // 篩選按鈕
    $('#button-filter').on('click', function() {
        var url = '{{ route('lang.ocadmin.account.account.list') }}?';
        var params = [];

        var filter_username = $('#input-username').val();
        if (filter_username) {
            params.push('filter_username=' + encodeURIComponent(filter_username));
        }

        var filter_name = $('#input-name').val();
        if (filter_name) {
            params.push('filter_name=' + encodeURIComponent(filter_name));
        }

        var filter_email = $('#input-email').val();
        if (filter_email) {
            params.push('filter_email=' + encodeURIComponent(filter_email));
        }

        var equal_is_active = $('#input-is-active').val();
        if (equal_is_active !== '*') {
            params.push('equal_is_active=' + encodeURIComponent(equal_is_active));
        }

        url += params.join('&');

        var displayUrl = url.replace('/list', '');
        window.history.pushState({}, null, displayUrl);
        $('#account-list').load(url);
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
                url: '{{ route('lang.ocadmin.account.account.batch-delete') }}',
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
