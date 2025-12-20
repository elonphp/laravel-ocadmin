<?php

namespace Elonphp\LaravelOcadminModules\Support;

use Illuminate\Support\Facades\App;

class LocaleHelper
{
    /**
     * Convert URL format to internal format
     * zh-hant → zh_Hant
     */
    public static function toInternal(string $urlLocale): string
    {
        $mapping = config('ocadmin.localization.url_mapping', []);

        return $mapping[strtolower($urlLocale)] ?? $urlLocale;
    }

    /**
     * Convert internal format to URL format
     * zh_Hant → zh-hant
     */
    public static function toUrl(string $locale): string
    {
        $mapping = config('ocadmin.localization.url_mapping', []);
        $flipped = array_flip($mapping);

        return $flipped[$locale] ?? strtolower(str_replace('_', '-', $locale));
    }

    /**
     * Get current locale (internal format)
     */
    public static function current(): string
    {
        return App::getLocale();
    }

    /**
     * Get current locale (URL format)
     */
    public static function currentUrl(): string
    {
        return self::toUrl(self::current());
    }

    /**
     * Set locale from URL and return URL locale prefix
     * Used in route definitions to set locale early
     */
    public static function setLocale(): string
    {
        $urlMapping = config('ocadmin.localization.url_mapping', []);
        $defaultLocale = self::default();

        // Get first segment from URL
        $path = request()->path();
        $segments = explode('/', trim($path, '/'));
        $urlLocale = strtolower($segments[0] ?? '');

        // Determine internal locale
        if (isset($urlMapping[$urlLocale])) {
            $locale = $urlMapping[$urlLocale];
        } else {
            $locale = $defaultLocale;
            $urlLocale = self::toUrl($defaultLocale);
        }

        // Set application locale
        App::setLocale($locale);

        return $urlLocale;
    }

    /**
     * Get default locale
     */
    public static function default(): string
    {
        return config('ocadmin.localization.default', 'zh_Hant');
    }

    /**
     * Get supported locales
     */
    public static function supported(): array
    {
        return config('ocadmin.localization.supported', ['zh_Hant']);
    }

    /**
     * Check if locale is supported
     */
    public static function isSupported(string $locale): bool
    {
        return in_array($locale, self::supported());
    }

    /**
     * Get locale display name
     */
    public static function name(string $locale): string
    {
        $names = config('ocadmin.localization.names', []);

        return $names[$locale] ?? $locale;
    }

    /**
     * Generate URL for switching to another locale
     */
    public static function switchUrl(string $locale): string
    {
        $currentUrl = request()->url();
        $urlMapping = config('ocadmin.localization.url_mapping', []);
        $validLocales = array_keys($urlMapping);

        // Parse path
        $path = parse_url($currentUrl, PHP_URL_PATH);
        $segments = array_filter(explode('/', trim($path, '/')));

        // Remove current locale prefix
        if (!empty($segments)) {
            $first = strtolower(reset($segments));
            if (in_array($first, $validLocales)) {
                array_shift($segments);
            }
        }

        // Build new path
        $newPath = implode('/', $segments);
        $urlLocale = self::toUrl($locale);

        return url('/' . $urlLocale . ($newPath ? '/' . $newPath : ''));
    }

    /**
     * Get all locale switch links
     */
    public static function switchLinks(): array
    {
        $links = [];
        $current = self::current();

        foreach (self::supported() as $locale) {
            $links[$locale] = [
                'url' => self::switchUrl($locale),
                'url_locale' => self::toUrl($locale),
                'name' => self::name($locale),
                'is_current' => $locale === $current,
            ];
        }

        return $links;
    }
}
