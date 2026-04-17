@extends('ocadmin::layouts.app')

@section('title', $lang->heading_title)

@section('styles')
<style>
.select2-container .select2-selection--single { height: 100% !important; }
</style>
@endsection

@section('content')
<div id="content">
    <div class="page-header">
        <div class="container-fluid">
            <div class="float-end">
                <button type="button" data-bs-toggle="tooltip" title="{{ $lang->button_filter }}" onclick="$('#filter-user').toggleClass('d-none');" class="btn btn-light d-lg-none">
                    <i class="fa-solid fa-filter"></i>
                </button>
                <a href="{{ $add_url }}" data-bs-toggle="tooltip" title="{{ $lang->button_add }}" class="btn btn-primary">
                    <i class="fa-solid fa-plus"></i>
                </a>
                <button type="button" id="button-delete" data-bs-toggle="tooltip" title="{{ $lang->button_delete }}" class="btn btn-danger">
                    <i class="fa-regular fa-trash-can"></i>
                </button>
            </div>
            <h1>{{ $lang->heading_title }}</h1>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            {{-- 篩選區塊 --}}
            <div id="filter-user" class="col-lg-3 col-md-12 order-lg-last d-none d-lg-block mb-3">
                <div class="card">
                    <div class="card-header"><i class="fa-solid fa-filter"></i> {{ $lang->text_filter }}</div>
                    <div class="card-body">
                        <form id="form-filter">
                            <div class="mb-3">
                                <label class="form-label">{{ $lang->column_search }}</label>
                                <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ $lang->placeholder_search }}" id="input-search" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">{{ $lang->column_roles }}</label>
                                <select name="filter_role_id" id="input-role" class="form-select">
                                    <option value="">-- {{ $lang->text_all }} --</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Portal</label>
                                <select name="filter_portal" id="input-portal" class="form-select">
                                    <option value="*">-- {{ $lang->text_all }} --</option>
                                    @foreach($portal_options as $value => $label)
                                    <option value="{{ $value }}" {{ request('filter_portal') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">{{ $lang->column_is_active }}</label>
                                <select name="equal_is_active" id="input-is-active" class="form-select">
                                    <option value="*">-- {{ $lang->text_all }} --</option>
                                    <option value="1" {{ request('equal_is_active', '1') == '1' ? 'selected' : '' }}>{{ $lang->text_yes }}</option>
                                    <option value="0" {{ request('equal_is_active') === '0' ? 'selected' : '' }}>{{ $lang->text_no }}</option>
                                </select>
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
                    <div id="user-list" class="card-body">
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
    var batchDeleteUrl = '{{ $batch_delete_url }}';

    // Select2 角色搜尋
    $('#input-role').select2({
        placeholder: '-- {{ $lang->text_all }} --',
        allowClear: true,
        width: '100%',
        ajax: {
            url: '{{ $role_search_url }}',
            dataType: 'json',
            delay: 250,
            data: function(params) { return { q: params.term }; },
            processResults: function(data) { return { results: data }; }
        }
    });

    // AJAX 分頁 & 排序
    $('#user-list').on('click', 'thead a, .pagination a', function(e) {
        e.preventDefault();
        var href = $(this).attr('href');
        $('#user-list').load(href);
        window.history.pushState({}, null, href.replace(/\/list\b/, ''));
    });

    // 篩選
    $('#button-filter').on('click', function() {
        var params = new URLSearchParams();

        var search = $('#input-search').val();
        if (search) params.set('search', search);

        var portal = $('#input-portal').val();
        if (portal && portal !== '*') params.set('filter_portal', portal);

        var roleId = $('#input-role').val();
        if (roleId) params.set('filter_role_id', roleId);

        var is_active = $('#input-is-active').val();
        if (is_active !== null && is_active !== '') params.set('equal_is_active', is_active);

        var qs = params.toString() ? '?' + params.toString() : '';
        $('#user-list').load(listUrl + qs);
        window.history.pushState({}, null, indexUrl + qs);
    });

    // 重設（恢復預設篩選條件）
    $('#button-reset').on('click', function() {
        setTimeout(function() { $('#button-filter').trigger('click'); }, 10);
    });

    // 清除（移除所有篩選條件）
    $('#button-clear').on('click', function() {
        $('#form-filter').find('input[type="text"]').val('');
        $('#input-role').val(null).trigger('change');
        $('#user-list').load(listUrl);
        window.history.pushState({}, null, indexUrl);
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
                url: batchDeleteUrl,
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
