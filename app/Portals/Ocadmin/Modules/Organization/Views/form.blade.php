@extends('ocadmin::layouts.app')

@section('title', $organization->exists ? $lang->text_edit : $lang->text_add)

@section('content')
<div id="content">
    <div class="page-header">
        <div class="container-fluid">
            <div class="float-end">
                <button type="submit" form="form-organization" data-bs-toggle="tooltip" title="{{ $lang->button_save }}" class="btn btn-primary">
                    <i class="fa-solid fa-save"></i>
                </button>
                <a href="{{ route('lang.ocadmin.organization.index') }}" data-bs-toggle="tooltip" title="{{ $lang->button_back }}" class="btn btn-secondary">
                    <i class="fa-solid fa-reply"></i>
                </a>
            </div>
            <h1>{{ $organization->exists ? $lang->text_edit : $lang->text_add }}</h1>
            @include('ocadmin::layouts.partials.breadcrumb')
        </div>
    </div>

    <div class="container-fluid">
        <div class="card card-default">
            <div class="card-header">
                <i class="fa-solid fa-pencil"></i> {{ $organization->exists ? $lang->text_edit : $lang->text_add }}
            </div>
            <div class="card-body">
                <ul class="nav nav-tabs">
                    <li class="nav-item"><a href="#tab-trans" data-bs-toggle="tab" class="nav-link active">{{ $lang->tab_trans }}</a></li>
                    <li class="nav-item"><a href="#tab-data" data-bs-toggle="tab" class="nav-link">{{ $lang->tab_data }}</a></li>
                </ul>
                <form action="{{ $organization->exists ? route('lang.ocadmin.organization.update', $organization) : route('lang.ocadmin.organization.store') }}" method="post" id="form-organization" data-oc-toggle="ajax">
                    @csrf
                    @if($organization->exists)
                    @method('PUT')
                    @endif

                    @php $translationsArray = $organization->exists ? $organization->getTranslationsArray() : []; @endphp

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
                            <div class="row mb-3" id="input-business-no">
                                <label for="input-business-no-field" class="col-sm-2 col-form-label">{{ $lang->column_business_no }}</label>
                                <div class="col-sm-10">
                                    <input type="text" name="business_no" value="{{ old('business_no', $organization->business_no) }}" placeholder="{{ $lang->placeholder_business_no }}" id="input-business-no-field" class="form-control" maxlength="20">
                                    <div id="error-business-no" class="invalid-feedback"></div>
                                </div>
                            </div>

                            <div class="row mb-3" id="input-shipping-state">
                                <label for="input-shipping-state-field" class="col-sm-2 col-form-label">{{ $lang->column_shipping_state }}</label>
                                <div class="col-sm-10">
                                    <input type="text" name="shipping_state" value="{{ old('shipping_state', $organization->shipping_state) }}" placeholder="{{ $lang->placeholder_shipping_state }}" id="input-shipping-state-field" class="form-control" maxlength="255">
                                    <div id="error-shipping-state" class="invalid-feedback"></div>
                                </div>
                            </div>

                            <div class="row mb-3" id="input-shipping-city">
                                <label for="input-shipping-city-field" class="col-sm-2 col-form-label">{{ $lang->column_shipping_city }}</label>
                                <div class="col-sm-10">
                                    <input type="text" name="shipping_city" value="{{ old('shipping_city', $organization->shipping_city) }}" placeholder="{{ $lang->placeholder_shipping_city }}" id="input-shipping-city-field" class="form-control" maxlength="255">
                                    <div id="error-shipping-city" class="invalid-feedback"></div>
                                </div>
                            </div>

                            <div class="row mb-3" id="input-shipping-address1">
                                <label for="input-shipping-address1-field" class="col-sm-2 col-form-label">{{ $lang->column_shipping_address1 }}</label>
                                <div class="col-sm-10">
                                    <input type="text" name="shipping_address1" value="{{ old('shipping_address1', $organization->shipping_address1) }}" placeholder="{{ $lang->placeholder_shipping_address1 }}" id="input-shipping-address1-field" class="form-control" maxlength="255">
                                    <div id="error-shipping-address1" class="invalid-feedback"></div>
                                </div>
                            </div>

                            <div class="row mb-3" id="input-shipping-address2">
                                <label for="input-shipping-address2-field" class="col-sm-2 col-form-label">{{ $lang->column_shipping_address2 }}</label>
                                <div class="col-sm-10">
                                    <input type="text" name="shipping_address2" value="{{ old('shipping_address2', $organization->shipping_address2) }}" placeholder="{{ $lang->placeholder_shipping_address2 }}" id="input-shipping-address2-field" class="form-control" maxlength="255">
                                    <div id="error-shipping-address2" class="invalid-feedback"></div>
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
