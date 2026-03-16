@extends('ocadmin::layouts.app')

@section('title', $token_id ? $lang->text_edit : $lang->text_add)

@section('content')
<div id="content">
    <div class="page-header">
        <div class="container-fluid">
            <div class="float-end">
                <button type="button" id="button-save" data-bs-toggle="tooltip" title="{{ $lang->button_save }}" class="btn btn-primary">
                    <i class="fa-solid fa-save"></i>
                </button>
                <a href="{{ route('lang.ocadmin.system.access-tokens.index') }}" data-bs-toggle="tooltip" title="{{ $lang->button_back }}" class="btn btn-secondary">
                    <i class="fa-solid fa-reply"></i>
                </a>
            </div>
            <h1>{{ $lang->heading_title }}</h1>
        </div>
    </div>

    <div class="container-fluid">
        <div class="card">
            <div class="card-header"><i class="fa-solid fa-pencil"></i> {{ $token_id ? $lang->text_edit : $lang->text_add }}</div>
            <div class="card-body">
                <form id="form-access-token" action="{{ route('lang.ocadmin.system.access-tokens.save', $token_id ? [$token_id] : []) }}" method="post">
                    @csrf

                    @if($token_id)
                    {{-- 編輯模式：唯讀使用者資訊 --}}
                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label">{{ $lang->column_user }}</label>
                        <div class="col-sm-10">
                            <span class="badge bg-primary fs-6">{{ $token_user_name }}</span>
                            <div class="form-text">{{ $lang->help_user_readonly ?? 'User binding cannot be changed' }}</div>
                        </div>
                    </div>
                    @else
                    {{-- 新增模式：使用者來源 --}}
                    <div class="row mb-3 required align-items-center">
                        <label class="col-sm-2 col-form-label pt-0">{{ $lang->column_user_source }}</label>
                        <div class="col-sm-10">
                            <input type="hidden" name="user_mode" id="input-user-mode" value="existing">
                            <div class="form-check form-check-inline mb-0">
                                <input class="form-check-input" type="radio" name="user_mode_radio" id="mode-existing" value="existing" checked>
                                <label class="form-check-label" for="mode-existing">{{ $lang->text_existing_user }}</label>
                            </div>
                            <div class="form-check form-check-inline mb-0">
                                <input class="form-check-input" type="radio" name="user_mode_radio" id="mode-create" value="create">
                                <label class="form-check-label" for="mode-create">{{ $lang->text_create_local }}</label>
                            </div>
                        </div>
                    </div>

                    {{-- 選擇現有使用者 --}}
                    <div id="section-existing-user" class="row mb-3 required">
                        <label for="input-user" class="col-sm-2 col-form-label">{{ $lang->column_user }}</label>
                        <div class="col-sm-10">
                            <input type="text" name="user_search" value="" placeholder="{{ $lang->placeholder_user_search }}" id="input-user" class="form-control" autocomplete="off">
                            <ul id="autocomplete-user" class="dropdown-menu"></ul>
                            <div class="form-text">{{ $lang->help_user_search }}</div>
                            <div id="selected-user" class="mt-2" style="display: none;">
                                <span class="badge bg-primary fs-6" id="selected-user-label"></span>
                                <button type="button" class="btn btn-sm btn-outline-danger ms-2" id="btn-clear-user"><i class="fa-solid fa-times"></i></button>
                                <input type="hidden" name="user_id" id="input-user-id" value="">
                            </div>
                            <div id="error-user_id" class="invalid-feedback"></div>
                        </div>
                    </div>

                    {{-- 建立本地帳號 --}}
                    <div id="section-create-user" style="display: none;">
                        <div class="row mb-3 required">
                            <label for="input-username" class="col-sm-2 col-form-label">{{ $lang->column_username }}</label>
                            <div class="col-sm-10">
                                <input type="text" id="input-username" name="username" value="" class="form-control" placeholder="{{ $lang->placeholder_username }}">
                                <div class="form-text">{{ $lang->help_username }}</div>
                                <div id="error-username" class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="row mb-3 required">
                            <label for="input-local-name" class="col-sm-2 col-form-label">{{ $lang->column_local_name }}</label>
                            <div class="col-sm-10">
                                <input type="text" id="input-local-name" name="local_name" value="" class="form-control" placeholder="{{ $lang->placeholder_local_name }}">
                                <div class="form-text">{{ $lang->help_local_name }}</div>
                                <div id="error-local_name" class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- 名稱 --}}
                    <div class="row mb-3 required">
                        <label for="input-name" class="col-sm-2 col-form-label">{{ $lang->column_name }}</label>
                        <div class="col-sm-10">
                            <input type="text" id="input-name" name="name" value="{{ $token_record->name ?? '' }}" class="form-control" placeholder="{{ $lang->placeholder_name }}">
                            <div class="form-text">{{ $lang->help_name }}</div>
                            <div id="error-name" class="invalid-feedback"></div>
                        </div>
                    </div>

                    {{-- Portal 權限 --}}
                    <div class="row mb-3 required">
                        <label class="col-sm-2 col-form-label">{{ $lang->column_abilities }}</label>
                        <div class="col-sm-10">
                            @foreach($portal_abilities as $ability => $label)
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" name="abilities[]" value="{{ $ability }}" id="ability-{{ $ability }}" @if(in_array($ability, $token_abilities)) checked @endif>
                                <label class="form-check-label" for="ability-{{ $ability }}">{{ $label }} <code>{{ $ability }}</code></label>
                            </div>
                            @endforeach
                            <div class="form-text">{{ $lang->help_abilities }}</div>
                            <div id="error-abilities" class="invalid-feedback"></div>
                        </div>
                    </div>

                    {{-- 到期日 --}}
                    <div class="row mb-3">
                        <label for="input-expires_at" class="col-sm-2 col-form-label">{{ $lang->column_expires_at }}</label>
                        <div class="col-sm-10">
                            <input type="date" id="input-expires_at" name="expires_at" value="{{ $token_record && $token_record->expires_at ? \Carbon\Carbon::parse($token_record->expires_at)->format('Y-m-d') : '' }}" class="form-control">
                            <div class="form-text">{{ $lang->help_expires_at }}</div>
                            <div id="error-expires_at" class="invalid-feedback"></div>
                        </div>
                    </div>

                </form>
            </div>
        </div>

        @if(!$token_id)
        {{-- Token 顯示區塊（新增後顯示） --}}
        <div id="token-result" class="card mt-3" style="display: none;">
            <div class="card-header bg-warning text-dark"><i class="fa-solid fa-key"></i> {{ $lang->text_token_created }}</div>
            <div class="card-body">
                <div class="alert alert-warning mb-3">
                    <i class="fa-solid fa-triangle-exclamation"></i> {{ $lang->text_token_warning }}
                </div>
                <div class="input-group">
                    <input type="text" id="token-value" class="form-control font-monospace" readonly>
                    <button type="button" class="btn btn-outline-secondary" id="btn-copy-token" title="{{ $lang->button_copy }}">
                        <i class="fa-regular fa-copy"></i> {{ $lang->button_copy }}
                    </button>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script type="text/javascript">
