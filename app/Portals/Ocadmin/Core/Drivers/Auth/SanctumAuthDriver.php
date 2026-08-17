<?php

namespace App\Portals\Ocadmin\Core\Drivers\Auth;

use App\Portals\Ocadmin\Core\Contracts\Auth\AuthDriver;
use App\Services\UserDeviceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

/**
 * Sanctum 帳密 + session 認證 driver
 *
 * ocadmin 範本預設 driver，使用 Laravel 內建 web guard + Sanctum SPA session。
 * 登入流程：表單 POST → Auth::attempt → session regenerate → UserDevice 記錄 → redirect intended。
 */
class SanctumAuthDriver implements AuthDriver
{
    public function __construct(
        private UserDeviceService $deviceService,
    ) {}

    public function showLoginForm(Request $request)
    {
        return view('ocadmin::auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $this->deviceService->recordDevice($request, Auth::user());

            return redirect()->intended(route('lang.ocadmin.dashboard'));
        }

        throw ValidationException::withMessages([
            'email' => __('auth.failed'),
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('lang.ocadmin.login.form');
    }
}
