<?php

namespace App\Portals\Hrm\Console\Commands;

use App\Portals\Hrm\Modules\Calendar\CalendarDayService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateCalendarDaysCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hrm:generate-calendar
                            {--days= : 產生未來幾天的行事曆（1-365），預設從今天開始}
                            {--yearmonth= : 產生指定月份的行事曆（格式：YYYYMM，例如：202603）}
                            {--from= : 開始日期（YYYY-MM-DD），預設為今天，與 --days 搭配使用}
                            {--weekends=0,6 : 週末日期（0=週日, 6=週六）}
                            {--fresh : 強制覆蓋已存在的日期記錄}
                            {--source= : 假日資料來源（例如：ruyut-TaiwanCalendar）}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '批次產生行事曆工作日記錄';

    /**
     * 範例：
     * php artisan hrm:generate-calendar --from=2026-01-01 --days=365 --source=ruyut-TaiwanCalendar --fresh
     * php artisan hrm:generate-calendar --yearmonth=202603 --source=ruyut-TaiwanCalendar
     * php artisan hrm:generate-calendar --days=90';
     */

    /**
     * Execute the console command.
     */
    public function handle(CalendarDayService $calendarService): int
    {
        $this->info('🚀 開始產生行事曆記錄...');

        try {
            // 解析參數
            [$fromDate, $toDate] = $this->parseDateRange();
            $weekends = $this->parseWeekends();

            $this->line("📅 開始日期: {$fromDate->format('Y-m-d')}");
            $this->line("📅 結束日期: {$toDate->format('Y-m-d')}");
            $this->line("🏖️  週末設定: " . implode(', ', $weekends));
            $this->newLine();

            // 執行批次建立
            $fresh = $this->option('fresh');
            $createdCount = $calendarService->batchCreateWorkdays(
                $fromDate,
                $toDate,
                $weekends,
                $fresh
            );

            if ($fresh) {
                $this->warn('⚠️  使用 --fresh 模式：已覆蓋現有記錄');
            }

            $this->newLine();
            $this->info("✅ 成功！共建立 {$createdCount} 筆行事曆記錄");

            // 處理假日資料來源
            if ($source = $this->option('source')) {
                $this->newLine();
                $this->info("🌍 開始匯入假日資料（來源：{$source}）...");

                $holidayCount = $this->importFromSource($source, $fromDate, $toDate, $calendarService);

                $this->info("✅ 成功！共匯入 {$holidayCount} 筆假日記錄");
            }

            // 顯示統計
            $this->displayStatistics($fromDate, $toDate);

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error("❌ 錯誤：{$e->getMessage()}");
            return self::FAILURE;
        }
    }

    /**
     * 解析日期範圍
     */
    protected function parseDateRange(): array
    {
        $yearmonth = $this->option('yearmonth');
        $days = $this->option('days');
        $from = $this->option('from');

        // 優先使用 --yearmonth
        if ($yearmonth) {
            return $this->parseYearMonth($yearmonth);
        }

        // 使用 --days（可搭配 --from）
        if ($days) {
            return $this->parseDays($days, $from);
        }

        // 預設：產生未來 30 天
        $fromDate = $from ? Carbon::parse($from) : Carbon::today();
        $toDate = $fromDate->copy()->addDays(30)->subDay();

        return [$fromDate, $toDate];
    }

    /**
     * 解析年月參數（YYYYMM）
     */
    protected function parseYearMonth(string $yearmonth): array
    {
        // 驗證格式
        if (!preg_match('/^\d{6}$/', $yearmonth)) {
            throw new \InvalidArgumentException('--yearmonth 格式錯誤，應為 6 位數字（例如：202603）');
        }

        $year = (int) substr($yearmonth, 0, 4);
        $month = (int) substr($yearmonth, 4, 2);

        // 驗證月份
        if ($month < 1 || $month > 12) {
            throw new \InvalidArgumentException('月份必須在 1-12 之間');
        }

        $fromDate = Carbon::create($year, $month, 1)->startOfMonth();
        $toDate = $fromDate->copy()->endOfMonth();

        return [$fromDate, $toDate];
    }

    /**
     * 解析天數參數
     */
    protected function parseDays(int $days, ?string $from): array
    {
        // 驗證天數範圍
        if ($days < 1 || $days > 365) {
            throw new \InvalidArgumentException('--days 必須在 1-365 之間');
        }

        $fromDate = $from ? Carbon::parse($from) : Carbon::today();
        $toDate = $fromDate->copy()->addDays($days)->subDay();

        return [$fromDate, $toDate];
    }

    /**
     * 解析週末參數
     */
    protected function parseWeekends(): array
    {
        $weekendsStr = $this->option('weekends');
        $weekends = array_map('intval', explode(',', $weekendsStr));

        // 驗證週末代碼
        foreach ($weekends as $day) {
            if ($day < 0 || $day > 6) {
                throw new \InvalidArgumentException('週末代碼必須在 0-6 之間（0=週日, 6=週六）');
            }
        }

        return $weekends;
    }

    /**
     * 從指定來源匯入假日資料
     *
     * @param string $source 資料來源名稱
     * @param Carbon $fromDate 開始日期
     * @param Carbon $toDate 結束日期
     * @param CalendarDayService $calendarService
     * @return int 匯入的假日數量
     */
    protected function importFromSource(
        string $source,
        Carbon $fromDate,
        Carbon $toDate,
        CalendarDayService $calendarService
    ): int {
        // 根據來源名稱呼叫對應的方法
        return match ($source) {
            'ruyut-TaiwanCalendar' => $this->importFromRuyutTaiwanCalendar($fromDate, $toDate, $calendarService),
            default => throw new \InvalidArgumentException("不支援的資料來源：{$source}"),
        };
    }

    /**
     * 從 ruyut/TaiwanCalendar 匯入台灣假日資料
     *
     * @param Carbon $fromDate
     * @param Carbon $toDate
     * @param CalendarDayService $calendarService
     * @return int
     */
    protected function importFromRuyutTaiwanCalendar(
        Carbon $fromDate,
        Carbon $toDate,
        CalendarDayService $calendarService
    ): int {
        $totalImported = 0;

        // 取得需要處理的年份範圍
        $years = range($fromDate->year, $toDate->year);

        foreach ($years as $year) {
            $this->line("  📥 正在處理 {$year} 年...");

            try {
                $count = $calendarService->importFromRuyutTaiwanCalendar($year);
                $totalImported += $count;
                $this->line("  ✓ {$year} 年：已匯入 {$count} 筆假日");
            } catch (\Exception $e) {
                $this->warn("  ⚠ {$year} 年：{$e->getMessage()}");
            }
        }

        return $totalImported;
    }

    /**
     * 顯示統計資訊
     */
    protected function displayStatistics(Carbon $fromDate, Carbon $toDate): void
    {
        $this->newLine();
        $this->line('📊 統計資訊：');

        $totalDays = $fromDate->diffInDays($toDate) + 1;
        $workdays = \App\Models\Hrm\CalendarDay::whereBetween('date', [$fromDate, $toDate])
            ->where('is_workday', true)
            ->count();
        $weekends = \App\Models\Hrm\CalendarDay::whereBetween('date', [$fromDate, $toDate])
            ->where('day_type', 'weekend')
            ->count();

        $this->table(
            ['項目', '數量'],
            [
                ['總天數', $totalDays],
                ['工作日', $workdays],
                ['週末', $weekends],
            ]
        );
    }
}
