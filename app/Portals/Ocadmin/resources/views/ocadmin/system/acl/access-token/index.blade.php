@extends('ocadmin::layouts.app')

@section('title', $lang->heading_title)

@section('content')
<div id="content">
    <div class="page-header">
        <div class="container-fluid">
            <div class="float-end">
                <button type="button" data-bs-toggle="tooltip" title="{{ $lang->button_filter }}" onclick="$('#filter-access-token').toggleClass('d-none');" class="btn btn-light d-lg-none">
                    <i class="fa-solid fa-filter"></i>
                </button>
                <a href="{{ $add_url }}" data-bs-toggle="tooltip" title="{{ $lang->text_add }}" class="btn btn-primary">
                    <i class="fa-solid fa-plus"></i>
                </a>
                <button type="button" id="button-revoke" data-bs-toggle="tooltip" title="{{ $lang->button_revoke }}" class="btn btn-danger">
                    <i class="fa-regular fa-trash-can"></i>
                </button>
            </div>
            <h1>{{ $lang->heading_title }}</h1>
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
            <div id="filter-access-token" class="col-lg-3 col-md-12 order-lg-last d-none d-lg-block mb-3">
                <div class="card">
                    <div class="card-header"><i class="fa-solid fa-filter"></i> {{ $lang->text_filter }}</div>
                    <div class="card-body">
                        <form id="form-filter">
                            <div class="mb-3">
                                <label class="form-label">{{ $lang->column_search }}</label>
                                <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ $lang->placeholder_search }}" id="input-search" class="form-control" autocomplete="off">
                            </div>
                            <div class="text-end">
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
                    <div id="access-token-list" class="card-body">
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
    var listUrl = '{{ $list_url }}';
    var indexUrl = '{{ $index_url }}';
    var revokeUrl = '{{ $revoke_url }}';

    // AJAX 分頁和排序
    $('#access-token-list').on('click', 'thead a, .pagination a', function(e) {
        e.preventDefault();
        var href = $(this).attr('href');
        $('#access-token-list').load(href);
        window.history.pushState({}, null, href.replace(/\/list\b/, ''));
    });

    // 篩選
    $('#button-filter').on('click', function() {
        var params = new URLSearchParams();

        var search = $('#input-search').val();
        if (search) params.set('search', search);

        var qs = params.toString() ? '?' + params.toString() : '';
        $('#access-token-list').load(listUrl + qs);
        window.history.pushState({}, null, indexUrl + qs);
    });

    // 清除
    $('#button-clear').on('click', function() {
        $('#form-filter').find('input[type="text"]').val('');
        var url = indexUrl;
        window.history.pushState({}, null, url);
        $('#access-token-list').load(listUrl + ' #access-token-list > *');
    });

    // 撤銷
    $('#button-revoke').on('click', function() {
        var selected = [];
        $('input[name*=\'selected\']:checked').each(function() {
            selected.push($(this).val());
        });

        if (selected.length === 0) {
            alert('{{ $lang->error_select_revoke }}');
            return;
        }

        var msg = '{{ $lang->text_confirm_revoke }}'.replace('%s', selected.length);
        if (confirm(msg)) {
            $.ajax({
                url: revokeUrl,
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
                        alert(json.message || '{{ $lang->text_error_revoke }}');
                    }
                },
                error: function(xhr) {
                    alert('{{ $lang->text_error_revoke }}' + ': ' + (xhr.responseJSON?.error || xhr.statusText));
                }
            });
        }
    });
});
</script>
@endsection
