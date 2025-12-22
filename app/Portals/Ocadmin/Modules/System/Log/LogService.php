<?php

namespace App\Portals\Ocadmin\Modules\System\Log;

use App\Repositories\LogFileRepository;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use ZipArchive;

class LogService
{
    protected string $logTable = 'logs';
    protected string $archivedDir;

    public function __construct(
        protected LogFileRepository $logFileRepository
    ) {
        $this->archivedDir = storage_path('logs/archived');
    }

    /**
     * 取得資料庫日誌（分頁）
     */
    public function getDatabaseLogs(array $filters = [], int $perPage = 50): LengthAwarePaginator
    {
        $query = DB::connection('sysdata')
            ->table($this->logTable)
            ->orderBy('created_at', 'desc');

        // 日期篩選
        if (!empty($filters['date'])) {
            $query->whereDate('created_at', $filters['date']);
        }

        // HTTP Method 篩選
        if (!empty($filters['method'])) {
            $query->where('method', $filters['method']);
        }

        // 狀態篩選
        if (!empty($filters['status'])) {
            if ($filters['status'] === 'empty') {
                $query->whereNull('status')->orWhere('status', '');
            } else {
                $query->where('status', $filters['status']);
            }
        }

        // 關鍵字搜尋
        if (!empty($filters['keyword'])) {
            $keyword = $filters['keyword'];
            $query->where(function ($q) use ($keyword) {
                $q->where('url', 'like', "%{$keyword}%")
                  ->orWhere('note', 'like', "%{$keyword}%")
                  ->orWhere('client_ip', 'like', "%{$keyword}%")
                  ->orWhere('request_trace_id', 'like', "%{$keyword}%");
            });
        }

        return $query->paginate($perPage);
    }

    /**
     * 取得資料庫日誌詳情
     */
    public function getDatabaseLog(int $id): ?object
    {
        return DB::connection('sysdata')
            ->table($this->logTable)
            ->find($id);
    }

    /**
     * 刪除超過指定月份的資料庫日誌
     */
    public function cleanupDatabaseLogs(int $months = 3): int
    {
        $cutoffDate = Carbon::now()->subMonths($months)->startOfDay();

        return DB::connection('sysdata')
            ->table($this->logTable)
            ->where('created_at', '<', $cutoffDate)
            ->delete();
    }

    /**
     * 取得壓縮檔列表
     */
    public function getArchivedFiles(): array
    {
        if (!File::exists($this->archivedDir)) {
            return [];
        }

        $files = File::files($this->archivedDir);
        $result = [];

        foreach ($files as $file) {
            if ($file->getExtension() === 'zip') {
                $filename = $file->getFilename();

                // 解析月份 logs_2024-06.zip
                preg_match('/logs_(\d{4}-\d{2})\.zip/', $filename, $matches);
                $month = $matches[1] ?? '';

                $result[] = [
                    'filename' => $filename,
                    'month' => $month,
                    'size' => $this->formatBytes($file->getSize()),
                    'size_bytes' => $file->getSize(),
                    'modified' => Carbon::createFromTimestamp($file->getMTime())->toDateTimeString(),
                ];
            }
        }

        // 按月份排序（新到舊）
        usort($result, fn($a, $b) => $b['month'] <=> $a['month']);

        return $result;
    }

    /**
     * 取得壓縮檔的完整路徑
     */
    public function getArchivedFilePath(string $filename): ?string
    {
        $path = $this->archivedDir . '/' . basename($filename);

        if (!File::exists($path) || pathinfo($path, PATHINFO_EXTENSION) !== 'zip') {
            return null;
        }

        return $path;
    }

    /**
     * 解壓並讀取壓縮檔內容
     */
    public function readArchivedFile(string $filename): array
    {
        $zipPath = $this->getArchivedFilePath($filename);

        if (!$zipPath) {
            return ['success' => false, 'message' => '找不到壓縮檔', 'files' => []];
        }

        $zip = new ZipArchive();
        if ($zip->open($zipPath) !== true) {
            return ['success' => false, 'message' => '無法開啟壓縮檔', 'files' => []];
        }

        $files = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $stat = $zip->statIndex($i);
            $files[] = [
                'name' => $stat['name'],
                'size' => $this->formatBytes($stat['size']),
                'size_bytes' => $stat['size'],
            ];
        }

        // 按檔名排序
        usort($files, fn($a, $b) => $a['name'] <=> $b['name']);

        $zip->close();

