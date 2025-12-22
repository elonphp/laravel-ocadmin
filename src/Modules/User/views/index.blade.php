@extends('ocadmin::layouts.app')

@section('title', __('user::user.title'))

@section('content')
<div id="content">
    <div class="page-header">
        <div class="container-fluid">
            <div class="float-end">
                <button type="button" data-bs-toggle="tooltip" title="{{ __('ocadmin::common.filter') }}" onclick="$('#filter-user').toggleClass('d-none');" class="btn btn-light d-lg-none">
                    <i class="fa-solid fa-filter"></i>
                </button>
                <a href="{{ ocadmin_route('users.create') }}" data-bs-toggle="tooltip" title="{{ __('ocadmin::common.create') }}" class="btn btn-primary">
                    <i class="fa-solid fa-plus"></i>
                </a>
                <button type="button" id="button-delete" data-bs-toggle="tooltip" title="{{ __('ocadmin::common.delete') }}" class="btn btn-danger" disabled>
                    <i class="fa-regular fa-trash-can"></i>
                </button>
            </div>
            <h1>{{ __('user::user.title') }}</h1>
            <ol class="breadcrumb">
                @foreach($breadcrumbs as $breadcrumb)
                    @if($loop->last)
                        <li class="breadcrumb-item active">{{ $breadcrumb->text }}</li>
                    @else
                        <li class="breadcrumb-item"><a href="{{ $breadcrumb->href }}">{{ $breadcrumb->text }}</a></li>
                    @endif
                @endforeach
            </ol>
        </div>
    </div>
    <div class="container-fluid">
        <div class="row">
            {{-- Filter Panel - Right Side --}}
            <div id="filter-user" class="col-lg-3 col-md-12 order-lg-last d-none d-lg-block mb-3">
                <div class="card">
                    <div class="card-header">
                        <i class="fa-solid fa-filter"></i> {{ __('ocadmin::common.filter') }}
                    </div>
                    <div class="card-body">
                        <form id="form-filter">
                            <div class="mb-3">
                                <label class="form-label">{{ __('user::user.name') }}</label>
                                <input type="text" name="search" class="form-control"
                                       placeholder="{{ __('user::user.search_placeholder') }}"
                                       value="{{ request('search') }}">
                            </div>
                            <div class="text-end">
                                <button type="button" id="button-clear" class="btn btn-light">
                                    <i class="fa-solid fa-rotate"></i> {{ __('ocadmin::common.reset') }}
                                </button>
                                <button type="button" id="button-filter" class="btn btn-light">
                                    <i class="fa-solid fa-filter"></i> {{ __('ocadmin::common.filter') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- List Panel - Left Side --}}
            <div class="col-lg-9 col-md-12">
                <div class="card">
                    <div class="card-header">
                        <i class="fa-solid fa-list"></i> {{ __('user::user.list') }}
                    </div>
                    <div id="list-container" class="card-body">
                        {!! $list !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function() {
    // Filter button click
    $('#button-filter').on('click', function() {
        loadList();
    });

    // Clear button click
    $('#button-clear').on('click', function() {
        $('#form-filter')[0].reset();
        setTimeout(function() {
            loadList();
        }, 10);
    });

    // Enter key on filter form
    $('#form-filter input').on('keypress', function(e) {
        if (e.which == 13) {
            e.preventDefault();
            loadList();
        }
    });

    // Pagination and sorting links (event delegation)
    $('#list-container').on('click', '.pagination a, thead a', function(e) {
        e.preventDefault();
        loadListByUrl($(this).attr('href'));
    });

    // Select all checkbox
    $('#list-container').on('change', '#select-all', function() {
        $('input[name="selected[]"]').prop('checked', $(this).prop('checked'));
        updateDeleteButton();
    });

    // Individual checkbox
    $('#list-container').on('change', 'input[name="selected[]"]', function() {
        updateDeleteButton();
    });

    // Delete button
    $('#button-delete').on('click', function() {
        var selected = [];
        $('input[name="selected[]"]:checked').each(function() {
            selected.push($(this).val());
        });

        if (selected.length === 0) {
            return;
        }

        if (!confirm('{{ __("ocadmin::messages.confirm_delete") }}')) {
            return;
        }

        $.ajax({
            url: '{{ ocadmin_route("users.destroy-multiple") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                ids: selected
            },
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    loadList();
                } else {
                    toastr.error(response.message);
                }
            },
            error: function() {
                toastr.error('{{ __("ocadmin::messages.error") }}');
            }
        });
    });

    function loadList() {
        var url = '{{ ocadmin_route("users.list") }}';
        var params = $('#form-filter').serialize();
        loadListByUrl(url + '?' + params);
    }

    function loadListByUrl(url) {
        $('#list-container').load(url, function() {
            // Update browser URL (remove /list from display)
            var displayUrl = url.replace('/list', '');
            window.history.pushState({}, '', displayUrl);
            updateDeleteButton();
        });
    }

    function updateDeleteButton() {
        var checked = $('input[name="selected[]"]:checked').length;
        $('#button-delete').prop('disabled', checked === 0);
    }
});
</script>
@endpush
