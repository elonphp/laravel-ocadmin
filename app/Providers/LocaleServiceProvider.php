<?php

namespace App\Providers;

use App\Helpers\Classes\LocaleHelper;
use App\Http\Controllers\LocaleFallbackController;
use App\Http\Middleware\HideDefaultLocale;
use App\Http\Middleware\SetLocale;
use Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

/**
 * 網址語系段機制的組裝點。各專案只改 config/localization.php，本檔不改。
 *
 * 需要列在 bootstrap/providers.php。
 *
 * 機制總覽：https://github.com/elonphp/laravel-ocadmin/blob/main/docs/common/10002_多語機制.md §二
 */
class LocaleServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // 不經過 SetLocale 的地方（/auth/* 之類）呼叫 route('lang.*') 也能補上語系段；SetLocale 會再依網址覆寫
        URL::defaults(['locale' => LocaleHelper::getDefaultUrlLocale()]);

        // SetLocale 排在登入檢查之前（StartSession 之後）：
        // 帶預設語系段的網址先 301 成不帶的，再決定要不要導去登入 —— 否則登入後回到的是舊網址；
        // 錯誤頁、登入頁也才會用網址上的語系
        $this->app->make(Kernel::class)->addToMiddlewarePriorityBefore(AuthenticatesRequests::class, SetLocale::class);

        if (LocaleHelper::hidesDefault()) {
            // 產生的網址去掉預設語系段：route()、url()、redirect() 都經過這裡（完整網址字串不經過）
            URL::formatPathUsing(fn (string $path) => LocaleHelper::stripDefaultPrefix($path));

            // 進來的網址補上預設語系段 —— 必須在比對路由之前，所以是全域 middleware、排最前面
            $this->app->make(Kernel::class)->prependMiddleware(HideDefaultLocale::class);
        }

        // 沒帶語系段、舊路徑、不存在的網址。
        // 不用 Route::fallback()：它只註冊 GET，於是 POST 到不存在的網址會變成 405（方法不允許）而不是 404
        Route::any('{fallbackPlaceholder}', LocaleFallbackController::class)
            ->where('fallbackPlaceholder', '.*')
            ->fallback()
            ->middleware('web');
    }
}
