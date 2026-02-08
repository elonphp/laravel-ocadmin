<?php

namespace App\Models\Hrm;

use Illuminate\Database\Eloquent\Model;

class CalendarDay extends Model
{
    protected $table = 'hrm_calendar_days';

    protected $fillable = [
        'date',
        'day_type',
        'is_workday',
        'name',
        'description',
        'color',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'is_workday' => 'boolean',
        ];
    }

    /**
     * 檢查是否為工作日
     */
    public function isWorkday(): bool
    {
        return $this->is_workday;
    }

    /**
     * 檢查是否為週末
     */
    public function isWeekend(): bool
    {
        return $this->day_type === 'weekend';
    }

    /**
     * 檢查是否為假日
     */
    public function isHoliday(): bool
    {
        return in_array($this->day_type, ['holiday', 'company_holiday']);
    }

    /**
     * 檢查是否為補班日
     */
    public function isMakeupWorkday(): bool
    {
        return $this->day_type === 'makeup_workday';
    }

    /**
     * 取得日期類型的顯示名稱
     */
    public function getDayTypeNameAttribute(): string
    {
        return match ($this->day_type) {
            'workday' => '工作日',
            'weekend' => '週末',
            'holiday' => '國定假日',
            'company_holiday' => '公司假日',
            'makeup_workday' => '補班日',
            'typhoon_day' => '颱風假',
            default => '未知',
        };
    }
}
