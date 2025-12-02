<?php

namespace App\Repositories;

use App\Models\System\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * 資料庫日誌存儲庫
 *
 * 功能：
 * - 將日誌寫入資料庫（sysdata.logs 表）
 * - 支援 SQL 查詢、篩選、統計
 * - 提供清理舊日誌的功能
 */
class LogDatabaseRepository
{
    /**
     * 記錄日誌（通用方法）
     */
    public function log(array $params): bool
    {
        try {
            Log::create([
                'request_trace_id' => $params['request_trace_id'] ?? app('request_trace_id'),
                'area' => config('app.env'),
                'url' => $params['url'] ?? '',
                'method' => $params['method'] ?? '',
                'data' => $params['data'] ?? [],
                'status' => $params['status'] ?? '',
                'note' => $params['note'] ?? '',
                'client_ip' => $this->getClientIp(),
                'api_ip' => request()->ip(),
            ]);

            return true;
        } catch (\Exception $e) {
            $this->logError($e);
            return false;
        }
    }

    /**
     * 記錄請求日誌
     */
    public function logRequest($note = ''): bool
    {
        try {
            // 讀取請求資料
            if (request()->isJson()) {
                $json = json_decode(request()->getContent(), true);
                $data = $json ?? [];
            } else {
                $data = request()->all();
            }

            // 過濾敏感資料（密碼等）
            $data = $this->filterSensitiveData($data);

            Log::create([
                'request_trace_id' => app('request_trace_id'),
                'area' => config('app.env'),
                'url' => request()->fullUrl() ?? '',
                'method' => request()->method() ?? '',
                'data' => $data,
                'status' => '',
                'note' => is_array($note) ? json_encode($note, JSON_UNESCAPED_UNICODE) : $note,
                'client_ip' => $this->getClientIp(),
                'api_ip' => request()->ip(),
            ]);

            return true;
        } catch (\Exception $e) {
            $this->logError($e);
            return false;
        }
    }

    /**
     * 記錄錯誤日誌（在請求之後）
     */
    public function logErrorAfterRequest(array $params): bool
    {
        try {
            Log::create([
                'request_trace_id' => app('request_trace_id'),
                'area' => config('app.env'),
                'url' => '',
                'method' => '',
                'data' => $params['data'] ?? [],
                'status' => $params['status'] ?? 'error',
                'note' => $params['note'] ?? '',
                'client_ip' => '',
                'api_ip' => '',
            ]);

            return true;
        } catch (\Exception $e) {
            $this->logError($e);
            return false;
        }
    }

    /**
     * 取得客戶端 IP
     */
    protected function getClientIp(): string
    {
        if (request()->hasHeader('X-CLIENT-IPV4')) {
            return request()->header('X-CLIENT-IPV4');
        }

        return request()->ip() ?? '';
    }

    /**
     * 過濾敏感資料
     */
    protected function filterSensitiveData(array $data): array
    {
        $sensitiveKeys = ['password', 'password_confirmation', 'token', 'secret', 'api_key'];

        foreach ($sensitiveKeys as $key) {
            if (isset($data[$key])) {
                $data[$key] = '***FILTERED***';
            }
        }

        return $data;
    }

    /**
     * 記錄錯誤到 error_log（當資料庫寫入失敗時）
     */
    protected function logError(\Exception $e): void
    {
        try {
            error_log('LogDatabaseRepository: 寫入日誌失敗 - ' . $e->getMessage());
        } catch (\Exception $innerException) {
            // 完全失敗，靜默處理
        }
    }

    /**
     * 讀取指定日期的日誌
     */
    public function readLogsByDate(string $date, int $limit = 0): array
    {
        try {
            $query = Log::whereDate('created_at', $date)
                ->orderBy('created_at', 'desc');

            if ($limit > 0) {
                $query->limit($limit);
            }

            $logs = $query->get();

            return [
                'success' => true,
                'message' => "成功讀取 {$date} 的日誌",
                'logs' => $logs->toArray(),
                'total' => $logs->count(),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => '讀取日誌失敗：' . $e->getMessage(),
                'logs' => [],
            ];
        }
    }

    /**
     * 查詢日誌（支援多條件篩選）
     */
    public function query(array $filters = [], int $perPage = 50): array
    {
        try {
            $query = Log::query()->orderBy('created_at', 'desc');

            // 日期範圍
            if (!empty($filters['date_from'])) {
                $query->where('created_at', '>=', $filters['date_from']);
            }
            if (!empty($filters['date_to'])) {
                $query->where('created_at', '<=', $filters['date_to'] . ' 23:59:59');
            }

            // 狀態篩選
            if (!empty($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            // 請求方法
            if (!empty($filters['method'])) {
                $query->where('method', $filters['method']);
            }

            // URL 關鍵字
            if (!empty($filters['url'])) {
                $query->where('url', 'like', '%' . $filters['url'] . '%');
            }

            // request_trace_id
            if (!empty($filters['request_trace_id'])) {
                $query->where('request_trace_id', $filters['request_trace_id']);
            }

            // IP 篩選
            if (!empty($filters['client_ip'])) {
                $query->where('client_ip', $filters['client_ip']);
            }

            $paginator = $query->paginate($perPage);

            return [
                'success' => true,
                'logs' => $paginator->items(),
                'total' => $paginator->total(),
                'per_page' => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => '查詢日誌失敗：' . $e->getMessage(),
                'logs' => [],
            ];
        }
    }

    /**
     * 統計日誌數量
     */
    public function getStats(string $dateFrom = null, string $dateTo = null): array
    {
        try {
            $query = Log::query();

            if ($dateFrom) {
                $query->where('created_at', '>=', $dateFrom);
            }
            if ($dateTo) {
                $query->where('created_at', '<=', $dateTo . ' 23:59:59');
            }

            $total = $query->count();

            // 按狀態統計
            $byStatus = (clone $query)
                ->selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();

            // 按方法統計
            $byMethod = (clone $query)
                ->selectRaw('method, COUNT(*) as count')
                ->groupBy('method')
                ->pluck('count', 'method')
                ->toArray();

            return [
                'success' => true,
                'total' => $total,
                'by_status' => $byStatus,
                'by_method' => $byMethod,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => '統計失敗：' . $e->getMessage(),
            ];
        }
    }

    /**
     * 清理舊日誌（保留指定天數）
     */
    public function cleanOldLogs(int $keepDays = 90): array
    {
        try {
            $cutoffDate = Carbon::now()->subDays($keepDays)->startOfDay();

            $deleted = Log::where('created_at', '<', $cutoffDate)->delete();

            return [
                'success' => true,
                'message' => "成功刪除 {$deleted} 筆舊日誌（{$keepDays} 天前）",
                'deleted_count' => $deleted,
                'cutoff_date' => $cutoffDate->toDateString(),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => '清理日誌失敗：' . $e->getMessage(),
            ];
        }
    }

    /**
     * 列出日誌統計（按日期）
     */
    public function listLogsByDate(int $days = 30): array
    {
        try {
            $startDate = Carbon::now()->subDays($days)->startOfDay();

            $stats = Log::where('created_at', '>=', $startDate)
                ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->groupBy('date')
                ->orderBy('date', 'desc')
                ->get()
                ->toArray();

            return [
                'success' => true,
                'stats' => $stats,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => '統計失敗：' . $e->getMessage(),
            ];
        }
    }
}
