<?php

namespace App\Console\Commands;

use App\Models\System\RequestLog;
use App\Repositories\LogDatabaseRepository;
use Illuminate\Console\Command;

class PurgeRequestLogsCommand extends Command
{
    protected $signature = 'request-logs:purge
                            {--months=6 : 保留月數}
                            {--dry-run : 預覽刪除數量，不實際執行}';

    protected $description = '清理超過指定月數的 request_logs 記錄';

    public function handle(): int
    {
        $months = (int) $this->option('months');
        $dryRun = $this->option('dry-run');
        $cutoff = now()->subMonths($months);

        $query = RequestLog::where('created_at', '<', $cutoff);
        $count = $query->count();

        if ($count === 0) {
            $this->info("沒有超過 {$months} 個月的記錄。");
            LogDatabaseRepository::logSchedule('request-logs:purge', 'success', "無需清理（cutoff: {$cutoff}）");
            return self::SUCCESS;
        }

        if ($dryRun) {
            $this->info("[Dry Run] 將刪除 {$count} 筆記錄（{$cutoff} 之前）");
            return self::SUCCESS;
        }

        $deleted = $query->delete();

        $this->info("已刪除 {$deleted} 筆記錄（{$cutoff} 之前）");
        LogDatabaseRepository::logSchedule('request-logs:purge', 'success', "已刪除 {$deleted} 筆（cutoff: {$cutoff}）");

        return self::SUCCESS;
    }
}
