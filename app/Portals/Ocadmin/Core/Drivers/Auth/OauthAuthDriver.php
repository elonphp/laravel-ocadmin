<?php

namespace App\Portals\Ocadmin\Core\Drivers\Auth;

use App\Portals\Ocadmin\Core\Contracts\Auth\AuthDriver;
use Illuminate\Http\Request;

/**
 * OAuth 2.0 / OIDC 認證 driver（骨架）
 *
 * ocadmin 範本提供本骨架但未實作 — 因為 OAuth 串接細節（authorize URL / token endpoint /
 * userinfo schema / RP-initiated logout）因 accounts-center 而異。商業專案啟用本 driver 時
 * （`.env` 設 `OCADMIN_AUTH_DRIVER=oauth`）需在自家專案：
 *
 * 1. 修改本檔（議題 5「Core/ 允許個別專案修改」原則）實作三個 method
 * 2. 或繼承本類別、覆寫 method，於 ServiceProvider 重綁 AuthDriver
 *
 * `[core-divergence]` 修改本檔時請在 commit message 標註並加 inline 註解，方便日後 backport 評估。
 *
 * @see \App\Portals\Ocadmin\Core\Contracts\Auth\AuthDriver
 */
class OauthAuthDriver implements AuthDriver
{
    public function showLoginForm(Request $request)
    {
        throw new \RuntimeException(
            'OauthAuthDriver::showLoginForm() 尚未實作。'
            . '請在本專案實作重導向到 OAuth provider 的 authorize URL，'
            . '參數讀取自 config/services.php 的 oauth section。'
        );
    }

    public function login(Request $request)
    {
        throw new \RuntimeException(
            'OauthAuthDriver::login() 尚未實作。'
            . 'OAuth 通常透過 provider callback 完成認證，而非 form POST；'
            . '若採 callback URL，可在本 driver 加 `handleCallback()` method '
            . '並於 routes 掛 `GET /auth/callback`。'
        );
    }

    public function logout(Request $request)
    {
        throw new \RuntimeException(
            'OauthAuthDriver::logout() 尚未實作。'
            . '請決定走 RP-initiated logout（打 provider end_session_endpoint）'
            . '或單純清本地 session，並在實作中執行。'
        );
    }
}
