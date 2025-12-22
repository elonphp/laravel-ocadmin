@extends('ocadmin::layouts.app')

@section('title', __('system-module-manager::module.title'))

@section('content')
<div id="content">
    <div class="page-header">
        <div class="container-fluid">
            <div class="float-end">
                <button type="button" id="button-refresh" data-bs-toggle="tooltip" title="{{ __('system-module-manager::module.refresh') }}" class="btn btn-light">
                    <i class="fa-solid fa-rotate"></i>
                </button>
            </div>
            <h1>{{ __('system-module-manager::module.title') }}</h1>
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
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row">
            @foreach($modules as $alias => $module)
            <div class="col-lg-4 col-md-6 mb-3">
                <div class="card h-100 {{ $module['enabled'] ? 'border-success' : '' }}">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>
                            @if($module['source'] === 'package')
                                <i class="fa-solid fa-cube text-primary" title="{{ __('system-module-manager::module.source_package') }}"></i>
                            @else
                                <i class="fa-solid fa-user text-info" title="{{ __('system-module-manager::module.source_custom') }}"></i>
                            @endif
                            <strong>{{ $module['name'] }}</strong>
                        </span>
                        <span class="badge bg-secondary">v{{ $module['version'] }}</span>
                    </div>
                    <div class="card-body">
                        <p class="card-text text-muted small">{{ $module['description'] ?: __('system-module-manager::module.no_description') }}</p>
                        <div class="mb-2">
                            @if($module['system'])
                                <span class="badge bg-dark">{{ __('system-module-manager::module.system_module') }}</span>
                            @endif
                            @if($module['installed'])
                                @if($module['enabled'])
                                    <span class="badge bg-success">{{ __('system-module-manager::module.status_enabled') }}</span>
                                @else
                                    <span class="badge bg-warning text-dark">{{ __('system-module-manager::module.status_disabled') }}</span>
                                @endif
                            @else
                                <span class="badge bg-secondary">{{ __('system-module-manager::module.status_not_installed') }}</span>
                            @endif
                        </div>
                        @if($module['installed'] && $module['installed_at'])
                            <small class="text-muted">
                                {{ __('system-module-manager::module.installed_at') }}: {{ $module['installed_at']->format('Y-m-d H:i') }}
                            </small>
                        @endif
                    </div>
                    <div class="card-footer bg-transparent">
                        @if(!$module['installed'])
                            {{-- Not installed --}}
                            <a href="{{ ocadmin_route('modules.install.form', $alias) }}" class="btn btn-primary btn-sm">
                                <i class="fa-solid fa-download"></i> {{ __('system-module-manager::module.install') }}
                            </a>
                        @elseif($module['enabled'])
                            {{-- Enabled --}}
                            @if(!$module['system'])
                                <form action="{{ ocadmin_route('modules.disable', $alias) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-warning btn-sm" onclick="return confirm('{{ __('system-module-manager::messages.confirm_disable') }}')">
                                        <i class="fa-solid fa-pause"></i> {{ __('system-module-manager::module.disable') }}
                                    </button>
                                </form>
                            @else
                                <button type="button" class="btn btn-secondary btn-sm" disabled title="{{ __('system-module-manager::module.system_cannot_disable') }}">
                                    <i class="fa-solid fa-lock"></i> {{ __('system-module-manager::module.system_module') }}
                                </button>
                            @endif
                        @else
                            {{-- Installed but disabled --}}
                            <form action="{{ ocadmin_route('modules.enable', $alias) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-success btn-sm">
                                    <i class="fa-solid fa-play"></i> {{ __('system-module-manager::module.enable') }}
                                </button>
                            </form>
                        @endif

                        <a href="{{ ocadmin_route('modules.show', $alias) }}" class="btn btn-light btn-sm">
                            <i class="fa-solid fa-info-circle"></i> {{ __('system-module-manager::module.details') }}
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function() {
    $('#button-refresh').on('click', function() {
        location.reload();
    });
});
</script>
@endpush
