<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\IpUtils;

/**
 * DevLogin 開發登入繞過機制
 *
 * 提供 CLI / 自動化測試 / AI Agent 短路登入通道。登入後 target user 即為
 * 系統當前登入者（無模擬層），所有後續行為跟正規登入完全等價。
 *
 *   POST /dev/login (form: email + token)
 *     → 通過 4 道閘 → Auth::login + 回傳 JSON
 *
 * 4 道閘：
 *   1. APP_ENV=local
 *   2. config('auth.dev_login.token') 非空（即 .env DEV_LOGIN_TOKEN）
 *   3. 來源 IP ∈ config('auth.dev_login.allowed_ips')（loopback + RFC1918 + IPv6 ULA）
 *   4. POST 帶的 token 與 .env 比對通過（hash_equals 防 timing attack）
 *
 * 任一閘不過 → 404 不洩漏 endpoint 是否啟用；閘 4 token 不對 → 403。
 *
 * 詳：docs/common/10027_DevLogin開發登入繞過機制.md
 */
class DevLoginController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        // 閘 1：環境
        if (!app()->environment('local')) {
            abort(404);
        }

        // 閘 2：.env 必設
        $expected = config('auth.dev_login.token');
        if (empty($expected)) {
            abort(404);
        }

        // 閘 3：IP 白名單
        $allowedRanges = config('auth.dev_login.allowed_ips', []);
        if (!IpUtils::checkIp($request->ip(), $allowedRanges)) {
            Log::warning('dev login: rejected, IP outside allowlist', [
                'ip' => $request->ip(),
            ]);
            abort(404);
        }

        // 閘 4：token 比對
        $token = (string) $request->input('token', '');
        if (!hash_equals($expected, $token)) {
            Log::warning('dev login: token mismatch', [
                'ip'    => $request->ip(),
                'email' => $request->input('email'),
            ]);
            abort(403, 'Invalid token');
        }

        // 取 user
        $email = (string) $request->input('email', '');
        if ($email === '') {
            abort(422, 'email is required');
        }

        $user = User::where('email', $email)->first();
        if (!$user) {
            abort(404, "User not found: {$email}");
        }

        if ($user->roles->isEmpty()) {
            // 不擋 — dev 端有時就是要測零角色帳號的行為
            Log::warning('dev login: target user has no roles', [
                'user_id' => $user->id,
                'email'   => $email,
            ]);
        }

        Auth::login($user, remember: true);
        $request->session()->regenerate();

        Log::warning('dev login used', [
            'user_id' => $user->id,
            'email'   => $email,
            'ip'      => $request->ip(),
            'roles'   => $user->roles->pluck('name')->all(),
        ]);

        return response()->json([
            'success' => true,
            'user'    => [
                'id'       => $user->id,
                'email'    => $user->email,
                'name'     => $user->name,
                'username' => $user->username,
                'roles'    => $user->roles->pluck('name')->all(),
            ],
        ]);
    }
}
