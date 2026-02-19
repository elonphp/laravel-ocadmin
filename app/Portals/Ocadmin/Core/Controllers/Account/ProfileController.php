<?php

namespace App\Portals\Ocadmin\Core\Controllers\Account;

use App\Portals\Ocadmin\Core\Controllers\OcadminController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ProfileController extends OcadminController
{
    protected function setLangFiles(): array
    {
        return ['common', 'account/profile'];
    }

    protected function setBreadcrumbs(): void
    {
        $this->breadcrumbs = [
            (object)[
                'text' => $this->lang->text_home,
                'href' => route('lang.ocadmin.dashboard'),
            ],
            (object)[
                'text' => $this->lang->heading_title,
                'href' => route('lang.ocadmin.account.profile'),
            ],
        ];
    }

    public function edit(): View
    {
        $user = auth()->user();

        $data['lang'] = $this->lang;
        $data['breadcrumbs'] = $this->breadcrumbs;
        $data['user'] = $user;

        return view('ocadmin::account.profile', $data);
    }

    public function update(Request $request): JsonResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'first_name' => 'nullable|string|max:100',
            'last_name'  => 'nullable|string|max:100',
            'email'      => 'required|email|max:255|unique:users,email,' . $user->id,
        ]);

        if ($request->filled('password')) {
            $request->validate([
                'current_password' => 'required|string',
                'password'         => 'required|string|min:6|confirmed',
            ]);

            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => $this->lang->error_current_password,
                ]);
            }

            $validated['password'] = $request->password;
        }

        $user->update($validated);

        return response()->json([
            'success' => true,
            'message' => $this->lang->text_success_edit,
        ]);
    }
}
