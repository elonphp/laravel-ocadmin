<?php

namespace App\Models\Hrm;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MonthlySummary extends Model
{
    use SoftDeletes;

    protected $table = 'hrm_monthly_summaries';

    protected $fillable = [
        'employee_id',
        'year_month',
        'scheduled_workdays',
        'actual_workdays',
        'absent_days',
        'holiday_workdays',
        'scheduled_minutes',
        'work_minutes',
        'overtime_minutes',
        'weekday_overtime_minutes',
        'holiday_overtime_minutes',
        'late_count',
        'late_minutes',
        'early_leave_count',
        'early_leave_minutes',
        'annual_leave_days',
        'sick_leave_days',
        'personal_leave_days',
        'other_leave_days',
        'status',
        'note',
        'reviewed_by',
        'reviewed_at',
        'calculated_at',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_workdays' => 'integer',
            'actual_workdays' => 'integer',
            'absent_days' => 'integer',
            'holiday_workdays' => 'integer',
            'scheduled_minutes' => 'integer',
            'work_minutes' => 'integer',
            'overtime_minutes' => 'integer',
            'weekday_overtime_minutes' => 'integer',
            'holiday_overtime_minutes' => 'integer',
            'late_count' => 'integer',
            'late_minutes' => 'integer',
            'early_leave_count' => 'integer',
            'early_leave_minutes' => 'integer',
            'annual_leave_days' => 'integer',
            'sick_leave_days' => 'integer',
            'personal_leave_days' => 'integer',
            'other_leave_days' => 'integer',
            'reviewed_at' => 'datetime',
            'calculated_at' => 'datetime',
        ];
    }

    /**
     * 所屬員工
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * 審核人
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * 檢查是否為草稿
     */
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * 檢查是否待審核
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * 檢查是否已審核
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * 檢查是否已鎖定
     */
    public function isLocked(): bool
    {
        return $this->status === 'locked';
    }

    /**
     * 取得總工時（小時）
     */
    public function getWorkHoursAttribute(): float
    {
        return round($this->work_minutes / 60, 2);
    }

    /**
     * 取得總加班時數（小時）
     */
    public function getOvertimeHoursAttribute(): float
    {
        return round($this->overtime_minutes / 60, 2);
    }

    /**
     * 取得平日加班時數（小時）
     */
    public function getWeekdayOvertimeHoursAttribute(): float
    {
        return round($this->weekday_overtime_minutes / 60, 2);
    }

    /**
     * 取得假日加班時數（小時）
     */
    public function getHolidayOvertimeHoursAttribute(): float
    {
        return round($this->holiday_overtime_minutes / 60, 2);
    }

    /**
     * 取得總請假天數
     */
    public function getTotalLeaveDaysAttribute(): int
    {
        return $this->annual_leave_days
            + $this->sick_leave_days
            + $this->personal_leave_days
            + $this->other_leave_days;
    }

    /**
     * 取得出勤率（百分比）
     */
    public function getAttendanceRateAttribute(): float
    {
        if ($this->scheduled_workdays === 0) {
            return 0;
        }

        return round(($this->actual_workdays / $this->scheduled_workdays) * 100, 2);
    }

    /**
     * 取得狀態顯示名稱
     */
    public function getStatusNameAttribute(): string
    {
        return match ($this->status) {
            'draft' => '草稿',
            'pending' => '待審核',
            'approved' => '已審核',
            'locked' => '已鎖定',
            default => '未知',
        };
    }

    /**
     * 取得年份
     */
    public function getYearAttribute(): int
    {
        return (int) substr($this->year_month, 0, 4);
    }

    /**
     * 取得月份
     */
    public function getMonthAttribute(): int
    {
        return (int) substr($this->year_month, 5, 2);
    }
}
