<?php

namespace App\Portals\Hrm\Console\Commands;

use App\Portals\Hrm\Modules\MonthlySummary\MonthlySummaryService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CalculateMonthlySummaryCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hrm:calculate-monthly-summary {yearMonth? : 年月（YYYYMM），不帶參數預設本月}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '計算指定月份的出勤統計並更新到月報表';

    /**
     * Execute the console command.
     */
    public function handle(MonthlySummaryService $service): int
    {
        try {
            $yearMonth = $this->argument('yearMonth');

            // 不帶參數預設本月
            if (empty($yearMonth)) {
                $yearMonth = Carbon::now()->format('Ym');
                $this->info("未提供年月參數，使用本月：{$yearMonth}");
            }

            // 驗證格式
            if (!preg_match('/^\d{6}$/', $yearMonth)) {
                $this->error('請提供正確格式的年月（YYYYMM），例如：202602');
                return self::FAILURE;
            }

            $formattedYearMonth = $service->getValidYearMonth($yearMonth);
            $startDate = Carbon::parse($formattedYearMonth . '-01')->startOfMonth()->toDateString();
            $endDate = Carbon::parse($formattedYearMonth . '-01')->endOfMonth()->toDateString();

            $this->info("計算月份：{$formattedYearMonth}");
            $this->info("分析範圍：{$startDate} ~ {$endDate}");
            $this->newLine();

            $startTime = microtime(true);

            $count = $service->calculateAll($yearMonth);

            $elapsed = round(microtime(true) - $startTime, 2);

            $this->info("完成！共處理 {$count} 位員工，耗時 {$elapsed} 秒");

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("錯誤：{$e->getMessage()}");
            return self::FAILURE;
        }
    }
}
