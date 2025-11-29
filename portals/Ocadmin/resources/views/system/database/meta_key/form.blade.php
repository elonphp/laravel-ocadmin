@extends('ocadmin::layouts.app')

@section('title', $metaKey->exists ? '編輯欄位定義' : '新增欄位定義')

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
                <button type="submit" form="form-meta-key" data-bs-toggle="tooltip" title="儲存" class="btn btn-primary">
                    <i class="fa-solid fa-floppy-disk"></i>
                </button>
                <a href="{{ route('ocadmin.system.database.meta_key.index') }}" data-bs-toggle="tooltip" title="返回" class="btn btn-light">
                    <i class="fa-solid fa-reply"></i>
                </a>
            </div>
            <h1>{{ $metaKey->exists ? '編輯欄位定義' : '新增欄位定義' }}</h1>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('ocadmin.dashboard') }}">首頁</a></li>
                <li class="breadcrumb-item"><a href="#">系統管理</a></li>
                <li class="breadcrumb-item"><a href="#">資料庫</a></li>
                <li class="breadcrumb-item"><a href="{{ route('ocadmin.system.database.meta_key.index') }}">欄位定義</a></li>
            </ol>
        </div>
    </div>
    <div class="container-fluid">
        <div class="card">
            <div class="card-header"><i class="fa-solid fa-pencil"></i> {{ $metaKey->exists ? '編輯欄位定義' : '新增欄位定義' }}</div>
            <div class="card-body">
                <form id="form-meta-key" action="{{ $metaKey->exists ? route('ocadmin.system.database.meta_key.update', $metaKey) : route('ocadmin.system.database.meta_key.store') }}" method="post" data-oc-toggle="ajax">
                    @csrf
                    @if($metaKey->exists)
                    @method('PUT')
                    @endif

                    @if($metaKey->exists)
                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label">ID</label>
                        <div class="col-sm-10">
                            <input type="text" value="{{ $metaKey->id }}" class="form-control" readonly disabled>
                            <div class="form-text">ID 為自動產生的數字，用於 EAV 模式的 key_id 欄位</div>
                        </div>
                    </div>
                    @endif

                    <div class="row mb-3 required" id="input-name">
                        <label for="input-name-field" class="col-sm-2 col-form-label">欄位名稱</label>
                        <div class="col-sm-10">
                            <input type="text" name="name" value="{{ old('name', $metaKey->name) }}" placeholder="如：phone, birthday, member_level" id="input-name-field" class="form-control" maxlength="50" pattern="[a-z][a-z0-9_]*">
                            <div id="error-name" class="invalid-feedback"></div>
                            <div class="form-text">只能使用小寫英文、數字和底線，必須以英文字母開頭</div>
                        </div>
                    </div>

                    <div class="row mb-3" id="input-table-name">
                        <label for="input-table-name-field" class="col-sm-2 col-form-label">所屬資料表</label>
                        <div class="col-sm-10">
                            <select name="table_name" id="input-table-name-field" class="form-select">
                                @if($metaKey->table_name)
                                <option value="{{ $metaKey->table_name }}" selected>{{ $metaKey->table_name }}</option>
                                @endif
                            </select>
                            <div id="error-table-name" class="invalid-feedback"></div>
                            <div class="form-text">留空表示共用欄位（所有資料表都可使用），指定表名則為該表專屬欄位。可輸入新的表名。</div>
                        </div>
                    </div>

                    <div class="row mb-3" id="input-description">
                        <label for="input-description-field" class="col-sm-2 col-form-label">欄位說明</label>
                        <div class="col-sm-10">
                            <input type="text" name="description" value="{{ old('description', $metaKey->description) }}" placeholder="如：電話、生日、會員等級" id="input-description-field" class="form-control" maxlength="100">
                            <div id="error-description" class="invalid-feedback"></div>
                        </div>
                    </div>

                    @if($metaKey->exists)
                    <input type="hidden" name="meta_key_id" value="{{ $metaKey->id }}" id="input-meta-key-id">
                    @endif
                </form>
            </div>
        </div>

        @if($metaKey->exists)
        <div class="card mt-3">
            <div class="card-header"><i class="fa-solid fa-info-circle"></i> 使用說明</div>
            <div class="card-body">
                <p>此欄位定義用於 EAV（Entity-Attribute-Value）模式的擴展欄位系統。</p>
                <h6>在 Model 中使用：</h6>
                <pre class="bg-light p-3 rounded"><code>// 設定 meta 值
$account->setMeta('{{ $metaKey->name }}', $value);

// 取得 meta 值
$value = $account->getMeta('{{ $metaKey->name }}');

// 檢查是否存在
if ($account->hasMeta('{{ $metaKey->name }}')) {
    // ...
}</code></pre>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('assets-ocadmin/vendor/select2/select2.min.js') }}"></script>
<script>
(function() {
    // 資料表名稱快取
    var tableNamesCache = null;

    // 編輯時已選的 table_name
    var selectedTableName = '{{ old("table_name", $metaKey->table_name) }}';

    // 初始化 select2
    var $tableNameSelect = $('#input-table-name-field');

    $tableNameSelect.select2({
        placeholder: '-- 共用欄位（留空）--',
        allowClear: true,
        width: '100%',
        tags: true,  // 允許輸入新的值
        createTag: function(params) {
            var term = $.trim(params.term);
            if (term === '') return null;
            // 驗證格式：只能是小寫英文、數字和底線，以英文開頭
            if (!/^[a-z][a-z0-9_]*$/.test(term)) {
                return null;
            }
            return {
                id: term,
                text: term,
                newTag: true
            };
        },
        ajax: {
            url: '{{ route("ocadmin.system.database.meta_key.table-names") }}',
            dataType: 'json',
            delay: 0,
            cache: true,
            transport: function(params, success, failure) {
                // 如果有快取，直接使用
                if (tableNamesCache !== null) {
                    success(tableNamesCache);
                    return;
                }

                // 發送 AJAX 請求
                $.ajax({
                    url: params.url,
                    dataType: 'json'
                }).then(function(data) {
                    // 儲存到快取
                    tableNamesCache = data;
                    success(data);
                }).fail(failure);
            },
            processResults: function(data, params) {
                var searchTerm = (params.term || '').toLowerCase();

                // 過濾結果（打字搜尋）
                var filtered = data.filter(function(tableName) {
                    if (!searchTerm) return true;
                    return tableName.toLowerCase().indexOf(searchTerm) > -1;
                });

                return {
                    results: filtered.map(function(tableName) {
                        return { id: tableName, text: tableName };
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
