<div class="table-responsive">
    <table class="table table-bordered table-hover">
        <thead>
            <tr>
                <th class="text-center" style="width: 1px;">
                    <input type="checkbox" onclick="$('input[name*=\'selected\']').prop('checked', $(this).prop('checked'));" class="form-check-input">
                </th>
                <th>
                    <a href="{{ $sort_employee_no }}" @class([$order => $sort === 'employee_no'])>{{ $lang->column_employee_no }}</a>
                </th>
                <th>
                    <a href="{{ $sort_first_name }}" @class([$order => $sort === 'first_name'])>{{ $lang->column_first_name }}</a>
                </th>
                <th>
                    <a href="{{ $sort_email }}" @class([$order => $sort === 'email'])>{{ $lang->column_email }}</a>
                </th>
                <th>{{ $lang->column_organization }}</th>
                <th>{{ $lang->column_job_title }}</th>
                <th>
                    <a href="{{ $sort_hire_date }}" @class([$order => $sort === 'hire_date'])>{{ $lang->column_hire_date }}</a>
                </th>
                <th class="text-center">{{ $lang->column_is_active }}</th>
                <th class="text-end">{{ $lang->column_action }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($employees as $employee)
            <tr>
                <td class="text-center">
                    <input type="checkbox" name="selected[]" value="{{ $employee->id }}" class="form-check-input">
                </td>
                <td>{{ $employee->employee_no ?: '-' }}</td>
                <td>{{ $employee->full_name }}</td>
                <td>{{ $employee->email ?: '-' }}</td>
                <td>{{ $employee->organization?->name ?: '-' }}</td>
                <td>{{ $employee->job_title ?: '-' }}</td>
                <td>{{ $employee->hire_date?->format('Y-m-d') ?: '-' }}</td>
                <td class="text-center">
                    @if($employee->is_active)
                    <span class="badge bg-success">{{ $lang->text_active }}</span>
                    @else
                    <span class="badge bg-secondary">{{ $lang->text_inactive }}</span>
                    @endif
                </td>
                <td class="text-end">
                    <a href="{{ route('lang.ocadmin.hrm.employee.edit', $employee) }}" data-bs-toggle="tooltip" title="{{ $lang->button_edit }}" class="btn btn-primary"><i class="fa-solid fa-pencil"></i></a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="text-center">{{ $lang->text_no_data }}</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="row">
    <div class="col-sm-6 text-start">{!! $pagination !!}</div>
    <div class="col-sm-6 text-end">{!! sprintf($lang->text_showing, $employees->firstItem() ?? 0, $employees->lastItem() ?? 0, $employees->total()) !!}</div>
</div>
