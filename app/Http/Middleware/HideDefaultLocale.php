<?php

namespace App\Http\Middleware;

use App\Helpers\Classes\LocaleHelper;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * hide_default_locale：預設語系的網址不帶語系段。
 *
 * 路由照舊定義在 {locale} 群組底下（路由檔、route name 都不必改），差別只在網址的「外觀」：
 *
 *   進來：/orders/1       → 內部改寫成 /zh-hant/orders/1 再比對路由（不重導，瀏覽器網址不變）—— 本檔
 *         /zh-hant/orders/1 → 301 到 /orders/1（舊書籤、寫死的連結）—— SetLocale
 *   出去：route()／url() 產生的網址由 URL::formatPathUsing() 去掉 /zh-hant（見 LocaleServiceProvider）
 *
 * 301 不在這裡做：這裡在 session 啟動之前，重導會吃掉 flash（驗證錯誤、舊輸入）。
 * 例：登入失敗 back() 回到的是 session 記的內部網址 /zh-hant/login，若在這裡 301，錯誤訊息就沒了。
 *
 * 判斷順序是「**根層級路由優先**」：/foo 本身就有路由（刻意定義在根層級的 /、/auth/*、/logout、/api/*）
 * 就照原路徑；沒有，才補上預設語系段當成語系路由。
 *
 * ⚠ 兩邊同一個網址都有路由時，語系路由那一條會被遮住 —— 最常見的是 `/`：
 * 根層級定義了首頁 `/`，語系群組的 `/{locale}/` 在不帶語系段時也是 `/`。
 * 這種專案要把語系群組裡的那一條換個路徑（例如 dashboard 用 /dashboard），否則它永遠到不了。
 *
 * 只在 hide_default_locale=true 時由 LocaleServiceProvider 掛成全域 middleware（排最前面，在比對路由之前）。
 *
 * 機制總覽：https://github.com/elonphp/laravel-ocadmin/blob/main/docs/common/10002_多語機制.md §二
 */
class HideDefaultLocale
{
    public const ATTRIBUTE = 'locale.prefix_added';

    public function handle(Request $request, Closure $next): Response
    {
        $path = $request->getPathInfo();
        $first = strtolower($request->segment(1) ?? '');
        $default = LocaleHelper::getDefaultUrlLocale();

        // 帶了語系段 → 照常（帶預設語系段的由 SetLocale 301 到不帶的網址）
        if (LocaleHelper::isUrlLocale($first)) {
            return $next($request);
        }

        // 沒帶語系段：根層級本身有路由就照原路徑；否則補上預設語系段接得住，就在內部改寫
        $method = $request->getMethod();
        $prefixed = '/' . $default . ($path === '/' ? '' : $path);
        if (LocaleHelper::isRoutable($path, $method) || ! LocaleHelper::isRoutable($prefixed, $method)) {
            return $next($request);
        }

        $query = $request->getQueryString();
        $server = $request->server->all();
        $server['REQUEST_URI'] = $request->getBaseUrl() . $prefixed . ($query ? '?' . $query : '');

        $rewritten = $request->duplicate(server: $server);
        $rewritten->attributes->set(self::ATTRIBUTE, true);   // 給 SetLocale 分辨：語系段是補的，不是使用者帶的

        return $next($rewritten);
    }
}
