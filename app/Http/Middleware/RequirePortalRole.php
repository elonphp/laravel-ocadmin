<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequirePortalRole
{
    /**
     * 檢查使用者是否擁有指定 Portal 前綴的角色。
     *
     * 用法：middleware('requirePortalRole:admin')
     *
     * @param string $prefix 角色前綴（如 admin, ess）
     */
    public function handle(Request $request, Closure $next, string $prefix): Response
    {
        $user = $request->user();

        if (!$user || !$user->hasPortalRole($prefix)) {
            if ($request->ajax() || $request->expectsJson()) {
                abort(401, '未授權');
            }

            return redirect()->guest('/admin/login');
        }

        return $next($request);
    }
}
