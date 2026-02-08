<?php

namespace App\Models\Hrm;

use App\Enums\Common\Gender;
use App\Models\Company;
use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    protected $table = 'hrm_employees';

    protected $fillable = [
        'user_id',
        'company_id',
        'department_id',
        'employee_no',
        'first_name',
        'last_name',
        'email',
        'phone',
        'hire_date',
        'birth_date',
        'gender',
        'job_title',
        'address',
        'note',
        'is_active',
        'default_work_start',
        'default_work_end',
    ];

    protected function casts(): array
    {
        return [
            'hire_date'  => 'date',
            'birth_date' => 'date',
            'gender'     => Gender::class,
            'is_active'  => 'boolean',
            'default_work_start' => 'datetime:H:i',
            'default_work_end'   => 'datetime:H:i',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    /**
     * 打卡記錄
     */
    public function clockRecords(): HasMany
    {
        return $this->hasMany(ClockRecord::class);
    }

    /**
     * 每日出勤記錄
     */
    public function dailyAttendances(): HasMany
    {
        return $this->hasMany(DailyAttendance::class);
    }

    /**
     * 每月統計
     */
    public function monthlySummaries(): HasMany
    {
        return $this->hasMany(MonthlySummary::class);
    }
}
