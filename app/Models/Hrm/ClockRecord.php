<?php

namespace App\Models\Hrm;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClockRecord extends Model
{
    use SoftDeletes;

    protected $table = 'hrm_clock_records';

    protected $fillable = [
        'employee_id',
        'clocked_at',
        'clock_type',
        'clock_method',
        'device_id',
        'device_name',
        'ip_address',
        'status',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'clocked_at' => 'datetime',
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
     * 建立人（手動補登時）
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * 檢查是否為打卡進
     */
    public function isClockIn(): bool
    {
        return $this->clock_type === 'in';
    }

    /**
     * 檢查是否為打卡出
     */
    public function isClockOut(): bool
    {
        return $this->clock_type === 'out';
    }

    /**
     * 檢查是否為有效記錄
     */
    public function isValid(): bool
    {
        return $this->status === 'valid';
    }

    /**
     * 檢查是否為手動補登
     */
    public function isManual(): bool
    {
        return $this->clock_method === 'manual';
    }

    /**
     * 取得打卡類型顯示名稱
     */
    public function getClockTypeNameAttribute(): string
    {
        return match ($this->clock_type) {
            'in' => '進',
            'out' => '出',
            default => '未知',
        };
    }

    /**
     * 取得打卡方式顯示名稱
     */
    public function getClockMethodNameAttribute(): string
    {
        return match ($this->clock_method) {
            'device' => '打卡機',
            'web' => '網頁打卡',
            'app' => 'APP打卡',
            'manual' => '手動補登',
            'import' => '批次匯入',
            default => '未知',
        };
    }

    /**
     * 取得狀態顯示名稱
     */
    public function getStatusNameAttribute(): string
    {
        return match ($this->status) {
            'valid' => '有效',
            'invalid' => '無效',
            'pending' => '待審核',
            default => '未知',
        };
    }
}
