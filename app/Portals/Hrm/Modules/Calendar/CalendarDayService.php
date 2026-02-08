<?php

namespace App\Portals\Hrm\Modules\Calendar;

use App\Models\Hrm\CalendarDay;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * 行事曆服務
 *
 * 處理行事曆相關業務邏輯
 */
class CalendarDayService
{
    /**
     * 建立單筆行事曆記錄
     *
     * @param array $data
     * @return CalendarDay
     */
    public function createCalendarDay(array $data): CalendarDay
    {
        return CalendarDay::create($data);
    }

    /**
     * 更新行事曆記錄
     *
     * @param CalendarDay $calendarDay
     * @param array $data
     * @return CalendarDay
     */
    public function updateCalendarDay(CalendarDay $calendarDay, array $data): CalendarDay
    {
        $calendarDay->update($data);
        return $calendarDay->fresh();
    }

    /**
     * 刪除行事曆記錄
     *
     * @param CalendarDay $calendarDay
     * @return bool
     */
    public function deleteCalendarDay(CalendarDay $calendarDay): bool
    {
        return $calendarDay->delete();
    }

    /**
     * 批次建立工作日記錄（依據週休規則）
     *
     * @param Carbon $startDate 開始日期
     * @param Carbon $endDate 結束日期
     * @param array $weekends 週末日期陣列（預設 [0, 6] = 週日、週六）
     * @return int 建立的記錄數
     */
    public function batchCreateWorkdays(
        Carbon $startDate,
        Carbon $endDate,
        array $weekends = [0, 6]
    ): int {
        $createdCount = 0;
        $current = $startDate->copy();

        DB::beginTransaction();
        try {
            while ($current->lte($endDate)) {
                // 檢查是否已存在
                $exists = CalendarDay::where('date', $current->format('Y-m-d'))->exists();

                if (!$exists) {
                    $isWeekend = in_array($current->dayOfWeek, $weekends);

                    CalendarDay::create([
                        'date' => $current->format('Y-m-d'),
                        'day_type' => $isWeekend ? 'weekend' : 'workday',
                        'is_workday' => !$isWeekend,
                    ]);

                    $createdCount++;
                }

                $current->addDay();
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return $createdCount;
    }

    /**
     * 批次匯入國定假日
     *
     * @param array $holidays [['date' => '2026-01-01', 'name' => '元旦'], ...]
     * @return int 更新的記錄數
     */
    public function importHolidays(array $holidays): int
    {
        $updatedCount = 0;

        DB::beginTransaction();
        try {
            foreach ($holidays as $holiday) {
                $date = $holiday['date'];
                $name = $holiday['name'] ?? null;

                // 查找或建立
                $calendarDay = CalendarDay::firstOrCreate(
                    ['date' => $date],
                    [
                        'day_type' => 'holiday',
                        'is_workday' => false,
                        'name' => $name,
                    ]
                );

                // 如果已存在，更新為假日
                if (!$calendarDay->wasRecentlyCreated) {
                    $calendarDay->update([
                        'day_type' => 'holiday',
                        'is_workday' => false,
                        'name' => $name,
                    ]);
                }

                $updatedCount++;
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return $updatedCount;
    }

    /**
     * 設定補班日
     *
     * @param string $date 日期（YYYY-MM-DD）
     * @param string|null $name 補班日名稱
     * @return CalendarDay
     */
    public function setMakeupWorkday(string $date, ?string $name = null): CalendarDay
    {
        $calendarDay = CalendarDay::firstOrCreate(['date' => $date]);

        $calendarDay->update([
            'day_type' => 'makeup_workday',
            'is_workday' => true,
            'name' => $name,
        ]);

        return $calendarDay->fresh();
    }

    /**
     * 取得指定月份的行事曆資料
     *
     * @param int $year 年份
     * @param int $month 月份
     * @return Collection
     */
    public function getCalendarByMonth(int $year, int $month): Collection
    {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        return CalendarDay::whereBetween('date', [$startDate, $endDate])
            ->orderBy('date')
            ->get();
    }

    /**
     * 取得指定年份的所有假日
     *
     * @param int $year 年份
     * @return Collection
     */
    public function getHolidaysByYear(int $year): Collection
    {
        $startDate = Carbon::create($year, 1, 1);
        $endDate = Carbon::create($year, 12, 31);

        return CalendarDay::whereBetween('date', [$startDate, $endDate])
            ->whereIn('day_type', ['holiday', 'company_holiday'])
            ->orderBy('date')
            ->get();
    }

    /**
     * 檢查指定日期是否為工作日
     *
     * @param string $date 日期（YYYY-MM-DD）
     * @return bool
     */
    public function isWorkday(string $date): bool
    {
        $calendarDay = CalendarDay::where('date', $date)->first();

        if (!$calendarDay) {
            // 如果不存在記錄，自動建立並判斷
            $carbon = Carbon::parse($date);
            $isWeekend = in_array($carbon->dayOfWeek, [0, 6]); // 週日、週六

            $calendarDay = CalendarDay::create([
                'date' => $date,
                'day_type' => $isWeekend ? 'weekend' : 'workday',
                'is_workday' => !$isWeekend,
            ]);
        }

        return $calendarDay->is_workday;
    }

    /**
     * 統計指定期間的工作日數量
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return int
     */
    public function countWorkdays(Carbon $startDate, Carbon $endDate): int
    {
        return CalendarDay::whereBetween('date', [$startDate, $endDate])
            ->where('is_workday', true)
            ->count();
    }
}
