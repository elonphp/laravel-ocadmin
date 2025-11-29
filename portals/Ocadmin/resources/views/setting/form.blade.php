@extends('ocadmin::layouts.app')

@section('title', $setting->exists ? '編輯參數' : '新增參數')

@section('content')
<div id="content">
    <div class="page-header">
        <div class="container-fluid">
            <div class="float-end">
                <button type="submit" form="form-setting" data-bs-toggle="tooltip" title="儲存" class="btn btn-primary">
                    <i class="fa-solid fa-save"></i>
                </button>
                <a href="{{ route('ocadmin.setting.index') }}" data-bs-toggle="tooltip" title="返回" class="btn btn-secondary">
                    <i class="fa-solid fa-reply"></i>
                </a>
            </div>
            <h1>{{ $setting->exists ? '編輯參數' : '新增參數' }}</h1>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('ocadmin.dashboard') }}">首頁</a></li>
                <li class="breadcrumb-item"><a href="#">系統管理</a></li>
                <li class="breadcrumb-item"><a href="{{ route('ocadmin.setting.index') }}">參數設定</a></li>
                <li class="breadcrumb-item active">{{ $setting->exists ? '編輯' : '新增' }}</li>
            </ol>
        </div>
    </div>

    <div class="container-fluid">
        @if($errors->any())
        <div class="alert alert-danger alert-dismissible">
            <i class="fa-solid fa-exclamation-circle"></i>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <div class="card card-default">
            <div class="card-header">
                <i class="fa-solid fa-pencil"></i> {{ $setting->exists ? '編輯參數' : '新增參數' }}
            </div>
            <div class="card-body">
                <form action="{{ $setting->exists ? route('ocadmin.setting.update', $setting) : route('ocadmin.setting.store') }}" method="post" id="form-setting">
                    @csrf
                    @if($setting->exists)
                    @method('PUT')
                    @endif

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3 required">
                                <label class="form-label" for="input-code">代碼</label>
                                <input type="text" name="code" value="{{ old('code', $setting->code) }}" placeholder="請輸入代碼（如：site_name）" id="input-code" class="form-control @error('code') is-invalid @enderror">
                                @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">唯一識別碼，用於程式取得設定值</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="input-group">群組</label>
                                <input type="text" name="group" value="{{ old('group', $setting->group) }}" placeholder="請輸入群組（如：general、mail）" id="input-group" class="form-control">
                                <div class="form-text">用於將設定分類管理</div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="input-locale">語系</label>
                                <input type="text" name="locale" value="{{ old('locale', $setting->locale) }}" placeholder="請輸入語系代碼（如：zh-TW、en）" id="input-locale" class="form-control">
                                <div class="form-text">留空表示全域設定</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3 required">
                                <label class="form-label" for="input-type">類型</label>
                                <select name="type" id="input-type" class="form-select @error('type') is-invalid @enderror">
                                    @foreach($types as $type)
                                    <option value="{{ $type->value }}" {{ old('type', $setting->type?->value) === $type->value ? 'selected' : '' }}>{{ $type->label() }}</option>
                                    @endforeach
                                </select>
                                @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="input-content">內容</label>
                        <textarea name="content" rows="6" placeholder="請輸入設定值" id="input-content" class="form-control @error('content') is-invalid @enderror">{{ old('content', $setting->content) }}</textarea>
                        @error('content')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text" id="content-hint">
                            根據類型輸入對應格式的內容
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="input-note">備註</label>
                        <input type="text" name="note" value="{{ old('note', $setting->note) }}" placeholder="請輸入備註說明" id="input-note" class="form-control">
                        <div class="form-text">供管理人員參考用</div>
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
    // 類型提示
    var hints = {
        'text': '輸入純文字',
        'line': '一行一個項目',
        'json': '輸入有效的 JSON 格式',
        'serialized': '輸入 PHP 序列化格式',
        'bool': '輸入 1（是）或 0（否）',
        'int': '輸入整數',
        'float': '輸入小數',
        'array': '輸入逗號分隔的值'
    };

    $('#input-type').on('change', function() {
        var type = $(this).val();
        $('#content-hint').text(hints[type] || '根據類型輸入對應格式的內容');
    }).trigger('change');
});
</script>
@endsection
