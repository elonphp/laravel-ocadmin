<?php

if (! function_exists('setting')) {
    /**
     * 從 settings 資料表讀取設定值（帶快取）。
     * 回傳經 SettingType 解析後的值（int / bool / array 等）。
     */
    function setting(string $code, mixed $default = null): mixed
    {
        static $cache = [];

        if (array_key_exists($code, $cache)) {
            return $cache[$code] ?? $default;
        }

        $row = \App\Models\System\Setting::where('code', $code)->first();

        $cache[$code] = $row?->parsed_value;

        return $cache[$code] ?? $default;
    }
}

/**
 * 產生帶有檔案修改時間版本號的 asset URL。
 * 檔案一改，時間戳就變，瀏覽器視為新 URL，自動下載新版。
 */
function versioned_asset(string $path): string
{
    $fullPath = public_path($path);
    $version = file_exists($fullPath) ? filemtime($fullPath) : '0';

    return asset($path) . '?v=' . $version;
}
