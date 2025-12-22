<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use ZipArchive;

/**
 * 歸檔日誌命令
 *
 * 功能：
 * 1. 將上個月的資料庫日誌匯出為每日一個 JSON Lines 檔案
 * 2. 壓縮成一個 ZIP 檔案
 * 3. 刪除暫存的每日檔案
 */
class ArchiveLogsCommand extends Command
{
    protected $signature = 'logs:archive {--month= : 指定月份 YYYY-MM，預設為上個月}';

    protected $description = '歸檔日誌：將資料庫日誌匯出為每日檔案並壓縮';

    protected string $logTable = 'logs';
    protected string $archivedDir;
    protected string $connection = 'sysdata';

    public function __construct()
    {
        parent::__construct();
        $this->archivedDir = storage_path('logs/archived');
    }

    public function handle(): int
    {
        $monthOption = $this->option('month');

        if ($monthOption) {
            try {
                $targetMonth = Carbon::createFromFormat('Y-m', $monthOption);
            } catch (\Exception $e) {
                $this->error('月份格式錯誤，應為 YYYY-MM');
                return Command::FAILURE;
            }
        } else {
            $targetMonth = Carbon::now()->subMonth();
        }

        $monthStr = $targetMonth->format('Y-m');
        $startDate = $targetMonth->copy()->startOfMonth();
        $endDate = $targetMonth->copy()->endOfMonth();

        $this->info("開始歸檔 {$monthStr} 的日誌...");

        // 確保歸檔目錄存在
        if (!File::exists($this->archivedDir)) {
            File::makeDirectory($this->archivedDir, 0755, true);
            $this->line("  建立歸檔目錄: {$this->archivedDir}");
        }

        // 建立暫存目錄
        $tempDir = storage_path('logs/temp_' . $monthStr);
        if (!File::exists($tempDir)) {
            File::makeDirectory($tempDir, 0755, true);
        }

        $exportedFiles = [];
        $totalLogs = 0;
        $currentDate = $startDate->copy();

        // 逐日匯出
        while ($currentDate->lte($endDate)) {
            $dateStr = $currentDate->format('Y-m-d');
            $logs = $this->getLogsByDate($dateStr);
            $count = count($logs);

            if ($count > 0) {
                $filename = "logs_{$dateStr}.txt";
                $filepath = $tempDir . '/' . $filename;

                $content = '';
                foreach ($logs as $log) {
                    $content .= json_encode((array)$log, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;
                }

                File::put($filepath, $content);
                $exportedFiles[] = $filename;
                $totalLogs += $count;

                $this->line("  匯出: {$filename} ({$count} 筆)");
            }

            $currentDate->addDay();
        }

        if (empty($exportedFiles)) {
            // 清理暫存目錄
            File::deleteDirectory($tempDir);
            $this->warn("沒有找到 {$monthStr} 的日誌記錄");
            return Command::SUCCESS;
        }

        // 建立 ZIP 壓縮檔
        $zipFilename = "logs_{$monthStr}.zip";
        $zipPath = $this->archivedDir . '/' . $zipFilename;

        // 如果已存在則先刪除
        if (File::exists($zipPath)) {
            File::delete($zipPath);
            $this->line("  刪除舊的壓縮檔: {$zipFilename}");
        }

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE) !== true) {
            File::deleteDirectory($tempDir);
            $this->error('無法建立壓縮檔');
            return Command::FAILURE;
        }

        foreach ($exportedFiles as $file) {
            $zip->addFile($tempDir . '/' . $file, $file);
        }

        $zip->close();

        // 刪除暫存目錄
        File::deleteDirectory($tempDir);

        $this->info("歸檔完成:");
        $this->line("  壓縮檔: {$zipFilename}");
        $this->line("  共 " . count($exportedFiles) . " 天，{$totalLogs} 筆日誌");

        return Command::SUCCESS;
    }

    /**
     * 取得指定日期的資料庫日誌
     */
    protected function getLogsByDate(string $date): array
    {
        return DB::connection($this->connection)
            ->table($this->logTable)
            ->whereDate('created_at', $date)
            ->orderBy('created_at', 'asc')
            ->get()
            ->toArray();
    }
}
