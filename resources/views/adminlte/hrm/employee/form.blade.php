@extends('ocadmin::layouts.app')

@section('title', $employee->exists ? $lang->text_edit : $lang->text_add)

@section('content')
<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">{{ $employee->exists ? $lang->text_edit : $lang->text_add }}</h3>
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
            <button type="submit" form="form-employee" data-bs-toggle="tooltip" title="{{ $lang->button_save }}" class="btn btn-primary">
                <i class="bi bi-floppy"></i>
            </button>
            <a href="{{ route('lang.ocadmin.hrm.employee.index') }}" data-bs-toggle="tooltip" title="{{ $lang->button_back }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i>
            </a>
        </div>

        <div class="card">
            <div class="card-header">
                <i class="bi bi-pencil"></i> {{ $employee->exists ? $lang->text_edit : $lang->text_add }}
            </div>
            <div class="card-body">
                <ul class="nav nav-tabs">
                    <li class="nav-item"><a href="#tab-basic" data-bs-toggle="tab" class="nav-link active">{{ $lang->tab_basic }}</a></li>
                    <li class="nav-item"><a href="#tab-relation" data-bs-toggle="tab" class="nav-link">{{ $lang->tab_relation }}</a></li>
                </ul>
                <form action="{{ $employee->exists ? route('lang.ocadmin.hrm.employee.update', $employee) : route('lang.ocadmin.hrm.employee.store') }}" method="post" id="form-employee" data-oc-toggle="ajax">
                    @csrf
                    @if($employee->exists)
                    @method('PUT')
                    @endif

                    <div class="tab-content">
                        {{-- 基本資料 --}}
                        <div id="tab-basic" class="tab-pane active">
                            <div class="row mb-3 required">
                                <label for="input-first_name" class="col-sm-2 col-form-label">{{ $lang->column_first_name }}</label>
                                <div class="col-sm-10">
                                    <input type="text" name="first_name" value="{{ old('first_name', $employee->first_name) }}" placeholder="{{ $lang->placeholder_first_name }}" id="input-first_name" class="form-control" maxlength="50">
                                    <div id="error-first_name" class="invalid-feedback"></div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="input-last_name" class="col-sm-2 col-form-label">{{ $lang->column_last_name }}</label>
                                <div class="col-sm-10">
                                    <input type="text" name="last_name" value="{{ old('last_name', $employee->last_name) }}" placeholder="{{ $lang->placeholder_last_name }}" id="input-last_name" class="form-control" maxlength="50">
                                    <div id="error-last_name" class="invalid-feedback"></div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="input-employee_no" class="col-sm-2 col-form-label">{{ $lang->column_employee_no }}</label>
                                <div class="col-sm-10">
                                    <input type="text" name="employee_no" value="{{ old('employee_no', $employee->employee_no) }}" placeholder="{{ $lang->placeholder_employee_no }}" id="input-employee_no" class="form-control" maxlength="20">
                                    <div id="error-employee_no" class="invalid-feedback"></div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="input-email" class="col-sm-2 col-form-label">{{ $lang->column_email }}</label>
                                <div class="col-sm-10">
                                    <input type="email" name="email" value="{{ old('email', $employee->email) }}" placeholder="{{ $lang->placeholder_email }}" id="input-email" class="form-control" maxlength="100">
                                    <div id="error-email" class="invalid-feedback"></div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="input-phone" class="col-sm-2 col-form-label">{{ $lang->column_phone }}</label>
                                <div class="col-sm-10">
                                    <input type="text" name="phone" value="{{ old('phone', $employee->phone) }}" placeholder="{{ $lang->placeholder_phone }}" id="input-phone" class="form-control" maxlength="30">
                                    <div id="error-phone" class="invalid-feedback"></div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="input-gender" class="col-sm-2 col-form-label">{{ $lang->column_gender }}</label>
                                <div class="col-sm-10">
                                    <select name="gender" id="input-gender" class="form-select">
                                        <option value="">{{ __('enums.gender_placeholder') }}</option>
                                        @foreach($genderOptions as $gender)
                                        <option value="{{ $gender->value }}" @selected(old('gender', $employee->gender?->value) === $gender->value)>{{ $gender->label() }}</option>
                                        @endforeach
                                    </select>
                                    <div id="error-gender" class="invalid-feedback"></div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="input-hire_date" class="col-sm-2 col-form-label">{{ $lang->column_hire_date }}</label>
                                <div class="col-sm-10">
                                    <input type="date" name="hire_date" value="{{ old('hire_date', $employee->hire_date?->format('Y-m-d')) }}" id="input-hire_date" class="form-control">
                                    <div id="error-hire_date" class="invalid-feedback"></div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="input-birth_date" class="col-sm-2 col-form-label">{{ $lang->column_birth_date }}</label>
                                <div class="col-sm-10">
                                    <input type="date" name="birth_date" value="{{ old('birth_date', $employee->birth_date?->format('Y-m-d')) }}" id="input-birth_date" class="form-control">
                                    <div id="error-birth_date" class="invalid-feedback"></div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="input-job_title" class="col-sm-2 col-form-label">{{ $lang->column_job_title }}</label>
                                <div class="col-sm-10">
                                    <input type="text" name="job_title" value="{{ old('job_title', $employee->job_title) }}" placeholder="{{ $lang->placeholder_job_title }}" id="input-job_title" class="form-control" maxlength="100">
                                    <div id="error-job_title" class="invalid-feedback"></div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="input-address" class="col-sm-2 col-form-label">{{ $lang->column_address }}</label>
                                <div class="col-sm-10">
                                    <textarea name="address" placeholder="{{ $lang->placeholder_address }}" id="input-address" class="form-control" rows="3">{{ old('address', $employee->address) }}</textarea>
                                    <div id="error-address" class="invalid-feedback"></div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="input-note" class="col-sm-2 col-form-label">{{ $lang->column_note }}</label>
                                <div class="col-sm-10">
                                    <textarea name="note" placeholder="{{ $lang->placeholder_note }}" id="input-note" class="form-control" rows="3">{{ old('note', $employee->note) }}</textarea>
                                    <div id="error-note" class="invalid-feedback"></div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="input-is_active" class="col-sm-2 col-form-label">{{ $lang->column_is_active }}</label>
                                <div class="col-sm-10">
                                    <select name="is_active" id="input-is_active" class="form-select">
                                        <option value="1" @selected(old('is_active', $employee->exists ? $employee->is_active : true) == true)>{{ $lang->text_active }}</option>
                                        <option value="0" @selected(old('is_active', $employee->exists ? $employee->is_active : true) == false)>{{ $lang->text_inactive }}</option>
                                    </select>
                                    <div id="error-is_active" class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>

                        {{-- 關聯資料 --}}
                        <div id="tab-relation" class="tab-pane">
                            <div class="row mb-3">
                                <label for="input-company_id" class="col-sm-2 col-form-label">{{ $lang->column_company }}</label>
                                <div class="col-sm-10">
                                    <select name="company_id" id="input-company_id" class="form-select">
                                        <option value="">{{ $lang->text_select_company }}</option>
                                        @foreach($companies as $company)
                                        <option value="{{ $company->id }}" @selected(old('company_id', $employee->company_id) == $company->id)>{{ $company->name }}</option>
                                        @endforeach
                                    </select>
                                    <div id="error-company_id" class="invalid-feedback"></div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="input-department_id" class="col-sm-2 col-form-label">{{ $lang->column_department }}</label>
                                <div class="col-sm-10">
                                    <select name="department_id" id="input-department_id" class="form-select">
                                        <option value="">{{ $lang->text_select_department }}</option>
                                        @foreach($departments as $dept)
                                        <option value="{{ $dept->id }}" data-company-id="{{ $dept->company_id }}" @selected(old('department_id', $employee->department_id) == $dept->id)>{{ $dept->name }}</option>
                                        @endforeach
                                    </select>
                                    <div id="error-department_id" class="invalid-feedback"></div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label">{{ $lang->column_user }}</label>
                                <div class="col-sm-10">
                                    <div class="input-group">
                                        <input type="text"
                                               id="input-user-search"
                                               class="form-control"
                                               placeholder="{{ $lang->placeholder_user_search }}"
                                               value="{{ $employee->user?->email ?? '' }}"
                                               autocomplete="off">
                                        <input type="hidden" name="user_id" id="input-user-id"
                                               value="{{ old('user_id', $employee->user_id) }}">
                                        <button type="button" class="btn btn-outline-secondary" id="btn-clear-user">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    </div>
                                    <div id="user-search-results" class="list-group position-absolute" style="z-index:1000; display:none; max-width: calc(100% - var(--bs-gutter-x));"></div>
                                    <div id="error-user_id" class="invalid-feedback"></div>
                                </div>
                            </div>
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
    // 公司→部門連動篩選
    $('#input-company_id').on('change', function() {
        var companyId = $(this).val();
        $('#input-department_id option').each(function() {
            var $opt = $(this);
            if (!$opt.val()) return; // 保留 placeholder
            $opt.toggle($opt.data('company-id') == companyId);
        });
        // 若已選部門不屬於新公司，清除
        var $selected = $('#input-department_id option:selected');
        if ($selected.val() && $selected.data('company-id') != companyId) {
            $('#input-department_id').val('');
        }
    }).trigger('change');

    // AJAX User 查找
    var searchTimer;
    $('#input-user-search').on('input', function() {
        clearTimeout(searchTimer);
        var q = $(this).val();
        if (q.length < 2) {
            $('#user-search-results').hide();
            return;
        }
        searchTimer = setTimeout(function() {
            $.get('{{ route("lang.ocadmin.hrm.employee.search-users") }}', { q: q }, function(users) {
                var $results = $('#user-search-results').empty();
                users.forEach(function(user) {
                    $results.append(
                        '<a href="#" class="list-group-item list-group-item-action user-result" data-id="' + user.id + '" data-email="' + user.email + '">' +
                        user.name + ' &lt;' + user.email + '&gt;' +
                        '</a>'
                    );
                });
                $results.toggle(users.length > 0);
            });
        }, 300);
    });

    $(document).on('click', '.user-result', function(e) {
        e.preventDefault();
        $('#input-user-id').val($(this).data('id'));
        $('#input-user-search').val($(this).data('email'));
        $('#user-search-results').hide();
    });

    $('#btn-clear-user').on('click', function() {
        $('#input-user-id').val('');
        $('#input-user-search').val('');
    });

    // 點擊外部關閉搜尋結果
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#input-user-search, #user-search-results').length) {
            $('#user-search-results').hide();
        }
    });
});
</script>
@endsection
