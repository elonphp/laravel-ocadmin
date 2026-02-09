<?php

namespace App\Portals\Hrm\Modules\Calendar;

use App\Models\Hrm\CalendarDay;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

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
     * @param bool $fresh 是否強制覆蓋已存在的記錄
     * @return int 建立的記錄數
     */
    public function batchCreateWorkdays(
        Carbon $startDate,
        Carbon $endDate,
        array $weekends = [0, 6],
        bool $fresh = false
    ): int {
        $createdCount = 0;
        $current = $startDate->copy();

        DB::beginTransaction();
        try {
            while ($current->lte($endDate)) {
                $dateStr = $current->format('Y-m-d');
                $isWeekend = in_array($current->dayOfWeek, $weekends);

                $data = [
                    'day_type' => $isWeekend ? 'weekend' : 'workday',
                    'is_workday' => !$isWeekend,
                ];

                if ($fresh) {
                    // 強制覆蓋模式：使用 updateOrCreate
                    CalendarDay::updateOrCreate(
                        ['date' => $dateStr],
                        $data
                    );
                    $createdCount++;
                } else {
                    // 一般模式：略過已存在的日期
                    $exists = CalendarDay::where('date', $dateStr)->exists();

                    if (!$exists) {
                        CalendarDay::create(array_merge(['date' => $dateStr], $data));
                        $createdCount++;
                    }
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
     * @param array $holidays [['date' => '2026-01-01', 'name' => '元旦', 'description' => '...'], ...]
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
                $description = $holiday['description'] ?? null;

                // 查找或建立
                $calendarDay = CalendarDay::firstOrCreate(
                    ['date' => $date],
                    [
                        'day_type' => 'holiday',
                        'is_workday' => false,
                        'name' => $name,
                        'description' => $description,
                    ]
                );

                // 如果已存在，更新為假日
                if (!$calendarDay->wasRecentlyCreated) {
                    $calendarDay->update([
                        'day_type' => 'holiday',
                        'is_workday' => false,
                        'name' => $name,
                        'description' => $description,
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

    /**
     * 從 ruyut/TaiwanCalendar 匯入指定年份的台灣假日資料
     *
     * @param int $year 年份
     * @return int 匯入的假日數量
     * @throws \Exception
     */
    public function importFromRuyutTaiwanCalendar(int $year): int
    {
        // 建構 CDN URL
        $url = "https://cdn.jsdelivr.net/gh/ruyut/TaiwanCalendar/data/{$year}.json";

        // 抓取 JSON 資料（開發環境暫時停用 SSL 驗證）
        $response = Http::withOptions([
            'verify' => false, // 停用 SSL 證書驗證（僅限開發環境）
        ])->timeout(10)->get($url);

        if (!$response->successful()) {
            throw new \Exception("無法從 {$url} 取得資料（HTTP {$response->status()}）");
        }

        $jsonData = $response->json();

        if (!is_array($jsonData)) {
            throw new \Exception('資料格式錯誤：非預期的 JSON 結構');
        }

        // 轉換資料格式
        $holidays = [];

        foreach ($jsonData as $item) {
            // 只處理標記為假日的項目
            if (!isset($item['isHoliday']) || !$item['isHoliday']) {
                continue;
            }

            // 跳過沒有 description 的項目
            if (empty($item['description'])) {
                continue;
            }

            // 轉換日期格式：20260101 -> 2026-01-01
            $dateStr = $item['date'];
            $date = Carbon::createFromFormat('Ymd', $dateStr)->format('Y-m-d');

            $holidays[] = [
                'date' => $date,
                'name' => $item['description'],
            ];
        }

        // 使用既有的 importHolidays 方法匯入
        return $this->importHolidays($holidays);
    }
}
