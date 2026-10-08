<?php

namespace App\Http\Controllers;

use App\Helpers\Classes\LocaleHelper;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

/**
 * 所有路由都沒對上時的最後一站（fallback 路由）：修正網址後重導，或 404。
 *
 *   /foo                 → 補語系段：/{偏好語系}/foo（偏好語系見 LocaleHelper::resolvePreferred）
 *   /{語系}/{舊路徑}/foo  → 依 config('localization.legacy_paths') 改寫第一段
 *   其他                 → 404
 *
 * **只重導到確實有路由接得住的網址，否則直接 404。**
 * 不先驗證的話會把人送到一個不存在的網址再 404，而且分不出「沒帶語系段」與
 * 「帶了無效 / 已停用的語系段」—— 兩者第一段都不是有效語系，
 * 於是 /ja/foo 被當成沒帶語系，補成 /zh-hant/ja/foo。先驗證就不必區分：那種網址直接 404。
 *
 * 用 fallback 路由而不是自己寫 {any} catch-all：fallback 永遠排在所有路由之後，
 * 不必靠路由檔的載入順序（provider 註冊的 portal 路由、routes/web.php 誰先誰後）。
 *
 * hide_default_locale 時，重導目標經 url() 會自動去掉預設語系段（見 LocaleServiceProvider）。
 *
 * 機制總覽：https://github.com/elonphp/laravel-ocadmin/blob/main/docs/common/10002_多語機制.md §二
 */
class LocaleFallbackController
{
    public function __invoke(Request $request): RedirectResponse
    {
        // 只修正 GET：表單送出不能被重導（POST 會變成 GET）
        if (! $request->isMethod('GET') && ! $request->isMethod('HEAD')) {
            $this->notFound($request);
        }

        $segments = $request->segments();

        $urlLocale = LocaleHelper::isUrlLocale($segments[0] ?? null)
            ? strtolower(array_shift($segments))
            : LocaleHelper::toUrlFormat(LocaleHelper::resolvePreferred($request));

        // 舊路徑改寫（各專案自己的歷史包袱，寫在 config）：['admin' => '', 'old-module' => 'new-module']
        $legacy = config('localization.legacy_paths', []);
        if ($segments && array_key_exists($segments[0], $legacy)) {
            $segments[0] = $legacy[$segments[0]];
            $segments = array_values(array_filter($segments, fn ($s) => $s !== ''));
        }

        $target = '/' . $urlLocale . ($segments ? '/' . implode('/', $segments) : '');

        // hide_default_locale 時，實際送出的網址是去掉預設語系段的那個（url() 會去掉）；
        // 它可能落在根層級路由上（/zh-hant → /），所以兩個都算接得住
        $visible = LocaleHelper::hidesDefault() ? LocaleHelper::stripDefaultPrefix($target) : $target;

        if ($visible === '/' . ltrim($request->path(), '/')
            || (! LocaleHelper::isRoutable($target) && ! LocaleHelper::isRoutable($visible))) {
            $this->notFound($request);
        }

        // 驗證錯誤、舊輸入多撐一次轉址
        if ($request->hasSession()) {
            $request->session()->reflash();
        }

        $query = $request->getQueryString();

        return redirect(url($target) . ($query ? '?' . $query : ''));
    }

    /**
     * 網址存在、只是方法不對 → 405；網址不存在 → 404。
     * fallback 路由接受所有方法，少了這一步，「方法不對」也會變成 404（Laravel 原本回 405）。
     */
    private function notFound(Request $request): never
    {
        $allowed = LocaleHelper::allowedMethods('/' . ltrim($request->path(), '/'));

        if ($allowed) {
            throw new MethodNotAllowedHttpException($allowed);
        }

        abort(404);
    }
}
