<?php

namespace App\Http\Middleware;

use App\Models\System\Setting;
use App\Models\User;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * 統一閘道驗證 Middleware（網路層 + 閘道層 + 身份層）
 *
 * 本 Middleware 負責前三層驗證：
 *   第一層 — 網路層：Per-Portal IP 限制（選用，僅內部限定 Portal 啟用）
 *   第二層 — 閘道層：X-API-KEY（驗證請求來自我方授權的 App）
 *   第三層 — 身份層：X-ACCESS-TOKEN / X-DEV-KEY + X-DEV-USER-ID / X-DEV-USER-EMAIL / session
 *            Access Token 驗證時會一併檢查 token 的 abilities（存取範圍），
 *            確認該 token 是否被允許存取此 Portal。
 *            X-DEV-USER-ID / X-DEV-USER-EMAIL 僅限非 production 環境，需搭配有效 X-DEV-KEY。
 *            X-ACCESS-TOKEN、X-DEV-USER-ID、X-DEV-USER-EMAIL 三者互斥，同時存在多個時回傳 400 錯誤。
 *
 * 權限層（使用者能做什麼）不在此 Middleware 處理，由後續機制負責：
 *   - requirePortalRole  — 角色前綴檢查（如 admin.*）
 *   - Controller 內部    — 細粒度權限判斷
 *
 * 用法：
 *   middleware('checkPortal:api')    → API 模式（失敗回 JSON）
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

        // ── 第一層：網路層（Per-Portal IP 限制）──
        $ipResponse = $this->checkIpRestriction($request, $portal, $mode, $config);
        if ($ipResponse) {
            return $ipResponse;
        }

        // ── 第二層：閘道層（X-API-KEY）──
        $apiKeyResponse = $this->checkApiKey($request, $mode, $config);
        if ($apiKeyResponse) {
            return $apiKeyResponse;
        }

        // ── 第三層：身份層（X-ACCESS-TOKEN / X-DEV-KEY + X-DEV-USER-* / session）──
        return $this->checkIdentity($request, $next, $portal, $mode, $config);
    }

    // ========================================================================
    // 第一層：網路層（Per-Portal IP 限制）
    // ========================================================================

    /**
     * Per-Portal IP 限制
     *
     * ip_restrict=false（預設）→ 跳過 IP 檢查
     * ip_restrict=true + 白名單為空 → 不限制（避免誤鎖）
     * ip_restrict=true + 白名單有值 → 比對 IP / CIDR
     *
     * 白名單來源：settings 資料表 → {portal}_allowed_ips（group=portal）
     *
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse|null
     */
    protected function checkIpRestriction(Request $request, string $portal, string $mode, array $config)
    {
        if (empty($config['ip_restrict'])) {
            return null;
        }

        $setting = Setting::where('code', "{$portal}_allowed_ips")
            ->where('group', 'portal')
            ->first();

        $allowedIps = $setting ? trim($setting->value ?? '') : '';

        if ($allowedIps === '') {
            return null; // 白名單為空 → 不限制（避免誤鎖）
        }

        $clientIp = $request->ip();
        $ipList = array_map('trim', explode(',', $allowedIps));

        foreach ($ipList as $allowed) {
            if ($allowed !== '' && $this->ipMatchesCidr($clientIp, $allowed)) {
                return null; // IP 在白名單中
            }
        }

        // IP 不在白名單
        if ($mode === 'web') {
            return redirect($config['redirect_url'] ?? '/', 302);
        }

        return response()->json(['error' => 'Access denied: IP not allowed.'], 403);
    }

    /**
     * 比對 IP 是否匹配精確 IP 或 CIDR 範圍
     *
     * 支援格式：精確 IP（127.0.0.1）、CIDR（10.0.0.0/8）、IPv6
     */
    protected function ipMatchesCidr(string $ip, string $cidr): bool
    {
        if (!str_contains($cidr, '/')) {
            return $ip === $cidr;
        }

        [$subnet, $bits] = explode('/', $cidr, 2);
        $bits = (int) $bits;

        $ipBin = inet_pton($ip);
        $subnetBin = inet_pton($subnet);

        if ($ipBin === false || $subnetBin === false) {
            return false;
        }

        $byteLen = strlen($ipBin);

        // 建構遮罩
        $mask = str_repeat("\xff", intdiv($bits, 8));
        $remainder = $bits % 8;
        if ($remainder !== 0) {
            $mask .= chr(0xff << (8 - $remainder) & 0xff);
        }
        $mask = str_pad($mask, $byteLen, "\x00");

        return ($ipBin & $mask) === ($subnetBin & $mask);
    }

    // ========================================================================
    // 第二層：閘道層（X-API-KEY）
    // ========================================================================

    /**
     * API 模式：X-API-KEY 必須存在且正確，否則 401
     * Web 模式：本地/私有 IP 放行，其他需 X-API-KEY
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|null
     */
    protected function checkApiKey(Request $request, string $mode, array $config)
    {
        $apiKey = $request->header('X-API-KEY');
        $configApiKey = $config['api_key'] ?? '';

        if ($mode === 'api') {
            // API 模式：X-API-KEY 必須存在且正確
            if (empty($configApiKey) || empty($apiKey) || !hash_equals($configApiKey, $apiKey)) {
                return response()->json(['error' => 'Unauthorized access.'], 401);
            }

            return null;
        }

        // Web 模式：本地/私有 IP 放行
        if ($this->isLocalOrPrivateIp($request)) {
            return null;
        }

        // Web 模式：非本地 IP → 需 X-API-KEY
        if (!empty($configApiKey) && !empty($apiKey) && hash_equals($configApiKey, $apiKey)) {
            return null;
        }

        return redirect($config['redirect_url'] ?? '/', 302);
    }

    /**
     * 判斷是否為本地或私有 IP
     */
    protected function isLocalOrPrivateIp(Request $request): bool
    {
        $ip = $request->ip();

        if ($ip === '127.0.0.1' || $ip === '::1') {
            return true;
        }

        // 私有 IP 範圍（10.x, 172.16-31.x, 192.168.x）
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_RES_RANGE) === false
            && filter_var($ip, FILTER_VALIDATE_IP) !== false) {
            return true;
        }

        return false;
    }

    // ========================================================================
    // 第三層：身份層（User Authentication）
    // ========================================================================

    /**
     * 身份層檢查
     *
     * 前提：X-ACCESS-TOKEN、X-DEV-USER-ID、X-DEV-USER-EMAIL 三者互斥
     *
     * 1. X-ACCESS-TOKEN   → authenticateByAccessToken()
     * 2. X-DEV-USER-ID    → authenticateByDevUser()（非 production + 有效 X-DEV-KEY）
     * 3. X-DEV-USER-EMAIL → authenticateByDevUser()（非 production + 有效 X-DEV-KEY）
     * 4. Web session 已登入 → 放行
     * 5. API 模式 + 未帶身份 header → 放行（由下游 checkSanctum 驗證 Bearer Token）
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

        // X-ACCESS-TOKEN → 驗證通過或 401（不會 fall through）
        if ($hasAccessToken) {
            return $this->authenticateByAccessToken($request, $next, $portal);
        }

        // X-DEV-USER-ID / X-DEV-USER-EMAIL → 需搭配有效 X-DEV-KEY
        if ($hasDevUserId || $hasDevUserEmail) {
            return $this->authenticateByDevUser($request, $next, $config);
        }

        // Web session（已透過帳號密碼登入）
        if ($mode === 'web' && Auth::check()) {
            return $next($request);
        }

        // API 模式：未提供任何身份 header → 放行至下游 checkSanctum 驗證 Bearer Token
        if ($mode === 'api' && $identityCount === 0) {
            return $next($request);
        }

        return $this->deny($mode, $config);
    }

    /**
     * X-ACCESS-TOKEN 驗證
     *
     * SHA-256 比對 + 到期日檢查 + 存取範圍檢查（abilities 含 portal:{portal}）
     * 驗證通過：Auth::setUser() + auth_method=access_token
     * 驗證失敗：回傳 401（不會 fall through 至其他驗證方式）
     */
    protected function authenticateByAccessToken(Request $request, Closure $next, string $portal)
    {
        $accessToken = $request->header('X-ACCESS-TOKEN');

        if (!str_contains($accessToken, '|')) {
            return response()->json(['error' => 'Invalid access token format.'], 401);
        }

        [$tokenId, $plainText] = explode('|', $accessToken, 2);

        $tokenRecord = DB::table('personal_access_tokens')->where('id', $tokenId)->first();

        if (!$tokenRecord) {
            return response()->json(['error' => 'Invalid access token.'], 401);
        }

        if (!hash_equals($tokenRecord->token, hash('sha256', $plainText))) {
            return response()->json(['error' => 'Invalid access token.'], 401);
        }

        if ($tokenRecord->expires_at && Carbon::parse($tokenRecord->expires_at)->isPast()) {
            return response()->json(['error' => 'Access token has expired.'], 401);
        }

        // 檢查 token 存取範圍（此 token 是否被允許存取此 Portal）
        $abilities = json_decode($tokenRecord->abilities, true) ?: [];
        if (!in_array('*', $abilities) && !in_array("portal:{$portal}", $abilities)) {
            return response()->json(['error' => 'Access token does not have access to this portal.'], 401);
        }

        $user = User::find($tokenRecord->tokenable_id);
        if (!$user) {
            return response()->json(['error' => 'Token owner not found.'], 401);
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
     * X-DEV-USER-ID / X-DEV-USER-EMAIL 驗證（Dev Impersonation）
     *
     * 僅限非 production 環境，需搭配有效 X-DEV-KEY（獨立於 X-API-KEY）
     * 驗證通過：Auth::setUser() + auth_method=dev_impersonation
     * User 不存在時：回傳 400 + 明確錯誤訊息（含查詢值，方便 debug）
     */
    protected function authenticateByDevUser(Request $request, Closure $next, array $config)
    {
        // 僅限非 production 環境
        if (app()->environment('production')) {
            return response()->json(['error' => 'Dev impersonation is not available in production.'], 403);
        }

        // 必須搭配有效的 X-DEV-KEY
        $devKey = $request->header('X-DEV-KEY');
        $configDevKey = $config['dev_key'] ?? '';

        if (empty($configDevKey) || empty($devKey) || !hash_equals($configDevKey, $devKey)) {
            return response()->json(['error' => 'Invalid or missing X-DEV-KEY.'], 401);
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

    // ========================================================================
    // 共用
    // ========================================================================

    protected function deny(string $mode, array $config)
    {
        if ($mode === 'web') {
            return redirect($config['redirect_url'] ?? '/', 302);
        }

        return response()->json(['error' => 'Unauthorized access.'], 401);
    }
}
