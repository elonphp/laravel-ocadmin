@extends('ocadmin::layouts.app')

@section('title', $menu->exists ? $lang->text_edit : $lang->text_add)

@section('content')
<div id="content">
    <div class="page-header">
        <div class="container-fluid">
            <div class="float-end">
                <button type="submit" form="form-menu" data-bs-toggle="tooltip" title="{{ $lang->button_save }}" class="btn btn-primary">
                    <i class="fa-solid fa-save"></i>
                </button>
                <a href="{{ $back_url }}" data-bs-toggle="tooltip" title="{{ $lang->button_back }}" class="btn btn-secondary">
                    <i class="fa-solid fa-reply"></i>
                </a>
            </div>
            <h1>{{ $menu->exists ? $lang->text_edit : $lang->text_add }}</h1>
        </div>
    </div>

    <div class="container-fluid">
        <div class="card card-default">
            <div class="card-header">
                <i class="fa-solid fa-pencil"></i> {{ $menu->exists ? $lang->text_edit : $lang->text_add }}
            </div>
            <div class="card-body">
                <form action="{{ $save_url }}" method="post" id="form-menu" data-oc-toggle="ajax">
                    @csrf
                    @if($menu->exists)
                    @method('PUT')
                    @endif

                    {{-- 翻譯欄位 --}}
                    <ul class="nav nav-tabs mb-3" id="lang-tabs">
                        @foreach($locales as $locale)
                        <li class="nav-item">
                            <a class="nav-link {{ $loop->first ? 'active' : '' }}" data-bs-toggle="tab" href="#language-{{ $locale }}">
                                {{ $localeNames[$locale] ?? $locale }}
                            </a>
                        </li>
                        @endforeach
                    </ul>
                    <div class="tab-content mb-3">
                        @foreach($locales as $locale)
                        <div class="tab-pane {{ $loop->first ? 'active' : '' }}" id="language-{{ $locale }}">
                            <div class="row mb-3 required">
                                <label for="input-display-name-{{ $locale }}" class="col-sm-2 col-form-label">{{ $lang->column_display_name }}</label>
                                <div class="col-sm-10">
                                    <input type="text" name="translations[{{ $locale }}][display_name]" value="{{ old("translations.{$locale}.display_name", $menu->translate($locale)?->display_name) }}" placeholder="{{ $lang->placeholder_display_name }}" id="input-display-name-{{ $locale }}" class="form-control">
                                    <div id="error-translations-{{ $locale }}-display_name" class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <div class="row mb-3 required">
                        <label for="input-portal" class="col-sm-2 col-form-label">{{ $lang->column_portal }}</label>
                        <div class="col-sm-10">
                            <select name="portal" id="input-portal" class="form-select">
                                @foreach($portals as $portal)
                                <option value="{{ $portal }}" @selected(old('portal', $menu->portal) == $portal)>{{ $portal }}</option>
                                @endforeach
                            </select>
                            <div id="error-portal" class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="row mb-3 required">
                        <label for="input-group" class="col-sm-2 col-form-label">{{ $lang->column_group }}</label>
                        <div class="col-sm-10">
                            <input type="text" name="group" value="{{ old('group', $menu->group ?? 'main') }}" placeholder="{{ $lang->placeholder_group }}" id="input-group" class="form-control">
                            <div id="error-group" class="invalid-feedback"></div>
                            <div class="form-text">{{ $lang->help_group }}</div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="input-parent" class="col-sm-2 col-form-label">{{ $lang->column_parent }}</label>
                        <div class="col-sm-10">
                            <select name="parent_id" id="input-parent" class="form-select">
                                <option value="">{{ $lang->text_none }}</option>
                                @foreach($parents as $parent)
                                <option value="{{ $parent->id }}" @selected(old('parent_id', $menu->parent_id) == $parent->id)>{{ $parent->name }}</option>
                                @endforeach
                            </select>
                            <div id="error-parent_id" class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="input-permission" class="col-sm-2 col-form-label">{{ $lang->column_permission_name }}</label>
                        <div class="col-sm-10">
                            <select name="permission_name" id="input-permission" class="form-select">
                                <option value="">{{ $lang->text_please_select }}</option>
                                @foreach($permissions as $name => $label)
                                <option value="{{ $name }}" @selected(old('permission_name', $menu->permission_name) == $name)>{{ $name }}</option>
                                @endforeach
                            </select>
                            <div id="error-permission_name" class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="input-route-name" class="col-sm-2 col-form-label">{{ $lang->column_route_name }}</label>
                        <div class="col-sm-10">
                            <input type="text" name="route_name" value="{{ old('route_name', $menu->route_name) }}" placeholder="{{ $lang->placeholder_route_name }}" id="input-route-name" class="form-control">
                            <div id="error-route_name" class="invalid-feedback"></div>
                            <div class="form-text">{{ $lang->help_route_name }}</div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="input-href" class="col-sm-2 col-form-label">{{ $lang->column_href }}</label>
                        <div class="col-sm-10">
                            <input type="text" name="href" value="{{ old('href', $menu->href) }}" placeholder="{{ $lang->placeholder_href }}" id="input-href" class="form-control">
                            <div id="error-href" class="invalid-feedback"></div>
                            <div class="form-text">{{ $lang->help_href }}</div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="input-icon" class="col-sm-2 col-form-label">{{ $lang->column_icon }}</label>
                        <div class="col-sm-10">
                            <input type="text" name="icon" value="{{ old('icon', $menu->icon) }}" placeholder="{{ $lang->placeholder_icon }}" id="input-icon" class="form-control">
                            <div id="error-icon" class="invalid-feedback"></div>
                            <div class="form-text">{{ $lang->help_icon }}</div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="input-sort-order" class="col-sm-2 col-form-label">{{ $lang->column_sort_order }}</label>
                        <div class="col-sm-10">
                            <input type="number" name="sort_order" value="{{ old('sort_order', $menu->sort_order ?? 0) }}" placeholder="{{ $lang->placeholder_sort_order }}" id="input-sort-order" class="form-control" min="0">
                            <div id="error-sort_order" class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label">{{ $lang->column_is_active }}</label>
                        <div class="col-sm-10 pt-2">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="is_active" id="input-active-yes" value="1" {{ old('is_active', $menu->is_active ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="input-active-yes">{{ $lang->text_enabled }}</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="is_active" id="input-active-no" value="0" {{ !old('is_active', $menu->is_active ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="input-active-no">{{ $lang->text_disabled }}</label>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
