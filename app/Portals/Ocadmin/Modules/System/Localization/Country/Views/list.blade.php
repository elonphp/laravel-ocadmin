<form id="form-country" method="post" data-oc-toggle="ajax" data-oc-load="{{ $action }}" data-oc-target="#country-list">
    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th class="text-center" style="width: 1px;">
                        <input type="checkbox" onclick="$('input[name*=\'selected\']').prop('checked', $(this).prop('checked'));" class="form-check-input">
                    </th>
                    <th>
                        <a href="{{ route('lang.ocadmin.system.localization.country.list', array_merge(request()->all(), ['sort' => 'name', 'order' => request('order') === 'asc' && request('sort') === 'name' ? 'desc' : 'asc'])) }}" @class([request('order', 'asc') => request('sort') === 'name'])>
                            {{ $lang->column_name }}
                        </a>
                    </th>
                    <th>
                        <a href="{{ route('lang.ocadmin.system.localization.country.list', array_merge(request()->all(), ['sort' => 'native_name', 'order' => request('order') === 'asc' && request('sort') === 'native_name' ? 'desc' : 'asc'])) }}" @class([request('order', 'asc') => request('sort') === 'native_name'])>
                            {{ $lang->column_native_name }}
                        </a>
                    </th>
                    <th>
                        <a href="{{ route('lang.ocadmin.system.localization.country.list', array_merge(request()->all(), ['sort' => 'iso_code_2', 'order' => request('order') === 'asc' && request('sort') === 'iso_code_2' ? 'desc' : 'asc'])) }}" @class([request('order', 'asc') => request('sort') === 'iso_code_2'])>
                            {{ $lang->column_iso_code_2 }}
                        </a>
                    </th>
                    <th>
                        <a href="{{ route('lang.ocadmin.system.localization.country.list', array_merge(request()->all(), ['sort' => 'iso_code_3', 'order' => request('order') === 'asc' && request('sort') === 'iso_code_3' ? 'desc' : 'asc'])) }}" @class([request('order', 'asc') => request('sort') === 'iso_code_3'])>
                            {{ $lang->column_iso_code_3 }}
                        </a>
                    </th>
                    <th class="text-end">{{ $lang->column_action }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($countries as $country)
                <tr @class(['table-active opacity-50' => !$country->is_active])>
                    <td class="text-center">
                        <input type="checkbox" name="selected[]" value="{{ $country->id }}" class="form-check-input">
                    </td>
                    <td>{{ $country->name }}</td>
                    <td>{{ $country->native_name }}</td>
                    <td>{{ $country->iso_code_2 }}</td>
                    <td>{{ $country->iso_code_3 }}</td>
                    <td class="text-end">
                        <a href="{{ route('lang.ocadmin.system.localization.country.edit', $country) }}" data-bs-toggle="tooltip" title="{{ $lang->button_edit }}" class="btn btn-primary"><i class="fa-solid fa-pencil"></i></a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center">{{ $lang->text_no_results }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="row">
        <div class="col-sm-6 text-start">{{ $countries->links('ocadmin::pagination.default') }}</div>
        <div class="col-sm-6 text-end">{{ sprintf($lang->text_pagination, $countries->firstItem() ?? 0, $countries->lastItem() ?? 0, $countries->total()) }}</div>
    </div>
</form>
