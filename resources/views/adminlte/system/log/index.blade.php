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
            <button type="button" data-bs-toggle="tooltip" title="{{ $lang->button_filter }}" onclick="$('#filter-log').toggleClass('d-none');" class="btn btn-light d-lg-none">
                <i class="bi bi-funnel"></i>
            </button>
        </div>

        <div class="row">
            {{-- 篩選區塊 --}}
            <div id="filter-log" class="col-lg-3 col-md-12 order-lg-last d-none d-lg-block mb-3">
                <div class="card">
                    <div class="card-header"><i class="bi bi-funnel"></i> {{ $lang->text_filter }}</div>
                    <div class="card-body">
                        <form id="form-filter">
                            <div class="mb-3">
                                <label class="form-label">{{ $lang->column_portal }}</label>
                                <select name="equal_portal" id="input-portal" class="form-select">
                                    <option value="">-- {{ $lang->text_all }} --</option>
                                    @foreach($portals as $portal)
                                    <option value="{{ $portal }}" {{ request('equal_portal') === $portal ? 'selected' : '' }}>{{ $portal }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">{{ $lang->column_method }}</label>
                                <select name="equal_method" id="input-method" class="form-select">
                                    <option value="">-- {{ $lang->text_all }} --</option>
                                    @foreach($methods as $method)
                                    <option value="{{ $method }}" {{ request('equal_method') === $method ? 'selected' : '' }}>{{ $method }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">{{ $lang->column_status }}</label>
                                <select name="equal_status" id="input-status" class="form-select">
                                    <option value="">-- {{ $lang->text_all }} --</option>
                                    @foreach($statuses as $status)
                                    <option value="{{ $status }}" {{ request('equal_status') === $status ? 'selected' : '' }}>{{ $status }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">{{ $lang->placeholder_date_start }}</label>
                                <input type="date" name="filter_date_start" value="{{ request('filter_date_start') }}" id="input-date-start" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">{{ $lang->placeholder_date_end }}</label>
                                <input type="date" name="filter_date_end" value="{{ request('filter_date_end') }}" id="input-date-end" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">{{ $lang->placeholder_search }}</label>
                                <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ $lang->placeholder_search }}" id="input-search" class="form-control">
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
                    <div id="log-list" class="card-body">
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
    $('#log-list').on('click', 'thead a, .pagination a', function(e) {
        e.preventDefault();
        $('#log-list').load($(this).attr('href'));
    });

    // 篩選
    $('#button-filter').on('click', function() {
        var url = '{{ route('lang.ocadmin.system.log.list') }}?';
        var params = [];

        var v;

        v = $('#input-portal').val();
        if (v) params.push('equal_portal=' + encodeURIComponent(v));

        v = $('#input-method').val();
        if (v) params.push('equal_method=' + encodeURIComponent(v));

        v = $('#input-status').val();
        if (v) params.push('equal_status=' + encodeURIComponent(v));

        v = $('#input-date-start').val();
        if (v) params.push('filter_date_start=' + encodeURIComponent(v));

        v = $('#input-date-end').val();
        if (v) params.push('filter_date_end=' + encodeURIComponent(v));

        v = $('#input-search').val();
        if (v) params.push('search=' + encodeURIComponent(v));

        url += params.join('&');
        window.history.pushState({}, null, url.replace('/list?', '?'));
        $('#log-list').load(url);
    });

    // 重設（恢復預設篩選條件）
    $('#button-reset').on('click', function() {
        setTimeout(function() { $('#button-filter').trigger('click'); }, 10);
    });

    // 清除（移除所有篩選條件）
    $('#button-clear').on('click', function() {
        $('#form-filter').find('input[type="text"], input[type="date"]').val('');
        $('#form-filter').find('select').each(function() { $(this).prop('selectedIndex', 0); });
        var url = '{{ route('lang.ocadmin.system.log.list') }}';
        window.history.pushState({}, null, '{{ route('lang.ocadmin.system.log.index') }}');
        $('#log-list').load(url);
    });
});
</script>
@endsection
