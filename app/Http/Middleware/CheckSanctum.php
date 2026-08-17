<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Sanctum Bearer Token 驗證 Middleware
 *
 * 位於 checkPortal 之後，負責驗證 Authorization: Bearer {token}。
 *
 * 旁路邏輯：
 *   若 checkPortal 已透過 Access Token 或 Dev Impersonation 完成使用者驗證
 *   （auth_method = access_token / dev_impersonation 且 Auth::check() 為 true），
 *   則自動跳過 Sanctum 驗證，直接放行。
 *
 * 用法：
 *   middleware('checkSanctum')
 *
 * 典型路由堆疊：
 *   checkPortal:api → checkSanctum → requirePortalRole:api
 */
class CheckSanctum
{
    public function handle(Request $request, Closure $next)
    {
        // 旁路：checkPortal 已完成使用者驗證，跳過 Sanctum
        $authMethod = $request->attributes->get('auth_method');

        if (in_array($authMethod, ['access_token', 'dev_impersonation']) && Auth::check()) {
            return $next($request);
        }

        // Sanctum Bearer Token 驗證
        $guard = Auth::guard('sanctum');

        if ($guard->check()) {
            $user = $guard->user();
            Auth::setUser($user);
            $request->setUserResolver(fn () => $user);

            return $next($request);
        }

        return response()->json(['error' => 'Unauthenticated.'], 401);
    }
}
