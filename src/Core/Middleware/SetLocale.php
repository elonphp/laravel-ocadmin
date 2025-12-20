<?php

namespace Elonphp\LaravelOcadminModules\Core\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\URL;
use Elonphp\LaravelOcadminModules\Support\LocaleHelper;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $config = config('ocadmin.localization');
        $urlConfig = $config['url'] ?? [];

        // If URL localization is disabled
        if (!($urlConfig['enabled'] ?? true)) {
            App::setLocale($config['default'] ?? 'zh_Hant');
            return $next($request);
        }

        // Get locale from URL
        $urlLocale = $request->route('locale');
        $locale = LocaleHelper::toInternal($urlLocale);

        // Validate locale
        $supported = $config['supported'] ?? ['zh_Hant'];
        if (!in_array($locale, $supported)) {
            $locale = $config['default'] ?? 'zh_Hant';
        }

        // Set application locale
        App::setLocale($locale);

        // Set URL defaults
        if ($urlConfig['prefix'] ?? true) {
            URL::defaults(['locale' => LocaleHelper::toUrl($locale)]);
        }

        // Remove locale parameter to avoid passing it to controller
        if ($request->route()) {
            $request->route()->forgetParameter('locale');
        }

        return $next($request);
    }
}
