<?php

namespace App\Models\Hrm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AttendanceSetting extends Model
{
    protected $table = 'hrm_attendance_settings';

    protected $fillable = [
        'settingable_type',
        'settingable_id',
        'workdays',
        'default_work_start',
        'default_work_end',
        'default_break_minutes',
        'late_threshold_minutes',
        'early_leave_threshold_minutes',
        'count_early_arrival',
        'count_late_departure',
    ];

    protected function casts(): array
    {
        return [
            'workdays'             => 'array',
            'default_work_start'   => 'datetime:H:i',
            'default_work_end'     => 'datetime:H:i',
            'count_early_arrival'  => 'boolean',
            'count_late_departure' => 'boolean',
        ];
    }

    public function settingable(): MorphTo
    {
        return $this->morphTo();
    }
}
