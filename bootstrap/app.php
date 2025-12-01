<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // 未登入時重導向到登入頁（保留當前語系）
        $middleware->redirectGuestsTo(function ($request) {
            $urlMapping = config('localization.url_mapping', []);
            $defaultLocale = config('localization.default_locale', 'zh_Hant');
            $segment = strtolower($request->segment(1) ?? '');

            // 取得當前語系的 URL 格式
            if (isset($urlMapping[$segment])) {
                $urlLocale = $segment;
            } else {
                $flipped = array_flip($urlMapping);
                $urlLocale = $flipped[$defaultLocale] ?? 'zh-hant';
            }

            return "/{$urlLocale}/ocadmin/login";
        });

        // 記錄所有非 GET 請求到日誌
        $middleware->web(append: [
            \App\Http\Middleware\LogRequest::class,
        ]);

        // 註冊語系中間件別名
        $middleware->alias([
            'locale' => \App\Http\Middleware\SetLocale::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
