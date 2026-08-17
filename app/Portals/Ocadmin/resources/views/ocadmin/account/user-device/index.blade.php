@extends('ocadmin::layouts.app')

@section('title', $lang->heading_title)

@section('content')
<div id="content">
    <div class="page-header">
        <div class="container-fluid">
            <div class="float-end">
                <button type="button" id="button-revoke" data-bs-toggle="tooltip" title="{{ $lang->button_revoke }}" class="btn btn-danger">
                    <i class="fa-regular fa-trash-can"></i>
                </button>
                <button type="button" id="button-revoke-others" data-bs-toggle="tooltip" title="{{ $lang->button_revoke_others }}" class="btn btn-warning">
                    <i class="fa-solid fa-right-from-bracket"></i>
                </button>
            </div>
            <h1>{{ $lang->heading_title }}</h1>
        </div>
    </div>

    <div class="container-fluid">
        @if(session('success'))
        <div class="alert alert-success alert-dismissible">
            <i class="fa-solid fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <div class="row">
            <div class="col-lg-12 col-md-12">
                <div class="card">
                    <div class="card-header"><i class="fa-solid fa-list"></i> {{ $lang->text_list }}</div>
                    <div id="device-list" class="card-body">
                        {!! $list !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script type="text/javascript">
$(document).ready(function() {
    var revokeUrl = '{{ $revoke_url }}';
    var revokeOthersUrl = '{{ $revoke_others_url }}';

    // AJAX 分頁和排序
    $('#device-list').on('click', 'thead a, .pagination a', function(e) {
        e.preventDefault();
        var href = $(this).attr('href');
        $('#device-list').load(href);
        window.history.pushState({}, null, href.replace(/\/list\b/, ''));
    });

    // 撤銷選取
    $('#button-revoke').on('click', function() {
        var selected = [];
        $('input[name*=\'selected\']:checked').each(function() {
            selected.push($(this).val());
        });

        if (selected.length === 0) {
            alert('{{ $lang->error_select_revoke }}');
            return;
        }

        var msg = '{{ $lang->text_confirm_revoke }}'.replace('%s', selected.length);
        if (confirm(msg)) {
            $.ajax({
                url: revokeUrl,
                type: 'POST',
                data: {
                    selected: selected,
                    _token: '{{ csrf_token() }}'
                },
                dataType: 'json',
                success: function(json) {
                    if (json.success) {
                        location.reload();
                    } else {
                        alert(json.error || '{{ $lang->text_error_revoke }}');
                    }
                },
                error: function(xhr) {
                    alert('{{ $lang->text_error_revoke }}' + ': ' + (xhr.responseJSON?.error || xhr.statusText));
                }
            });
        }
    });

    // 登出所有其他裝置
    $('#button-revoke-others').on('click', function() {
        if (confirm('{{ $lang->text_confirm_revoke_others }}')) {
            $.ajax({
                url: revokeOthersUrl,
                type: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                dataType: 'json',
                success: function(json) {
                    if (json.success) {
                        location.reload();
                    } else {
                        alert(json.error || '{{ $lang->text_error_revoke }}');
                    }
                },
                error: function(xhr) {
                    alert('{{ $lang->text_error_revoke }}' + ': ' + (xhr.responseJSON?.error || xhr.statusText));
                }
            });
        }
    });
});
</script>
@endsection
