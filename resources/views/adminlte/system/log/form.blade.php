@extends('adminlte::layouts.app')

@section('title', $lang->text_detail)

@section('content')
<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">{{ $lang->text_detail }}</h3>
            </div>
            <div class="col-sm-6">
                @include('adminlte::layouts.partials.breadcrumb')
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="mb-3 text-end">
            <a href="{{ route('lang.ocadmin.system.log.index') }}" data-bs-toggle="tooltip" title="{{ $lang->button_back }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i>
            </a>
        </div>

        <div class="card">
            <div class="card-header"><i class="bi bi-eye"></i> {{ $lang->text_detail }} #{{ $log->id }}</div>
            <div class="card-body">
                <div class="row mb-3">
                    <label class="col-sm-2 col-form-label fw-bold">{{ $lang->column_trace_id }}</label>
                    <div class="col-sm-10">
                        <p class="form-control-plaintext">{{ $log->request_trace_id ?: '-' }}</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <label class="col-sm-2 col-form-label fw-bold">{{ $lang->column_user }}</label>
                    <div class="col-sm-10">
                        <p class="form-control-plaintext">
                            @if($log->user)
                                {{ $log->user->name }} (ID: {{ $log->user_id }})
                            @elseif($log->user_id)
                                ID: {{ $log->user_id }}
                            @else
                                -
                            @endif
                        </p>
                    </div>
                </div>

                <div class="row mb-3">
                    <label class="col-sm-2 col-form-label fw-bold">{{ $lang->column_app_name }}</label>
                    <div class="col-sm-10">
                        <p class="form-control-plaintext">{{ $log->app_name ?: '-' }}</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <label class="col-sm-2 col-form-label fw-bold">{{ $lang->column_portal }}</label>
                    <div class="col-sm-10">
                        <p class="form-control-plaintext">{{ $log->portal ?: '-' }}</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <label class="col-sm-2 col-form-label fw-bold">{{ $lang->column_area }}</label>
                    <div class="col-sm-10">
                        <p class="form-control-plaintext">{{ $log->area ?: '-' }}</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <label class="col-sm-2 col-form-label fw-bold">{{ $lang->column_method }}</label>
                    <div class="col-sm-10">
                        <p class="form-control-plaintext">{{ $log->method ?: '-' }}</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <label class="col-sm-2 col-form-label fw-bold">{{ $lang->column_url }}</label>
                    <div class="col-sm-10">
                        <p class="form-control-plaintext" style="word-break: break-all;">{{ $log->url }}</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <label class="col-sm-2 col-form-label fw-bold">{{ $lang->column_status_code }}</label>
                    <div class="col-sm-10">
                        <p class="form-control-plaintext">
                            @if($log->status_code)
                            <span class="badge bg-{{ $log->status_code >= 500 ? 'danger' : ($log->status_code >= 400 ? 'warning' : ($log->status_code >= 300 ? 'info' : 'success')) }}">{{ $log->status_code }}</span>
                            @else
                            -
                            @endif
                        </p>
                    </div>
                </div>

                <div class="row mb-3">
                    <label class="col-sm-2 col-form-label fw-bold">{{ $lang->column_status }}</label>
                    <div class="col-sm-10">
                        <p class="form-control-plaintext">
                            @if($log->status)
                            <span class="badge bg-{{ match($log->status) { 'success' => 'success', 'warning' => 'warning', 'error' => 'danger', default => 'secondary' } }}">{{ $log->status }}</span>
                            @else
                            -
                            @endif
                        </p>
                    </div>
                </div>

                <div class="row mb-3">
                    <label class="col-sm-2 col-form-label fw-bold">{{ $lang->column_client_ip }}</label>
                    <div class="col-sm-10">
                        <p class="form-control-plaintext">{{ $log->client_ip ?: '-' }}</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <label class="col-sm-2 col-form-label fw-bold">{{ $lang->column_api_ip }}</label>
                    <div class="col-sm-10">
                        <p class="form-control-plaintext">{{ $log->api_ip ?: '-' }}</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <label class="col-sm-2 col-form-label fw-bold">{{ $lang->column_note }}</label>
                    <div class="col-sm-10">
                        <p class="form-control-plaintext">{{ $log->note ?: '-' }}</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <label class="col-sm-2 col-form-label fw-bold">{{ $lang->column_created_at }}</label>
                    <div class="col-sm-10">
                        <p class="form-control-plaintext">{{ $log->created_at?->format('Y-m-d H:i:s') }}</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <label class="col-sm-2 col-form-label fw-bold">{{ $lang->column_request_data }}</label>
                    <div class="col-sm-10">
                        @if($log->request_data)
                        <pre class="bg-light p-3 rounded" style="max-height: 400px; overflow: auto;"><code>{{ json_encode($log->request_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                        @else
                        <p class="form-control-plaintext">-</p>
                        @endif
                    </div>
                </div>

                <div class="row mb-3">
                    <label class="col-sm-2 col-form-label fw-bold">{{ $lang->column_response_data }}</label>
                    <div class="col-sm-10">
                        @if($log->response_data)
                        <pre class="bg-light p-3 rounded" style="max-height: 400px; overflow: auto;"><code>{{ json_encode($log->response_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                        @else
                        <p class="form-control-plaintext">-</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
