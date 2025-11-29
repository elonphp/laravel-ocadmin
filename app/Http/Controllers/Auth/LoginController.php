<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /**
     * 顯示登入頁面
     */
    public function showLoginForm()
    {
        return view('ocadmin::auth.login');
    }

    /**
     * 處理登入請求
     */
    public function login(Request $request)
    {
        $request->validate([
            'account' => 'required|string',
            'password' => 'required|string',
        ]);

        $account = $request->input('account');
        $password = $request->input('password');
        $remember = $request->boolean('remember');

        // 嘗試用 username 或 email 登入
        $credentials = filter_var($account, FILTER_VALIDATE_EMAIL)
            ? ['email' => $account, 'password' => $password]
            : ['username' => $account, 'password' => $password];

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            return redirect()->intended(route('ocadmin.dashboard'));
        }

        return back()->withErrors([
            'account' => '帳號或密碼錯誤',
        ])->withInput($request->only('account'));
    }

    /**
     * 登出
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('ocadmin.login.form');
    }
}
