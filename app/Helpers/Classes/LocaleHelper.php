<?php

namespace App\Helpers\Classes;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * 網址語系段（URL locale）的共用邏輯。
 *
 * 各專案只改 config/localization.php，本檔不改 —— ocadmin 系列所有專案放同一份。
 * 機制總覽：https://github.com/elonphp/laravel-ocadmin/blob/main/docs/common/10002_多語機制.md §二
 *
 * 兩種格式：
 *   - internal：PHP / Laravel 用的 locale（zh_Hant、en）
 *   - url     ：網址第一段（zh-hant、en），對照表是 config('localization.url_mapping')
 */
class LocaleHelper
{
    // ── 設定 ─────────────────────────────────────────────

    public static function getDefaultLocale(): string
    {
        return config('localization.default_locale', 'zh_Hant');
    }

    /**
     * 取得支援的語系列表
     *
     * @return string[] ['zh_Hant', 'en']
     */
    public static function getSupportedLocales(): array
    {
        return config('localization.supported_locales', ['zh_Hant']);
    }

    /**
     * 取得語系顯示名稱對照（只含支援中的語系）
     *
     * @return array ['zh_Hant' => '繁體中文', 'en' => 'English']
     */
    public static function getLocaleNames(): array
    {
        return array_intersect_key(
            config('localization.locale_names', []),
            array_flip(self::getSupportedLocales())
        );
    }

    /** 預設語系的網址不帶語系段（/foo 即預設語系；/zh-hant/foo 會 301 到 /foo） */
    public static function hidesDefault(): bool
    {
        return (bool) config('localization.hide_default_locale', false);
    }

    // ── 格式轉換 ─────────────────────────────────────────

    public static function toUrlFormat(string $locale): string
    {
        $flipped = array_flip(config('localization.url_mapping', []));

        return $flipped[$locale] ?? strtolower(str_replace('_', '-', $locale));
    }

    public static function toInternalFormat(string $urlLocale): string
    {
        return config('localization.url_mapping', [])[strtolower($urlLocale)] ?? $urlLocale;
    }

    public static function getCurrentLocale(): string
    {
        return app()->getLocale();
    }

    public static function getCurrentUrlLocale(): string
    {
        return self::toUrlFormat(app()->getLocale());
    }

    public static function getDefaultUrlLocale(): string
    {
        return self::toUrlFormat(self::getDefaultLocale());
    }

    /**
     * 有效的網址語系段：url_mapping 裡、且對應的語系在 supported_locales 內。
     * 只寫在 url_mapping 但已停用的語系不算 —— 那種網址一律 404。
     *
     * @return string[] ['zh-hant', 'en']
     */
    public static function getUrlLocales(): array
    {
        $supported = self::getSupportedLocales();

        return array_keys(array_filter(
            config('localization.url_mapping', []),
            fn ($internal) => in_array($internal, $supported, true)
        ));
    }

    public static function isUrlLocale(?string $segment): bool
    {
        return $segment !== null && in_array(strtolower($segment), self::getUrlLocales(), true);
    }

    /**
     * 路由群組 {locale} 的比對條件：只接受有效的網址語系段。
     *
     *   Route::group(['prefix' => '{locale}', 'where' => ['locale' => LocaleHelper::routePattern()]], ...)
     *
     * 不加這個條件，任何第一段都會被當成語系送進群組（/orders 的 orders、/ja/x 的 ja），
     * 再由 SetLocale 判成無效 —— 判了也只能錯：它分不出「沒帶語系」與「帶了無效的語系」。
     */
    public static function routePattern(): string
    {
        return implode('|', array_map(fn ($s) => preg_quote($s, '/'), self::getUrlLocales())) ?: 'zh-hant';
    }

    // ── 沒帶語系段時用哪個語系 ────────────────────────────

    /**
     * 網址沒帶語系段時，該用哪個語系（internal 格式）。
     *
     * - hide_default_locale：沒帶語系段＝預設語系，不偵測。
     *   否則同一個網址會因人而異顯示不同語言，網址就失去意義
     * - detect_preferred=false：一律預設語系
     * - detect_preferred=true ：依序取第一個有效的（不在 supported_locales 內的視為無效）
     *     1. 帳號的預設語言（users.locale；沒登入、沒設定、或資料表根本沒有這個欄位時跳過）
     *     2. session('locale')：這次瀏覽最後看過的語系（SetLocale 寫入）
     *     3. 瀏覽器語言（Accept-Language，經 bcp47_aliases 對照）
     *     4. 預設語系
     *
     * 帳號排在 session 前面：已登入的人以帳號設定為準，不因為剛點過一頁英文，
     * 之後不帶語系段的網址就全變英文。
     */
    public static function resolvePreferred(Request $request): string
    {
        if (self::hidesDefault() || ! config('localization.detect_preferred', false)) {
            return self::getDefaultLocale();
        }

        $supported = self::getSupportedLocales();

        // 1. users.locale —— 看已載入的欄位，沒有這個欄位就跳過。
        //    不用 $user->locale：開了 Model::preventAccessingMissingAttributes() 時讀不存在的欄位會丟例外；
        //    也不要加 Schema::hasColumn() 防呆 —— 那是每次 resolve 都查 schema
        $userLocale = $request->user()?->getAttributes()['locale'] ?? null;
        if ($userLocale && in_array($userLocale, $supported, true)) {
            return $userLocale;
        }

        // 2. session
        $sessionLocale = $request->hasSession() ? $request->session()->get('locale') : null;
        if ($sessionLocale && in_array($sessionLocale, $supported, true)) {
            return $sessionLocale;
        }

        // 3. Accept-Language → 4. 預設
        return self::matchAcceptLanguage($request, $supported) ?? self::getDefaultLocale();
    }

