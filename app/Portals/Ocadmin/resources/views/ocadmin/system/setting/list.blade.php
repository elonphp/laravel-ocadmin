<div class="table-responsive">
    <table class="table table-bordered table-hover">
        <thead>
            <tr>
                <th class="text-center" style="width: 1px;">
                    <input type="checkbox" onclick="$('input[name*=\'selected\']').prop('checked', $(this).prop('checked'));" class="form-check-input">
                </th>
                <th>
                    <a href="{{ $sort_code }}" @class([$order => $sort === 'code'])>
                        代碼
                    </a>
                </th>
                <th>
                    <a href="{{ $sort_group }}" @class([$order => $sort === 'group'])>
                        群組
                    </a>
                </th>
                <th>內容</th>
                <th>類型</th>
                <th class="text-end">操作</th>
            </tr>
        </thead>
        <tbody>
            @forelse($settings as $setting)
            <tr>
                <td class="text-center">
                    <input type="checkbox" name="selected[]" value="{{ $setting->id }}" class="form-check-input">
                </td>
                <td>{{ $setting->code }}</td>
                <td>{{ $setting->group ?: '-' }}</td>
                <td>
                    <span title="{{ $setting->value }}">
                        {{ Str::limit($setting->value, 50) }}
                    </span>
                </td>
                <td>
                    <span class="badge bg-secondary">{{ $setting->type->label() }}</span>
                </td>
                <td class="text-end">
                    <a href="{{ route('lang.ocadmin.system.settings.edit', $setting) . $urlParams }}" data-bs-toggle="tooltip" title="編輯" class="btn btn-primary"><i class="fa-solid fa-pencil"></i></a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center">暫無資料</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="row">
    <div class="col-sm-6 text-start">{!! $pagination !!}</div>
    <div class="col-sm-6 text-end">{!! sprintf($lang->text_showing, $settings->firstItem() ?? 0, $settings->lastItem() ?? 0, $settings->total()) !!}</div>
</div>
