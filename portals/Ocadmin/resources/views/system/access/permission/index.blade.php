@extends('ocadmin::layouts.app')

@section('title', $lang->heading_title)

@section('content')
<div id="content">
    <div class="page-header">
        <div class="container-fluid">
            <div class="float-end">
                <button type="button" data-bs-toggle="tooltip" title="{{ $lang->button_filter }}" onclick="$('#filter-permission').toggleClass('d-none');" class="btn btn-light d-lg-none">
                    <i class="fa-solid fa-filter"></i>
                </button>
                <a href="{{ route('lang.ocadmin.system.access.permission.create') }}" data-bs-toggle="tooltip" title="{{ $lang->button_add }}" class="btn btn-primary">
                    <i class="fa-solid fa-plus"></i>
                </a>
                <button type="button" id="button-delete" data-bs-toggle="tooltip" title="{{ $lang->button_delete }}" class="btn btn-danger">
                    <i class="fa-regular fa-trash-can"></i>
                </button>
            </div>
            <h1>{{ $lang->heading_title }}</h1>
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
            <div id="filter-permission" class="col-lg-3 col-md-12 order-lg-last d-none d-lg-block mb-3">
                <div class="card">
                    <div class="card-header"><i class="fa-solid fa-filter"></i> {{ $lang->text_filter }}</div>
                    <div class="card-body">
                        <form id="form-filter">
                            <div class="mb-3">
                                <label class="form-label">{{ $lang->text_search }}</label>
                                <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ $lang->placeholder_search }}" id="input-search" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">{{ $lang->column_type }}</label>
                                <select name="equal_type" id="input-type" class="form-select">
                                    <option value="*">-- {{ $lang->text_all }} --</option>
                                    <option value="menu" {{ request('equal_type') === 'menu' ? 'selected' : '' }}>{{ $lang->text_type_menu }}</option>
                                    <option value="action" {{ request('equal_type') === 'action' ? 'selected' : '' }}>{{ $lang->text_type_action }}</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">{{ $lang->column_parent }}</label>
                                <select name="equal_parent_id" id="input-parent-id" class="form-select">
                                    <option value="*">-- {{ $lang->text_all }} --</option>
                                    <option value="0" {{ request('equal_parent_id') === '0' ? 'selected' : '' }}>{{ $lang->text_top_level }}</option>
                                </select>
                            </div>
                            <div class="text-end">
                                <button type="reset" id="button-clear" class="btn btn-light"><i class="fa-solid fa-rotate"></i> {{ $lang->button_reset }}</button>
                                <button type="button" id="button-filter" class="btn btn-light"><i class="fa-solid fa-filter"></i> {{ $lang->button_filter }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- 列表區塊 - 左側 --}}
            <div class="col-lg-9 col-md-12">
                <div class="card">
                    <div class="card-header"><i class="fa-solid fa-list"></i> {{ $lang->text_list }}</div>
                    <div id="permission-list" class="card-body">
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
    $('#permission-list').on('click', 'thead a, .pagination a', function(e) {
        e.preventDefault();
        var url = $(this).attr('href');
        // 更新瀏覽器 URL（將 /list 替換為 index 路由）
        var displayUrl = url.replace('/list', '');
        window.history.pushState({}, null, displayUrl);
        // AJAX 載入表格內容
        $('#permission-list').load(url);
    });

    // 篩選按鈕
    $('#button-filter').on('click', function() {
        var url = '{{ route('lang.ocadmin.system.access.permission.list') }}?';
        var params = [];

        var search = $('#input-search').val();
        if (search) {
            params.push('search=' + encodeURIComponent(search));
        }

        var equal_type = $('#input-type').val();
        if (equal_type !== '*') {
            params.push('equal_type=' + encodeURIComponent(equal_type));
        }

        var equal_parent_id = $('#input-parent-id').val();
        if (equal_parent_id !== '*') {
            params.push('equal_parent_id=' + encodeURIComponent(equal_parent_id));
        }

        url += params.join('&');

        // 更新瀏覽器 URL（將 /list 替換為 index 路由）
        var displayUrl = url.replace('/list', '');
        window.history.pushState({}, null, displayUrl);
        // AJAX 載入表格內容
        $('#permission-list').load(url);
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
            alert('{{ $lang->error_select_required }}');
            return;
        }

        if (confirm('{{ $lang->text_confirm_delete }}')) {
            $.ajax({
                url: '{{ route('lang.ocadmin.system.access.permission.batch-delete') }}',
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
                        alert(json.message || '{{ $lang->error_delete_failed }}');
                    }
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    alert('{{ $lang->error_delete_failed }}：' + thrownError);
                }
            });
        }
    });

    // 載入父層選項
    loadParentOptions();

    function loadParentOptions() {
        $.ajax({
            url: '{{ route('lang.ocadmin.system.access.permission.all') }}?type=menu',
            type: 'GET',
            dataType: 'json',
            success: function(permissions) {
                var currentParentId = '{{ request('equal_parent_id') }}';
                permissions.forEach(function(permission) {
                    if (permission.parent_id === null) {
                        var selected = currentParentId == permission.id ? 'selected' : '';
                        var title = permission.title || permission.name;
                        $('#input-parent-id').append('<option value="' + permission.id + '" ' + selected + '>' + title + '</option>');
                    }
                });
            }
        });
    }
});
</script>
@endsection
