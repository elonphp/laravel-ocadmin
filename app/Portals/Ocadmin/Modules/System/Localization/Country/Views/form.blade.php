@extends('ocadmin::layouts.app')

@section('title', $country->exists ? $lang->text_edit : $lang->text_add)

@section('content')
<div id="content">
    <div class="page-header">
        <div class="container-fluid">
            <div class="float-end">
                <button type="submit" form="form-country" data-bs-toggle="tooltip" title="{{ $lang->button_save }}" class="btn btn-primary">
                    <i class="fa-solid fa-floppy-disk"></i>
                </button>
                <a href="{{ route('lang.ocadmin.system.localization.country.index') }}" data-bs-toggle="tooltip" title="{{ $lang->button_back }}" class="btn btn-light">
                    <i class="fa-solid fa-reply"></i>
                </a>
            </div>
            <h1>{{ $country->exists ? $lang->text_edit : $lang->text_add }}</h1>
            @include('ocadmin::layouts.partials.breadcrumb')
        </div>
    </div>
    <div class="container-fluid">
        <div class="card">
            <div class="card-header"><i class="fa-solid fa-pencil"></i> {{ $country->exists ? $lang->text_edit : $lang->text_add }}</div>
            <div class="card-body">
                <form id="form-country" action="{{ $country->exists ? route('lang.ocadmin.system.localization.country.update', $country) : route('lang.ocadmin.system.localization.country.store') }}" method="post" data-oc-toggle="ajax">
                    @csrf
                    @if($country->exists)
                    @method('PUT')
                    @endif

                    <div class="row mb-3 required" id="input-name">
                        <label for="input-name-field" class="col-sm-2 col-form-label">{{ $lang->entry_name }}</label>
                        <div class="col-sm-10">
                            <input type="text" name="name" value="{{ old('name', $country->name) }}" placeholder="{{ $lang->placeholder_name }}" id="input-name-field" class="form-control">
                            <div id="error-name" class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="row mb-3" id="input-native-name">
                        <label for="input-native-name-field" class="col-sm-2 col-form-label">{{ $lang->entry_native_name }}</label>
                        <div class="col-sm-10">
                            <input type="text" name="native_name" value="{{ old('native_name', $country->native_name) }}" placeholder="{{ $lang->placeholder_native_name }}" id="input-native-name-field" class="form-control">
                            <div id="error-native-name" class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="row mb-3 required" id="input-iso-code-2">
                        <label for="input-iso-code-2-field" class="col-sm-2 col-form-label">{{ $lang->entry_iso_code_2 }}</label>
                        <div class="col-sm-10">
                            <input type="text" name="iso_code_2" value="{{ old('iso_code_2', $country->iso_code_2) }}" placeholder="{{ $lang->placeholder_iso_code_2 }}" id="input-iso-code-2-field" class="form-control" maxlength="2" style="text-transform: uppercase;">
                            <div id="error-iso-code-2" class="invalid-feedback"></div>
                            <div class="form-text">{{ $lang->help_iso_code_2 }}</div>
                        </div>
                    </div>

                    <div class="row mb-3 required" id="input-iso-code-3">
                        <label for="input-iso-code-3-field" class="col-sm-2 col-form-label">{{ $lang->entry_iso_code_3 }}</label>
                        <div class="col-sm-10">
                            <input type="text" name="iso_code_3" value="{{ old('iso_code_3', $country->iso_code_3) }}" placeholder="{{ $lang->placeholder_iso_code_3 }}" id="input-iso-code-3-field" class="form-control" maxlength="3" style="text-transform: uppercase;">
                            <div id="error-iso-code-3" class="invalid-feedback"></div>
                            <div class="form-text">{{ $lang->help_iso_code_3 }}</div>
                        </div>
                    </div>

                    <div class="row mb-3" id="input-address-format">
                        <label for="input-address-format-field" class="col-sm-2 col-form-label">{{ $lang->entry_address_format }}</label>
                        <div class="col-sm-10">
                            <textarea name="address_format" rows="4" placeholder="{{ $lang->placeholder_address_format }}" id="input-address-format-field" class="form-control">{{ old('address_format', $country->address_format) }}</textarea>
                            <div id="error-address-format" class="invalid-feedback"></div>
                            <div class="form-text">{{ $lang->help_address_format }}</div>
                        </div>
                    </div>

                    <div class="row mb-3" id="input-postcode-required">
                        <label class="col-sm-2 col-form-label">{{ $lang->entry_postcode_required }}</label>
                        <div class="col-sm-10">
                            <div class="form-check form-switch form-switch-lg">
                                <input type="hidden" name="postcode_required" value="0">
                                <input type="checkbox" name="postcode_required" value="1" id="input-postcode-required-field" class="form-check-input" {{ old('postcode_required', $country->postcode_required) ? 'checked' : '' }}>
                            </div>
                            <div id="error-postcode-required" class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="row mb-3" id="input-is-active">
                        <label class="col-sm-2 col-form-label">{{ $lang->text_status }}</label>
                        <div class="col-sm-10">
                            <div class="form-check form-switch form-switch-lg">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" name="is_active" value="1" id="input-is-active-field" class="form-check-input" {{ old('is_active', $country->is_active ?? true) ? 'checked' : '' }}>
                            </div>
                            <div id="error-is-active" class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="row mb-3" id="input-sort-order">
                        <label for="input-sort-order-field" class="col-sm-2 col-form-label">{{ $lang->text_sort_order }}</label>
                        <div class="col-sm-10">
                            <input type="number" name="sort_order" value="{{ old('sort_order', $country->sort_order ?? 0) }}" placeholder="0" id="input-sort-order-field" class="form-control" min="0">
                            <div id="error-sort-order" class="invalid-feedback"></div>
                        </div>
                    </div>

                    @if($country->exists)
                    <input type="hidden" name="country_id" value="{{ $country->id }}" id="input-country-id">
                    @endif
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
