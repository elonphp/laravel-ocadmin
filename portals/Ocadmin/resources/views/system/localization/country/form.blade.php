@extends('ocadmin::layouts.app')

@section('title', $country->exists ? '編輯國家' : '新增國家')

@section('content')
<div id="content">
    <div class="page-header">
        <div class="container-fluid">
            <div class="float-end">
                <button type="submit" form="form-country" data-bs-toggle="tooltip" title="儲存" class="btn btn-primary">
                    <i class="fa-solid fa-floppy-disk"></i>
                </button>
                <a href="{{ route('ocadmin.system.localization.country.index') }}" data-bs-toggle="tooltip" title="返回" class="btn btn-light">
                    <i class="fa-solid fa-reply"></i>
                </a>
            </div>
            <h1>{{ $country->exists ? '編輯國家' : '新增國家' }}</h1>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('ocadmin.dashboard') }}">首頁</a></li>
                <li class="breadcrumb-item"><a href="#">系統管理</a></li>
                <li class="breadcrumb-item"><a href="{{ route('ocadmin.system.localization.country.index') }}">國家管理</a></li>
            </ol>
        </div>
    </div>
    <div class="container-fluid">
        <div class="card">
            <div class="card-header"><i class="fa-solid fa-pencil"></i> {{ $country->exists ? '編輯國家' : '新增國家' }}</div>
            <div class="card-body">
                <form id="form-country" action="{{ $country->exists ? route('ocadmin.system.localization.country.update', $country) : route('ocadmin.system.localization.country.store') }}" method="post" data-oc-toggle="ajax">
                    @csrf
                    @if($country->exists)
                    @method('PUT')
                    @endif

                    <div class="row mb-3 required" id="input-name">
                        <label for="input-name-field" class="col-sm-2 col-form-label">國家名稱</label>
                        <div class="col-sm-10">
                            <input type="text" name="name" value="{{ old('name', $country->name) }}" placeholder="請輸入國家名稱" id="input-name-field" class="form-control">
                            <div id="error-name" class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="row mb-3" id="input-native-name">
                        <label for="input-native-name-field" class="col-sm-2 col-form-label">本地名稱</label>
                        <div class="col-sm-10">
                            <input type="text" name="native_name" value="{{ old('native_name', $country->native_name) }}" placeholder="如：中華民國、日本国" id="input-native-name-field" class="form-control">
                            <div id="error-native-name" class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="row mb-3 required" id="input-iso-code-2">
                        <label for="input-iso-code-2-field" class="col-sm-2 col-form-label">ISO 代碼 (2)</label>
                        <div class="col-sm-10">
                            <input type="text" name="iso_code_2" value="{{ old('iso_code_2', $country->iso_code_2) }}" placeholder="如：TW" id="input-iso-code-2-field" class="form-control" maxlength="2" style="text-transform: uppercase;">
                            <div id="error-iso-code-2" class="invalid-feedback"></div>
                            <div class="form-text">ISO 3166-1 alpha-2 代碼</div>
                        </div>
                    </div>

                    <div class="row mb-3 required" id="input-iso-code-3">
                        <label for="input-iso-code-3-field" class="col-sm-2 col-form-label">ISO 代碼 (3)</label>
                        <div class="col-sm-10">
                            <input type="text" name="iso_code_3" value="{{ old('iso_code_3', $country->iso_code_3) }}" placeholder="如：TWN" id="input-iso-code-3-field" class="form-control" maxlength="3" style="text-transform: uppercase;">
                            <div id="error-iso-code-3" class="invalid-feedback"></div>
                            <div class="form-text">ISO 3166-1 alpha-3 代碼</div>
                        </div>
                    </div>

                    <div class="row mb-3" id="input-address-format">
                        <label for="input-address-format-field" class="col-sm-2 col-form-label">地址格式</label>
                        <div class="col-sm-10">
                            <textarea name="address_format" rows="4" placeholder="請輸入地址格式範本" id="input-address-format-field" class="form-control">{{ old('address_format', $country->address_format) }}</textarea>
                            <div id="error-address-format" class="invalid-feedback"></div>
                            <div class="form-text">可用變數：{firstname}, {lastname}, {company}, {address_1}, {address_2}, {city}, {postcode}, {zone}, {country}</div>
                        </div>
                    </div>

                    <div class="row mb-3" id="input-postcode-required">
                        <label class="col-sm-2 col-form-label">郵遞區號必填</label>
                        <div class="col-sm-10">
                            <div class="form-check form-switch form-switch-lg">
                                <input type="hidden" name="postcode_required" value="0">
                                <input type="checkbox" name="postcode_required" value="1" id="input-postcode-required-field" class="form-check-input" {{ old('postcode_required', $country->postcode_required) ? 'checked' : '' }}>
                            </div>
                            <div id="error-postcode-required" class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="row mb-3" id="input-is-active">
                        <label class="col-sm-2 col-form-label">狀態</label>
                        <div class="col-sm-10">
                            <div class="form-check form-switch form-switch-lg">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" name="is_active" value="1" id="input-is-active-field" class="form-check-input" {{ old('is_active', $country->is_active ?? true) ? 'checked' : '' }}>
                            </div>
                            <div id="error-is-active" class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="row mb-3" id="input-sort-order">
                        <label for="input-sort-order-field" class="col-sm-2 col-form-label">排序</label>
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
