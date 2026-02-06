<?php

namespace App\Repositories;

use App\Models\System\RequestLog;
use Illuminate\Support\Facades\Log;

class LogDatabaseRepository
{
    /**
     * 敏感欄位名稱（會被替換為 ***FILTERED***）
     */
    protected static array $sensitiveFields = [
        'password',
        'password_confirmation',
        'token',
        'secret',
        'api_key',
    ];

    /**
     * Base64 圖片 magic bytes
     */
    protected static array $base64ImagePrefixes = [
        '/9j/'    => 'jpeg',  // JPEG
        'iVBOR'   => 'png',   // PNG
        'R0lGOD'  => 'gif',   // GIF
        'UklGR'   => 'webp',  // WebP
        'Qk0'     => 'bmp',   // BMP
    ];

    /**
     * 單一欄位最大長度（bytes）
     */
    protected static int $maxFieldSize = 1048576; // 1MB

    /**
     * Middleware 自動呼叫：記錄 HTTP 請求
     */
    public static function logRequest(?int $statusCode = null, ?string $note = null): ?RequestLog
    {
        $request = request();

        $data = [
            'request_trace_id' => app('request_id'),
            'user_id'          => auth()->id(),
            'app_name'         => config('app.name'),
            'portal'           => self::detectPortal($request->path()),
            'area'             => config('app.env'),
            'url'              => $request->fullUrl(),
            'method'           => $request->method(),
            'status_code'      => $statusCode,
            'request_data'     => self::filterRequestData($request->all()),
            'response_data'    => null,
            'status'           => self::resolveStatus($statusCode),
            'note'             => $note,
            'client_ip'        => $request->ip(),
            'api_ip'           => $request->server('SERVER_ADDR'),
        ];

        return self::log($data);
    }

    /**
     * 通用手動記錄
     */
    public static function log(array $params): ?RequestLog
    {
        try {
            return RequestLog::create($params);
        } catch (\Throwable $e) {
            Log::error('LogDatabaseRepository::log failed', [
                'message' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * 根據 HTTP 狀態碼判斷 status 字串
     */
    protected static function resolveStatus(?int $statusCode): string
    {
        if ($statusCode === null) {
            return 'success';
        }

        if ($statusCode >= 200 && $statusCode < 400) {
            return 'success';
        }

        if ($statusCode >= 400 && $statusCode < 500) {
            return 'warning';
        }

        return 'error';
    }

    /**
     * 偵測 Portal 來源
     */
    protected static function detectPortal(string $path): ?string
    {
        if (preg_match('#^[a-z-]+/admin(/|$)#', $path)) {
            return 'ocadmin';
        }

        if (preg_match('#^[a-z-]+/ess(/|$)#', $path)) {
            return 'ess';
        }

        return null;
    }

    /**
     * 過濾請求資料（敏感資料、Base64 圖片、大型資料）
     */
    protected static function filterRequestData(array $data): ?array
    {
        if (empty($data)) {
            return null;
        }

        $data = self::filterSensitiveFields($data);
        $data = self::filterBase64Images($data);
        $data = self::truncateLargeFields($data);

        return $data;
    }

    /**
     * 過濾敏感欄位
     */
    protected static function filterSensitiveFields(array $data): array
    {
        foreach ($data as $key => &$value) {
            if (is_array($value)) {
                $value = self::filterSensitiveFields($value);
                continue;
            }

            if (in_array(strtolower($key), self::$sensitiveFields, true)) {
                $value = '***FILTERED***';
            }
        }

        return $data;
    }

    /**
     * 偵測並替換 Base64 圖片
     */
    protected static function filterBase64Images(array $data): array
    {
        foreach ($data as $key => &$value) {
            if (is_array($value)) {
                $value = self::filterBase64Images($value);
                continue;
            }

            if (!is_string($value) || strlen($value) < 100) {
                continue;
            }

            // Data URI 格式：data:image/xxx;base64,...
            if (preg_match('#^data:image/(\w+);base64,(.+)$#s', $value, $matches)) {
                $type = $matches[1];
                $size = self::formatBytes(strlen(base64_decode($matches[2], true) ?: ''));
                $value = "***BASE64_IMAGE:{$type},{$size}***";
                continue;
            }

            // 純 Base64（檢測 magic bytes）
            foreach (self::$base64ImagePrefixes as $prefix => $type) {
                if (str_starts_with($value, $prefix)) {
                    $decoded = base64_decode($value, true);
                    if ($decoded !== false) {
                        $size = self::formatBytes(strlen($decoded));
                        $value = "***BASE64_IMAGE:{$type},{$size}***";
                    }
                    break;
                }
            }
        }

        return $data;
    }

    /**
     * 截斷超過 1MB 的欄位
     */
    protected static function truncateLargeFields(array $data): array
    {
        foreach ($data as $key => &$value) {
            if (is_array($value)) {
                $value = self::truncateLargeFields($value);
                continue;
            }

            if (is_string($value) && strlen($value) > self::$maxFieldSize) {
                $size = self::formatBytes(strlen($value));
                $value = "***TRUNCATED:{$size}***";
            }
        }

        return $data;
    }

    /**
     * 格式化位元組為人類可讀格式
     */
    protected static function formatBytes(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 1) . 'MB';
        }

        if ($bytes >= 1024) {
            return round($bytes / 1024, 1) . 'KB';
        }

        return $bytes . 'B';
    }
}
