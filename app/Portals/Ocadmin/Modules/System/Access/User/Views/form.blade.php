@extends('ocadmin::layouts.app')

@section('title', $ocadminUser ? $lang->text_edit : $lang->text_add)

@section('styles')
<link href="{{ asset('assets/vendor/select2/select2.min.css') }}" rel="stylesheet">
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
.select2-results__options {
    max-height: 250px !important;
}
.select2-result-user__title {
    font-weight: 500;
}
.select2-result-user__description {
    font-size: 0.85em;
}
</style>
@endsection

@section('content')
<div id="content">
    <div class="page-header">
        <div class="container-fluid">
            <div class="float-end">
                <button type="submit" form="form-user" data-bs-toggle="tooltip" title="{{ $lang->button_save }}" class="btn btn-primary">
                    <i class="fa-solid fa-floppy-disk"></i>
                </button>
                <a href="{{ route('lang.ocadmin.system.access.user.index') }}" data-bs-toggle="tooltip" title="{{ $lang->button_back }}" class="btn btn-light">
                    <i class="fa-solid fa-reply"></i>
                </a>
            </div>
            <h1>{{ $ocadminUser ? $lang->text_edit : $lang->text_add }}</h1>
            @include('ocadmin::layouts.partials.breadcrumb')
        </div>
    </div>
    <div class="container-fluid">
        <div class="card">
            <div class="card-header"><i class="fa-solid fa-pencil"></i> {{ $ocadminUser ? $lang->text_edit : $lang->text_add }}</div>
            <div class="card-body">
                <form id="form-user" action="{{ $ocadminUser ? route('lang.ocadmin.system.access.user.update', $ocadminUser->id) : route('lang.ocadmin.system.access.user.store') }}" method="post" data-oc-toggle="ajax">
                    @csrf
                    @if($ocadminUser)
                    @method('PUT')
                    @endif

                    {{-- 新增模式：搜尋選擇使用者 --}}
                    @if(!$ocadminUser)
                    <div class="row mb-3 required" id="input-user">
                        <label for="input-user-field" class="col-sm-2 col-form-label">{{ $lang->entry_user }}</label>
                        <div class="col-sm-10">
                            <select name="user_id" id="input-user-field" class="form-select">
                            </select>
                            <div id="error-user_id" class="invalid-feedback"></div>
                            <div class="form-text">{{ $lang->help_search_user }}</div>
                        </div>
                    </div>
                    @else
                    {{-- 編輯模式：顯示使用者資訊 --}}
                    <div class="row mb-3" id="input-user-info">
                        <label class="col-sm-2 col-form-label">{{ $lang->entry_user }}</label>
                        <div class="col-sm-10">
                            <div class="form-control-plaintext">
                                <strong>{{ $ocadminUser->name ?: $ocadminUser->username }}</strong>
                                @if($ocadminUser->email)
                                    <span class="text-muted">({{ $ocadminUser->email }})</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- 角色選擇 --}}
                    <div class="row mb-3" id="input-roles">
                        <label class="col-sm-2 col-form-label">{{ $lang->entry_roles }}</label>
                        <div class="col-sm-10">
                            {{-- ocadmin 角色（必選，僅顯示） --}}
                            <div class="form-check mb-2">
                                <input type="checkbox" class="form-check-input" checked disabled>
                                <label class="form-check-label">
                                    <strong>ocadmin</strong> <span class="text-muted">({{ $lang->text_ocadmin_required }})</span>
                                </label>
                            </div>
                            <hr class="my-2">
                            {{-- 其他角色 --}}
                            @if($roles->isEmpty())
                                <div class="text-muted">{{ $lang->text_no_other_roles }}</div>
                            @else
                                @foreach($roles as $role)
                                <div class="form-check">
                                    <input type="checkbox"
                                        name="roles[]"
                                        value="{{ $role->id }}"
                                        id="input-role-{{ $role->id }}"
                                        class="form-check-input"
                                        {{ $userRoles->contains($role->id) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="input-role-{{ $role->id }}">
                                        {{ $role->title ?: $role->name }}
                                        @if($role->description)
                                            <small class="text-muted">- {{ $role->description }}</small>
                                        @endif
                                    </label>
                                </div>
                                @endforeach
                            @endif
                            <div id="error-roles" class="invalid-feedback"></div>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('assets/vendor/select2/select2.min.js') }}"></script>
<script type="text/javascript">
$(document).ready(function() {
    @if(!$ocadminUser)
    // Select2 使用者搜尋
    $('#input-user-field').select2({
        placeholder: '{{ $lang->placeholder_search_user }}',
        allowClear: true,
        width: '100%',
        minimumInputLength: 2,
        ajax: {
            url: '{{ route("lang.ocadmin.system.access.user.search") }}',
            dataType: 'json',
            delay: 300,
            data: function(params) {
                return {
                    q: params.term
                };
            },
            processResults: function(data) {
                return {
                    results: data
                };
            },
            cache: true
        },
        templateResult: function(item) {
            if (item.loading) {
                return item.text;
            }

            var $container = $(
                '<div class="select2-result-user">' +
                    '<div class="select2-result-user__title"></div>' +
                    '<div class="select2-result-user__description text-muted"></div>' +
                '</div>'
            );

            // 顯示 email 或 username
            $container.find('.select2-result-user__title').text(item.email || item.username);

            // 顯示姓名
            if (item.name) {
                $container.find('.select2-result-user__description').text(item.name);
            }

            return $container;
        },
        templateSelection: function(item) {
            if (!item.id) {
                return item.text;
            }
            return item.text || item.email || item.username;
        }
    });
    @endif
});
</script>
@endsection
