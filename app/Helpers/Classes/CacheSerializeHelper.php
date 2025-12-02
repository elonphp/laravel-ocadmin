<?php

namespace App\Helpers\Classes;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/**
 * 自訂檔案快取 Helper
 *
 * 優點：
 * - 鍵名即檔案路徑，易於辨識和管理
 * - 可直接刪除指定快取或整個目錄
 * - 支援萬用字元刪除 (deletePattern)
 *
 * 使用方式：
 *   CacheSerializeHelper::remember('cache/menu/user_1', 3600, fn() => $data);
 *   CacheSerializeHelper::delete('cache/menu/user_1');
 *   CacheSerializeHelper::deletePattern('cache/menu/*');
 */
class CacheSerializeHelper
{
    /**
     * 預設快取秒數（7 天）
     */
    private const DEFAULT_TTL = 60 * 60 * 24 * 7;

    /**
     * Lock 等待秒數
     */
    private const LOCK_WAIT_SECONDS = 5;

    /**
     * Lock 持有秒數
     */
    private const LOCK_HOLD_SECONDS = 10;

    /**
     * 記住快取（帶 Lock 防止並發）
     *
     * @param string $key 快取鍵名（即檔案路徑）
     * @param int $seconds 過期秒數
     * @param callable $callback 產生資料的回呼函式
     * @return mixed
     */
    public static function remember(string $key, int $seconds, callable $callback): mixed
    {
        // 先嘗試讀取快取（不需要 lock）
        $data = self::get($key);
        if ($data !== null) {
            return $data;
        }

        // 使用 Laravel 的 atomic lock 防止並發重複執行 callback
        $lockKey = "file_cache_lock:{$key}";

        return Cache::lock($lockKey, self::LOCK_HOLD_SECONDS)
            ->block(self::LOCK_WAIT_SECONDS, function () use ($key, $seconds, $callback) {
                // Double-check：取得 lock 後再次確認快取是否已被其他進程建立
                $data = self::get($key);
                if ($data !== null) {
                    return $data;
                }

                // 執行 callback 並儲存
                $data = $callback();
                self::put($key, $data, $seconds);

                return $data;
            });
    }

    /**
     * 儲存快取
     *
     * @param string $key 快取鍵名
     * @param mixed $data 要儲存的資料
     * @param int|null $seconds 過期秒數（null 使用預設值）
     * @return bool
     */
    public static function put(string $key, mixed $data, ?int $seconds = null): bool
    {
        $seconds = $seconds ?? self::DEFAULT_TTL;

        $payload = [
            '_expires_at' => time() + $seconds,
            '_created_at' => time(),
            'data' => $data,
        ];

        Storage::put($key, serialize($payload));

        return true;
    }

    /**
     * 取得快取
     *
     * @param string $key 快取鍵名
     * @param mixed $default 預設值
     * @return mixed
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        if (!Storage::exists($key)) {
            return $default;
        }

        try {
            $payload = unserialize(Storage::get($key));

            // 檢查是否過期
            if (!empty($payload['_expires_at']) && $payload['_expires_at'] >= time()) {
                return $payload['data'];
            }

            // 已過期，刪除檔案
            Storage::delete($key);

            return $default;
        } catch (\Exception $e) {
            // 反序列化失敗，刪除損壞的快取檔案
            Storage::delete($key);

            return $default;
        }
    }

    /**
     * 檢查快取是否存在且未過期
     *
     * @param string $key 快取鍵名
     * @return bool
     */
    public static function has(string $key): bool
    {
        return self::get($key) !== null;
    }

    /**
     * 刪除指定快取
     *
     * @param string $key 快取鍵名
     * @return bool
     */
    public static function delete(string $key): bool
    {
        if (Storage::exists($key)) {
            return Storage::delete($key);
        }

        return true;
    }

    /**
     * 刪除符合模式的快取（支援萬用字元）
     *
     * @param string $pattern 模式，例如 'cache/menu/*'
     * @return int 刪除的檔案數量
     */
    public static function deletePattern(string $pattern): int
    {
        $files = Storage::files(dirname($pattern));
        $basename = basename($pattern);
        $count = 0;

        foreach ($files as $file) {
            if ($basename === '*' || fnmatch($basename, basename($file))) {
                Storage::delete($file);
                $count++;
            }
        }

        return $count;
    }

    /**
     * 刪除整個目錄的快取
     *
     * @param string $directory 目錄路徑
     * @return bool
     */
    public static function deleteDirectory(string $directory): bool
    {
        return Storage::deleteDirectory($directory);
    }

    /**
     * 取得快取資訊（用於除錯）
     *
     * @param string $key 快取鍵名
     * @return array|null
     */
    public static function info(string $key): ?array
    {
        if (!Storage::exists($key)) {
            return null;
        }

        try {
            $payload = unserialize(Storage::get($key));

            return [
                'key' => $key,
                'exists' => true,
                'expired' => ($payload['_expires_at'] ?? 0) < time(),
                'expires_at' => date('Y-m-d H:i:s', $payload['_expires_at'] ?? 0),
                'created_at' => date('Y-m-d H:i:s', $payload['_created_at'] ?? 0),
                'ttl_remaining' => max(0, ($payload['_expires_at'] ?? 0) - time()),
                'size' => Storage::size($key),
            ];
        } catch (\Exception $e) {
            return [
                'key' => $key,
                'exists' => true,
                'error' => $e->getMessage(),
            ];
        }
    }

    // ===== 相容舊版方法（已棄用）=====

    /**
     * @deprecated 使用 put() 替代
     */
    public static function saveDataToStorage(string $path, mixed $data, ?int $seconds = null): bool
    {
        return self::put($path, $data, $seconds);
    }

    /**
     * @deprecated 使用 get() 替代
     */
    public static function getDataFromStorage(string $path): mixed
    {
        return self::get($path);
    }
}
