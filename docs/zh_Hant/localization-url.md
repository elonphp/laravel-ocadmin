# 網址多語

本文件說明 URL 語系前綴機制。

---

## 概述

網址多語透過 URL 路徑識別語系：

```
https://example.com/{locale}/ocadmin/...

範例：
https://example.com/zh-hant/ocadmin/dashboard
https://example.com/en/ocadmin/dashboard
```

---

## 設定

### config/ocadmin.php

```php
'localization' => [
    // 網址多語設定
    'url' => [
        'enabled' => true,          // 啟用網址多語
        'prefix' => true,           // URL 顯示語系前綴
        'hide_default' => false,    // 隱藏預設語系前綴
    ],

    // 預設語系
    'default' => 'zh_Hant',

    // 支援的語系
    'supported' => ['zh_Hant', 'en'],

    // 語系名稱（用於切換選單）
    'names' => [
        'zh_Hant' => '繁體中文',
        'en' => 'English',
    ],

    // URL 格式與內部格式對應
    'url_mapping' => [
        'zh-hant' => 'zh_Hant',
        'en' => 'en',
    ],
],
```

### 設定說明

| 設定 | 說明 |
|------|------|
| `url.enabled` | 啟用網址多語功能 |
| `url.prefix` | URL 是否顯示語系前綴 |
| `url.hide_default` | 預設語系是否隱藏前綴 |
| `default` | 預設語系（內部格式） |
| `supported` | 支援的語系清單 |
| `names` | 語系顯示名稱 |
| `url_mapping` | URL 格式 → 內部格式對應 |

---

## URL 模式

### 模式一：全部顯示前綴（預設）

```php
'url' => [
    'prefix' => true,
    'hide_default' => false,
],
```

```
/zh-hant/ocadmin/dashboard  → 繁體中文
/en/ocadmin/dashboard       → English
/ocadmin/dashboard          → 重導向到 /zh-hant/ocadmin/dashboard
```

### 模式二：隱藏預設語系

```php
'url' => [
    'prefix' => true,
    'hide_default' => true,
],
```

```
/ocadmin/dashboard          → 繁體中文（預設，無前綴）
/en/ocadmin/dashboard       → English
```

### 模式三：無前綴

```php
'url' => [
    'prefix' => false,
],
```

```
/ocadmin/dashboard          → 從 session/cookie 判斷語系
```

---

## Middleware

### SetLocale Middleware

```php
<?php

namespace Elonphp\LaravelOcadminModules\Core\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\URL;
use Elonphp\LaravelOcadminModules\Support\LocaleHelper;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        $config = config('ocadmin.localization');
        $urlConfig = $config['url'] ?? [];

        // 未啟用網址多語
        if (!($urlConfig['enabled'] ?? true)) {
            App::setLocale($config['default'] ?? 'zh_Hant');
            return $next($request);
        }

        // 從 URL 取得語系
        $urlLocale = $request->route('locale');
        $locale = LocaleHelper::toInternal($urlLocale);

        // 驗證語系
        $supported = $config['supported'] ?? ['zh_Hant'];
        if (!in_array($locale, $supported)) {
            $locale = $config['default'] ?? 'zh_Hant';
        }

        // 設定應用程式語系
        App::setLocale($locale);

        // 設定 URL 預設參數
        if ($urlConfig['prefix'] ?? true) {
            URL::defaults(['locale' => LocaleHelper::toUrl($locale)]);
        }

        // 移除 locale 參數，避免傳遞給 Controller
        if ($request->route()) {
            $request->route()->forgetParameter('locale');
        }

        return $next($request);
    }
}
```

### RedirectToLocale Middleware

處理無語系前綴的請求：

```php
<?php

namespace Elonphp\LaravelOcadminModules\Core\Middleware;

use Closure;
use Illuminate\Http\Request;
use Elonphp\LaravelOcadminModules\Support\LocaleHelper;

class RedirectToLocale
{
    public function handle(Request $request, Closure $next)
    {
        $config = config('ocadmin.localization');
        $urlConfig = $config['url'] ?? [];

        // 未啟用或無前綴模式
        if (!($urlConfig['enabled'] ?? true) || !($urlConfig['prefix'] ?? true)) {
            return $next($request);
        }

        $path = $request->path();
        $prefix = config('ocadmin.prefix', 'ocadmin');
        $urlMapping = $config['url_mapping'] ?? [];
        $validLocales = array_keys($urlMapping);

        // 檢查是否已有語系前綴
        $firstSegment = strtolower($request->segment(1) ?? '');
        if (in_array($firstSegment, $validLocales)) {
            return $next($request);
        }

        // 檢查是否為 ocadmin 路由
        if (!preg_match("#^{$prefix}(/|$)#", $path)) {
            return $next($request);
        }

        // 隱藏預設語系模式：不重導向
        if ($urlConfig['hide_default'] ?? false) {
            App::setLocale($config['default'] ?? 'zh_Hant');
            return $next($request);
        }

        // 重導向到帶語系的 URL
        $locale = $this->detectLocale($request, $config);
        $urlLocale = LocaleHelper::toUrl($locale);
        $queryString = $request->getQueryString();
        $redirectUrl = "/{$urlLocale}/{$path}";

        if ($queryString) {
            $redirectUrl .= '?' . $queryString;
        }

        return redirect($redirectUrl);
    }

    protected function detectLocale(Request $request, array $config): string
    {
        // 1. 從 session 取得
        if ($locale = session('ocadmin_locale')) {
            return $locale;
        }

        // 2. 從瀏覽器偏好取得
        $supported = $config['supported'] ?? [];
        $browserLocale = $request->getPreferredLanguage($supported);

        if ($browserLocale && in_array($browserLocale, $supported)) {
            return $browserLocale;
        }

        // 3. 使用預設
        return $config['default'] ?? 'zh_Hant';
    }
}
```

