@extends('ocadmin::layouts.app')

@section('title', $lang->heading_title)

@section('content')
<div id="content">
    <div class="page-header">
        <div class="container-fluid">
            <div class="float-end">
                <button type="button" data-bs-toggle="tooltip" title="{{ $lang->button_filter }}" onclick="$('#filter-calendar-day').toggleClass('d-none');" class="btn btn-light d-lg-none">
                    <i class="fa-solid fa-filter"></i>
                </button>
                <a href="{{ route('lang.ocadmin.hrm.calendar-day.create') }}" data-bs-toggle="tooltip" title="{{ $lang->button_add }}" class="btn btn-primary">
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
            {{-- 篩選區塊 --}}
            <div id="filter-calendar-day" class="col-lg-3 col-md-12 order-lg-last d-none d-lg-block mb-3">
                <div class="card">
                    <div class="card-header"><i class="fa-solid fa-filter"></i> {{ $lang->text_filter }}</div>
                    <div class="card-body">
                        <form id="form-filter">
                            <div class="mb-3">
                                <label class="form-label">{{ $lang->column_year }}</label>
                                <input type="number" name="equal_year" value="{{ request('equal_year') }}" placeholder="{{ $lang->column_year }}" id="input-year" class="form-control" min="2000" max="2099">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">{{ $lang->column_month }}</label>
                                <select name="equal_month" id="input-month" class="form-select">
                                    <option value="">-- {{ $lang->text_all }} --</option>
                                    @for($i = 1; $i <= 12; $i++)
                                    <option value="{{ $i }}" {{ request('equal_month') == $i ? 'selected' : '' }}>{{ $i }} 月</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">{{ $lang->column_day_type }}</label>
                                <select name="equal_day_type" id="input-day-type" class="form-select">
                                    <option value="">-- {{ $lang->text_all }} --</option>
                                    <option value="workday" {{ request('equal_day_type') == 'workday' ? 'selected' : '' }}>{{ $lang->option_workday }}</option>
                                    <option value="weekend" {{ request('equal_day_type') == 'weekend' ? 'selected' : '' }}>{{ $lang->option_weekend }}</option>
                                    <option value="holiday" {{ request('equal_day_type') == 'holiday' ? 'selected' : '' }}>{{ $lang->option_holiday }}</option>
                                    <option value="company_holiday" {{ request('equal_day_type') == 'company_holiday' ? 'selected' : '' }}>{{ $lang->option_company_holiday }}</option>
                                    <option value="makeup_workday" {{ request('equal_day_type') == 'makeup_workday' ? 'selected' : '' }}>{{ $lang->option_makeup_workday }}</option>
                                    <option value="typhoon_day" {{ request('equal_day_type') == 'typhoon_day' ? 'selected' : '' }}>{{ $lang->option_typhoon_day }}</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">{{ $lang->column_is_workday }}</label>
                                <select name="equal_is_workday" id="input-is-workday" class="form-select">
                                    <option value="">-- {{ $lang->text_all }} --</option>
                                    <option value="1" {{ request('equal_is_workday') === '1' ? 'selected' : '' }}>{{ $lang->text_yes }}</option>
                                    <option value="0" {{ request('equal_is_workday') === '0' ? 'selected' : '' }}>{{ $lang->text_no }}</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">{{ $lang->column_name }}</label>
                                <input type="text" name="filter_name" value="{{ request('filter_name') }}" placeholder="{{ $lang->column_name }}" id="input-name" class="form-control">
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
                    <div class="card-header"><i class="fa-solid fa-calendar-days"></i> {{ $lang->text_list }}</div>
                    <div id="calendar-day-list" class="card-body">
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
    var listUrl = '{{ route("lang.ocadmin.hrm.calendar-day.list") }}';
    var indexUrl = '{{ route("lang.ocadmin.hrm.calendar-day.index") }}';
    var listContainer = '#calendar-day-list';

    // AJAX 分頁 & 排序
    $(listContainer).on('click', 'thead a, .pagination a', function(e) {
        e.preventDefault();
        $(listContainer).load($(this).attr('href'));
    });

    // 篩選
    $('#button-filter').on('click', function() {
        var params = [];

        var v = $('#input-year').val();
        if (v) params.push('equal_year=' + encodeURIComponent(v));

        v = $('#input-month').val();
        if (v) params.push('equal_month=' + encodeURIComponent(v));

        v = $('#input-day-type').val();
        if (v) params.push('equal_day_type=' + encodeURIComponent(v));

        v = $('#input-is-workday').val();
        if (v) params.push('equal_is_workday=' + encodeURIComponent(v));

        v = $('#input-name').val();
        if (v) params.push('filter_name=' + encodeURIComponent(v));

        var url = listUrl + '?' + params.join('&');
        window.history.pushState({}, null, url.replace('/list?', '?'));
        $(listContainer).load(url);
    });

    // 重設（恢復預設篩選條件）
    $('#button-reset').on('click', function() {
        setTimeout(function() { $('#button-filter').trigger('click'); }, 10);
    });

    // 清除（移除所有篩選條件）
    $('#button-clear').on('click', function() {
        $('#form-filter').find('input[type="text"], input[type="number"]').val('');
        $('#form-filter').find('select').val('');
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

        if (confirm('{{ $lang->text_confirm_batch_delete }}'.replace('%s', selected.length))) {
            $.ajax({
                url: '{{ route("lang.ocadmin.hrm.calendar-day.batch-delete") }}',
                type: 'POST',
                data: { selected: selected, _token: '{{ csrf_token() }}' },
                dataType: 'json',
                success: function(json) {
                    if (json.success) {
                        location.reload();
                    } else {
                        alert(json.message || '{{ $lang->text_error }}');
                    }
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    alert('{{ $lang->text_error }}: ' + thrownError);
                }
            });
        }
    });
});
</script>
@endsection
