<?php

namespace App\Portals\Ocadmin\Core\Controllers;

use App\Models\Acl\SystemUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends OcadminController
{
    public function showLoginForm()
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

            SystemUser::where('user_id', Auth::id())
                ->whereNull('revoked_at')
                ->update(['last_login_at' => now()]);

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
