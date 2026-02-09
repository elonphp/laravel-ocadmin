<div class="table-responsive">
    <table class="table table-bordered table-hover">
        <thead>
            <tr>
                <th class="text-center" style="width: 1px;">
                    <input type="checkbox" onclick="$('input[name*=\'selected\']').prop('checked', $(this).prop('checked'));" class="form-check-input">
                </th>
                <th>
                    <a href="{{ $sort_date }}" @class([$order => $sort === 'date'])>
                        {{ $lang->column_date }}
                    </a>
                </th>
                <th>
                    <a href="{{ $sort_day_type }}" @class([$order => $sort === 'day_type'])>
                        {{ $lang->column_day_type }}
                    </a>
                </th>
                <th>
                    <a href="{{ $sort_name }}" @class([$order => $sort === 'name'])>
                        {{ $lang->column_name }}
                    </a>
                </th>
                <th class="text-center">{{ $lang->column_is_workday }}</th>
                <th>{{ $lang->column_description }}</th>
                <th class="text-end">{{ $lang->column_action }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($calendarDays as $calendarDay)
            <tr>
                <td class="text-center">
                    <input type="checkbox" name="selected[]" value="{{ $calendarDay->id }}" class="form-check-input">
                </td>
                <td>{{ $calendarDay->date->format('Y-m-d') }}</td>
                <td>
                    @if($calendarDay->color)
                    <span class="badge" style="background-color: {{ $calendarDay->color }}">{{ $dayTypeOptions[$calendarDay->day_type] ?? $calendarDay->day_type }}</span>
                    @else
                    {{ $dayTypeOptions[$calendarDay->day_type] ?? $calendarDay->day_type }}
                    @endif
                </td>
                <td>{{ $calendarDay->name ?: '-' }}</td>
                <td class="text-center">
                    @if($calendarDay->is_workday)
                    <span class="badge bg-success">{{ $lang->text_yes }}</span>
                    @else
                    <span class="badge bg-secondary">{{ $lang->text_no }}</span>
                    @endif
                </td>
                <td>{{ $calendarDay->description ?: '-' }}</td>
                <td class="text-end">
                    <a href="{{ route('lang.ocadmin.hrm.calendar-day.edit', $calendarDay) }}" data-bs-toggle="tooltip" title="{{ $lang->button_edit }}" class="btn btn-primary">
                        <i class="fa-solid fa-pencil"></i>
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center">{{ $lang->text_no_data }}</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="row">
    <div class="col-sm-6 text-start">{!! $pagination !!}</div>
    <div class="col-sm-6 text-end">{!! sprintf($lang->text_showing, $calendarDays->firstItem() ?? 0, $calendarDays->lastItem() ?? 0, $calendarDays->total()) !!}</div>
</div>
