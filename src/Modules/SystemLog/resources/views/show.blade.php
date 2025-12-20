@extends('ocadmin::layouts.app')

@section('title', __('system-log::logs.log_detail'))

@section('content')
<div class="page-header">
    <div class="page-title">
        <h4>{{ __('system-log::logs.log_detail') }}</h4>
    </div>
    <div class="page-btn">
        <a href="{{ ocadmin_route('system.logs.database') }}" class="btn btn-secondary">
            <i data-feather="arrow-left"></i>
            {{ __('ocadmin::common.back') }}
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="row mb-4">
            <div class="col-md-6">
                <table class="table table-borderless">
                    <tr>
                        <th style="width: 120px;">{{ __('ocadmin::common.id') }}</th>
                        <td>{{ $log->id }}</td>
                    </tr>
                    <tr>
                        <th>{{ __('system-log::logs.level') }}</th>
                        <td>
                            <span class="badge bg-{{ $log->level === 'error' ? 'danger' : ($log->level === 'warning' ? 'warning' : 'info') }}">
                                {{ ucfirst($log->level) }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>{{ __('system-log::logs.channel') }}</th>
                        <td>{{ $log->channel ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>{{ __('ocadmin::common.datetime') }}</th>
                        <td>{{ $log->created_at }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="mb-4">
            <h6 class="fw-bold">{{ __('system-log::logs.message') }}</h6>
            <div class="p-3 bg-light rounded">
                <pre class="mb-0" style="white-space: pre-wrap; word-break: break-word;">{{ $log->message }}</pre>
            </div>
        </div>

        @if($log->context)
            <div class="mb-4">
                <h6 class="fw-bold">{{ __('system-log::logs.context') }}</h6>
                <div class="p-3 bg-light rounded">
                    <pre class="mb-0" style="white-space: pre-wrap; word-break: break-word;">{{ is_string($log->context) ? $log->context : json_encode(json_decode($log->context), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                </div>
            </div>
        @endif

        @if($log->extra)
            <div class="mb-4">
                <h6 class="fw-bold">{{ __('system-log::logs.extra') }}</h6>
                <div class="p-3 bg-light rounded">
                    <pre class="mb-0" style="white-space: pre-wrap; word-break: break-word;">{{ is_string($log->extra) ? $log->extra : json_encode(json_decode($log->extra), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
