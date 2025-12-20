<?php

namespace Elonphp\LaravelOcadminModules\Core\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Elonphp\LaravelOcadminModules\Support\LocaleHelper;
use Symfony\Component\HttpFoundation\Response;

class RedirectToLocale
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $config = config('ocadmin.localization');
        $urlConfig = $config['url'] ?? [];

        // If URL localization is disabled or prefix is disabled
        if (!($urlConfig['enabled'] ?? true) || !($urlConfig['prefix'] ?? true)) {
            return $next($request);
        }

        $path = $request->path();
        $prefix = config('ocadmin.prefix', 'ocadmin');
        $urlMapping = $config['url_mapping'] ?? [];
        $validLocales = array_keys($urlMapping);

        // Check if already has locale prefix
        $firstSegment = strtolower($request->segment(1) ?? '');
        if (in_array($firstSegment, $validLocales)) {
            return $next($request);
        }

        // Check if this is an ocadmin route
        if (!preg_match("#^{$prefix}(/|$)#", $path)) {
            return $next($request);
        }

        // Hide default locale mode: don't redirect
        if ($urlConfig['hide_default'] ?? false) {
            App::setLocale($config['default'] ?? 'zh_Hant');
            return $next($request);
        }

        // Redirect to URL with locale
        $locale = $this->detectLocale($request, $config);
        $urlLocale = LocaleHelper::toUrl($locale);
        $queryString = $request->getQueryString();
        $redirectUrl = "/{$urlLocale}/{$path}";

        if ($queryString) {
            $redirectUrl .= '?' . $queryString;
        }

        return redirect($redirectUrl);
    }

    /**
     * Detect user's preferred locale.
     */
    protected function detectLocale(Request $request, array $config): string
    {
        // 1. From session
        if ($locale = session('ocadmin_locale')) {
            return $locale;
        }

        // 2. From browser preference
        $supported = $config['supported'] ?? [];
        $browserLocale = $request->getPreferredLanguage($supported);

        if ($browserLocale && in_array($browserLocale, $supported)) {
            return $browserLocale;
        }

        // 3. Use default
        return $config['default'] ?? 'zh_Hant';
    }
}
