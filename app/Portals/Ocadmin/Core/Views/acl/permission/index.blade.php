@extends('ocadmin::layouts.app')

@section('title', '權限管理')

@section('content')
<div id="content">
    <div class="page-header">
        <div class="container-fluid">
            <div class="float-end">
                <button type="button" data-bs-toggle="tooltip" title="篩選" onclick="$('#filter-permission').toggleClass('d-none');" class="btn btn-light d-lg-none">
                    <i class="fa-solid fa-filter"></i>
                </button>
                <a href="{{ route('lang.ocadmin.system.permission.create') }}" data-bs-toggle="tooltip" title="新增" class="btn btn-primary">
                    <i class="fa-solid fa-plus"></i>
                </a>
                <button type="button" id="button-delete" data-bs-toggle="tooltip" title="刪除" class="btn btn-danger">
                    <i class="fa-regular fa-trash-can"></i>
                </button>
            </div>
            <h1>權限管理</h1>
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
            <div id="filter-permission" class="col-lg-3 col-md-12 order-lg-last d-none d-lg-block mb-3">
                <div class="card">
                    <div class="card-header"><i class="fa-solid fa-filter"></i> 篩選條件</div>
                    <div class="card-body">
                        <form id="form-filter">
                            <div class="mb-3">
                                <label class="form-label">權限代碼</label>
                                <input type="text" name="filter_name" value="{{ request('filter_name') }}" placeholder="如 mss.employee.list" id="input-name" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">顯示名稱</label>
                                <input type="text" name="filter_display_name" value="{{ request('filter_display_name') }}" placeholder="顯示名稱" id="input-display-name" class="form-control">
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
                    <div class="card-header"><i class="fa-solid fa-list"></i> 權限列表</div>
                    <div id="permission-list" class="card-body">
                        @include('ocadmin::acl.permission.list')
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
    $('#permission-list').on('click', 'thead a, .pagination a', function(e) {
        e.preventDefault();
        $('#permission-list').load($(this).attr('href') + ' #permission-list > *');
    });

    $('#button-filter').on('click', function() {
        var url = '{{ route('lang.ocadmin.system.permission.index') }}?';
        var params = [];

        var v = $('#input-name').val();
        if (v) params.push('filter_name=' + encodeURIComponent(v));

        v = $('#input-display-name').val();
        if (v) params.push('filter_display_name=' + encodeURIComponent(v));

        url += params.join('&');
        window.history.pushState({}, null, url);
        $('#permission-list').load(url + ' #permission-list > *');
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
                url: '{{ route('lang.ocadmin.system.permission.batch-delete') }}',
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
