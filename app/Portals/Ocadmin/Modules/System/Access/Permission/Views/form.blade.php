@extends('ocadmin::layouts.app')

@section('title', $permission->exists ? $lang->text_edit : $lang->text_add)

@section('content')
<div id="content">
    <div class="page-header">
        <div class="container-fluid">
            <div class="float-end">
                <button type="submit" form="form-permission" data-bs-toggle="tooltip" title="{{ $lang->button_save }}" class="btn btn-primary">
                    <i class="fa-solid fa-floppy-disk"></i>
                </button>
                <a href="{{ route('lang.ocadmin.system.access.permission.index') }}" data-bs-toggle="tooltip" title="{{ $lang->button_back }}" class="btn btn-light">
                    <i class="fa-solid fa-reply"></i>
                </a>
            </div>
            <h1>{{ $permission->exists ? $lang->text_edit : $lang->text_add }}</h1>
            @include('ocadmin::layouts.partials.breadcrumb')
        </div>
    </div>
    <div class="container-fluid">
        <div class="card">
            <div class="card-header"><i class="fa-solid fa-pencil"></i> {{ $permission->exists ? $lang->text_edit : $lang->text_add }}</div>
            <div class="card-body">
                <form id="form-permission" action="{{ $permission->exists ? route('lang.ocadmin.system.access.permission.update', $permission) : route('lang.ocadmin.system.access.permission.store') }}" method="post" data-oc-toggle="ajax">
                    @csrf
                    @if($permission->exists)
                    @method('PUT')
                    @endif

                    <div class="row mb-3 required" id="input-name">
                        <label for="input-name-field" class="col-sm-2 col-form-label">{{ $lang->entry_name }}</label>
                        <div class="col-sm-10">
                            <input type="text" name="name" value="{{ old('name', $permission->name) }}" placeholder="{{ $lang->placeholder_name }}" id="input-name-field" class="form-control">
                            <div id="error-name" class="invalid-feedback"></div>
                            <div class="form-text">{{ $lang->help_name }}</div>
                        </div>
                    </div>

                    <div class="row mb-3 required" id="input-guard-name">
                        <label for="input-guard-name-field" class="col-sm-2 col-form-label">{{ $lang->entry_guard_name }}</label>
                        <div class="col-sm-10">
                            <input type="text" name="guard_name" value="{{ old('guard_name', $permission->guard_name ?? 'web') }}" placeholder="web" id="input-guard-name-field" class="form-control">
                            <div id="error-guard-name" class="invalid-feedback"></div>
                            <div class="form-text">{{ $lang->help_guard_name }}</div>
                        </div>
                    </div>

                    <div class="row mb-3" id="input-title">
                        <label for="input-title-field" class="col-sm-2 col-form-label">{{ $lang->entry_title }}</label>
                        <div class="col-sm-10">
                            <input type="text" name="title" value="{{ old('title', $permission->title) }}" placeholder="{{ $lang->placeholder_title }}" id="input-title-field" class="form-control">
                            <div id="error-title" class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="row mb-3" id="input-description">
                        <label for="input-description-field" class="col-sm-2 col-form-label">{{ $lang->entry_description }}</label>
                        <div class="col-sm-10">
                            <textarea name="description" rows="3" placeholder="{{ $lang->placeholder_description }}" id="input-description-field" class="form-control">{{ old('description', $permission->description) }}</textarea>
                            <div id="error-description" class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="row mb-3 required" id="input-type">
                        <label class="col-sm-2 col-form-label">{{ $lang->entry_type }}</label>
                        <div class="col-sm-10">
                            <select name="type" id="input-type-field" class="form-select">
                                <option value="menu" {{ old('type', $permission->type ?? 'menu') === 'menu' ? 'selected' : '' }}>{{ $lang->text_type_menu }}</option>
                                <option value="action" {{ old('type', $permission->type) === 'action' ? 'selected' : '' }}>{{ $lang->text_type_action }}</option>
                            </select>
                            <div id="error-type" class="invalid-feedback"></div>
                            <div class="form-text">{{ $lang->help_type }}</div>
                        </div>
                    </div>

                    <div class="row mb-3" id="input-parent-id">
                        <label class="col-sm-2 col-form-label">{{ $lang->entry_parent }}</label>
                        <div class="col-sm-10">
                            <select name="parent_id" id="input-parent-id-field" class="form-select">
                                <option value="">{{ $lang->text_none }}</option>
                                @foreach($parentOptions as $parentOption)
                                <option value="{{ $parentOption->id }}" {{ old('parent_id', $permission->parent_id) == $parentOption->id ? 'selected' : '' }}>
                                    {{ $parentOption->title ?: $parentOption->name }}
                                </option>
                                @endforeach
                            </select>
                            <div id="error-parent-id" class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="row mb-3" id="input-sort-order">
                        <label for="input-sort-order-field" class="col-sm-2 col-form-label">{{ $lang->entry_sort_order }}</label>
                        <div class="col-sm-10">
                            <input type="number" name="sort_order" value="{{ old('sort_order', $permission->sort_order ?? 0) }}" placeholder="0" id="input-sort-order-field" class="form-control" min="0">
                            <div id="error-sort-order" class="invalid-feedback"></div>
                        </div>
                    </div>

                    @if($permission->exists)
                    <input type="hidden" name="permission_id" value="{{ $permission->id }}" id="input-permission-id">
                    @endif
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
