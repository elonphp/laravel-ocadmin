<div class="table-responsive">
    <table class="table table-bordered table-hover">
        <thead>
            <tr>
                <th class="text-center" style="width: 1px;">
                    <input type="checkbox" onclick="$('input[name*=\'selected\']').prop('checked', $(this).prop('checked'));" class="form-check-input">
                </th>
                <th>
                    <a href="{{ $sort_name }}" @class([$order => $sort === 'name'])>{{ $lang->column_name }}</a>
                </th>
                <th>{{ $lang->column_short_name }}</th>
                <th>
                    <a href="{{ $sort_code }}" @class([$order => $sort === 'code'])>{{ $lang->column_code }}</a>
                </th>
                <th>{{ $lang->column_business_no }}</th>
                <th>{{ $lang->column_parent }}</th>
                <th>{{ $lang->column_phone }}</th>
                <th>{{ $lang->column_is_active }}</th>
                <th>
                    <a href="{{ $sort_sort_order }}" @class([$order => $sort === 'sort_order'])>{{ $lang->column_sort_order }}</a>
                </th>
                <th class="text-end">{{ $lang->column_action }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($companies as $company)
            <tr>
                <td class="text-center">
                    <input type="checkbox" name="selected[]" value="{{ $company->id }}" class="form-check-input">
                </td>
                <td>{{ $company->name }}</td>
                <td>{{ $company->short_name ?: '-' }}</td>
                <td>{{ $company->code ?: '-' }}</td>
                <td>{{ $company->business_no ?: '-' }}</td>
                <td>{{ $company->parent?->name ?: '-' }}</td>
                <td>{{ $company->phone ?: '-' }}</td>
                <td>
                    @if($company->is_active)
                        <span class="badge bg-success">{{ $lang->text_active }}</span>
                    @else
                        <span class="badge bg-secondary">{{ $lang->text_inactive }}</span>
                    @endif
                </td>
                <td>{{ $company->sort_order }}</td>
                <td class="text-end">
                    <a href="{{ route('lang.ocadmin.hrm.company.edit', $company) }}" data-bs-toggle="tooltip" title="{{ $lang->button_edit }}" class="btn btn-primary"><i class="bi bi-pencil"></i></a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="10" class="text-center">{{ $lang->text_no_data }}</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="row">
    <div class="col-sm-6 text-start">{!! $pagination !!}</div>
    <div class="col-sm-6 text-end">{!! sprintf($lang->text_showing, $companies->firstItem() ?? 0, $companies->lastItem() ?? 0, $companies->total()) !!}</div>
</div>
