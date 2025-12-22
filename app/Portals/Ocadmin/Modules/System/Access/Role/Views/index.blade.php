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
                <a href="{{ route('lang.ocadmin.system.access.role.create') }}" data-bs-toggle="tooltip" title="{{ $lang->button_add }}" class="btn btn-primary">
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
            <div id="filter-role" class="col-lg-3 col-md-12 order-lg-last d-none d-lg-block mb-3">
                <div class="card">
                    <div class="card-header"><i class="fa-solid fa-filter"></i> {{ $lang->text_filter }}</div>
                    <div class="card-body">
                        <form id="form-filter">
                            <div class="mb-3">
                                <label class="form-label">{{ $lang->text_search }}</label>
                                <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ $lang->placeholder_search }}" id="input-search" class="form-control">
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
    // AJAX 分頁和排序
    $('#role-list').on('click', 'thead a, .pagination a', function(e) {
        e.preventDefault();
        var url = $(this).attr('href');
        var displayUrl = url.replace('/list', '');
        window.history.pushState({}, null, displayUrl);
        $('#role-list').load(url);
    });

    // 篩選按鈕
    $('#button-filter').on('click', function() {
        var url = '{{ route('lang.ocadmin.system.access.role.list') }}?';
        var params = [];

        var search = $('#input-search').val();
        if (search) {
            params.push('search=' + encodeURIComponent(search));
        }

        url += params.join('&');

        var displayUrl = url.replace('/list', '');
        window.history.pushState({}, null, displayUrl);
        $('#role-list').load(url);
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
            alert('{{ $lang->error_select_required }}');
            return;
        }

        if (confirm('{{ $lang->text_confirm_delete }}')) {
            $.ajax({
                url: '{{ route('lang.ocadmin.system.access.role.batch-delete') }}',
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
                        alert(json.message || '{{ $lang->error_delete_failed }}');
                    }
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    alert('{{ $lang->error_delete_failed }}：' + thrownError);
                }
            });
        }
    });
});
</script>
@endsection
