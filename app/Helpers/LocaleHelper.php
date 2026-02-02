<?php

namespace App\Helpers;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\URL;

class LocaleHelper
{
    /**
     * 設定 locale 並返回 URL 前綴
     */
    public static function setLocale(): string
    {
        $urlMapping = config('localization.url_mapping', []);
        $supportedLocales = config('localization.supported_locales', []);
        $defaultLocale = config('localization.default_locale', 'zh_Hant');

        $segment = request()->segment(1);
        $segment = $segment ? strtolower($segment) : '';

        if (isset($urlMapping[$segment]) && in_array($urlMapping[$segment], $supportedLocales)) {
            $locale = $urlMapping[$segment];
            $urlLocale = $segment;
        } else {
            $locale = $defaultLocale;
            $urlLocale = self::toUrlFormat($locale);
        }

        App::setLocale($locale);
        URL::defaults(['locale' => $urlLocale]);

        return $urlLocale;
    }

    public static function toUrlFormat(string $locale): string
    {
        $urlMapping = config('localization.url_mapping', []);
        $flipped = array_flip($urlMapping);

        return $flipped[$locale] ?? strtolower(str_replace('_', '-', $locale));
    }

    public static function toInternalFormat(string $urlLocale): string
    {
        $urlMapping = config('localization.url_mapping', []);

        return $urlMapping[strtolower($urlLocale)] ?? $urlLocale;
    }

    public static function getCurrentLocale(): string
    {
        return app()->getLocale();
    }

    public static function getCurrentUrlLocale(): string
    {
        return self::toUrlFormat(app()->getLocale());
    }

    public static function getDefaultLocale(): string
    {
        return config('localization.default_locale', 'zh_Hant');
    }
}
