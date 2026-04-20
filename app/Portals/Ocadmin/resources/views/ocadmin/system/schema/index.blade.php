@extends('ocadmin::layouts.app')

@section('title', $lang->heading_title)

@section('content')
<div id="content">
    <div class="page-header">
        <div class="container-fluid">
            <h1>{{ $lang->heading_title }}</h1>
        </div>
    </div>

    <div class="container-fluid">
        <div class="card">
            <div class="card-header"><i class="fa-solid fa-list"></i> {{ $lang->text_list }}</div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>{{ $lang->column_table_name }}</th>
                                <th class="text-center" style="width: 100px;">{{ $lang->column_column_count }}</th>
                                <th>{{ $lang->column_comment }}</th>
                                <th class="text-end" style="width: 100px;">{{ $lang->column_action }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tables as $t)
                            <tr>
                                <td><code>{{ $t['name'] }}</code></td>
                                <td class="text-center">{{ $t['column_count'] }}</td>
                                <td>{{ $t['comment'] ?: '-' }}</td>
                                <td class="text-end">
                                    <a href="{{ route('lang.ocadmin.system.schemas.edit', $t['name']) }}" data-bs-toggle="tooltip" title="{{ $lang->button_edit }}" class="btn btn-primary btn-sm">
                                        <i class="fa-solid fa-pencil"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center">{{ $lang->text_no_data }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
