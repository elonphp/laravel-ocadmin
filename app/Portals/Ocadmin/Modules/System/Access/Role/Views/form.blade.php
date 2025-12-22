@extends('ocadmin::layouts.app')

@section('title', $role->exists ? $lang->text_edit : $lang->text_add)

@section('content')
<div id="content">
    <div class="page-header">
        <div class="container-fluid">
            <div class="float-end">
                <button type="submit" form="form-role" data-bs-toggle="tooltip" title="{{ $lang->button_save }}" class="btn btn-primary">
                    <i class="fa-solid fa-floppy-disk"></i>
                </button>
                <a href="{{ route('lang.ocadmin.system.access.role.index') }}" data-bs-toggle="tooltip" title="{{ $lang->button_back }}" class="btn btn-light">
                    <i class="fa-solid fa-reply"></i>
                </a>
            </div>
            <h1>{{ $role->exists ? $lang->text_edit : $lang->text_add }}</h1>
            @include('ocadmin::layouts.partials.breadcrumb')
        </div>
    </div>
    <div class="container-fluid">
        <div class="card">
            <div class="card-header"><i class="fa-solid fa-pencil"></i> {{ $role->exists ? $lang->text_edit : $lang->text_add }}</div>
            <div class="card-body">
                <form id="form-role" action="{{ $role->exists ? route('lang.ocadmin.system.access.role.update', $role) : route('lang.ocadmin.system.access.role.store') }}" method="post" data-oc-toggle="ajax">
                    @csrf
                    @if($role->exists)
                    @method('PUT')
                    @endif

                    <ul class="nav nav-tabs mb-3" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-general" type="button">{{ $lang->tab_general }}</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-permissions" type="button">{{ $lang->tab_permissions }}</button>
                        </li>
                    </ul>

                    <div class="tab-content">
                        {{-- 基本資料 --}}
                        <div class="tab-pane fade show active" id="tab-general" role="tabpanel">
                            <div class="row mb-3 required" id="input-name">
                                <label for="input-name-field" class="col-sm-2 col-form-label">{{ $lang->entry_name }}</label>
                                <div class="col-sm-10">
                                    <input type="text" name="name" value="{{ old('name', $role->name) }}" placeholder="{{ $lang->placeholder_name }}" id="input-name-field" class="form-control">
                                    <div id="error-name" class="invalid-feedback"></div>
                                    <div class="form-text">{{ $lang->help_name }}</div>
                                </div>
                            </div>

                            <div class="row mb-3 required" id="input-guard-name">
                                <label for="input-guard-name-field" class="col-sm-2 col-form-label">{{ $lang->entry_guard_name }}</label>
                                <div class="col-sm-10">
                                    <input type="text" name="guard_name" value="{{ old('guard_name', $role->guard_name ?? 'web') }}" placeholder="web" id="input-guard-name-field" class="form-control">
                                    <div id="error-guard-name" class="invalid-feedback"></div>
                                    <div class="form-text">{{ $lang->help_guard_name }}</div>
                                </div>
                            </div>

                            <div class="row mb-3" id="input-title">
                                <label for="input-title-field" class="col-sm-2 col-form-label">{{ $lang->entry_title }}</label>
                                <div class="col-sm-10">
                                    <input type="text" name="title" value="{{ old('title', $role->title) }}" placeholder="{{ $lang->placeholder_title }}" id="input-title-field" class="form-control">
                                    <div id="error-title" class="invalid-feedback"></div>
                                </div>
                            </div>

                            <div class="row mb-3" id="input-description">
                                <label for="input-description-field" class="col-sm-2 col-form-label">{{ $lang->entry_description }}</label>
                                <div class="col-sm-10">
                                    <textarea name="description" rows="3" placeholder="{{ $lang->placeholder_description }}" id="input-description-field" class="form-control">{{ old('description', $role->description) }}</textarea>
                                    <div id="error-description" class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>

                        {{-- 權限設定 --}}
                        <div class="tab-pane fade" id="tab-permissions" role="tabpanel">
                            <div class="row mb-3">
                                <div class="col-12">
                                    <div class="mb-2">
                                        <button type="button" class="btn btn-sm btn-outline-primary" id="btn-select-all">{{ $lang->button_select_all }}</button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-deselect-all">{{ $lang->button_deselect_all }}</button>
                                    </div>

                                    @if($permissions->isEmpty())
                                        <div class="alert alert-info">{{ $lang->text_no_permissions }}</div>
                                    @else
                                        <div class="row">
                                            @foreach($permissions as $group)
                                                <div class="col-md-6 col-lg-4 mb-3">
                                                    <div class="card h-100">
                                                        <div class="card-header bg-light">
                                                            <div class="form-check">
                                                                <input type="checkbox"
                                                                    class="form-check-input permission-group-toggle"
                                                                    id="group-{{ $group['id'] }}"
                                                                    data-group="{{ $group['id'] }}"
                                                                    {{ $rolePermissions->contains($group['id']) ? 'checked' : '' }}>
                                                                <label class="form-check-label fw-bold" for="group-{{ $group['id'] }}">
                                                                    {{ $group['title'] }}
                                                                </label>
                                                            </div>
                                                            <input type="checkbox"
                                                                name="permissions[]"
                                                                value="{{ $group['id'] }}"
                                                                class="d-none permission-checkbox group-{{ $group['id'] }}"
                                                                {{ $rolePermissions->contains($group['id']) ? 'checked' : '' }}>
                                                        </div>
                                                        @if(!empty($group['children']))
                                                            <div class="card-body">
                                                                @include('ocadmin.system.access.role::partials.permission-tree', ['items' => $group['children'], 'groupId' => $group['id'], 'rolePermissions' => $rolePermissions])
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($role->exists)
                    <input type="hidden" name="role_id" value="{{ $role->id }}" id="input-role-id">
                    @endif
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script type="text/javascript">
$(document).ready(function() {
    // 全選
    $('#btn-select-all').on('click', function() {
        $('input[name="permissions[]"]').prop('checked', true);
        $('.permission-group-toggle').prop('checked', true);
    });

    // 取消全選
    $('#btn-deselect-all').on('click', function() {
        $('input[name="permissions[]"]').prop('checked', false);
        $('.permission-group-toggle').prop('checked', false);
    });

    // 群組 toggle
    $('.permission-group-toggle').on('change', function() {
        var groupId = $(this).data('group');
        var isChecked = $(this).prop('checked');

        // 同步隱藏的 checkbox
        $('.group-' + groupId).prop('checked', isChecked);

        // 同步該群組下所有子權限
        $(this).closest('.card').find('input[name="permissions[]"]').prop('checked', isChecked);
    });

    // 子權限變化時更新群組狀態
    $('input[name="permissions[]"]').on('change', function() {
        var card = $(this).closest('.card');
        var toggle = card.find('.permission-group-toggle');
        var allCheckboxes = card.find('input[name="permissions[]"]');
        var checkedCount = allCheckboxes.filter(':checked').length;

        if (checkedCount === 0) {
            toggle.prop('checked', false).prop('indeterminate', false);
        } else if (checkedCount === allCheckboxes.length) {
            toggle.prop('checked', true).prop('indeterminate', false);
        } else {
            toggle.prop('indeterminate', true);
        }
    });

    // 初始化群組狀態
    $('.permission-group-toggle').each(function() {
        var card = $(this).closest('.card');
        var allCheckboxes = card.find('input[name="permissions[]"]');
        var checkedCount = allCheckboxes.filter(':checked').length;

        if (checkedCount > 0 && checkedCount < allCheckboxes.length) {
            $(this).prop('indeterminate', true);
        }
    });
});
</script>
@endsection