        return ['success' => true, 'files' => $files];
    }

    /**
     * 從壓縮檔讀取指定日期的日誌
     */
    public function readLogsFromArchive(string $zipFilename, string $logFilename): array
    {
        $zipPath = $this->getArchivedFilePath($zipFilename);

        if (!$zipPath) {
            return ['success' => false, 'message' => '找不到壓縮檔', 'logs' => []];
        }

        $zip = new ZipArchive();
        if ($zip->open($zipPath) !== true) {
            return ['success' => false, 'message' => '無法開啟壓縮檔', 'logs' => []];
        }

        $content = $zip->getFromName($logFilename);
        $zip->close();

        if ($content === false) {
            return ['success' => false, 'message' => '找不到日誌檔案', 'logs' => []];
        }

        $lines = explode("\n", trim($content));
        $logs = [];

        foreach ($lines as $line) {
            if (empty($line)) continue;
            $log = json_decode($line, true);
            if ($log) {
                $logs[] = $log;
            }
        }

        return ['success' => true, 'logs' => $logs];
    }

    /**
     * 歸檔上個月的日誌
     * 1. 將上個月的資料庫日誌匯出為每日一個檔案
     * 2. 壓縮成一個 ZIP 檔
     * 3. 刪除原始的每日檔案
     */
    public function archiveLastMonth(): array
    {
        $lastMonth = Carbon::now()->subMonth();
        $monthStr = $lastMonth->format('Y-m');
        $startDate = $lastMonth->copy()->startOfMonth();
        $endDate = $lastMonth->copy()->endOfMonth();

        // 確保歸檔目錄存在
        if (!File::exists($this->archivedDir)) {
            File::makeDirectory($this->archivedDir, 0755, true);
        }

        // 建立暫存目錄
        $tempDir = storage_path('logs/temp_' . $monthStr);
        if (!File::exists($tempDir)) {
            File::makeDirectory($tempDir, 0755, true);
        }

        $exportedFiles = [];
        $currentDate = $startDate->copy();

        // 逐日匯出
        while ($currentDate->lte($endDate)) {
            $dateStr = $currentDate->format('Y-m-d');
            $logs = $this->getLogsByDate($dateStr);

            if (count($logs) > 0) {
                $filename = "logs_{$dateStr}.txt";
                $filepath = $tempDir . '/' . $filename;

                $content = '';
                foreach ($logs as $log) {
                    $content .= json_encode((array)$log, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;
                }

                File::put($filepath, $content);
                $exportedFiles[] = $filename;
            }

            $currentDate->addDay();
        }

        if (empty($exportedFiles)) {
            // 清理暫存目錄
            File::deleteDirectory($tempDir);
            return [
                'success' => false,
                'message' => "沒有找到 {$monthStr} 的日誌記錄",
            ];
        }

        // 建立 ZIP 壓縮檔
        $zipFilename = "logs_{$monthStr}.zip";
        $zipPath = $this->archivedDir . '/' . $zipFilename;

        // 如果已存在則先刪除
        if (File::exists($zipPath)) {
            File::delete($zipPath);
        }

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE) !== true) {
            File::deleteDirectory($tempDir);
            return [
                'success' => false,
                'message' => '無法建立壓縮檔',
            ];
        }

        foreach ($exportedFiles as $file) {
            $zip->addFile($tempDir . '/' . $file, $file);
        }

        $zip->close();

        // 刪除暫存目錄
        File::deleteDirectory($tempDir);

        return [
            'success' => true,
            'message' => "成功歸檔 {$monthStr}，共 " . count($exportedFiles) . " 天的日誌",
            'zip_path' => $zipPath,
            'files_count' => count($exportedFiles),
        ];
    }

    /**
     * 取得指定日期的資料庫日誌
     */
    protected function getLogsByDate(string $date): array
    {
        return DB::connection('sysdata')
            ->table($this->logTable)
            ->whereDate('created_at', $date)
            ->orderBy('created_at', 'asc')
            ->get()
            ->toArray();
    }

    /**
     * 取得排程任務資訊
     */
    public function getSchedulerInfo(): array
    {
        return [
            [
                'name' => 'logs:archive',
                'description' => '歸檔上月日誌',
                'schedule' => '每月 1 日 03:00',
                'cron' => '0 3 1 * *',
                'last_run' => $this->getLastRunTime('logs:archive'),
            ],
            [
                'name' => 'logs:cleanup',
                'description' => '刪除超過三個月的資料庫記錄',
                'schedule' => '每月 1 日 04:00',
                'cron' => '0 4 1 * *',
                'last_run' => $this->getLastRunTime('logs:cleanup'),
            ],
            [
                'name' => 'app:delete-file-logs',
                'description' => '刪除超過 90 天的檔案日誌',
                'schedule' => '每日 02:00',
                'cron' => '0 2 * * *',
                'last_run' => $this->getLastRunTime('app:delete-file-logs'),
            ],
        ];
    }

    /**
     * 取得指令最後執行時間（從 cache 或設定檔取得）
     */
    protected function getLastRunTime(string $command): ?string
    {
        // 這裡可以從 cache 或資料庫取得最後執行時間
        // 目前先回傳 null
        return null;
    }

    /**
     * 格式化位元組大小
     */
    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
