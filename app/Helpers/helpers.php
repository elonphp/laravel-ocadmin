<?php

if (! function_exists('setting')) {
    /**
     * 從 settings 讀取設定值（帶快取）。
     *
     * 優先從 Config 讀取（由 SettingServiceProvider 預載 is_autoload=true 的設定），
     * 未命中才查 DB 並快取於 per-request static cache。
     */
    function setting(string $code, mixed $default = null): mixed
    {
        // 1. 先查 Config（SettingServiceProvider 已預載 is_autoload=true 的設定）
        $configKey = "settings.{$code}";

        if (config()->has($configKey)) {
            return config($configKey) ?? $default;
        }

        // 2. Config 未命中 → 查 DB（per-request static cache）
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

/**
 * 將 Portal 目錄名（如 Ocadmin）轉換為 role_prefix（如 admin）。
 *
 * role_prefix 是角色命名與 Portal 存取控制的識別依據，
 * 用於 requirePortalRole middleware。
 */
function resolvePortal(string $directory): ?string
{
    foreach (config('portals') as $config) {
        if (($config['dir'] ?? null) === $directory) {
            return $config['role_prefix'] ?? null;
        }
    }

    return null;
}

/**
 * 拼接 ocadmin permission 名稱：{permission_prefix}.{suffix}
 *
 * 用於 seeder / menu / 權限檢查等需要 permission 字串的場景。
 * 衍生專案改 config('portals.ocadmin.permission_prefix') 即可換掉所有 permission 字串。
 *
 * @see docs/common/10007_權限機制.md
 */
function permName(string $suffix): string
{
    $prefix = config('portals.ocadmin.permission_prefix', 'admin');
    return "{$prefix}.{$suffix}";
}

/**
 * 拼接 ocadmin role 名稱：{role_prefix}.{name}
 *
 * $isException = true 時不加 prefix（用於 super_admin / system 等
 * 跨 portal 通用角色）。
 *
 * @see docs/common/10007_權限機制.md
 */
function roleName(string $name, bool $isException = false): string
{
    if ($isException) {
        return $name;
    }
    $prefix = config('portals.ocadmin.role_prefix', 'admin');
    return "{$prefix}.{$name}";
}

