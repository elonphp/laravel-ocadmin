@extends('ocadmin::layouts.app')

@section('title', __('system-log::menu.archived_logs'))

@section('content')
<div class="page-header">
    <div class="page-title">
        <h4>{{ __('system-log::menu.archived_logs') }}</h4>
    </div>
</div>

<div class="row">
    {{-- File List --}}
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">{{ __('system-log::logs.log_files') }}</h5>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @forelse($files as $file)
                        <a href="{{ ocadmin_route('system.logs.archived', ['file' => $file['name']]) }}"
                           class="list-group-item list-group-item-action {{ $selectedFile === $file['name'] ? 'active' : '' }}">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <i data-feather="file-text" class="me-2"></i>
                                    {{ $file['name'] }}
                                </div>
                                <small class="text-muted">{{ $file['size'] }}</small>
                            </div>
                            <small class="d-block mt-1 {{ $selectedFile === $file['name'] ? 'text-white-50' : 'text-muted' }}">
                                {{ $file['modified'] }}
                            </small>
                        </a>
                    @empty
                        <li class="list-group-item text-center text-muted py-4">
                            {{ __('system-log::logs.no_log_files') }}
                        </li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

    {{-- Log Content --}}
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    @if($selectedFile)
                        {{ $selectedFile }}
                    @else
                        {{ __('system-log::logs.select_file') }}
                    @endif
                </h5>
            </div>
            <div class="card-body">
                @if($selectedFile && count($logs) > 0)
                    <div class="log-entries" style="max-height: 600px; overflow-y: auto;">
                        @foreach($logs as $log)
                            <div class="log-entry mb-3 p-3 rounded {{ $log['level'] === 'ERROR' ? 'bg-danger-subtle' : ($log['level'] === 'WARNING' ? 'bg-warning-subtle' : 'bg-light') }}">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="badge bg-{{ $log['level'] === 'ERROR' ? 'danger' : ($log['level'] === 'WARNING' ? 'warning' : 'info') }}">
                                        {{ $log['level'] }}
                                    </span>
                                    <small class="text-muted">{{ $log['timestamp'] }}</small>
                                </div>
                                <pre class="mb-0 small" style="white-space: pre-wrap; word-break: break-word;">{{ $log['message'] }}</pre>
                            </div>
                        @endforeach
                    </div>
                @elseif($selectedFile)
                    <div class="text-center text-muted py-5">
                        {{ __('system-log::logs.empty_file') }}
                    </div>
                @else
                    <div class="text-center text-muted py-5">
                        <i data-feather="file-text" style="width: 48px; height: 48px;"></i>
                        <p class="mt-3">{{ __('system-log::logs.select_file_hint') }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
