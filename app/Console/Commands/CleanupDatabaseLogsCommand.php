<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * 清理資料庫日誌命令
 *
 * 功能：刪除超過指定月份的資料庫日誌記錄
 */
class CleanupDatabaseLogsCommand extends Command
{
    protected $signature = 'logs:cleanup {months=3 : 保留月數，預設 3 個月}';

    protected $description = '刪除超過指定月份的資料庫日誌記錄';

    protected string $logTable = 'logs';
    protected string $connection = 'sysdata';

    public function handle(): int
    {
        $months = (int) $this->argument('months');

        if ($months < 1) {
            $this->error('保留月數必須大於 0');
            return Command::FAILURE;
        }

        $cutoffDate = Carbon::now()->subMonths($months)->startOfDay();

        $this->info("開始清理資料庫日誌（刪除 {$cutoffDate->format('Y-m-d')} 之前的記錄）...");

        // 先計算要刪除的數量
        $count = DB::connection($this->connection)
            ->table($this->logTable)
            ->where('created_at', '<', $cutoffDate)
            ->count();

        if ($count === 0) {
            $this->info('沒有找到需要刪除的日誌記錄');
            return Command::SUCCESS;
        }

        $this->line("  找到 {$count} 筆需要刪除的記錄");

        // 分批刪除以避免鎖定過久
        $batchSize = 10000;
        $deleted = 0;

        while (true) {
            $affected = DB::connection($this->connection)
                ->table($this->logTable)
                ->where('created_at', '<', $cutoffDate)
                ->limit($batchSize)
                ->delete();

            if ($affected === 0) {
                break;
            }

            $deleted += $affected;
            $this->line("  已刪除 {$deleted} / {$count} 筆");
        }

        $this->info("清理完成：共刪除 {$deleted} 筆日誌記錄");

        return Command::SUCCESS;
    }
}
