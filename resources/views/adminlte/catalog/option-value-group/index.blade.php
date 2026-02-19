@extends('ocadmin::layouts.app')

@section('title', $lang->heading_title)

@section('content')
<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">{{ $lang->heading_title }}</h3>
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
            <button type="button" data-bs-toggle="tooltip" title="{{ $lang->button_filter }}" onclick="$('#filter-group').toggleClass('d-none');" class="btn btn-light d-lg-none">
                <i class="bi bi-funnel"></i>
            </button>
            <a href="{{ $url_create }}" data-bs-toggle="tooltip" title="{{ $lang->button_add }}" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i>
            </a>
            <button type="button" id="button-delete" data-bs-toggle="tooltip" title="{{ $lang->button_delete }}" class="btn btn-danger">
                <i class="bi bi-trash"></i>
            </button>
        </div>

        <div class="row">
            {{-- 篩選區塊 --}}
            <div id="filter-group" class="col-lg-3 col-md-12 order-lg-last d-none d-lg-block mb-3">
                <div class="card">
                    <div class="card-header"><i class="bi bi-funnel"></i> {{ $lang->text_filter }}</div>
                    <div class="card-body">
                        <form id="form-filter">
                            <div class="mb-3">
                                <label class="form-label">{{ $lang->column_name }}</label>
                                <input type="text" name="filter_name" value="{{ request('filter_name') }}" placeholder="{{ $lang->placeholder_name }}" id="input-name" class="form-control">
                            </div>
                            <div class="text-end">
                                <button type="reset" id="button-reset" class="btn btn-light"><i class="bi bi-arrow-clockwise"></i> {{ $lang->button_reset }}</button>
                                <button type="button" id="button-clear" class="btn btn-light"><i class="bi bi-eraser"></i> {{ $lang->button_clear }}</button>
                                <button type="button" id="button-filter" class="btn btn-light"><i class="bi bi-funnel"></i> {{ $lang->button_filter }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- 列表區塊 --}}
            <div class="col-lg-9 col-md-12">
                <div class="card">
                    <div class="card-header"><i class="bi bi-list-ul"></i> {{ $lang->text_list }}</div>
                    <div id="group-list" class="card-body">
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
    var url_list = '{{ $url_list }}';
    var url_batch_delete = '{{ $url_batch_delete }}';

    $('#group-list').on('click', 'thead a, .pagination a', function(e) {
        e.preventDefault();
        $('#group-list').load($(this).attr('href'));
    });

    // 篩選
    $('#button-filter').on('click', function() {
        var url = url_list + '?';
        var params = [];

        var v = $('#input-name').val();
        if (v) params.push('filter_name=' + encodeURIComponent(v));

        url += params.join('&');
        window.history.pushState({}, null, url.replace('/list?', '?'));
        $('#group-list').load(url);
    });

    // 重設
    $('#button-reset').on('click', function() {
        document.getElementById('form-filter').reset();
        $('#button-filter').trigger('click');
    });

    // 清除
    $('#button-clear').on('click', function() {
        $('#form-filter').find('input[type="text"]').val('');
        $('#button-filter').trigger('click');
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

        if (confirm('{{ $lang->text_confirm_batch_delete }}')) {
            $.ajax({
                url: url_batch_delete,
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
                    alert('{{ $lang->text_error_delete }}：' + thrownError);
                }
            });
        }
    });
});
</script>
@endsection
