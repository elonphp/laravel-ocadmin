<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Auth\AuthenticationException;
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
        // 未登入時導向 Ocadmin 登入頁
        $middleware->redirectGuestsTo(fn () => '/admin/login');

        // 自訂 Middleware 別名
        $middleware->alias([
            'setLocale' => \App\Http\Middleware\SetLocale::class,
            'logRequest' => \App\Http\Middleware\LogRequest::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (Throwable $e, Request $request) {
            if (!$request->expectsJson()) {
                return null; // 非 AJAX 請求，使用 Laravel 預設 Blade 錯誤頁
            }

            // 1. 表單驗證錯誤（ValidationException）
            if ($e instanceof ValidationException) {
                $errors = [];

                foreach ($e->errors() as $field => $messages) {
                    // 轉換 translations.{locale}.{column} → {column}-{locale}
                    if (str_starts_with($field, 'translations.')) {
                        $parts = explode('.', $field);
                        $locale = $parts[1];
                        $column = $parts[2];
                        $key = $column . '-' . $locale;
                    } else {
                        $key = $field;
                    }

                    $errors[$key] = $messages[0];
                }

                return response()->json([
                    'success' => false,
                    'message' => reset($errors),
                    'errors'  => $errors,
                ], 422);
            }

            // 2. 未認證（session 過期、未登入）
            if ($e instanceof AuthenticationException) {
                return response()->json([
                    'success' => false,
                    'message' => '登入已過期，請重新登入',
                    'redirect' => '/admin/login',
                ], 401);
            }

            // 3. HTTP 例外（abort(404, '...'), ModelNotFoundException, AuthorizationException 等）
            if ($e instanceof HttpException) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage() ?: '請求錯誤',
                ], $e->getStatusCode());
            }

            // 4. CustomException（預期的業務邏輯錯誤）
            if ($e instanceof CustomException) {
                return sendJsonErrorResponse(
                    $e->getGeneralError(),
                    $e->getSysError(),
                    $e->getStatusCode(),
                    $request,
                    $e
                );
            }

            // 5. 其他錯誤（非預期的系統層級錯誤）
            return sendJsonErrorResponse(
                '系統發生錯誤，請聯絡管理員。',
                $e->getMessage(),
                500,
                $request,
                $e
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
    Request $request,
    ?Throwable $e = null
): \Illuminate\Http\JsonResponse {
    $user = $request->user();
    $isDebugUser = config('app.debug')
        || ($user && method_exists($user, 'hasRole') && $user->hasRole('super_admin'));

    if ($isDebugUser) {
        $response = [
            'success' => false,
            'message' => $sysError ?? $generalError,
        ];

        // debug 模式額外附帶完整除錯資訊（前端不使用，供 browser console 查看）
        if ($e) {
            $response['debug'] = [
                'exception' => get_class($e),
                'file'      => $e->getFile(),
                'line'      => $e->getLine(),
                'trace'     => collect($e->getTrace())->map(function ($frame) {
                    return ($frame['file'] ?? '') . ':' . ($frame['line'] ?? '') . ' ' . ($frame['class'] ?? '') . ($frame['type'] ?? '') . ($frame['function'] ?? '');
                })->take(20)->toArray(),
            ];
        }

        return response()->json($response, $statusCode);
    }

    return response()->json([
        'success' => false,
        'message' => $generalError,
    ], $statusCode);
}
