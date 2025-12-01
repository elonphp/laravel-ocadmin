<?php

namespace App\Helpers;

/**
 * 多語系輔助類別
 *
 * 所有路由都包含語系前綴，路由名稱使用 lang. 前綴。
 * 例如：lang.home, lang.login, lang.catalog.products.index
 */
class LocaleHelper
{
    /**
     * 內部格式轉 URL 格式
     *
     * zh_Hant → zh-hant
     */
    public static function toUrlFormat(string $locale): string
    {
        $urlMapping = config('localization.url_mapping', []);
        $flipped = array_flip($urlMapping);

        return $flipped[$locale] ?? strtolower(str_replace('_', '-', $locale));
    }

    /**
     * URL 格式轉內部格式
     *
     * zh-hant → zh_Hant
     */
    public static function toInternalFormat(string $urlLocale): string
    {
        $urlMapping = config('localization.url_mapping', []);

        return $urlMapping[strtolower($urlLocale)] ?? $urlLocale;
    }

    /**
     * 產生切換語系的 URL
     *
     * 保留當前路徑，只切換語系前綴
     */
    public static function switchUrl(string $locale): string
    {
        $currentUrl = request()->url();
        $urlMapping = config('localization.url_mapping', []);

        // 取得所有 URL 格式的語系
        $urlLocales = array_keys($urlMapping);

        // 解析當前路徑
        $path = parse_url($currentUrl, PHP_URL_PATH);
        $segments = array_filter(explode('/', trim($path, '/')));

        // 移除當前語系前綴（如果有）
        if (!empty($segments)) {
            $first = strtolower(reset($segments));
            if (in_array($first, $urlLocales)) {
                array_shift($segments);
            }
        }

        // 組合新路徑
        $newPath = implode('/', $segments);

        // 轉換為 URL 格式
        $urlLocale = self::toUrlFormat($locale);

        // 永遠加上語系前綴
        return url('/' . $urlLocale . ($newPath ? '/' . $newPath : ''));
    }

    /**
     * 取得所有語系的切換連結
     */
    public static function getSwitchLinks(): array
    {
        $links = [];
        $supported = config('localization.supported_locales', []);
        $currentLocale = app()->getLocale();

        foreach ($supported as $locale) {
            $links[$locale] = [
                'url' => self::switchUrl($locale),
                'url_locale' => self::toUrlFormat($locale),
                'internal_locale' => $locale,
                'name' => self::getLocaleName($locale),
                'is_current' => $locale === $currentLocale,
            ];
        }

        return $links;
    }

    /**
     * 取得語系顯示名稱
     */
    public static function getLocaleName(string $locale): string
    {
        $names = config('localization.locale_names', []);

        return $names[$locale] ?? $locale;
    }

    /**
     * 取得當前語系（內部格式）
     */
    public static function getCurrentLocale(): string
    {
        return app()->getLocale();
    }

    /**
     * 取得當前語系（URL 格式）
     */
    public static function getCurrentUrlLocale(): string
    {
        return self::toUrlFormat(app()->getLocale());
    }

    /**
     * 取得預設語系
     */
    public static function getDefaultLocale(): string
    {
        return config('localization.default_locale', 'zh_Hant');
    }

    /**
     * 取得支援的語系列表（內部格式）
     */
    public static function getSupportedLocales(): array
    {
        return config('localization.supported_locales', []);
    }

    /**
     * 取得支援的語系列表（URL 格式）
     */
    public static function getSupportedUrlLocales(): array
    {
        return array_map([self::class, 'toUrlFormat'], self::getSupportedLocales());
    }

    /**
     * 檢查語系是否支援
     */
    public static function isSupported(string $locale): bool
    {
        return in_array($locale, self::getSupportedLocales());
    }

    /**
     * 產生帶語系的路由 URL
     *
     * 路由名稱格式：lang.{name}
     * 例如：lang.home, lang.login, lang.catalog.products.index
     *
     * @param string $name 路由名稱（不含 lang. 前綴）
     * @param array $parameters 路由參數
     * @param string|null $locale 指定語系，null 使用當前語系
     */
    public static function route(string $name, array $parameters = [], ?string $locale = null): string
    {
        $locale = $locale ?? self::getCurrentLocale();
        $urlLocale = self::toUrlFormat($locale);

        return route('lang.' . $name, array_merge(['locale' => $urlLocale], $parameters));
    }
}
