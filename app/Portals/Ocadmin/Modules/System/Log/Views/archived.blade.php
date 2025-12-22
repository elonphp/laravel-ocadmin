@extends('ocadmin::layouts.app')

@section('title', '歷史壓縮檔')

@section('content')
<div id="content">
    <div class="page-header">
        <div class="container-fluid">
            <h1>歷史壓縮檔</h1>
            @include('ocadmin::layouts.partials.breadcrumb')
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            {{-- 檔案列表 - 左側 --}}
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header"><i class="fa-solid fa-archive"></i> 壓縮檔列表</div>
                    <div class="card-body p-0">
                        @if(count($files) > 0)
                            <ul class="list-group list-group-flush">
                                @foreach($files as $file)
                                    <a href="{{ route('lang.ocadmin.system.log.archived', ['file' => $file['filename']]) }}"
                                       class="list-group-item list-group-item-action {{ ($selectedFile ?? '') === $file['filename'] ? 'active' : '' }}">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <i class="fa-solid fa-file-zipper me-2"></i>
                                                {{ $file['month'] }}
                                            </div>
                                            <span class="badge bg-secondary">{{ $file['size'] }}</span>
                                        </div>
                                        <small class="d-block mt-1 {{ ($selectedFile ?? '') === $file['filename'] ? 'text-white-50' : 'text-muted' }}">
                                            {{ $file['filename'] }}
                                        </small>
                                    </a>
                                @endforeach
                            </ul>
                        @else
                            <div class="p-4 text-center text-muted">
                                <i class="fa-solid fa-archive fa-3x mb-3"></i>
                                <p>沒有找到壓縮檔</p>
                                <small>歸檔的日誌會顯示在這裡</small>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- 檔案內容 - 右側 --}}
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <span>
                                <i class="fa-solid fa-folder-open"></i>
                                @if($selectedFile)
                                    {{ $selectedFile }}
                                @else
                                    選擇壓縮檔
                                @endif
                            </span>
                            @if($selectedFile)
                                <a href="{{ route('lang.ocadmin.system.log.archived.download', ['filename' => $selectedFile]) }}"
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="fa-solid fa-download"></i> 下載
                                </a>
                            @endif
                        </div>
                    </div>
                    <div class="card-body">
                        @if($selectedFile && $fileContents)
                            @if($fileContents['success'] && count($fileContents['files']) > 0)
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th>檔案名稱</th>
                                                <th style="width: 120px;">大小</th>
                                                <th style="width: 100px;">操作</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($fileContents['files'] as $logFile)
                                                <tr>
                                                    <td>
                                                        <i class="fa-solid fa-file-lines me-2 text-muted"></i>
                                                        {{ $logFile['name'] }}
                                                    </td>
                                                    <td>{{ $logFile['size'] }}</td>
                                                    <td>
                                                        <a href="{{ route('lang.ocadmin.system.log.archived.view', ['filename' => $selectedFile]) }}?log_file={{ urlencode($logFile['name']) }}"
                                                           class="btn btn-sm btn-outline-primary"
                                                           target="_blank">
                                                            <i class="fa-solid fa-eye"></i> 查看
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @elseif(!$fileContents['success'])
                                <div class="text-center text-danger py-5">
                                    <i class="fa-solid fa-exclamation-triangle fa-3x mb-3"></i>
                                    <p>{{ $fileContents['message'] }}</p>
                                </div>
                            @else
                                <div class="text-center text-muted py-5">
                                    <i class="fa-solid fa-folder-open fa-3x mb-3"></i>
                                    <p>此壓縮檔是空的</p>
                                </div>
                            @endif
                        @else
                            <div class="text-center text-muted py-5">
                                <i class="fa-solid fa-hand-pointer fa-3x mb-3"></i>
                                <p>請從左側選擇一個壓縮檔查看內容</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
