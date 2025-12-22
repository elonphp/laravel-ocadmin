<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Exceptions\CustomException;

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
        // API 請求的錯誤處理
        $exceptions->render(function (Throwable $e, Request $request) {
            if (!$request->expectsJson()) {
                return null; // 非 API 請求，使用預設處理（Blade 錯誤頁）
            }

            // 1. 表單驗證錯誤
            if ($e instanceof ValidationException) {
                return response()->json([
                    'success' => false,
                    'message' => '資料驗證失敗',
                    'errors'  => $e->errors(),
                ], 422);
            }

            // 2. HTTP 例外（abort(400, 'xxx')）
            if ($e instanceof HttpException) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage() ?: '請求錯誤',
                ], $e->getStatusCode());
            }

            // 3. CustomException（預期的業務邏輯錯誤）
            if ($e instanceof CustomException) {
                return sendJsonErrorResponse(
                    $e->getGeneralError(),
                    $e->getSysError(),
                    $e->getStatusCode(),
                    $request
                );
            }

            // 4. 其他錯誤（非預期的系統層級錯誤）
            return sendJsonErrorResponse(
                '系統發生錯誤，請聯絡管理員。',
                $e->getMessage(),
                500,
                $request
            );
        });
    })->create();

/**
 * 根據 debug 模式或使用者角色決定回傳的錯誤訊息
 */
function sendJsonErrorResponse(
    string $generalError,
    ?string $sysError,
    int $statusCode,
    Request $request
): \Illuminate\Http\JsonResponse {
    $user = $request->user();

    // debug 模式或 sys_admin 角色時，顯示詳細錯誤
    if (config('app.debug') || ($user && method_exists($user, 'hasRole') && $user->hasRole('ocadmin.sys_admin'))) {
        return response()->json([
            'success' => false,
            'message' => $sysError ?? $generalError,
        ], $statusCode);
    }

    return response()->json([
        'success' => false,
        'message' => $generalError,
    ], $statusCode);
}
