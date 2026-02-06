<?php

namespace App\Portals\ESS\Core\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleEssInertiaRequests extends Middleware
{
    protected $rootView = 'ess::ess';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            'locale'  => app()->getLocale(),
            'locales' => config('localization.locale_names'),
            'flash'   => [
                'success' => fn () => $request->session()->get('success'),
                'error'   => fn () => $request->session()->get('error'),
            ],
            'menu' => $this->buildMenu(),
        ];
    }

    protected function buildMenu(): array
    {
        return [
            ['name' => '儀表板',   'href' => route('lang.ess.dashboard'),    'icon' => 'home'],
            ['name' => '個人資料', 'href' => route('lang.ess.profile.edit'), 'icon' => 'user'],
        ];
    }
}
