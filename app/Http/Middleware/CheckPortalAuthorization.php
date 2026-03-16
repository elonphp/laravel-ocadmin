<?php

namespace App\Http\Middleware;

use App\Models\User;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * 統一閘道驗證 Middleware（網路層 + 身份層）
 *
 * 本 Middleware 負責前兩層驗證：
 *   Step 1 — 網路層：IP 白名單 或 X-API-KEY（請求來源是否合法）
 *   Step 2 — 身份層：session / X-ACCESS-TOKEN / X-DEV-USER-ID / X-DEV-USER-EMAIL（使用者是誰）
 *            Access Token 驗證時會一併檢查 token 的 abilities（存取範圍），
 *            確認該 token 是否被允許存取此 Portal。
 *            X-DEV-USER-ID / X-DEV-USER-EMAIL 僅限非 production 環境，需搭配有效 X-API-KEY。
 *            X-ACCESS-TOKEN、X-DEV-USER-ID、X-DEV-USER-EMAIL 三者互斥，同時存在多個時回傳錯誤。
 *
 * 權限層（使用者能做什麼）不在此 Middleware 處理，由後續機制負責：
 *   - requirePortalRole  — 角色前綴檢查（如 admin.*）
 *   - Controller 內部    — 細粒度權限判斷
 *
 * 用法：
 *   middleware('checkPortal:api')    → API 模式（失敗回 401 JSON）
 *   middleware('checkPortal:admin')  → Web 模式（session 放行 + 失敗 redirect）
 *
 * 設定：config('vars.portal_keys.{portal}')
 */
class CheckPortalAuthorization
{
    public function handle(Request $request, Closure $next, string $portal)
    {
        $config = config("vars.portal_keys.{$portal}");

        if (empty($config)) {
            return response()->json(['error' => "Unknown portal: {$portal}"], 500);
        }

        $mode = $config['mode'] ?? 'api';

        // ── Step 1：網路層（IP 白名單 或 X-API-KEY）──
        if (!$this->checkNetwork($request, $config)) {
            return $this->deny($mode, $config);
        }

        // ── Step 2：身份層（X-ACCESS-TOKEN 或 Web session）──
        return $this->checkIdentity($request, $next, $portal, $mode, $config);
    }

    /**
     * Step 1：網路層檢查
     * IP 白名單 或 X-API-KEY，擇一通過即可
     */
    protected function checkNetwork(Request $request, array $config): bool
    {
        // IP 白名單
        if ($this->isIpAllowed($request)) {
            return true;
        }

        // X-API-KEY
        $apiKey = $request->header('X-API-KEY');
        $configApiKey = $config['api_key'] ?? '';

        if (!empty($configApiKey) && !empty($apiKey) && hash_equals($configApiKey, $apiKey)) {
            return true;
        }

        return false;
    }

    /**
     * Step 2：身份層檢查
     * X-ACCESS-TOKEN / X-DEV-USER-ID / X-DEV-USER-EMAIL / Web session，擇一通過即可
     * X-ACCESS-TOKEN、X-DEV-USER-ID、X-DEV-USER-EMAIL 三者互斥，同時存在多個時回傳錯誤
     */
    protected function checkIdentity(Request $request, Closure $next, string $portal, string $mode, array $config)
    {
        $hasAccessToken  = !empty($request->header('X-ACCESS-TOKEN'));
        $hasDevUserId    = !empty($request->header('X-DEV-USER-ID'));
        $hasDevUserEmail = !empty($request->header('X-DEV-USER-EMAIL'));

        // 互斥檢查：三者只能擇一
        $identityCount = (int) $hasAccessToken + (int) $hasDevUserId + (int) $hasDevUserEmail;
        if ($identityCount > 1) {
            return response()->json([
                'error' => 'X-ACCESS-TOKEN, X-DEV-USER-ID, and X-DEV-USER-EMAIL are mutually exclusive. Please use only one.',
            ], 400);
        }

        // X-ACCESS-TOKEN
        if ($hasAccessToken) {
            $result = $this->authenticateByAccessToken($request, $next, $portal);
            if ($result) {
                return $result;
            }
        }

        // X-DEV-USER-ID（僅限非 production，需搭配有效 X-API-KEY）
        if ($hasDevUserId) {
            $result = $this->authenticateByDevUser($request, $next, $config);
            if ($result) {
                return $result;
            }
        }

        // X-DEV-USER-EMAIL（僅限非 production，需搭配有效 X-API-KEY）
        if ($hasDevUserEmail) {
            $result = $this->authenticateByDevUser($request, $next, $config);
            if ($result) {
                return $result;
            }
        }

        // Web session（已透過帳號密碼登入）
        if ($mode === 'web' && auth()->check()) {
            return $next($request);
        }

        return $this->deny($mode, $config);
    }

