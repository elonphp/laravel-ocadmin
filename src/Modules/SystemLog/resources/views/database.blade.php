@extends('ocadmin::layouts.app')

@section('title', __('system-log::menu.database_logs'))

@section('content')
<div class="page-header">
    <div class="page-title">
        <h4>{{ __('system-log::menu.database_logs') }}</h4>
    </div>
</div>

<div class="card">
    <div class="card-body">
        {{-- Filters --}}
        <form method="GET" action="{{ ocadmin_route('system.logs.database') }}" class="row g-3 mb-4">
            <div class="col-md-3">
                <select name="level" class="form-select">
                    <option value="">{{ __('ocadmin::common.all') }}</option>
                    @foreach($levels as $value => $label)
                        <option value="{{ $value }}" {{ request('level') === $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-5">
                <input type="text" name="search" class="form-control"
                       placeholder="{{ __('ocadmin::common.search') }}"
                       value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    {{ __('ocadmin::common.filter') }}
                </button>
            </div>
            <div class="col-md-2">
                <a href="{{ ocadmin_route('system.logs.database') }}" class="btn btn-secondary w-100">
                    {{ __('ocadmin::common.reset') }}
                </a>
            </div>
        </form>

        {{-- Logs Table --}}
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th style="width: 160px;">{{ __('ocadmin::common.datetime') }}</th>
                        <th style="width: 100px;">{{ __('system-log::logs.level') }}</th>
                        <th>{{ __('system-log::logs.message') }}</th>
                        <th style="width: 80px;">{{ __('ocadmin::common.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td>{{ $log->created_at }}</td>
                            <td>
                                <span class="badge bg-{{ $log->level === 'error' ? 'danger' : ($log->level === 'warning' ? 'warning' : 'info') }}">
                                    {{ ucfirst($log->level) }}
                                </span>
                            </td>
                            <td class="text-truncate" style="max-width: 400px;">
                                {{ Str::limit($log->message, 100) }}
                            </td>
                            <td>
                                <a href="{{ ocadmin_route('system.logs.show', $log->id) }}"
                                   class="btn btn-sm btn-outline-primary">
                                    <i data-feather="eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                {{ __('ocadmin::common.no_data') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="d-flex justify-content-end">
            {{ $logs->withQueryString()->links() }}
        </div>
    </div>
</div>

{{-- Cleanup Form --}}
<div class="card mt-4">
    <div class="card-header">
        <h5 class="card-title mb-0">{{ __('system-log::logs.cleanup') }}</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ ocadmin_route('system.logs.cleanup') }}"
              onsubmit="return confirm('{{ __('system-log::logs.cleanup_confirm') }}')">
            @csrf
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">{{ __('system-log::logs.older_than') }}</label>
                    <select name="days" class="form-select">
                        <option value="7">7 {{ __('ocadmin::common.days') }}</option>
                        <option value="14">14 {{ __('ocadmin::common.days') }}</option>
                        <option value="30" selected>30 {{ __('ocadmin::common.days') }}</option>
                        <option value="60">60 {{ __('ocadmin::common.days') }}</option>
                        <option value="90">90 {{ __('ocadmin::common.days') }}</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-danger">
                        <i data-feather="trash-2"></i>
                        {{ __('system-log::logs.cleanup') }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