---

## 路由定義

### 套件核心路由

```php
<?php
// routes/ocadmin.php

use Illuminate\Support\Facades\Route;
use Elonphp\LaravelOcadminModules\Support\LocaleHelper;

$config = config('ocadmin.localization');
$urlConfig = $config['url'] ?? [];

// 決定路由前綴
$prefix = config('ocadmin.prefix', 'ocadmin');
if ($urlConfig['prefix'] ?? true) {
    $prefix = '{locale}/' . $prefix;
}

Route::middleware(['web', 'ocadmin.locale'])
    ->prefix($prefix)
    ->as('ocadmin.')
    ->where(['locale' => '[a-zA-Z-]+'])
    ->group(function () {
        // Dashboard
        Route::get('/', [DashboardController::class, 'index'])
            ->name('dashboard');

        // 載入模組路由...
    });
```

### 模組路由

```php
<?php
// Modules/SystemLog/Routes/routes.php

use Illuminate\Support\Facades\Route;

// 不需處理語系，由套件自動處理
Route::prefix('system/logs')->name('system.logs.')->group(function () {
    Route::get('/', [LogController::class, 'index'])->name('index');
    Route::get('/form', [LogController::class, 'form'])->name('form');
});
```

### 完整路由

```
GET /{locale}/ocadmin/system/logs       → ocadmin.system.logs.index
GET /{locale}/ocadmin/system/logs/form  → ocadmin.system.logs.form
```

---

## 路由名稱

本套件使用 `ocadmin.` 為路由前綴（不使用 `lang.`）：

```php
// 路由名稱
ocadmin.dashboard
ocadmin.system.logs.index
ocadmin.access-control.roles.form

// 路由生成
route('ocadmin.dashboard')
route('ocadmin.system.logs.index')
```

---

## LocaleHelper

```php
<?php

namespace Elonphp\LaravelOcadminModules\Support;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\URL;

class LocaleHelper
{
    /**
     * URL 格式轉內部格式
     * zh-hant → zh_Hant
     */
    public static function toInternal(string $urlLocale): string
    {
        $mapping = config('ocadmin.localization.url_mapping', []);
        return $mapping[strtolower($urlLocale)] ?? $urlLocale;
    }

    /**
     * 內部格式轉 URL 格式
     * zh_Hant → zh-hant
     */
    public static function toUrl(string $locale): string
    {
        $mapping = config('ocadmin.localization.url_mapping', []);
        $flipped = array_flip($mapping);
        return $flipped[$locale] ?? strtolower(str_replace('_', '-', $locale));
    }

    /**
     * 取得當前語系（內部格式）
     */
    public static function current(): string
    {
        return App::getLocale();
    }

    /**
     * 取得當前語系（URL 格式）
     */
    public static function currentUrl(): string
    {
        return self::toUrl(self::current());
    }

    /**
     * 取得預設語系
     */
    public static function default(): string
    {
        return config('ocadmin.localization.default', 'zh_Hant');
    }

    /**
     * 取得支援的語系
     */
    public static function supported(): array
    {
        return config('ocadmin.localization.supported', ['zh_Hant']);
    }

    /**
     * 檢查語系是否支援
     */
    public static function isSupported(string $locale): bool
    {
        return in_array($locale, self::supported());
    }

    /**
     * 取得語系名稱
     */
    public static function name(string $locale): string
    {
        $names = config('ocadmin.localization.names', []);
        return $names[$locale] ?? $locale;
    }

    /**
     * 產生切換語系的 URL
     */
    public static function switchUrl(string $locale): string
    {
        $currentUrl = request()->url();
        $urlMapping = config('ocadmin.localization.url_mapping', []);
        $validLocales = array_keys($urlMapping);

        // 解析路徑
        $path = parse_url($currentUrl, PHP_URL_PATH);
        $segments = array_filter(explode('/', trim($path, '/')));

        // 移除當前語系前綴
        if (!empty($segments)) {
            $first = strtolower(reset($segments));
            if (in_array($first, $validLocales)) {
                array_shift($segments);
            }
        }

        // 組合新路徑
        $newPath = implode('/', $segments);
        $urlLocale = self::toUrl($locale);

        return url('/' . $urlLocale . ($newPath ? '/' . $newPath : ''));
    }

    /**
     * 取得所有語系的切換連結
     */
    public static function switchLinks(): array
    {
        $links = [];
        $current = self::current();

        foreach (self::supported() as $locale) {
            $links[$locale] = [
                'url' => self::switchUrl($locale),
                'url_locale' => self::toUrl($locale),
                'name' => self::name($locale),
                'is_current' => $locale === $current,
            ];
        }

        return $links;
    }
}
```

