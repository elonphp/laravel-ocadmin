<?php

namespace App\Portals\Ocadmin\Core\Controllers;

use App\Portals\Ocadmin\Core\Contracts\Auth\AuthDriver;
use Illuminate\Http\Request;

/**
 * 認證入口 Controller
 *
 * 本 Controller 不直接處理認證邏輯，全部委派給 inject 進來的 {@see AuthDriver}。
 * 具體 driver 由 {@see \App\Portals\Ocadmin\Core\Providers\OcadminServiceProvider}
 * 依 `config/portals.php` 的 `auth_driver` 設定 bind。
 *
 * 切換 driver：`.env` 設 `OCADMIN_AUTH_DRIVER=oauth`（或其他 driver），
 * 不必動 routes，不必動 Controller。
 */
class LoginController extends OcadminController
{
    public function __construct(
        private AuthDriver $driver,
    ) {}

    public function showLoginForm(Request $request)
    {
        return $this->driver->showLoginForm($request);
    }

    public function login(Request $request)
    {
        return $this->driver->login($request);
    }

    public function logout(Request $request)
    {
        return $this->driver->logout($request);
    }
}