    /**
     * X-ACCESS-TOKEN 驗證（正式機制）
     * SHA-256 比對 + 到期日檢查 + 存取範圍檢查
     */
    protected function authenticateByAccessToken(Request $request, Closure $next, string $portal)
    {
        $accessToken = $request->header('X-ACCESS-TOKEN');

        if (!str_contains($accessToken, '|')) {
            return null;
        }

        [$tokenId, $plainText] = explode('|', $accessToken, 2);

        $tokenRecord = DB::table('personal_access_tokens')->where('id', $tokenId)->first();

        if (!$tokenRecord
            || !hash_equals($tokenRecord->token, hash('sha256', $plainText))
            || ($tokenRecord->expires_at && Carbon::parse($tokenRecord->expires_at)->isPast())
        ) {
            return null;
        }

        // 檢查 token 存取範圍（此 token 是否被允許存取此 portal）
        $abilities = json_decode($tokenRecord->abilities, true) ?: [];

        if (!in_array('*', $abilities) && !in_array("portal:{$portal}", $abilities)) {
            return null;
        }

        $user = User::find($tokenRecord->tokenable_id);

        if (!$user) {
            return null;
        }

        Auth::setUser($user);
        $request->setUserResolver(fn () => $user);
        $request->attributes->set('auth_method', 'access_token');
        DB::table('personal_access_tokens')
            ->where('id', $tokenId)
            ->update(['last_used_at' => now()]);

        return $next($request);
    }

    /**
     * X-DEV-USER-ID / X-DEV-USER-EMAIL 驗證（開發便利機制）
     * 僅限非 production 環境，需搭配有效 X-API-KEY
     */
    protected function authenticateByDevUser(Request $request, Closure $next, array $config)
    {
        // 僅限非 production 環境
        if (app()->environment('production')) {
            return null;
        }

        // 必須搭配有效的 X-API-KEY
        $apiKey = $request->header('X-API-KEY');
        $configApiKey = $config['api_key'] ?? '';

        if (empty($configApiKey) || empty($apiKey) || !hash_equals($configApiKey, $apiKey)) {
            return null;
        }

        // 依 header 查找使用者
        $devUserId    = $request->header('X-DEV-USER-ID');
        $devUserEmail = $request->header('X-DEV-USER-EMAIL');

        if ($devUserId) {
            $user = User::find($devUserId);
            $hint = "X-DEV-USER-ID: User #{$devUserId} not found.";
        } else {
            $user = User::where('email', $devUserEmail)->first();
            $hint = "X-DEV-USER-EMAIL: User '{$devUserEmail}' not found.";
        }

        if (!$user) {
            return response()->json(['error' => $hint], 400);
        }

        Auth::setUser($user);
        $request->setUserResolver(fn () => $user);
        $request->attributes->set('auth_method', 'dev_impersonation');

        return $next($request);
    }

    protected function isIpAllowed(Request $request): bool
    {
        $ip = $request->ip();

        // 本地 IP 或私有 IP 直接放行
        if ($ip === '127.0.0.1' || $ip === '::1') {
            return true;
        }

        // 私有 IP 範圍
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_RES_RANGE) === false
            && filter_var($ip, FILTER_VALIDATE_IP) !== false) {
            return true;
        }

        return false;
    }

    protected function deny(string $mode, array $config)
    {
        if ($mode === 'web') {
            $redirectUrl = $config['redirect_url'] ?? '/';
            return redirect($redirectUrl, 302);
        }

        return response()->json(['error' => 'Unauthorized access.'], 401);
    }
}
