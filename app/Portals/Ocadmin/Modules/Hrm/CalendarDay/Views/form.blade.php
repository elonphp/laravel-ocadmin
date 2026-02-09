@extends('ocadmin::layouts.app')

@section('title', $calendarDay->exists ? $lang->text_edit : $lang->text_add)

@section('content')
<div id="content">
    <div class="page-header">
        <div class="container-fluid">
            <div class="float-end">
                <button type="submit" form="form-calendar-day" data-bs-toggle="tooltip" title="{{ $lang->button_save }}" class="btn btn-primary">
                    <i class="fa-solid fa-save"></i>
                </button>
                <a href="{{ route('lang.ocadmin.hrm.calendar-day.index') }}" data-bs-toggle="tooltip" title="{{ $lang->button_back }}" class="btn btn-light">
                    <i class="fa-solid fa-reply"></i>
                </a>
            </div>
            <h1>{{ $calendarDay->exists ? $lang->text_edit : $lang->text_add }}</h1>
            @include('ocadmin::layouts.partials.breadcrumb')
        </div>
    </div>

    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <i class="fa-solid fa-pencil"></i> {{ $calendarDay->exists ? $lang->text_edit : $lang->text_add }}
            </div>
            <div class="card-body">
                <form action="{{ $calendarDay->exists ? route('lang.ocadmin.hrm.calendar-day.update', $calendarDay) : route('lang.ocadmin.hrm.calendar-day.store') }}"
                      method="post"
                      id="form-calendar-day"
                      data-oc-toggle="ajax">
                    @csrf
                    @if($calendarDay->exists)
                    @method('PUT')
                    @endif

                    <div class="row mb-3 required">
                        <label for="input-date" class="col-sm-2 col-form-label">{{ $lang->column_date }}</label>
                        <div class="col-sm-10">
                            <input type="date"
                                   name="date"
                                   value="{{ old('date', $calendarDay->date?->format('Y-m-d')) }}"
                                   placeholder="{{ $lang->placeholder_date }}"
                                   id="input-date"
                                   class="form-control">
                            <div id="error-date" class="invalid-feedback"></div>
                            @if($lang->help_date ?? null)
                            <div class="form-text">{{ $lang->help_date }}</div>
                            @endif
                        </div>
                    </div>

                    <div class="row mb-3 required">
                        <label for="input-day-type" class="col-sm-2 col-form-label">{{ $lang->column_day_type }}</label>
                        <div class="col-sm-10">
                            <select name="day_type" id="input-day-type" class="form-select">
                                <option value="">-- {{ $lang->text_select }} --</option>
                                @foreach($dayTypeOptions as $value => $label)
                                <option value="{{ $value }}" {{ old('day_type', $calendarDay->day_type) == $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            <div id="error-day_type" class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="input-is-workday" class="col-sm-2 col-form-label">{{ $lang->column_is_workday }}</label>
                        <div class="col-sm-10">
                            <div class="form-check form-switch">
                                <input type="checkbox"
                                       name="is_workday"
                                       value="1"
                                       id="input-is-workday"
                                       class="form-check-input"
                                       {{ old('is_workday', $calendarDay->is_workday) ? 'checked' : '' }}>
                                <label class="form-check-label" for="input-is-workday">{{ $lang->help_is_workday }}</label>
                            </div>
                            <div id="error-is_workday" class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="input-name" class="col-sm-2 col-form-label">{{ $lang->column_name }}</label>
                        <div class="col-sm-10">
                            <input type="text"
                                   name="name"
                                   value="{{ old('name', $calendarDay->name) }}"
                                   placeholder="{{ $lang->placeholder_name }}"
                                   id="input-name"
                                   class="form-control">
                            <div id="error-name" class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="input-description" class="col-sm-2 col-form-label">{{ $lang->column_description }}</label>
                        <div class="col-sm-10">
                            <textarea name="description"
                                      rows="3"
                                      placeholder="{{ $lang->placeholder_description }}"
                                      id="input-description"
                                      class="form-control">{{ old('description', $calendarDay->description) }}</textarea>
                            <div id="error-description" class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="input-color" class="col-sm-2 col-form-label">{{ $lang->column_color }}</label>
                        <div class="col-sm-10">
                            <div class="input-group">
                                <input type="color"
                                       name="color"
                                       value="{{ old('color', $calendarDay->color ?: '#3498db') }}"
                                       id="input-color"
                                       class="form-control form-control-color">
                                <input type="text"
                                       id="input-color-text"
                                       value="{{ old('color', $calendarDay->color) }}"
                                       placeholder="{{ $lang->placeholder_color }}"
                                       class="form-control"
                                       readonly>
                            </div>
                            <div id="error-color" class="invalid-feedback"></div>
                            @if($lang->help_color ?? null)
                            <div class="form-text">{{ $lang->help_color }}</div>
                            @endif
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script type="text/javascript">
$(document).ready(function() {
    // 同步顏色選擇器與文字輸入
    $('#input-color').on('change', function() {
        $('#input-color-text').val($(this).val());
    });

    // 日期類型改變時，自動設定工作日狀態
    $('#input-day-type').on('change', function() {
        var dayType = $(this).val();
        var isWorkday = $('#input-is-workday');

        // 自動判斷是否為工作日
        if (dayType === 'workday' || dayType === 'makeup_workday') {
            isWorkday.prop('checked', true);
        } else if (dayType === 'weekend' || dayType === 'holiday' || dayType === 'company_holiday' || dayType === 'typhoon_day') {
            isWorkday.prop('checked', false);
        }
    });
});
</script>
@endsection
