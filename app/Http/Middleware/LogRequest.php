<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Repositories\LogFileRepository;

class LogRequest
{
    public function handle(Request $request, Closure $next): Response
    {
        // 記錄所有非 GET 請求（POST, PUT, PATCH, DELETE 等）
        if ($request->method() != 'GET') {
            (new LogFileRepository)->logRequest();
        }

        return $next($request);
    }
}
