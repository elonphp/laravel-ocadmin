<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * 刪除檔案日誌命令
 *
 * 說明：此命令用於刪除檔案系統中的日誌檔案（storage/logs/app/logs_*.txt）
 * 如果專案使用資料庫日誌，則不會有檔案日誌，此命令會自動跳過。
 */
class DeleteFileLogsCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'app:delete-file-logs {days=90 : 保留天數，預設 90 天}';

    /**
     * The console command description.
     */
    protected $description = '刪除超過指定天數的檔案日誌（storage/logs/app/logs_*.txt）';

    /**
     * 日誌目錄
     */
    protected string $logDir;

    public function __construct()
    {
        parent::__construct();
        $this->logDir = storage_path('logs/app');
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = (int) $this->argument('days');

        $this->info("開始清理檔案日誌（保留 {$days} 天內的日誌）...");

        // 檢查日誌目錄是否存在
        if (!File::exists($this->logDir)) {
            $this->info('日誌目錄不存在，可能使用資料庫日誌，跳過清理。');
            return Command::SUCCESS;
        }

        // 取得所有日誌檔案
        $files = File::files($this->logDir);

        if (empty($files)) {
            $this->info('沒有找到任何日誌檔案，跳過清理。');
            return Command::SUCCESS;
        }

        $cutoffDate = Carbon::now()->subDays($days)->startOfDay();
        $deletedCount = 0;
        $skippedCount = 0;

        foreach ($files as $file) {
            $filename = $file->getFilename();

            // 只處理 logs_yyyy-mm-dd.txt 格式的檔案
            if (!preg_match('/^logs_(\d{4}-\d{2}-\d{2})\.txt$/', $filename, $matches)) {
                // 跳過壓縮檔和其他格式
                $skippedCount++;
                continue;
            }

            $fileDate = Carbon::createFromFormat('Y-m-d', $matches[1]);

            if ($fileDate && $fileDate->lt($cutoffDate)) {
                try {
                    File::delete($file->getPathname());
                    $deletedCount++;
                    $this->line("  刪除: {$filename}");
                } catch (\Exception $e) {
                    $this->error("  刪除失敗: {$filename} - {$e->getMessage()}");
                }
            }
        }

        $this->info("清理完成：刪除 {$deletedCount} 個檔案，跳過 {$skippedCount} 個檔案。");

        return Command::SUCCESS;
    }
}
