@extends('ocadmin::layouts.app')

@section('title', $company->exists ? $lang->text_edit : $lang->text_add)

@section('content')
<div id="content">
    <div class="page-header">
        <div class="container-fluid">
            <div class="float-end">
                <button type="submit" form="form-company" data-bs-toggle="tooltip" title="{{ $lang->button_save }}" class="btn btn-primary">
                    <i class="fa-solid fa-save"></i>
                </button>
                <a href="{{ route('lang.ocadmin.corp.company.index') }}" data-bs-toggle="tooltip" title="{{ $lang->button_back }}" class="btn btn-secondary">
                    <i class="fa-solid fa-reply"></i>
                </a>
            </div>
            <h1>{{ $company->exists ? $lang->text_edit : $lang->text_add }}</h1>
            @include('ocadmin::layouts.partials.breadcrumb')
        </div>
    </div>

    <div class="container-fluid">
        <div class="card card-default">
            <div class="card-header">
                <i class="fa-solid fa-pencil"></i> {{ $company->exists ? $lang->text_edit : $lang->text_add }}
            </div>
            <div class="card-body">
                <ul class="nav nav-tabs">
                    <li class="nav-item"><a href="#tab-trans" data-bs-toggle="tab" class="nav-link active">{{ $lang->tab_trans }}</a></li>
                    <li class="nav-item"><a href="#tab-data" data-bs-toggle="tab" class="nav-link">{{ $lang->tab_data }}</a></li>
                </ul>
                <form action="{{ $company->exists ? route('lang.ocadmin.corp.company.update', $company) : route('lang.ocadmin.corp.company.store') }}" method="post" id="form-company" data-oc-toggle="ajax">
                    @csrf
                    @if($company->exists)
                    @method('PUT')
                    @endif

                    @php $translationsArray = $company->exists ? $company->getTranslationsArray() : []; @endphp

                    <div class="tab-content">
                        {{-- 語言資料 --}}
                        <div id="tab-trans" class="tab-pane active">
                            <ul class="nav nav-tabs">
                                @foreach($locales as $locale)
                                <li class="nav-item"><a href="#language-{{ $locale }}" data-bs-toggle="tab" class="nav-link @if($loop->first) active @endif">{{ $localeNames[$locale] ?? $locale }}</a></li>
                                @endforeach
                            </ul>
                            <div class="tab-content">
                                @foreach($locales as $locale)
                                <div id="language-{{ $locale }}" class="tab-pane @if($loop->first) active @endif">
                                    <div class="row mb-3 required" id="input-name-{{ str_replace('_', '-', $locale) }}">
                                        <label for="input-name-{{ str_replace('_', '-', $locale) }}-field" class="col-sm-2 col-form-label">{{ $lang->column_name }}</label>
                                        <div class="col-sm-10">
                                            <input type="text" name="translations[{{ $locale }}][name]" value="{{ old("translations.{$locale}.name", $translationsArray[$locale]['name'] ?? '') }}" placeholder="{{ $lang->placeholder_name }}" id="input-name-{{ str_replace('_', '-', $locale) }}-field" class="form-control" maxlength="200">
                                            <div id="error-name-{{ str_replace('_', '-', $locale) }}" class="invalid-feedback"></div>
                                        </div>
                                    </div>
                                    <div class="row mb-3" id="input-short-name-{{ str_replace('_', '-', $locale) }}">
                                        <label for="input-short-name-{{ str_replace('_', '-', $locale) }}-field" class="col-sm-2 col-form-label">{{ $lang->column_short_name }}</label>
                                        <div class="col-sm-10">
                                            <input type="text" name="translations[{{ $locale }}][short_name]" value="{{ old("translations.{$locale}.short_name", $translationsArray[$locale]['short_name'] ?? '') }}" placeholder="{{ $lang->placeholder_short_name }}" id="input-short-name-{{ str_replace('_', '-', $locale) }}-field" class="form-control" maxlength="100">
                                            <div id="error-short-name-{{ str_replace('_', '-', $locale) }}" class="invalid-feedback"></div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- 基本資料 --}}
                        <div id="tab-data" class="tab-pane">
                            <div class="row mb-3" id="input-parent-id">
                                <label for="input-parent-id-field" class="col-sm-2 col-form-label">{{ $lang->column_parent }}</label>
                                <div class="col-sm-10">
                                    <select name="parent_id" id="input-parent-id-field" class="form-select">
                                        <option value="">{{ $lang->text_select_parent }}</option>
                                        @foreach($parentOptions as $option)
                                        <option value="{{ $option->id }}" {{ old('parent_id', $company->parent_id) == $option->id ? 'selected' : '' }}>{{ $option->name }}</option>
                                        @endforeach
                                    </select>
                                    <div id="error-parent-id" class="invalid-feedback"></div>
                                </div>
                            </div>

                            <div class="row mb-3" id="input-code">
                                <label for="input-code-field" class="col-sm-2 col-form-label">{{ $lang->column_code }}</label>
                                <div class="col-sm-10">
                                    <input type="text" name="code" value="{{ old('code', $company->code) }}" placeholder="{{ $lang->placeholder_code }}" id="input-code-field" class="form-control" maxlength="20">
                                    <div id="error-code" class="invalid-feedback"></div>
                                </div>
                            </div>

                            <div class="row mb-3" id="input-business-no">
                                <label for="input-business-no-field" class="col-sm-2 col-form-label">{{ $lang->column_business_no }}</label>
                                <div class="col-sm-10">
                                    <input type="text" name="business_no" value="{{ old('business_no', $company->business_no) }}" placeholder="{{ $lang->placeholder_business_no }}" id="input-business-no-field" class="form-control" maxlength="20">
                                    <div id="error-business-no" class="invalid-feedback"></div>
                                </div>
                            </div>

                            <div class="row mb-3" id="input-phone">
                                <label for="input-phone-field" class="col-sm-2 col-form-label">{{ $lang->column_phone }}</label>
                                <div class="col-sm-10">
                                    <input type="text" name="phone" value="{{ old('phone', $company->phone) }}" placeholder="{{ $lang->placeholder_phone }}" id="input-phone-field" class="form-control" maxlength="30">
                                    <div id="error-phone" class="invalid-feedback"></div>
                                </div>
                            </div>

                            <div class="row mb-3" id="input-address">
                                <label for="input-address-field" class="col-sm-2 col-form-label">{{ $lang->column_address }}</label>
                                <div class="col-sm-10">
                                    <input type="text" name="address" value="{{ old('address', $company->address) }}" placeholder="{{ $lang->placeholder_address }}" id="input-address-field" class="form-control" maxlength="255">
                                    <div id="error-address" class="invalid-feedback"></div>
                                </div>
                            </div>

                            <div class="row mb-3 required" id="input-is-active">
                                <label for="input-is-active-field" class="col-sm-2 col-form-label">{{ $lang->column_is_active }}</label>
                                <div class="col-sm-10">
                                    <select name="is_active" id="input-is-active-field" class="form-select">
                                        <option value="1" {{ old('is_active', $company->exists ? $company->is_active : 1) == 1 ? 'selected' : '' }}>{{ $lang->text_active }}</option>
                                        <option value="0" {{ old('is_active', $company->exists ? $company->is_active : 1) == 0 ? 'selected' : '' }}>{{ $lang->text_inactive }}</option>
                                    </select>
                                    <div id="error-is-active" class="invalid-feedback"></div>
                                </div>
                            </div>

                            <div class="row mb-3 required" id="input-sort-order">
                                <label for="input-sort-order-field" class="col-sm-2 col-form-label">{{ $lang->column_sort_order }}</label>
                                <div class="col-sm-10">
                                    <input type="number" name="sort_order" value="{{ old('sort_order', $company->sort_order ?? 0) }}" placeholder="{{ $lang->placeholder_sort_order }}" id="input-sort-order-field" class="form-control" min="0">
                                    <div id="error-sort-order" class="invalid-feedback"></div>
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
