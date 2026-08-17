<?php

namespace App\Portals\Ocadmin\Core\Contracts\Auth;

use Illuminate\Http\Request;

/**
 * 認證 driver 契約
 *
 * 切換 driver 透過 config/portals.php 的 `auth_driver` 設定（'sanctum' / 'oauth' / ...）。
 * 由 OcadminServiceProvider 依此 bind 對應實作。LoginController 永遠 inject 本介面，
 * 不直接引用具體 driver。
 *
 * @see \App\Portals\Ocadmin\Core\Drivers\Auth\SanctumAuthDriver
 * @see \App\Portals\Ocadmin\Core\Drivers\Auth\OauthAuthDriver
 */
interface AuthDriver
{
    /**
     * 顯示 / 進入登入流程入口
     *
     * - Sanctum：return view（顯示帳號密碼表單）
     * - OAuth：return redirect（重導到 provider authorize URL）
     */
    public function showLoginForm(Request $request);

    /**
     * 處理登入動作
     *
     * - Sanctum：處理 POST /login 的表單提交，驗證後建立 session
     * - OAuth：處理 callback（如不走 form POST）。OAuth driver 可選擇於此處實作，
     *   或於 OauthAuthDriver 額外暴露 `handleCallback()` 由衍生專案自行掛 route。
     */
    public function login(Request $request);

    /**
     * 處理登出
     *
     * - Sanctum：Auth::logout + session invalidate + token regenerate
     * - OAuth：RP-initiated logout 打 provider，再清本地 session
     */
    public function logout(Request $request);
}
