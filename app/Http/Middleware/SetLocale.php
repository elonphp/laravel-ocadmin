<?php

namespace App\Http\Middleware;

use App\Helpers\Classes\LocaleHelper;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

/**
 * 多語系中間件
 *
 * 純粹使用 URL 識別語系，不使用 Session。
 * 無效語系前綴會重導向到預設語系。
 *
 * URL: /zh-hant/admin/... → App locale: zh_Hant
 * URL: /en/admin/...      → App locale: en
 */
class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $segment = strtolower($request->segment(1) ?? '');
        $locale = LocaleHelper::toInternalFormat($segment);

        // 檢查是否為有效的語系前綴
        $isValidLocale = $locale !== $segment || in_array($segment, LocaleHelper::getSupportedLocales());

        // 無效語系：重導向到預設語系
        if (!$isValidLocale) {
            $defaultUrlLocale = LocaleHelper::toUrlFormat(LocaleHelper::getDefaultLocale());
            $path = $request->path();
            $queryString = $request->getQueryString();
            $redirectUrl = '/' . $defaultUrlLocale . ($path !== '/' ? '/' . $path : '');

            if ($queryString) {
                $redirectUrl .= '?' . $queryString;
            }

            return redirect($redirectUrl);
        }

        // 設定應用程式語系
        App::setLocale($locale);

        // 設定 URL 預設參數，讓 route() 自動帶入 {locale}
        URL::defaults(['locale' => $segment]);

        // 從路由參數中移除 locale，避免傳遞給 Controller
        if ($request->route()) {
            $request->route()->forgetParameter('locale');
        }

        return $next($request);
    }
}
