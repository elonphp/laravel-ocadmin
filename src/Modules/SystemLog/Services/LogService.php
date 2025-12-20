<?php

namespace Elonphp\LaravelOcadminModules\Modules\SystemLog\Services;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class LogService
{
    protected string $logTable = 'system_logs';

    /**
     * Get database logs with filtering.
     */
    public function getDatabaseLogs(?string $level = null, ?string $search = null, int $perPage = 50): LengthAwarePaginator
    {
        $query = DB::table($this->logTable)
            ->orderBy('created_at', 'desc');

        if ($level) {
            $query->where('level', $level);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('message', 'like', "%{$search}%")
                  ->orWhere('context', 'like', "%{$search}%");
            });
        }

        return $query->paginate($perPage);
    }

    /**
     * Get available log levels.
     */
    public function getLevels(): array
    {
        return [
            'emergency' => 'Emergency',
            'alert' => 'Alert',
            'critical' => 'Critical',
            'error' => 'Error',
            'warning' => 'Warning',
            'notice' => 'Notice',
            'info' => 'Info',
            'debug' => 'Debug',
        ];
    }

    /**
     * Get log files from storage.
     */
    public function getLogFiles(): array
    {
        $logPath = storage_path('logs');
        $files = [];

        if (!File::exists($logPath)) {
            return $files;
        }

        foreach (File::files($logPath) as $file) {
            if ($file->getExtension() === 'log') {
                $files[] = [
                    'name' => $file->getFilename(),
                    'path' => $file->getPathname(),
                    'size' => $this->formatBytes($file->getSize()),
                    'modified' => date('Y-m-d H:i:s', $file->getMTime()),
                ];
            }
        }

        // Sort by modified date descending
        usort($files, fn($a, $b) => $b['modified'] <=> $a['modified']);

        return $files;
    }

    /**
     * Parse a log file.
     */
    public function parseLogFile(string $filename): array
    {
        $path = storage_path('logs/' . basename($filename));

        if (!File::exists($path)) {
            return [];
        }

        $content = File::get($path);
        $logs = [];

        // Parse Laravel log format
        $pattern = '/\[(\d{4}-\d{2}-\d{2}\s\d{2}:\d{2}:\d{2})\]\s+(\w+)\.(\w+):\s+(.+?)(?=\[\d{4}-\d{2}-\d{2}|$)/s';

        preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $logs[] = [
                'timestamp' => $match[1],
                'environment' => $match[2],
                'level' => $match[3],
                'message' => trim($match[4]),
            ];
        }

        return array_reverse($logs);
    }

    /**
     * Find a log entry by ID.
     */
    public function find(int $id): ?object
    {
        return DB::table($this->logTable)->find($id);
    }

    /**
     * Cleanup old logs.
     */
    public function cleanup(int $days): int
    {
        $date = now()->subDays($days);

        return DB::table($this->logTable)
            ->where('created_at', '<', $date)
            ->delete();
    }

    /**
     * Format bytes to human readable.
     */
    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}
