@extends('ocadmin::layouts.app')

@section('title', $department->exists ? $lang->text_edit : $lang->text_add)

@section('content')
<div id="content">
    <div class="page-header">
        <div class="container-fluid">
            <div class="float-end">
                <button type="submit" form="form-department" data-bs-toggle="tooltip" title="{{ $lang->button_save }}" class="btn btn-primary">
                    <i class="fa-solid fa-save"></i>
                </button>
                <a href="{{ route('lang.ocadmin.hrm.department.index') }}" data-bs-toggle="tooltip" title="{{ $lang->button_back }}" class="btn btn-secondary">
                    <i class="fa-solid fa-reply"></i>
                </a>
            </div>
            <h1>{{ $department->exists ? $lang->text_edit : $lang->text_add }}</h1>
            @include('ocadmin::layouts.partials.breadcrumb')
        </div>
    </div>

    <div class="container-fluid">
        <div class="card card-default">
            <div class="card-header">
                <i class="fa-solid fa-pencil"></i> {{ $department->exists ? $lang->text_edit : $lang->text_add }}
            </div>
            <div class="card-body">
                <form action="{{ $department->exists ? route('lang.ocadmin.hrm.department.update', $department) : route('lang.ocadmin.hrm.department.store') }}" method="post" id="form-department" data-oc-toggle="ajax">
                    @csrf
                    @if($department->exists)
                    @method('PUT')
                    @endif

                    <div class="row mb-3 required">
                        <label for="input-company_id" class="col-sm-2 col-form-label">{{ $lang->column_company }}</label>
                        <div class="col-sm-10">
                            <select name="company_id" id="input-company_id" class="form-select">
                                <option value="">{{ $lang->text_select_company }}</option>
                                @foreach($companyOptions as $option)
                                <option value="{{ $option->id }}" {{ old('company_id', $department->company_id) == $option->id ? 'selected' : '' }}>{{ $option->name }}</option>
                                @endforeach
                            </select>
                            <div id="error-company_id" class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="input-parent_id" class="col-sm-2 col-form-label">{{ $lang->column_parent }}</label>
                        <div class="col-sm-10">
                            <select name="parent_id" id="input-parent_id" class="form-select">
                                <option value="">{{ $lang->text_select_parent }}</option>
                                @foreach($parentOptions as $option)
                                <option value="{{ $option->id }}" {{ old('parent_id', $department->parent_id) == $option->id ? 'selected' : '' }}>{{ $option->name }}</option>
                                @endforeach
                            </select>
                            <div id="error-parent_id" class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="row mb-3 required">
                        <label for="input-name" class="col-sm-2 col-form-label">{{ $lang->column_name }}</label>
                        <div class="col-sm-10">
                            <input type="text" name="name" value="{{ old('name', $department->name) }}" placeholder="{{ $lang->placeholder_name }}" id="input-name" class="form-control" maxlength="100">
                            <div id="error-name" class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="input-code" class="col-sm-2 col-form-label">{{ $lang->column_code }}</label>
                        <div class="col-sm-10">
                            <input type="text" name="code" value="{{ old('code', $department->code) }}" placeholder="{{ $lang->placeholder_code }}" id="input-code" class="form-control" maxlength="20">
                            <div id="error-code" class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="row mb-3 required">
                        <label for="input-is_active" class="col-sm-2 col-form-label">{{ $lang->column_is_active }}</label>
                        <div class="col-sm-10">
                            <select name="is_active" id="input-is_active" class="form-select">
                                <option value="1" {{ old('is_active', $department->exists ? $department->is_active : 1) == 1 ? 'selected' : '' }}>{{ $lang->text_active }}</option>
                                <option value="0" {{ old('is_active', $department->exists ? $department->is_active : 1) == 0 ? 'selected' : '' }}>{{ $lang->text_inactive }}</option>
                            </select>
                            <div id="error-is_active" class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="row mb-3 required">
                        <label for="input-sort_order" class="col-sm-2 col-form-label">{{ $lang->column_sort_order }}</label>
                        <div class="col-sm-10">
                            <input type="number" name="sort_order" value="{{ old('sort_order', $department->sort_order ?? 0) }}" placeholder="{{ $lang->placeholder_sort_order }}" id="input-sort_order" class="form-control" min="0">
                            <div id="error-sort_order" class="invalid-feedback"></div>
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
    // 公司切換時，動態載入該公司的上層部門選項
    $('#input-company_id').on('change', function() {
        var companyId = $(this).val();
        var $parent = $('#input-parent_id');
        var excludeId = '{{ $department->id ?? '' }}';

        $parent.html('<option value="">{{ $lang->text_select_parent }}</option>');

        if (!companyId) return;

        $.get('{{ route("lang.ocadmin.hrm.department.parent-options") }}', {
            company_id: companyId,
            exclude_id: excludeId
        }, function(options) {
            $.each(options, function(i, opt) {
                $parent.append('<option value="' + opt.id + '">' + opt.name + '</option>');
            });
        });
    });
});
</script>
@endsection
