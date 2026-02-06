@extends('ocadmin::layouts.app')

@section('title', '參數設定')

@section('content')
<div id="content">
    <div class="page-header">
        <div class="container-fluid">
            <div class="float-end">
                <button type="button" data-bs-toggle="tooltip" title="篩選" onclick="$('#filter-setting').toggleClass('d-none');" class="btn btn-light d-lg-none">
                    <i class="fa-solid fa-filter"></i>
                </button>
                <a href="{{ route('lang.ocadmin.system.setting.create') }}" data-bs-toggle="tooltip" title="新增" class="btn btn-primary">
                    <i class="fa-solid fa-plus"></i>
                </a>
                <button type="button" id="button-delete" data-bs-toggle="tooltip" title="刪除" class="btn btn-danger">
                    <i class="fa-regular fa-trash-can"></i>
                </button>
            </div>
            <h1>參數設定</h1>
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
            <div id="filter-setting" class="col-lg-3 col-md-12 order-lg-last d-none d-lg-block mb-3">
                <div class="card">
                    <div class="card-header"><i class="fa-solid fa-filter"></i> 篩選條件</div>
                    <div class="card-body">
                        <form id="form-filter">
                            <div class="mb-3">
                                <label class="form-label">代碼</label>
                                <input type="text" name="filter_code" value="{{ request('filter_code') }}" placeholder="代碼" id="input-code" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">群組</label>
                                <input type="text" name="filter_group" value="{{ request('filter_group') }}" placeholder="群組" id="input-group" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">類型</label>
                                <select name="filter_type" id="input-type" class="form-select">
                                    <option value="">-- 全部 --</option>
                                    @foreach($types as $type)
                                    <option value="{{ $type->value }}" {{ request('filter_type') === $type->value ? 'selected' : '' }}>{{ $type->label() }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="text-end">
                                <button type="reset" id="button-reset" class="btn btn-light"><i class="fa-solid fa-rotate"></i> 重設</button>
                                <button type="button" id="button-clear" class="btn btn-light"><i class="fa-solid fa-eraser"></i> 清除</button>
                                <button type="button" id="button-filter" class="btn btn-light"><i class="fa-solid fa-filter"></i> 篩選</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- 列表區塊 - 左側 --}}
            <div class="col-lg-9 col-md-12">
                <div class="card">
                    <div class="card-header"><i class="fa-solid fa-list"></i> 參數列表</div>
                    <div id="setting-list" class="card-body">
                        @include('ocadmin::system.setting.list')
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
    $('#setting-list').on('click', 'thead a, .pagination a', function(e) {
        e.preventDefault();
        $('#setting-list').load($(this).attr('href') + ' #setting-list > *');
    });

    // 篩選按鈕
    $('#button-filter').on('click', function() {
        var url = '{{ route('lang.ocadmin.system.setting.index') }}?';
        var params = [];

        var filter_code = $('#input-code').val();
        if (filter_code) {
            params.push('filter_code=' + encodeURIComponent(filter_code));
        }

        var filter_group = $('#input-group').val();
        if (filter_group) {
            params.push('filter_group=' + encodeURIComponent(filter_group));
        }

        var filter_type = $('#input-type').val();
        if (filter_type) {
            params.push('filter_type=' + encodeURIComponent(filter_type));
        }

        url += params.join('&');

        window.history.pushState({}, null, url);
        $('#setting-list').load(url + ' #setting-list > *');
    });

    // 重設（恢復預設篩選條件）
    $('#button-reset').on('click', function() {
        setTimeout(function() { $('#button-filter').trigger('click'); }, 10);
    });

    // 清除（移除所有篩選條件）
    $('#button-clear').on('click', function() {
        $('#form-filter').find('input[type="text"]').val('');
        $('#form-filter').find('select').each(function() { $(this).prop('selectedIndex', 0); });
        var url = '{{ route('lang.ocadmin.system.setting.index') }}';
        window.history.pushState({}, null, url);
        $('#setting-list').load(url + ' #setting-list > *');
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
                url: '{{ route('lang.ocadmin.system.setting.batch-delete') }}',
                type: 'POST',
                data: {
                    selected: selected,
                    _token: '{{ csrf_token() }}'
                },
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
