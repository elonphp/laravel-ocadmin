@extends('ocadmin::layouts.app')

@section('title', $permission->exists ? '編輯權限' : '新增權限')

@section('content')
<div id="content">
    <div class="page-header">
        <div class="container-fluid">
            <div class="float-end">
                <button type="submit" form="form-permission" data-bs-toggle="tooltip" title="儲存" class="btn btn-primary">
                    <i class="fa-solid fa-save"></i>
                </button>
                <a href="{{ route('lang.ocadmin.system.permission.index') }}" data-bs-toggle="tooltip" title="返回" class="btn btn-secondary">
                    <i class="fa-solid fa-reply"></i>
                </a>
            </div>
            <h1>{{ $permission->exists ? '編輯權限' : '新增權限' }}</h1>
            @include('ocadmin::layouts.partials.breadcrumb')
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
                <i class="fa-solid fa-pencil"></i> {{ $permission->exists ? '編輯權限' : '新增權限' }}
            </div>
            <div class="card-body">
                <ul class="nav nav-tabs">
                    <li class="nav-item"><a href="#tab-trans" data-bs-toggle="tab" class="nav-link active">翻譯</a></li>
                    <li class="nav-item"><a href="#tab-data" data-bs-toggle="tab" class="nav-link">資料</a></li>
                </ul>
                <form action="{{ $permission->exists ? route('lang.ocadmin.system.permission.update', $permission) : route('lang.ocadmin.system.permission.store') }}" method="post" id="form-permission">
                    @csrf
                    @if($permission->exists)
                    @method('PUT')
                    @endif

                    @php $translationsArray = $permission->exists ? $permission->getTranslationsArray() : []; @endphp

                    <div class="tab-content">
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
                                        <label for="input-display-name-{{ $locale }}" class="col-sm-2 col-form-label">顯示名稱</label>
                                        <div class="col-sm-10">
                                            <input type="text" name="translations[{{ $locale }}][display_name]" value="{{ old("translations.{$locale}.display_name", $translationsArray[$locale]['display_name'] ?? '') }}" placeholder="請輸入顯示名稱" id="input-display-name-{{ $locale }}" class="form-control @error("translations.{$locale}.display_name") is-invalid @enderror" maxlength="100">
                                            @error("translations.{$locale}.display_name")
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label for="input-note-{{ $locale }}" class="col-sm-2 col-form-label">備註</label>
                                        <div class="col-sm-10">
                                            <input type="text" name="translations[{{ $locale }}][note]" value="{{ old("translations.{$locale}.note", $translationsArray[$locale]['note'] ?? '') }}" placeholder="備註說明" id="input-note-{{ $locale }}" class="form-control" maxlength="255">
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        <div id="tab-data" class="tab-pane">
                            <div class="row mb-3 required">
                                <label for="input-name" class="col-sm-2 col-form-label">權限代碼</label>
                                <div class="col-sm-10">
                                    <input type="text" name="name" value="{{ old('name', $permission->name) }}" placeholder="如 mss.employee.list" id="input-name" class="form-control @error('name') is-invalid @enderror" pattern="[a-z][a-z0-9_]*(\.[a-z][a-z0-9_]*)*" maxlength="100">
                                    @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">三段式格式：{module}.{resource}.{action}，僅限小寫英文、數字、底線、點</div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="input-guard-name" class="col-sm-2 col-form-label">Guard</label>
                                <div class="col-sm-10">
                                    <input type="text" name="guard_name" value="{{ old('guard_name', $permission->guard_name ?? 'web') }}" id="input-guard-name" class="form-control" maxlength="50">
                                    <div class="form-text">預設為 web，通常不需修改</div>
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
