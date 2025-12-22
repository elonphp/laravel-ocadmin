@extends('ocadmin::layouts.app')

@section('title', $module['name'] . ' - ' . __('system-module-manager::module.title'))

@section('content')
<div id="content">
    <div class="page-header">
        <div class="container-fluid">
            <div class="float-end">
                <a href="{{ ocadmin_route('modules.index') }}" class="btn btn-light">
                    <i class="fa-solid fa-reply"></i> {{ __('ocadmin::common.back') }}
                </a>
            </div>
            <h1>{{ $module['name'] }}</h1>
            <ol class="breadcrumb">
                @foreach($breadcrumbs as $breadcrumb)
                    <li class="breadcrumb-item"><a href="{{ $breadcrumb->href }}">{{ $breadcrumb->text }}</a></li>
                @endforeach
                <li class="breadcrumb-item active">{{ $module['name'] }}</li>
            </ol>
        </div>
    </div>
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-8">
                <div class="card mb-3">
                    <div class="card-header">
                        <i class="fa-solid fa-info-circle"></i> {{ __('system-module-manager::module.basic_info') }}
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            <tr>
                                <th style="width: 150px;">{{ __('system-module-manager::module.name') }}</th>
                                <td>{{ $module['name'] }}</td>
                            </tr>
                            <tr>
                                <th>{{ __('system-module-manager::module.alias') }}</th>
                                <td><code>{{ $module['alias'] }}</code></td>
                            </tr>
                            <tr>
                                <th>{{ __('system-module-manager::module.version') }}</th>
                                <td>{{ $module['version'] }}</td>
                            </tr>
                            <tr>
                                <th>{{ __('system-module-manager::module.source') }}</th>
                                <td>
                                    @if($module['source'] === 'package')
                                        <span class="badge bg-primary">{{ __('system-module-manager::module.source_package') }}</span>
                                    @else
                                        <span class="badge bg-info">{{ __('system-module-manager::module.source_custom') }}</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>{{ __('system-module-manager::module.description') }}</th>
                                <td>{{ $module['description'] ?: '-' }}</td>
                            </tr>
                            <tr>
                                <th>{{ __('system-module-manager::module.priority') }}</th>
                                <td>{{ $module['priority'] }}</td>
                            </tr>
                            <tr>
                                <th>{{ __('system-module-manager::module.status') }}</th>
                                <td>
                                    @if($module['installed'])
                                        @if($module['enabled'])
                                            <span class="badge bg-success">{{ __('system-module-manager::module.status_enabled') }}</span>
                                        @else
                                            <span class="badge bg-warning text-dark">{{ __('system-module-manager::module.status_disabled') }}</span>
                                        @endif
                                    @else
                                        <span class="badge bg-secondary">{{ __('system-module-manager::module.status_not_installed') }}</span>
                                    @endif
                                    @if($module['system'])
                                        <span class="badge bg-dark">{{ __('system-module-manager::module.system_module') }}</span>
                                    @endif
                                </td>
                            </tr>
                            @if($module['installed'] && $module['installed_at'])
                            <tr>
                                <th>{{ __('system-module-manager::module.installed_at') }}</th>
                                <td>{{ $module['installed_at']->format('Y-m-d H:i:s') }}</td>
                            </tr>
                            @endif
                            <tr>
                                <th>{{ __('system-module-manager::module.path') }}</th>
                                <td><code class="small">{{ $module['path'] }}</code></td>
                            </tr>
                        </table>
                    </div>
                </div>

                @if(!empty($module['config']['dependencies']))
                <div class="card mb-3">
                    <div class="card-header">
                        <i class="fa-solid fa-link"></i> {{ __('system-module-manager::module.dependencies') }}
                    </div>
                    <div class="card-body">
                        @if(isset($module['config']['dependencies']['php']))
                            <p><strong>PHP:</strong> {{ $module['config']['dependencies']['php'] }}</p>
                        @endif
                        @if(isset($module['config']['dependencies']['laravel']))
                            <p><strong>Laravel:</strong> {{ $module['config']['dependencies']['laravel'] }}</p>
                        @endif
                        @if(!empty($module['config']['dependencies']['modules']))
                            <p><strong>{{ __('system-module-manager::module.required_modules') }}:</strong></p>
                            <ul>
                                @foreach($module['config']['dependencies']['modules'] as $dep)
                                    <li><code>{{ $dep }}</code></li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
                @endif

                @if(!empty($module['config']['migrations']))
                <div class="card mb-3">
                    <div class="card-header">
                        <i class="fa-solid fa-database"></i> {{ __('system-module-manager::module.migrations') }}
                    </div>
                    <div class="card-body">
                        <ul class="mb-0">
                            @foreach($module['config']['migrations'] as $migration)
                                <li><code>{{ $migration }}</code></li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                @endif

                @if(!empty($module['config']['permissions']))
                <div class="card mb-3">
                    <div class="card-header">
                        <i class="fa-solid fa-key"></i> {{ __('system-module-manager::module.permissions') }}
                    </div>
                    <div class="card-body">
                        <ul class="mb-0">
                            @foreach($module['config']['permissions'] as $permission)
                                <li><code>{{ $permission }}</code></li>
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
                        @if(!$module['installed'])
                            <a href="{{ ocadmin_route('modules.install.form', $module['alias']) }}" class="btn btn-primary w-100 mb-2">
                                <i class="fa-solid fa-download"></i> {{ __('system-module-manager::module.install') }}
                            </a>
                        @elseif($module['enabled'])
                            @if(!$module['system'])
                                <form action="{{ ocadmin_route('modules.disable', $module['alias']) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-warning w-100 mb-2" onclick="return confirm('{{ __('system-module-manager::messages.confirm_disable') }}')">
                                        <i class="fa-solid fa-pause"></i> {{ __('system-module-manager::module.disable') }}
                                    </button>
                                </form>
                            @else
                                <div class="alert alert-info small mb-2">
                                    <i class="fa-solid fa-lock"></i> {{ __('system-module-manager::module.system_cannot_disable') }}
                                </div>
                            @endif
                        @else
                            <form action="{{ ocadmin_route('modules.enable', $module['alias']) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-success w-100 mb-2">
                                    <i class="fa-solid fa-play"></i> {{ __('system-module-manager::module.enable') }}
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
