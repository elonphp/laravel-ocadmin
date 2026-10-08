<?php

namespace App\Http\Middleware;

use App\Helpers\Classes\LocaleHelper;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

/**
 * 多語系中間件：網址第一段決定這次 request 的語系。
 *
 * URL: /zh-hant/... → App locale: zh_Hant
 * URL: /en/...      → App locale: en
 *
 * 只掛在 {locale} 路由群組上，而群組已用 LocaleHelper::routePattern() 限定只接受有效語系，
 * 所以這裡不處理「語系無效」—— 進得來就是有效的。
 * 沒帶語系段、無效語系的網址由 LocaleFallbackController 處理（補語系段或 404）。
 *
 * 機制總覽：https://github.com/elonphp/laravel-ocadmin/blob/main/docs/common/10002_多語機制.md §二
 */
class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $segment = strtolower($request->segment(1) ?? '');

        // 群組沒加 where 條件才會走到這裡 —— 不重導（重導只會把無效語系疊進網址），直接 404
        if (! LocaleHelper::isUrlLocale($segment)) {
            abort(404);
        }

        // hide_default_locale：使用者自己帶了預設語系段（舊書籤、寫死的連結、back() 回到的內部網址）
        // → 301 到不帶的網址。只對 GET（不能讓 POST 變成 GET）；reflash 讓驗證錯誤、舊輸入多撐一次轉址
        if (LocaleHelper::hidesDefault()
            && $segment === LocaleHelper::getDefaultUrlLocale()
            && ! $request->attributes->get(HideDefaultLocale::ATTRIBUTE)
            && $request->isMethod('GET')) {
            if ($request->hasSession()) {
                $request->session()->reflash();
            }
            $query = $request->getQueryString();

            return redirect(LocaleHelper::stripDefaultPrefix('/' . $request->path()) . ($query ? '?' . $query : ''), 301);
        }

        // 設定應用程式語系
        $locale = LocaleHelper::toInternalFormat($segment);
        App::setLocale($locale);

        // 記進 session：網址沒帶語系段時的偏好來源之一（見 LocaleHelper::resolvePreferred）。
        // 不寫 users.locale —— 那是使用者主動設定的，不該被瀏覽軌跡覆蓋
        if ($request->hasSession() && $request->session()->get('locale') !== $locale) {
            $request->session()->put('locale', $locale);
        }

        // 設定 URL 預設參數，讓 route() 自動帶入 {locale}
        URL::defaults(['locale' => $segment]);

        // 從路由參數中移除 locale，避免傳遞給 Controller
        if ($request->route()) {
            $request->route()->forgetParameter('locale');
        }

        return $next($request);
    }
}
