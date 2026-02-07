<div class="table-responsive">
    <table class="table table-bordered table-hover">
        <thead>
            <tr>
                <th>{{ $lang->column_table_name }}</th>
                <th>{{ $lang->column_comment }}</th>
                <th class="text-center">{{ $lang->column_column_count }}</th>
                <th class="text-center">{{ $lang->column_translation_count }}</th>
                <th class="text-center">{{ $lang->column_status }}</th>
                <th class="text-end">{{ $lang->column_action }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tables as $table)
            <tr>
                <td><code>{{ $table['name'] }}</code></td>
                <td>{{ $table['comment'] ?: '-' }}</td>
                <td class="text-center">{{ $table['column_count'] }}</td>
                <td class="text-center">{{ $table['translation_count'] ?: '-' }}</td>
                <td class="text-center">
                    @switch($table['status'])
                        @case('synced')
                            <span class="badge bg-success"><i class="fa-solid fa-check"></i> {{ $lang->text_synced }}</span>
                            @break
                        @case('diff')
                            <span class="badge bg-warning text-dark"><i class="fa-solid fa-exclamation-triangle"></i> {{ $lang->text_diff }} ({{ $table['change_count'] }})</span>
                            @break
                        @case('db_only')
                            <span class="badge bg-secondary">{{ $lang->text_db_only }}</span>
                            @break
                        @case('schema_only')
                            <span class="badge bg-info">{{ $lang->text_schema_only }}</span>
                            @break
                    @endswitch
                </td>
                <td class="text-end text-nowrap">
                    @if($table['status'] !== 'db_only')
                    <a href="{{ route('lang.ocadmin.system.schema.edit', $table['name']) }}" data-bs-toggle="tooltip" title="{{ $lang->button_edit }}" class="btn btn-primary btn-sm">
                        <i class="fa-solid fa-pencil"></i>
                    </a>
                    @endif

                    @if($table['status'] !== 'db_only')
                    <button type="button" class="btn {{ in_array($table['status'], ['diff', 'schema_only']) ? 'btn-warning' : 'btn-info' }} btn-sm btn-diff" data-table="{{ $table['name'] }}" data-url="{{ route('lang.ocadmin.system.schema.diff', $table['name']) }}" data-bs-toggle="tooltip" title="{{ $lang->button_diff }}">
                        <i class="fa-solid fa-code-compare"></i>
                    </button>
                    @endif

                    @if($table['status'] !== 'schema_only')
                    <button type="button" class="btn btn-outline-secondary btn-sm btn-export" data-url="{{ route('lang.ocadmin.system.schema.export', $table['name']) }}" data-bs-toggle="tooltip" title="{{ $lang->button_export }}">
                        <i class="fa-solid fa-download"></i>
                    </button>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center">{{ $lang->text_no_data }}</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="row">
    <div class="col-sm-6 text-end">共 {{ count($tables) }} 個資料表</div>
</div>
