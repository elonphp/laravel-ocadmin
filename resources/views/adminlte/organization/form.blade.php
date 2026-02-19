@extends('ocadmin::layouts.app')

@section('title', $organization->exists ? $lang->text_edit : $lang->text_add)

@section('content')
<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">{{ $organization->exists ? $lang->text_edit : $lang->text_add }}</h3>
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
            <button type="submit" form="form-organization" data-bs-toggle="tooltip" title="{{ $lang->button_save }}" class="btn btn-primary">
                <i class="bi bi-floppy"></i>
            </button>
            <a href="{{ route('lang.ocadmin.organization.index') }}" data-bs-toggle="tooltip" title="{{ $lang->button_back }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i>
            </a>
        </div>

        <div class="card">
            <div class="card-header">
                <i class="bi bi-pencil"></i> {{ $organization->exists ? $lang->text_edit : $lang->text_add }}
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
                                    <div class="row mb-3 required">
                                        <label for="input-name-{{ $locale }}" class="col-sm-2 col-form-label">{{ $lang->column_name }}</label>
                                        <div class="col-sm-10">
                                            <input type="text" name="translations[{{ $locale }}][name]" value="{{ old("translations.{$locale}.name", $translationsArray[$locale]['name'] ?? '') }}" placeholder="{{ $lang->placeholder_name }}" id="input-name-{{ $locale }}" class="form-control" maxlength="200">
                                            <div id="error-name-{{ $locale }}" class="invalid-feedback"></div>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label for="input-short_name-{{ $locale }}" class="col-sm-2 col-form-label">{{ $lang->column_short_name }}</label>
                                        <div class="col-sm-10">
                                            <input type="text" name="translations[{{ $locale }}][short_name]" value="{{ old("translations.{$locale}.short_name", $translationsArray[$locale]['short_name'] ?? '') }}" placeholder="{{ $lang->placeholder_short_name }}" id="input-short_name-{{ $locale }}" class="form-control" maxlength="100">
                                            <div id="error-short_name-{{ $locale }}" class="invalid-feedback"></div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- 基本資料 --}}
                        <div id="tab-data" class="tab-pane">
                            <div class="row mb-3">
                                <label for="input-business_no" class="col-sm-2 col-form-label">{{ $lang->column_business_no }}</label>
                                <div class="col-sm-10">
                                    <input type="text" name="business_no" value="{{ old('business_no', $organization->business_no) }}" placeholder="{{ $lang->placeholder_business_no }}" id="input-business_no" class="form-control" maxlength="20">
                                    <div id="error-business_no" class="invalid-feedback"></div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="input-shipping_state" class="col-sm-2 col-form-label">{{ $lang->column_shipping_state }}</label>
                                <div class="col-sm-10">
                                    <input type="text" name="shipping_state" value="{{ old('shipping_state', $organization->shipping_state) }}" placeholder="{{ $lang->placeholder_shipping_state }}" id="input-shipping_state" class="form-control" maxlength="255">
                                    <div id="error-shipping_state" class="invalid-feedback"></div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="input-shipping_city" class="col-sm-2 col-form-label">{{ $lang->column_shipping_city }}</label>
                                <div class="col-sm-10">
                                    <input type="text" name="shipping_city" value="{{ old('shipping_city', $organization->shipping_city) }}" placeholder="{{ $lang->placeholder_shipping_city }}" id="input-shipping_city" class="form-control" maxlength="255">
                                    <div id="error-shipping_city" class="invalid-feedback"></div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="input-shipping_address1" class="col-sm-2 col-form-label">{{ $lang->column_shipping_address1 }}</label>
                                <div class="col-sm-10">
                                    <input type="text" name="shipping_address1" value="{{ old('shipping_address1', $organization->shipping_address1) }}" placeholder="{{ $lang->placeholder_shipping_address1 }}" id="input-shipping_address1" class="form-control" maxlength="255">
                                    <div id="error-shipping_address1" class="invalid-feedback"></div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="input-shipping_address2" class="col-sm-2 col-form-label">{{ $lang->column_shipping_address2 }}</label>
                                <div class="col-sm-10">
                                    <input type="text" name="shipping_address2" value="{{ old('shipping_address2', $organization->shipping_address2) }}" placeholder="{{ $lang->placeholder_shipping_address2 }}" id="input-shipping_address2" class="form-control" maxlength="255">
                                    <div id="error-shipping_address2" class="invalid-feedback"></div>
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