---

## Helper 函數

```php
<?php
// src/helpers.php

if (!function_exists('ocadmin_route')) {
    /**
     * 產生 Ocadmin 路由（自動處理語系）
     */
    function ocadmin_route(string $name, array $parameters = [], ?string $locale = null): string
    {
        $config = config('ocadmin.localization.url');

        // 啟用 URL 前綴時加入 locale 參數
        if ($config['prefix'] ?? true) {
            $locale = $locale ?? app()->getLocale();
            $parameters['locale'] = \Elonphp\LaravelOcadminModules\Support\LocaleHelper::toUrl($locale);
        }

        return route('ocadmin.' . $name, $parameters);
    }
}

if (!function_exists('ocadmin_locale')) {
    /**
     * 取得當前語系（內部格式）
     */
    function ocadmin_locale(): string
    {
        return app()->getLocale();
    }
}

if (!function_exists('ocadmin_url_locale')) {
    /**
     * 取得當前語系（URL 格式）
     */
    function ocadmin_url_locale(): string
    {
        return \Elonphp\LaravelOcadminModules\Support\LocaleHelper::toUrl(app()->getLocale());
    }
}

if (!function_exists('ocadmin_switch_url')) {
    /**
     * 產生切換語系的 URL
     */
    function ocadmin_switch_url(string $locale): string
    {
        return \Elonphp\LaravelOcadminModules\Support\LocaleHelper::switchUrl($locale);
    }
}
```

---

## Blade 使用

### 產生路由

```php
{{-- 自動帶入當前語系 --}}
<a href="{{ ocadmin_route('dashboard') }}">Dashboard</a>
<a href="{{ ocadmin_route('system.logs.index') }}">系統日誌</a>

{{-- 帶參數 --}}
<a href="{{ ocadmin_route('system.logs.form', ['id' => $log->id]) }}">詳情</a>

{{-- 指定語系 --}}
<a href="{{ ocadmin_route('dashboard', [], 'en') }}">English Dashboard</a>
```

### 語系切換元件

```php
{{-- resources/views/components/locale-switcher.blade.php --}}

@php
    $links = \Elonphp\LaravelOcadminModules\Support\LocaleHelper::switchLinks();
@endphp

<div class="dropdown">
    <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
        {{ $links[ocadmin_locale()]['name'] ?? ocadmin_locale() }}
    </button>
    <ul class="dropdown-menu">
        @foreach ($links as $locale => $link)
            <li>
                <a class="dropdown-item @if($link['is_current']) active @endif"
                   href="{{ $link['url'] }}">
                    {{ $link['name'] }}
                </a>
            </li>
        @endforeach
    </ul>
</div>
```

---

## 登入重導向

未登入時重導向到登入頁（保留語系）：

```php
// bootstrap/app.php 或 OcadminServiceProvider

$middleware->redirectGuestsTo(function ($request) {
    $config = config('ocadmin.localization');
    $urlConfig = $config['url'] ?? [];

    if (!($urlConfig['prefix'] ?? true)) {
        return route('ocadmin.login');
    }

    $segment = strtolower($request->segment(1) ?? '');
    $urlMapping = $config['url_mapping'] ?? [];

    $urlLocale = isset($urlMapping[$segment])
        ? $segment
        : \Elonphp\LaravelOcadminModules\Support\LocaleHelper::toUrl($config['default']);

    return "/{$urlLocale}/" . config('ocadmin.prefix') . "/login";
});
```

---

## 無語系 API 路由

API 路由不需要語系前綴：

```php
// 套件會自動註冊無語系的 API 路由群組

Route::middleware(['web', 'auth'])
    ->prefix(config('ocadmin.prefix') . '/api')
    ->as('ocadmin.api.')
    ->group(function () {
        Route::post('/upload', [UploadController::class, 'store'])->name('upload');
    });

// 路由：/ocadmin/api/upload
// 名稱：ocadmin.api.upload
```

---

*文件版本：v1.1 - 更新語系格式規範（底線取代橫線）*
