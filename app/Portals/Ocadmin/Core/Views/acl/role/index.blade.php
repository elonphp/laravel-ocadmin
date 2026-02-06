@extends('ocadmin::layouts.app')

@section('title', $lang->heading_title)

@section('content')
<div id="content">
    <div class="page-header">
        <div class="container-fluid">
            <div class="float-end">
                <button type="button" data-bs-toggle="tooltip" title="{{ $lang->button_filter }}" onclick="$('#filter-role').toggleClass('d-none');" class="btn btn-light d-lg-none">
                    <i class="fa-solid fa-filter"></i>
                </button>
                <a href="{{ route('lang.ocadmin.system.role.create') }}" data-bs-toggle="tooltip" title="{{ $lang->button_add }}" class="btn btn-primary">
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
        <div class="row">
            {{-- 篩選區塊 --}}
            <div id="filter-role" class="col-lg-3 col-md-12 order-lg-last d-none d-lg-block mb-3">
                <div class="card">
                    <div class="card-header"><i class="fa-solid fa-filter"></i> {{ $lang->text_filter }}</div>
                    <div class="card-body">
                        <form id="form-filter">
                            <div class="mb-3">
                                <label class="form-label">{{ $lang->column_search }}</label>
                                <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ $lang->placeholder_search }}" id="input-search" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">{{ $lang->column_name }}</label>
                                <input type="text" name="filter_name" value="{{ request('filter_name') }}" placeholder="{{ $lang->placeholder_name }}" id="input-filter-name" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">{{ $lang->column_display_name }}</label>
                                <input type="text" name="filter_display_name" value="{{ request('filter_display_name') }}" placeholder="{{ $lang->column_display_name }}" id="input-filter-display-name" class="form-control">
                            </div>
                            <div class="text-end">
                                <button type="reset" id="button-reset" class="btn btn-light"><i class="fa-solid fa-rotate"></i> {{ $lang->button_reset }}</button>
                                <button type="button" id="button-clear" class="btn btn-light"><i class="fa-solid fa-eraser"></i> {{ $lang->button_clear }}</button>
                                <button type="button" id="button-filter" class="btn btn-light"><i class="fa-solid fa-filter"></i> {{ $lang->button_filter }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- 列表區塊 --}}
            <div class="col-lg-9 col-md-12">
                <div class="card">
                    <div class="card-header"><i class="fa-solid fa-list"></i> {{ $lang->text_list }}</div>
                    <div id="role-list" class="card-body">
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
    // AJAX 分頁 & 排序
    $('#role-list').on('click', 'thead a, .pagination a', function(e) {
        e.preventDefault();
        $('#role-list').load($(this).attr('href'));
    });

    // 篩選
    $('#button-filter').on('click', function() {
        var url = '{{ route('lang.ocadmin.system.role.list') }}?';
        var params = [];

        var v = $('#input-search').val();
        if (v) params.push('search=' + encodeURIComponent(v));

        v = $('#input-filter-name').val();
        if (v) params.push('filter_name=' + encodeURIComponent(v));

        v = $('#input-filter-display-name').val();
        if (v) params.push('filter_display_name=' + encodeURIComponent(v));

        url += params.join('&');
        window.history.pushState({}, null, url.replace('/list?', '?'));
        $('#role-list').load(url);
    });

    // 重設（恢復預設篩選條件）
    $('#button-reset').on('click', function() {
        setTimeout(function() { $('#button-filter').trigger('click'); }, 10);
    });

    // 清除（移除所有篩選條件）
    $('#button-clear').on('click', function() {
        $('#form-filter').find('input[type="text"]').val('');
        $('#form-filter').find('select').each(function() { $(this).prop('selectedIndex', 0); });
        var url = '{{ route('lang.ocadmin.system.role.list') }}';
        window.history.pushState({}, null, '{{ route('lang.ocadmin.system.role.index') }}');
        $('#role-list').load(url);
    });

    // 批次刪除
    $('#button-delete').on('click', function() {
        var selected = [];
        $('input[name*=\'selected\']:checked').each(function() {
            selected.push($(this).val());
        });

        if (selected.length === 0) {
            alert('{{ $lang->error_select_delete }}');
            return;
        }

        if (confirm('{{ $lang->text_confirm_batch_delete }}'.replace('%s', selected.length))) {
            $.ajax({
                url: '{{ route('lang.ocadmin.system.role.batch-delete') }}',
                type: 'POST',
                data: { selected: selected, _token: '{{ csrf_token() }}' },
                dataType: 'json',
                success: function(json) {
                    if (json.success) {
                        location.reload();
                    } else {
                        alert(json.message || '{{ $lang->text_error_delete }}');
                    }
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    alert('{{ $lang->text_error_delete }}' + '：' + thrownError);
                }
            });
        }
    });
});
</script>
@endsection