$(document).ready(function() {
    @if(!$token_id)
    // 使用者來源切換
    $('input[name="user_mode_radio"]').on('change', function () {
        var mode = $(this).val();
        $('#input-user-mode').val(mode);
        if (mode === 'existing') {
            $('#section-existing-user').show();
            $('#section-create-user').hide();
        } else {
            $('#section-existing-user').hide();
            $('#section-create-user').show();
        }
    });

    // 使用者 Autocomplete
    var searchTimer = null;
    $('#input-user').on('input', function() {
        var keyword = $(this).val();
        clearTimeout(searchTimer);

        if (keyword.length < 1) {
            $('#autocomplete-user').removeClass('show').empty();
            return;
        }

        searchTimer = setTimeout(function() {
            $.ajax({
                url: '{{ route('lang.ocadmin.system.access-tokens.search-users') }}',
                data: { q: keyword },
                dataType: 'json',
                success: function(users) {
                    var $list = $('#autocomplete-user').empty();

                    if (users.length === 0) {
                        $list.append('<li><span class="dropdown-item text-muted">{{ $lang->text_no_results }}</span></li>');
                    } else {
                        $.each(users, function(i, user) {
                            var text = user.id + ' - ' + user.name + (user.username ? ' (' + user.username + ')' : '') + (user.email ? ' - ' + user.email : '');
                            $list.append(
                                $('<li>').append(
                                    $('<a class="dropdown-item" href="javascript:void(0)"></a>')
                                        .text(text)
                                        .data('user', user)
                                        .on('click', function() {
                                            var u = $(this).data('user');
                                            $('#input-user').val('').hide();
                                            $('#input-user-id').val(u.id);
                                            $('#selected-user-label').text(u.id + ' - ' + u.name);
                                            $('#selected-user').show();
                                            $('#autocomplete-user').removeClass('show').empty();
                                        })
                                )
                            );
                        });
                    }

                    $list.addClass('show');
                }
            });
        }, 300);
    });

    // 點擊外部關閉 autocomplete
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#input-user, #autocomplete-user').length) {
            $('#autocomplete-user').removeClass('show');
        }
    });

    // 清除已選使用者
    $('#btn-clear-user').on('click', function() {
        $('#input-user-id').val('');
        $('#selected-user').hide();
        $('#input-user').val('').show();
    });
    @endif

    // 儲存 Access Token
    $('#button-save').on('click', function() {
        var $btn = $(this);
        $btn.prop('disabled', true);

        // 清除先前的錯誤
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');

        $.ajax({
            url: $('#form-access-token').attr('action'),
            type: 'POST',
            data: $('#form-access-token').serialize(),
            dataType: 'json',
            success: function(json) {
                if (json.redirect) {
                    // 編輯模式：redirect 回列表
                    window.location.href = json.redirect;
                    return;
                }
                if (json.token) {
                    // 新增模式：顯示 token
                    $('#token-value').val(json.token);
                    $('#token-result').show();

                    // 禁用表單
                    $('#form-access-token :input').prop('disabled', true);
                    $btn.hide();
                }
            },
            error: function(xhr) {
                $btn.prop('disabled', false);
                if (xhr.status === 422) {
                    var errors = xhr.responseJSON.errors || {};
                    $.each(errors, function(field, messages) {
                        var $el = $('#error-' + field);
                        $el.text(messages[0]).show();
                        $el.siblings('input, select, textarea').first().addClass('is-invalid');
                    });
                } else {
                    var msg = xhr.responseJSON?.error || '{{ $lang->text_error_create }}';
                    alert(msg);
                }
            }
        });
    });

    @if(!$token_id)
    // 複製 token
    $('#btn-copy-token').on('click', function() {
        var tokenEl = document.getElementById('token-value');
        tokenEl.select();
        document.execCommand('copy');
        $(this).html('<i class="fa-solid fa-check"></i> {{ $lang->text_copied }}');
        var btnCopyLabel = '{{ $lang->button_copy }}';
        setTimeout(function() {
            $('#btn-copy-token').html('<i class="fa-regular fa-copy"></i> ' + btnCopyLabel);
        }, 2000);
    });
    @endif
});
</script>
@endsection
