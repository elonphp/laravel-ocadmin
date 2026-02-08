<?php

namespace App\Models\Hrm;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DailyAttendance extends Model
{
    use SoftDeletes;

    protected $table = 'hrm_daily_attendances';

    protected $fillable = [
        'employee_id',
        'work_date',
        'scheduled_start',
        'scheduled_end',
        'clocked_in',
        'clocked_out',
        'break_start',
        'break_end',
        'work_start',
        'work_end',
        'approved_clocked_in',
        'approved_clocked_out',
        'approved_break_start',
        'approved_break_end',
        'approved_work_start',
        'approved_work_end',
        'reviewed_by',
        'reviewed_at',
        'correction_reason',
        'corrected_by',
        'corrected_at',
        'scheduled_minutes',
        'work_minutes',
        'break_minutes',
        'overtime_minutes',
        'is_late',
        'late_minutes',
        'is_early_leave',
        'early_leave_minutes',
        'is_absent',
        'is_abnormal',
        'abnormal_reason',
        'status',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'work_date' => 'date',
            'scheduled_start' => 'datetime',
            'scheduled_end' => 'datetime',
            'clocked_in' => 'datetime',
            'clocked_out' => 'datetime',
            'break_start' => 'datetime',
            'break_end' => 'datetime',
            'work_start' => 'datetime',
            'work_end' => 'datetime',
            'approved_clocked_in' => 'datetime',
            'approved_clocked_out' => 'datetime',
            'approved_break_start' => 'datetime',
            'approved_break_end' => 'datetime',
            'approved_work_start' => 'datetime',
            'approved_work_end' => 'datetime',
            'reviewed_at' => 'datetime',
            'corrected_at' => 'datetime',
            'scheduled_minutes' => 'integer',
            'work_minutes' => 'integer',
            'break_minutes' => 'integer',
            'overtime_minutes' => 'integer',
            'is_late' => 'boolean',
            'late_minutes' => 'integer',
            'is_early_leave' => 'boolean',
            'early_leave_minutes' => 'integer',
            'is_absent' => 'boolean',
            'is_abnormal' => 'boolean',
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
     * 修正人
     */
    public function corrector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'corrected_by');
    }

    /**
     * 檢查是否已審核
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * 檢查是否待審核
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * 檢查是否已駁回
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * 檢查是否有異常
     */
    public function hasAbnormal(): bool
    {
        return $this->is_abnormal || $this->is_absent || $this->is_late || $this->is_early_leave;
    }

    /**
     * 檢查是否已修正
     */
    public function isCorrected(): bool
    {
        return !is_null($this->corrected_by);
    }

    /**
     * 取得有效的上班打卡時間（審核修正優先）
     */
    public function getEffectiveClockedInAttribute(): ?string
    {
        return $this->approved_clocked_in ?? $this->clocked_in;
    }

    /**
     * 取得有效的下班打卡時間（審核修正優先）
     */
    public function getEffectiveClockedOutAttribute(): ?string
    {
        return $this->approved_clocked_out ?? $this->clocked_out;
    }

    /**
     * 取得有效的工作開始時間（審核修正優先）
     */
    public function getEffectiveWorkStartAttribute(): ?string
    {
        return $this->approved_work_start ?? $this->work_start;
    }

    /**
     * 取得有效的工作結束時間（審核修正優先）
     */
    public function getEffectiveWorkEndAttribute(): ?string
    {
        return $this->approved_work_end ?? $this->work_end;
    }

    /**
     * 取得工時（小時）
     */
    public function getWorkHoursAttribute(): float
    {
        return round($this->work_minutes / 60, 2);
    }

    /**
     * 取得加班時數（小時）
     */
    public function getOvertimeHoursAttribute(): float
    {
        return round($this->overtime_minutes / 60, 2);
    }

    /**
     * 取得狀態顯示名稱
     */
    public function getStatusNameAttribute(): string
    {
        return match ($this->status) {
            'pending' => '待審核',
            'approved' => '已審核',
            'rejected' => '已駁回',
            default => '未知',
        };
    }
}
