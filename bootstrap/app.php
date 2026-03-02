<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Exceptions\CustomException;
use App\Helpers\Classes\JsonResponseHelper;
use App\Repositories\LogDatabaseRepository;

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
            'requirePortalRole' => \App\Http\Middleware\RequirePortalRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // 記錄例外到資料庫
        $exceptions->reportable(function (Throwable $e) {
            LogDatabaseRepository::logRequest(
                statusCode: 500,
                note: $e->getMessage()
            );
        });

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
                    'message' => collect($errors)->first(),
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
                return JsonResponseHelper::error(
                    $e->getGeneralError(),
                    $e->getSysError(),
                    $e->getStatusCode(),
                    $request,
                    $e
                );
            }

            // 5. 其他錯誤（非預期的系統層級錯誤）
            return JsonResponseHelper::error(
                __('admin/default.text_error_system'),
                $e->getMessage(),
                500,
                $request,
                $e
            );
        });
    })->create();
