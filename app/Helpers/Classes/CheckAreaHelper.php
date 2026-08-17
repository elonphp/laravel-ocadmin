<?php

namespace App\Helpers\Classes;

use Illuminate\Http\Request;

class CheckAreaHelper
{
    /**
     * 判斷是否為後台區域
     *
     * 匹配：
     * - /admin
     * - /admin/...
     * - /zh-hant/admin
     * - /zh-hant/admin/...
     */
    public static function isAdminArea(Request $request): bool
    {
        $adminFolder = config('vars.admin_folder', 'admin');
        $path = $request->path();

        // 取得支援的語系 URL 前綴
        $urlMapping = config('localization.url_mapping', []);
        $locales = array_map(
            fn($locale) => preg_quote($locale, '#'),
            array_keys($urlMapping)
        );
        $localePattern = implode('|', $locales);

        // 匹配模式：
        // 1. admin 或 admin/...
        // 2. {locale}/admin 或 {locale}/admin/...
        $pattern = '#^((' . $localePattern . ')/)?' . preg_quote($adminFolder, '#') . '(/|$)#';

        return preg_match($pattern, $path) === 1;
    }

    /**
     * 判斷是否為 API 區域
     */
    public static function isApiArea(Request $request): bool
    {
        return str_starts_with($request->path(), 'api');
    }

    /**
     * 判斷是否為前台區域
     */
    public static function isPublicArea(Request $request): bool
    {
        if (self::isAdminArea($request) || self::isApiArea($request)) {
            return false;
        }

        return true;
    }
}
