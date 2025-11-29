@extends('ocadmin::layouts.app')

@section('title', $division->exists ? '編輯行政區域' : '新增行政區域')

@section('styles')
<link href="{{ asset('assets-ocadmin/vendor/select2/select2.min.css') }}" rel="stylesheet">
<style>
.select2-container--default .select2-selection--single {
    height: 38px;
    border: 1px solid #ced4da;
    border-radius: 0.375rem;
}
.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 36px;
    padding-left: 12px;
}
.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 36px;
}
</style>
@endsection

@section('content')
<div id="content">
    <div class="page-header">
        <div class="container-fluid">
            <div class="float-end">
                <button type="submit" form="form-division" data-bs-toggle="tooltip" title="儲存" class="btn btn-primary">
                    <i class="fa-solid fa-floppy-disk"></i>
                </button>
                <a href="{{ route('ocadmin.system.localization.division.index') }}" data-bs-toggle="tooltip" title="返回" class="btn btn-light">
                    <i class="fa-solid fa-reply"></i>
                </a>
            </div>
            <h1>{{ $division->exists ? '編輯行政區域' : '新增行政區域' }}</h1>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('ocadmin.dashboard') }}">首頁</a></li>
                <li class="breadcrumb-item"><a href="#">系統管理</a></li>
                <li class="breadcrumb-item"><a href="{{ route('ocadmin.system.localization.division.index') }}">行政區域</a></li>
            </ol>
        </div>
    </div>
    <div class="container-fluid">
        <div class="card">
            <div class="card-header"><i class="fa-solid fa-pencil"></i> {{ $division->exists ? '編輯行政區域' : '新增行政區域' }}</div>
            <div class="card-body">
                <form id="form-division" action="{{ $division->exists ? route('ocadmin.system.localization.division.update', $division) : route('ocadmin.system.localization.division.store') }}" method="post" data-oc-toggle="ajax">
                    @csrf
                    @if($division->exists)
                    @method('PUT')
                    @endif

                    <div class="row mb-3 required" id="input-country-code">
                        <label for="input-country-code-field" class="col-sm-2 col-form-label">所屬國家</label>
                        <div class="col-sm-10">
                            <select name="country_code" id="input-country-code-field" class="form-select">
                                @if($division->country)
                                <option value="{{ $division->country_code }}" selected>{{ $division->country->name }}{{ $division->country->native_name && $division->country->native_name !== $division->country->name ? ' (' . $division->country->native_name . ')' : '' }}</option>
                                @endif
                            </select>
                            <div id="error-country-code" class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="row mb-3 required" id="input-level">
                        <label for="input-level-field" class="col-sm-2 col-form-label">層級</label>
                        <div class="col-sm-10">
                            <select name="level" id="input-level-field" class="form-select">
                                <option value="">-- 請選擇 --</option>
                                <option value="1" {{ old('level', $division->level) == 1 ? 'selected' : '' }}>1 - 一級行政區（如：省、直轄市）</option>
                                <option value="2" {{ old('level', $division->level) == 2 ? 'selected' : '' }}>2 - 二級行政區（如：縣、市）</option>
                                <option value="3" {{ old('level', $division->level) == 3 ? 'selected' : '' }}>3 - 三級行政區（如：區、鄉、鎮）</option>
                            </select>
                            <div id="error-level" class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="row mb-3 required" id="input-name">
                        <label for="input-name-field" class="col-sm-2 col-form-label">區域名稱</label>
                        <div class="col-sm-10">
                            <input type="text" name="name" value="{{ old('name', $division->name) }}" placeholder="請輸入區域名稱（英文）" id="input-name-field" class="form-control">
                            <div id="error-name" class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="row mb-3 required" id="input-native-name">
                        <label for="input-native-name-field" class="col-sm-2 col-form-label">本地名稱</label>
                        <div class="col-sm-10">
                            <input type="text" name="native_name" value="{{ old('native_name', $division->native_name) }}" placeholder="如：台北市、新北市" id="input-native-name-field" class="form-control">
                            <div id="error-native-name" class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="row mb-3" id="input-code">
                        <label for="input-code-field" class="col-sm-2 col-form-label">代碼</label>
                        <div class="col-sm-10">
                            <input type="text" name="code" value="{{ old('code', $division->code) }}" placeholder="如：TPE、TXG" id="input-code-field" class="form-control" maxlength="32" style="text-transform: uppercase;">
                            <div id="error-code" class="invalid-feedback"></div>
                            <div class="form-text">行政區域代碼，用於系統識別</div>
                        </div>
                    </div>

                    <div class="row mb-3" id="input-is-active">
                        <label class="col-sm-2 col-form-label">狀態</label>
                        <div class="col-sm-10">
                            <div class="form-check form-switch form-switch-lg">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" name="is_active" value="1" id="input-is-active-field" class="form-check-input" {{ old('is_active', $division->is_active ?? true) ? 'checked' : '' }}>
                            </div>
                            <div id="error-is-active" class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="row mb-3" id="input-sort-order">
                        <label for="input-sort-order-field" class="col-sm-2 col-form-label">排序</label>
                        <div class="col-sm-10">
                            <input type="number" name="sort_order" value="{{ old('sort_order', $division->sort_order ?? 0) }}" placeholder="0" id="input-sort-order-field" class="form-control" min="0">
                            <div id="error-sort-order" class="invalid-feedback"></div>
                        </div>
                    </div>

                    @if($division->exists)
                    <input type="hidden" name="division_id" value="{{ $division->id }}" id="input-division-id">
                    @endif
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('assets-ocadmin/vendor/select2/select2.min.js') }}"></script>
<script>
(function() {
    // 國家資料快取（使用 JS 變數，頁面生命週期內有效）
    var countriesCache = null;

    // 編輯時已選的國家代碼
    var selectedCountryCode = '{{ old("country_code", $division->country_code) }}';

    // 初始化 select2
    var $countrySelect = $('#input-country-code-field');

    $countrySelect.select2({
        placeholder: '-- 請選擇國家 --',
        allowClear: true,
        width: '100%',
        ajax: {
            url: '{{ route("ocadmin.system.localization.country.all") }}',
            dataType: 'json',
            delay: 0,
            cache: true,
            transport: function(params, success, failure) {
                // 如果有快取，直接使用
                if (countriesCache !== null) {
                    success(countriesCache);
                    return;
                }

                // 發送 AJAX 請求
                $.ajax({
                    url: params.url,
                    dataType: 'json'
                }).then(function(data) {
                    // 儲存到快取
                    countriesCache = data;
                    success(data);
                }).fail(failure);
            },
            processResults: function(data, params) {
                var searchTerm = (params.term || '').toLowerCase();

                // 過濾結果（打字搜尋）
                var filtered = data.filter(function(country) {
                    if (!searchTerm) return true;
                    var name = (country.name || '').toLowerCase();
                    var nativeName = (country.native_name || '').toLowerCase();
                    var id = (country.id || '').toLowerCase();
                    return name.indexOf(searchTerm) > -1 ||
                           nativeName.indexOf(searchTerm) > -1 ||
                           id.indexOf(searchTerm) > -1;
                });

                return {
                    results: filtered.map(function(country) {
                        var text = country.name;
                        if (country.native_name && country.native_name !== country.name) {
                            text += ' (' + country.native_name + ')';
                        }
                        return { id: country.id, text: text };
                    })
                };
            }
        }
    });
})();
</script>
<style>
.select2-results__options {
    max-height: 200px !important;
}
</style>
@endsection