    /**
     * 比對瀏覽器 Accept-Language 與本系統 supported locales。
     *
     * 為什麼不用 Symfony getPreferredLanguage：
     *   Symfony 只做「拿掉 region 退化到 primary」（zh-TW → zh），不認得 BCP-47
     *   script subtag 語意（zh-TW ≡ zh-Hant，zh-CN ≡ zh-Hans）。中文繁簡分流必錯。
     *
     * 演算法：
     *   1. 解析 Accept-Language 拿到 [(tag, q)]，依 q 排序
     *   2. 每個 tag 跑 config('localization.bcp47_aliases') 對 internal locale
     *   3. 命中且該 internal 在 supported 內 → 回傳該 internal
     *   4. 都沒命中 → 回 null
     */
    private static function matchAcceptLanguage(Request $request, array $supported): ?string
    {
        $header = $request->header('Accept-Language', '');
        if ($header === '') {
            return null;
        }

        $aliases = config('localization.bcp47_aliases', []);

        // Symfony AcceptHeader 會解析 q value 並依優先序排序
        $items = \Symfony\Component\HttpFoundation\AcceptHeader::fromString($header)->all();

        foreach ($items as $tag => $_item) {
            $internal = self::matchTagToInternal($tag, $supported, $aliases);
            if ($internal !== null) {
                return $internal;
            }
        }

        return null;
    }

    /**
     * 把單一 BCP-47 tag 對應到 internal locale。
     *
     * 比對順序：
     *   1. tag 直接等於某 supported（罕見）
     *   2. tag 命中 aliases 表，且該 internal 在 supported 內
     *   3. 拿掉 region/script，用 primary tag 重試（如 en-NZ → en）
     */
    private static function matchTagToInternal(string $tag, array $supported, array $aliases): ?string
    {
        $tagLower = strtolower($tag);

        foreach ($supported as $sup) {
            if (strtolower($sup) === $tagLower) {
                return $sup;
            }
        }

        foreach ($aliases as $internal => $browserTags) {
            if (! in_array($internal, $supported, true)) {
                continue;
            }
            foreach ($browserTags as $alias) {
                if (strtolower($alias) === $tagLower) {
                    return $internal;
                }
            }
        }

        $primary = explode('-', $tagLower)[0];
        if ($primary !== $tagLower) {
            return self::matchTagToInternal($primary, $supported, $aliases);
        }

        return null;
    }

    // ── 路徑 ────────────────────────────────────────────

    /**
     * 這個路徑有沒有路由接得住（fallback 不算 —— 它什麼都接）。
     *
     * 所有「補語系段 / 修舊路徑」的重導都先問這一句：只重導到確實存在的網址，否則 404。
     * 不問的話，會先把人送到一個不存在的網址再 404（例：/ja/foo → /zh-hant/ja/foo）。
     */
    public static function isRoutable(string $path, string $method = 'GET'): bool
    {
        try {
            return ! app('router')->getRoutes()->match(Request::create($path, $method))->isFallback;
        } catch (HttpException) {
            return false;   // NotFound / MethodNotAllowed
        }
    }

    /**
     * 這個路徑有哪些 HTTP 方法接得住（fallback 不算）。
     * 給 fallback 分辨 404（網址不存在）與 405（網址存在、方法不對）。
     *
     * @return string[] ['GET', 'HEAD', 'POST']
     */
    public static function allowedMethods(string $path): array
    {
        return array_values(array_filter(
            ['GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
            fn ($method) => self::isRoutable($path, $method)
        ));
    }

    /**
     * hide_default_locale 時，把產生的網址去掉預設語系段：/zh-hant/foo → /foo、/zh-hant → /
     * 掛在 URL::formatPathUsing()，route()、url()、redirect() 都會經過。
     */
    public static function stripDefaultPrefix(string $path): string
    {
        $prefix = '/' . self::getDefaultUrlLocale();

        if ($path === $prefix) {
            return '/';
        }

        return str_starts_with($path, $prefix . '/') ? substr($path, strlen($prefix)) : $path;
    }

    /**
     * 目前頁面換成另一個語系的網址（語系切換選單用）。
     * 目前不在語系路由上（如 /auth/*）→ 該語系的首頁。
     */
    public static function switchUrl(string $locale): string
    {
        $request = request();
        $segments = $request->segments();
        $target = self::toUrlFormat($locale);

        $path = ($segments && self::isUrlLocale($segments[0]))
            ? implode('/', array_merge([$target], array_slice($segments, 1)))
            : $target;

        $query = $request->getQueryString();

        return url($path) . ($query ? '?' . $query : '');
    }

    /**
     * 依網址第一段設定 locale 並返回網址語系段（給不經過 SetLocale 的地方用）
     */
    public static function setLocale(): string
    {
        $segment = strtolower(request()->segment(1) ?? '');
        $urlLocale = self::isUrlLocale($segment) ? $segment : self::getDefaultUrlLocale();

        App::setLocale(self::toInternalFormat($urlLocale));
        URL::defaults(['locale' => $urlLocale]);

        return $urlLocale;
    }
}
