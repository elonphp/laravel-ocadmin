@extends('adminlte::layouts.app')

@section('title', $role->exists ? $lang->text_edit : $lang->text_add)

@section('content')
<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">{{ $role->exists ? $lang->text_edit : $lang->text_add }}</h3>
            </div>
            <div class="col-sm-6">
                @include('adminlte::layouts.partials.breadcrumb')
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="mb-3 text-end">
            <button type="submit" form="form-role" data-bs-toggle="tooltip" title="{{ $lang->button_save }}" class="btn btn-primary">
                <i class="bi bi-floppy"></i>
            </button>
            <a href="{{ route('lang.ocadmin.system.role.index') }}" data-bs-toggle="tooltip" title="{{ $lang->button_back }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i>
            </a>
        </div>

        <div class="card">
            <div class="card-header">
                <i class="bi bi-pencil"></i> {{ $role->exists ? $lang->text_edit : $lang->text_add }}
            </div>
            <div class="card-body">
                <ul class="nav nav-tabs">
                    <li class="nav-item"><a href="#tab-trans" data-bs-toggle="tab" class="nav-link active">{{ $lang->tab_trans }}</a></li>
                    <li class="nav-item"><a href="#tab-data" data-bs-toggle="tab" class="nav-link">{{ $lang->tab_data }}</a></li>
                    <li class="nav-item"><a href="#tab-permission" data-bs-toggle="tab" class="nav-link">{{ $lang->tab_permission }}</a></li>
                </ul>
                <form action="{{ $role->exists ? route('lang.ocadmin.system.role.update', $role) : route('lang.ocadmin.system.role.store') }}" method="post" id="form-role" data-oc-toggle="ajax">
                    @csrf
                    @if($role->exists)
                    @method('PUT')
                    @endif

                    @php $translationsArray = $role->exists ? $role->getTranslationsArray() : []; @endphp

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
                                        <label for="input-display_name-{{ $locale }}" class="col-sm-2 col-form-label">{{ $lang->column_display_name }}</label>
                                        <div class="col-sm-10">
                                            <input type="text" name="translations[{{ $locale }}][display_name]" value="{{ old("translations.{$locale}.display_name", $translationsArray[$locale]['display_name'] ?? '') }}" placeholder="{{ $lang->placeholder_display_name }}" id="input-display_name-{{ $locale }}" class="form-control" maxlength="100">
                                            <div id="error-display_name-{{ $locale }}" class="invalid-feedback"></div>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label for="input-note-{{ $locale }}" class="col-sm-2 col-form-label">{{ $lang->column_note }}</label>
                                        <div class="col-sm-10">
                                            <input type="text" name="translations[{{ $locale }}][note]" value="{{ old("translations.{$locale}.note", $translationsArray[$locale]['note'] ?? '') }}" placeholder="{{ $lang->placeholder_note }}" id="input-note-{{ $locale }}" class="form-control" maxlength="255">
                                            <div id="error-note-{{ $locale }}" class="invalid-feedback"></div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- 基本資料 --}}
                        <div id="tab-data" class="tab-pane">
                            <div class="row mb-3 required">
                                <label for="input-name" class="col-sm-2 col-form-label">{{ $lang->column_name }}</label>
                                <div class="col-sm-10">
                                    <input type="text" name="name" value="{{ old('name', $role->name) }}" placeholder="{{ $lang->placeholder_name }}" id="input-name" class="form-control" pattern="[a-z][a-z0-9_]*(\.[a-z][a-z0-9_]*)*" maxlength="100">
                                    <div id="error-name" class="invalid-feedback"></div>
                                    <div class="form-text">{{ $lang->help_name }}</div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="input-guard_name" class="col-sm-2 col-form-label">{{ $lang->column_guard_name }}</label>
                                <div class="col-sm-10">
                                    <input type="text" name="guard_name" value="{{ old('guard_name', $role->guard_name ?? 'web') }}" id="input-guard_name" class="form-control" maxlength="50">
                                    <div id="error-guard_name" class="invalid-feedback"></div>
                                    <div class="form-text">{{ $lang->help_guard_name }}</div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="input-sort_order" class="col-sm-2 col-form-label">{{ $lang->column_sort_order }}</label>
                                <div class="col-sm-10">
                                    <input type="number" name="sort_order" value="{{ old('sort_order', $role->sort_order ?? 0) }}" id="input-sort_order" class="form-control" min="0">
                                    <div id="error-sort_order" class="invalid-feedback"></div>
                                </div>
                            </div>

                            <div class="row mb-3 required">
                                <label for="input-is_active" class="col-sm-2 col-form-label">{{ $lang->column_is_active }}</label>
                                <div class="col-sm-10">
                                    <select name="is_active" id="input-is_active" class="form-select">
                                        <option value="1" @selected(old('is_active', $role->is_active ?? 1) == 1)>{{ $lang->text_enabled }}</option>
                                        <option value="0" @selected(old('is_active', $role->is_active ?? 1) == 0)>{{ $lang->text_disabled }}</option>
                                    </select>
                                    <div id="error-is_active" class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>

                        {{-- 權限指派 --}}
                        <div id="tab-permission" class="tab-pane">
                            @forelse($permissionGroups as $group => $permissions)
                            <div class="card mb-3">
                                <div class="card-header">
                                    @php $groupLabel = $lang->{'permission_group_' . str_replace('.', '_', $group)}; @endphp
                                    <strong>{{ $group }}.* — {{ $groupLabel }}</strong>
                                    <label class="float-end">
                                        <input type="checkbox" class="form-check-input check-group" data-group="{{ $group }}"> {{ $lang->text_all }}
                                    </label>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        @foreach($permissions as $permission)
                                        <div class="col-lg-4 col-md-6 mb-2">
                                            <div class="form-check">
                                                <input type="checkbox" name="permissions[]" value="{{ $permission->id }}" id="permission-{{ $permission->id }}" class="form-check-input permission-item" data-group="{{ $group }}" @checked(in_array($permission->id, $rolePermissions))>
                                                <label for="permission-{{ $permission->id }}" class="form-check-label">
                                                    <code>{{ $permission->name }}</code>
                                                    <small class="text-muted d-block">{{ $permission->display_name }}</small>
                                                </label>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            @empty
                            <div class="text-center text-muted py-4">{{ $lang->text_no_data }}</div>
                            @endforelse
                        </div>
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
    // 群組全選/取消
    $('.check-group').on('change', function() {
        var group = $(this).data('group');
        var checked = $(this).prop('checked');
        $('.permission-item[data-group="' + group + '"]').prop('checked', checked);
    });

    // 更新群組 checkbox 狀態
    $('.permission-item').on('change', function() {
        var group = $(this).data('group');
        var $items = $('.permission-item[data-group="' + group + '"]');
        var total = $items.length;
        var checked = $items.filter(':checked').length;
        $('.check-group[data-group="' + group + '"]').prop('checked', total === checked);
    });

    // 初始化群組 checkbox 狀態
    $('.check-group').each(function() {
        var group = $(this).data('group');
        var $items = $('.permission-item[data-group="' + group + '"]');
        var total = $items.length;
        var checked = $items.filter(':checked').length;
        $(this).prop('checked', total > 0 && total === checked);
    });
});
</script>
@endsection
