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
                    <a href="{{ $sort_business_no }}" @class([$order => $sort === 'business_no'])>{{ $lang->column_business_no }}</a>
                </th>
                <th>{{ $lang->column_shipping_state }}</th>
                <th>{{ $lang->column_shipping_city }}</th>
                <th>{{ $lang->column_shipping_address1 }}</th>
                <th class="text-end">{{ $lang->column_action }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($organizations as $organization)
            <tr>
                <td class="text-center">
                    <input type="checkbox" name="selected[]" value="{{ $organization->id }}" class="form-check-input">
                </td>
                <td>{{ $organization->name }}</td>
                <td>{{ $organization->short_name ?: '-' }}</td>
                <td>{{ $organization->business_no ?: '-' }}</td>
                <td>{{ $organization->shipping_state ?: '-' }}</td>
                <td>{{ $organization->shipping_city ?: '-' }}</td>
                <td>{{ $organization->shipping_address1 ?: '-' }}</td>
                <td class="text-end">
                    <a href="{{ route('lang.ocadmin.organization.edit', $organization) }}" data-bs-toggle="tooltip" title="{{ $lang->button_edit }}" class="btn btn-primary"><i class="bi bi-pencil"></i></a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center">{{ $lang->text_no_data }}</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="row">
    <div class="col-sm-6 text-start">{!! $pagination !!}</div>
    <div class="col-sm-6 text-end">{!! sprintf($lang->text_showing, $organizations->firstItem() ?? 0, $organizations->lastItem() ?? 0, $organizations->total()) !!}</div>
</div>
