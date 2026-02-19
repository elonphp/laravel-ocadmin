@extends('ocadmin::layouts.app')

@section('title', $company->exists ? $lang->text_edit : $lang->text_add)

@section('content')
<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">{{ $company->exists ? $lang->text_edit : $lang->text_add }}</h3>
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
            <button type="submit" form="form-company" data-bs-toggle="tooltip" title="{{ $lang->button_save }}" class="btn btn-primary">
                <i class="bi bi-floppy"></i>
            </button>
            <a href="{{ route('lang.ocadmin.hrm.company.index') }}" data-bs-toggle="tooltip" title="{{ $lang->button_back }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i>
            </a>
        </div>

        <div class="card">
            <div class="card-header">
                <i class="bi bi-pencil"></i> {{ $company->exists ? $lang->text_edit : $lang->text_add }}
            </div>
            <div class="card-body">
                <form action="{{ $company->exists ? route('lang.ocadmin.hrm.company.update', $company) : route('lang.ocadmin.hrm.company.store') }}" method="post" id="form-company" data-oc-toggle="ajax">
                    @csrf
                    @if($company->exists)
                    @method('PUT')
                    @endif

                    <div class="row mb-3 required">
                        <label for="input-name" class="col-sm-2 col-form-label">{{ $lang->column_name }}</label>
                        <div class="col-sm-10">
                            <input type="text" name="name" value="{{ old('name', $company->name) }}" placeholder="{{ $lang->placeholder_name }}" id="input-name" class="form-control" maxlength="200">
                            <div id="error-name" class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="input-short_name" class="col-sm-2 col-form-label">{{ $lang->column_short_name }}</label>
                        <div class="col-sm-10">
                            <input type="text" name="short_name" value="{{ old('short_name', $company->short_name) }}" placeholder="{{ $lang->placeholder_short_name }}" id="input-short_name" class="form-control" maxlength="100">
                            <div id="error-short_name" class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="input-parent_id" class="col-sm-2 col-form-label">{{ $lang->column_parent }}</label>
                        <div class="col-sm-10">
                            <select name="parent_id" id="input-parent_id" class="form-select">
                                <option value="">{{ $lang->text_select_parent }}</option>
                                @foreach($parentOptions as $option)
                                <option value="{{ $option->id }}" {{ old('parent_id', $company->parent_id) == $option->id ? 'selected' : '' }}>{{ $option->name }}</option>
                                @endforeach
                            </select>
                            <div id="error-parent_id" class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="input-code" class="col-sm-2 col-form-label">{{ $lang->column_code }}</label>
                        <div class="col-sm-10">
                            <input type="text" name="code" value="{{ old('code', $company->code) }}" placeholder="{{ $lang->placeholder_code }}" id="input-code" class="form-control" maxlength="20">
                            <div id="error-code" class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="input-business_no" class="col-sm-2 col-form-label">{{ $lang->column_business_no }}</label>
                        <div class="col-sm-10">
                            <input type="text" name="business_no" value="{{ old('business_no', $company->business_no) }}" placeholder="{{ $lang->placeholder_business_no }}" id="input-business_no" class="form-control" maxlength="20">
                            <div id="error-business_no" class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="input-phone" class="col-sm-2 col-form-label">{{ $lang->column_phone }}</label>
                        <div class="col-sm-10">
                            <input type="text" name="phone" value="{{ old('phone', $company->phone) }}" placeholder="{{ $lang->placeholder_phone }}" id="input-phone" class="form-control" maxlength="30">
                            <div id="error-phone" class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="input-address" class="col-sm-2 col-form-label">{{ $lang->column_address }}</label>
                        <div class="col-sm-10">
                            <input type="text" name="address" value="{{ old('address', $company->address) }}" placeholder="{{ $lang->placeholder_address }}" id="input-address" class="form-control" maxlength="255">
                            <div id="error-address" class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="row mb-3 required">
                        <label for="input-is_active" class="col-sm-2 col-form-label">{{ $lang->column_is_active }}</label>
                        <div class="col-sm-10">
                            <select name="is_active" id="input-is_active" class="form-select">
                                <option value="1" {{ old('is_active', $company->exists ? $company->is_active : 1) == 1 ? 'selected' : '' }}>{{ $lang->text_active }}</option>
                                <option value="0" {{ old('is_active', $company->exists ? $company->is_active : 1) == 0 ? 'selected' : '' }}>{{ $lang->text_inactive }}</option>
                            </select>
                            <div id="error-is_active" class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="row mb-3 required">
                        <label for="input-sort_order" class="col-sm-2 col-form-label">{{ $lang->column_sort_order }}</label>
                        <div class="col-sm-10">
                            <input type="number" name="sort_order" value="{{ old('sort_order', $company->sort_order ?? 0) }}" placeholder="{{ $lang->placeholder_sort_order }}" id="input-sort_order" class="form-control" min="0">
                            <div id="error-sort_order" class="invalid-feedback"></div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
