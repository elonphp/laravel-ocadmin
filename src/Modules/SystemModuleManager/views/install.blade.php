@extends('ocadmin::layouts.app')

@section('title', __('system-module-manager::module.install') . ' - ' . $module['name'])

@section('content')
<div id="content">
    <div class="page-header">
        <div class="container-fluid">
            <div class="float-end">
                <a href="{{ ocadmin_route('modules.index') }}" class="btn btn-light">
                    <i class="fa-solid fa-reply"></i> {{ __('ocadmin::common.back') }}
                </a>
            </div>
            <h1>{{ __('system-module-manager::module.install') }}: {{ $module['name'] }}</h1>
            <ol class="breadcrumb">
                @foreach($breadcrumbs as $breadcrumb)
                    <li class="breadcrumb-item"><a href="{{ $breadcrumb->href }}">{{ $breadcrumb->text }}</a></li>
                @endforeach
                <li class="breadcrumb-item active">{{ __('system-module-manager::module.install') }}</li>
            </ol>
        </div>
    </div>
    <div class="container-fluid">
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <form action="{{ ocadmin_route('modules.install', $module['alias']) }}" method="POST">
            @csrf
            <div class="row">
                <div class="col-lg-8">
                    {{-- Module Info --}}
                    <div class="card mb-3">
                        <div class="card-header">
                            <i class="fa-solid fa-info-circle"></i> {{ __('system-module-manager::module.basic_info') }}
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless mb-0">
                                <tr>
                                    <th style="width: 120px;">{{ __('system-module-manager::module.name') }}</th>
                                    <td>{{ $module['name'] }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('system-module-manager::module.version') }}</th>
                                    <td>{{ $module['version'] }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('system-module-manager::module.description') }}</th>
                                    <td>{{ $module['description'] ?: '-' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    {{-- Pre-install Checks --}}
                    <div class="card mb-3">
                        <div class="card-header">
                            <i class="fa-solid fa-clipboard-check"></i> {{ __('system-module-manager::module.install_checks') }}
                        </div>
                        <div class="card-body">
                            {{-- PHP Version --}}
                            <div class="mb-3">
                                @if($checks['php_version']['passed'])
                                    <i class="fa-solid fa-check text-success"></i>
                                @else
                                    <i class="fa-solid fa-times text-danger"></i>
                                @endif
                                {{ $checks['php_version']['message'] }}
                            </div>

                            {{-- Module Dependencies --}}
                            <div class="mb-3">
                                @if($checks['dependencies']['passed'])
                                    <i class="fa-solid fa-check text-success"></i>
                                @else
                                    <i class="fa-solid fa-times text-danger"></i>
                                @endif
                                {{ $checks['dependencies']['message'] }}
                            </div>

                            {{-- Table Conflicts --}}
                            <div class="mb-3">
                                @if($checks['tables']['passed'])
                                    <i class="fa-solid fa-check text-success"></i> {{ $checks['tables']['message'] }}
                                @else
                                    <i class="fa-solid fa-exclamation-triangle text-warning"></i> {{ $checks['tables']['message'] }}
                                    <div class="mt-2 ms-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="use_existing_table" value="1" id="use_existing_table">
                                            <label class="form-check-label" for="use_existing_table">
                                                {{ __('system-module-manager::module.use_existing_table') }}
                                            </label>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Seeders --}}
                    @if(!empty($module['config']['seeders']))
                    <div class="card mb-3">
                        <div class="card-header">
                            <i class="fa-solid fa-database"></i> {{ __('system-module-manager::module.default_data') }}
                        </div>
                        <div class="card-body">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="run_seeders" value="1" id="run_seeders" checked>
                                <label class="form-check-label" for="run_seeders">
                                    {{ __('system-module-manager::module.run_seeders') }}
                                </label>
                            </div>
                            <ul class="mt-2 mb-0 small text-muted">
                                @foreach($module['config']['seeders'] as $seeder)
                                    <li>{{ $seeder }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    @endif
                </div>

                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <i class="fa-solid fa-cogs"></i> {{ __('system-module-manager::module.actions') }}
                        </div>
                        <div class="card-body">
                            @php
                                $canInstall = $checks['php_version']['passed'] && $checks['dependencies']['passed'];
                            @endphp

                            @if($canInstall)
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fa-solid fa-download"></i> {{ __('system-module-manager::module.confirm_install') }}
                                </button>
                            @else
                                <div class="alert alert-danger small mb-0">
                                    <i class="fa-solid fa-times-circle"></i> {{ __('system-module-manager::module.cannot_install') }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
