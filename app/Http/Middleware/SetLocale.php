<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

/**
 * 多語系中間件
 *
 * 純粹使用 URL 識別語系，不使用 Session。
 * 根路徑 `/` 會重導向到預設語系 `/zh-hant`。
 *
 * URL: /zh-hant/ → 內部: zh_Hant
 * URL: /en/      → 內部: en
 * URL: /         → 重導向到 /zh-hant/
 */
class SetLocale
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $urlMapping = config('localization.url_mapping', []);
        $defaultLocale = config('localization.default_locale', 'zh_Hant');
        $supportedLocales = config('localization.supported_locales', []);

        // 取得 URL 第一段
        $segment = strtolower($request->segment(1) ?? '');

        // 檢查是否為有效的語系前綴
        $isValidLocale = isset($urlMapping[$segment]) && in_array($urlMapping[$segment], $supportedLocales);

        // 根路徑或無語系前綴：重導向到預設語系
        if (!$isValidLocale) {
            $defaultUrlLocale = $this->toUrlFormat($defaultLocale);
            $path = $request->path();
            $queryString = $request->getQueryString();
            $redirectUrl = '/' . $defaultUrlLocale . ($path !== '/' ? '/' . $path : '');

            if ($queryString) {
                $redirectUrl .= '?' . $queryString;
            }

            return redirect($redirectUrl);
        }

        // 從 URL 映射取得內部格式
        $locale = $urlMapping[$segment];

        // 設定應用程式語系（內部格式）
        App::setLocale($locale);

        // 設定 URL 預設參數（URL 格式）- 用於 route() 產生連結
        URL::defaults(['locale' => $segment]);

        // 從路由參數中移除 locale，避免傳遞給 Controller
        if ($request->route()) {
            $request->route()->forgetParameter('locale');
        }

        return $next($request);
    }

    /**
     * 內部格式轉 URL 格式
     *
     * zh_Hant → zh-hant
     */
    protected function toUrlFormat(string $locale): string
    {
        $urlMapping = config('localization.url_mapping', []);
        $flipped = array_flip($urlMapping);

        return $flipped[$locale] ?? strtolower(str_replace('_', '-', $locale));
    }
}
