<?php

namespace App\Http\Middleware;

use App\Repositories\LogDatabaseRepository;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogRequest
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // 僅記錄非 GET 請求
        if ($request->method() !== 'GET') {
            try {
                LogDatabaseRepository::logRequest($response->getStatusCode());
            } catch (\Throwable $e) {
                Log::error('LogRequest middleware failed', [
                    'message' => $e->getMessage(),
                ]);
            }
        }

        return $response;
    }
}
