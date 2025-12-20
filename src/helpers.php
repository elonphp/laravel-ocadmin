<?php

use Elonphp\LaravelOcadminModules\Support\LocaleHelper;

if (!function_exists('ocadmin_route')) {
    /**
     * Generate Ocadmin route URL (handles locale automatically)
     *
     * Since routes are registered with locale prefix via LocaleHelper::setLocale(),
     * the URL already contains the correct locale prefix. No need to add locale parameter.
     */
    function ocadmin_route(string $name, array $parameters = []): string
    {
        return route('ocadmin.' . $name, $parameters);
    }
}

if (!function_exists('ocadmin_locale')) {
    /**
     * Get current locale (internal format)
     */
    function ocadmin_locale(): string
    {
        return app()->getLocale();
    }
}

if (!function_exists('ocadmin_url_locale')) {
    /**
     * Get current locale (URL format)
     */
    function ocadmin_url_locale(): string
    {
        return LocaleHelper::toUrl(app()->getLocale());
    }
}

if (!function_exists('ocadmin_switch_url')) {
    /**
     * Generate URL for switching to another locale
     */
    function ocadmin_switch_url(string $locale): string
    {
        return LocaleHelper::switchUrl($locale);
    }
}

if (!function_exists('ocadmin_model')) {
    /**
     * Get model class from config
     */
    function ocadmin_model(string $key): string
    {
        $models = config('ocadmin.models', []);

        if (isset($models[$key])) {
            return $models[$key];
        }

        // Default model mapping
        $defaults = [
            'user' => \App\Models\User::class,
            'log' => \Elonphp\LaravelOcadminModules\Models\Log::class,
            'setting' => \Elonphp\LaravelOcadminModules\Models\Setting::class,
            'role' => \Spatie\Permission\Models\Role::class,
            'permission' => \Spatie\Permission\Models\Permission::class,
        ];

        return $defaults[$key] ?? throw new InvalidArgumentException("Unknown model key: {$key}");
    }
}

if (!function_exists('ocadmin_asset')) {
    /**
     * Generate URL for Ocadmin asset
     */
    function ocadmin_asset(string $path): string
    {
        return asset('vendor/ocadmin/' . ltrim($path, '/'));
    }
}
