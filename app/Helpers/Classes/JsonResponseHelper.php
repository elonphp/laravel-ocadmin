<?php

namespace App\Helpers\Classes;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class JsonResponseHelper
{
    /**
     * 根據 debug 模式或使用者角色決定回傳的錯誤訊息
     */
    public static function error(
        string $generalError,
        ?string $sysError,
        int $statusCode,
        Request $request,
        ?Throwable $e = null
    ): JsonResponse {
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
}
