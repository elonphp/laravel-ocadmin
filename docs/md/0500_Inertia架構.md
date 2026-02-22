# ESS Portal — Inertia + React 架構

## 概述

本系統的 ESS（Employee Self-Service）Portal 採用 Inertia.js + React 架構，與 Ocadmin Portal（Blade + jQuery）共存於同一 Laravel 專案中。兩個 Portal 共用後端 Models、認證系統與多語機制，但前端技術棧完全獨立。

### 架構對比

| 項目 | Ocadmin Portal | ESS Portal |
|------|---------------|------------|
| 前端框架 | Blade + jQuery + Bootstrap 5 | React 19 + Inertia.js 2 |
| 路由前綴 | `/{locale}/admin` | `/{locale}/ess` |
| 視圖位置 | `app/Portals/Ocadmin/**/Views/` | `app/Portals/ESS/resources/js/Pages/` |
| Controller 位置 | `app/Portals/Ocadmin/{Core,Modules}/` | `app/Portals/ESS/{Core,Modules}/` |
| ServiceProvider | `OcadminServiceProvider` | `EssServiceProvider` |
| CSS 框架 | Bootstrap 5（自訂佈局） | Tailwind CSS 4 + DaisyUI 5 + Headless UI 2 |
| 建構工具 | Vite 7 + Laravel Mix 遺留 | Vite 7 + `@tailwindcss/vite` |
| AJAX 機制 | jQuery $.ajax / load() | Inertia router |

## 目錄結構（方案 C：後端 Modules 化 + 前端 Pages 鏡射）

ESS Portal 的所有檔案（後端 + 前端）完整收納在 `app/Portals/ESS/` 下，達到模組自包含。

### 架構原則

- **`Core/`**：僅放共用基礎設施（抽象基礎 Controller、Middleware、ServiceProvider）
- **`Modules/`**：各功能模組的 Controller，與 Ocadmin 的 Modules 結構對齊
- **`resources/js/Pages/`**：目錄結構「鏡射」後端 `Modules/` 路徑，Inertia 自動對應
- **`resources/js/Components/`**：跨模組共用元件（Layout、Form、UI 等）

```
app/Portals/ESS/
├── Core/                                      # 共用核心
│   ├── Controllers/
│   │   └── EssController.php                  # 抽象基礎 Controller
│   ├── Providers/
│   │   └── EssServiceProvider.php             # 路由、視圖命名空間註冊
│   └── Middleware/
│       └── HandleEssInertiaRequests.php       # ESS 專用 Inertia 中介層
│
├── Modules/                                   # 後端模組（與 Ocadmin 風格對齊）
│   ├── Auth/
│   │   └── LoginController.php                # 登入/登出
│   ├── Dashboard/
│   │   └── DashboardController.php
│   └── Hrm/
│       └── Employee/
│           └── ProfileController.php          # 個人資料
│       └── Attendance/                        # （未來擴充範例）
│           └── AttendanceController.php
│
├── resources/                                 # 前端（集中）
│   ├── js/
│   │   ├── ess.tsx                            # Inertia 進入點
│   │   ├── Pages/                             # 鏡射 Modules 結構
│   │   │   ├── Auth/
│   │   │   │   └── Login.tsx
│   │   │   ├── Dashboard.tsx
│   │   │   └── Hrm/
│   │   │       └── Employee/
│   │   │           ├── Edit.tsx               # 頁面元件
│   │   │           └── components/            # 該頁專屬小元件（小寫，不被 glob 當 Page）
│   │   ├── Components/                        # 跨模組共用元件
│   │   │   ├── Layout/
│   │   │   │   └── AuthenticatedLayout.tsx    # 已登入佈局（DaisyUI drawer）
│   │   │   ├── Form/                          # 共用表單元件（未來擴充）
│   │   │   └── UI/                            # 共用 UI 元件（未來擴充）
│   │   └── types/
│   │       └── index.d.ts                     # TypeScript 型別定義
│   ├── css/
│   │   └── ess.css                            # Tailwind v4 directives + DaisyUI
│   └── views/
│       └── ess.blade.php                      # Inertia root template
│
└── routes/
    └── ess.php
```

> **Inertia 頁面解析**：`ess.tsx` 使用 `import.meta.glob('./Pages/**/*.tsx')` 掃描所有頁面。Controller 的 `Inertia::render('Hrm/Employee/Edit')` 自動對應到 `./Pages/Hrm/Employee/Edit.tsx`。

### 與 Ocadmin 結構對照

```
app/Portals/
├── Ocadmin/                               # Blade Portal
│   ├── Core/
│   │   ├── Controllers/                   ← 共用基礎 Controller
│   │   ├── Views/                         ← 共用 Blade 視圖
│   │   ├── Providers/
│   │   └── Middleware/
│   ├── Modules/                           ← 功能模組（各含 Controller + Views）
│   │   └── Hrm/Employee/
│   └── routes/
│
└── ESS/                                   # Inertia Portal
    ├── Core/
    │   ├── Controllers/                   ← 抽象基礎 Controller
    │   ├── Providers/
    │   └── Middleware/
    ├── Modules/                           ← 功能模組（僅 Controller）
    │   └── Hrm/Employee/
    ├── resources/
    │   ├── js/Pages/                      ← React 頁面（鏡射 Modules 路徑）
    │   ├── js/Components/                 ← 共用 React 元件
    │   ├── css/
    │   └── views/                         ← Inertia root template
    └── routes/
```

兩個 Portal 遵循相同原則：
- **模組自包含**：所有相關檔案收在各自的 Portal 目錄下
- **Core vs Modules 分離**：Core 放共用基礎設施，Modules 放功能模組
- **命名對齊**：ESS 的 `Modules/Hrm/Employee/` 與 Ocadmin 的 `Modules/Hrm/Employee/` 結構一致，降低認知負擔

## 關鍵檔案說明

### EssServiceProvider

註冊路由、Blade 視圖命名空間（給 Inertia root template 用）：

```php
namespace App\Portals\ESS\Core\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class EssServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutes();
        $this->loadViews();
    }

    protected function loadRoutes(): void
    {
        Route::middleware(['web'])
            ->group(app_path('Portals/ESS/routes/ess.php'));
    }

    protected function loadViews(): void
    {
        // 註冊 root template 所在的 views 目錄
        View::addNamespace('ess', app_path('Portals/ESS/resources/views'));
    }
}
```

在 `bootstrap/providers.php` 中註冊：

```php
return [
    App\Providers\AppServiceProvider::class,
    App\Portals\Ocadmin\Core\Providers\OcadminServiceProvider::class,
    App\Portals\ESS\Core\Providers\EssServiceProvider::class,  // 新增
];
```

### ESS 路由（ess.php）

Controller 的 `use` 語句指向 `Modules/` 命名空間：

```php
use App\Portals\ESS\Modules\Auth\LoginController;
use App\Portals\ESS\Modules\Dashboard\DashboardController;
use App\Portals\ESS\Modules\Hrm\Employee\ProfileController;
use App\Portals\ESS\Core\Middleware\HandleEssInertiaRequests;

Route::group([
    'prefix'     => '{locale}/ess',
    'as'         => 'lang.ess.',
    'middleware'  => ['setLocale', HandleEssInertiaRequests::class],
], function () {

    // 未登入
    Route::middleware('guest')->group(function () {
        Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
    });

    // 已登入
    Route::middleware('auth')->group(function () {
        Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    });
});
```

### ESS Controller 回傳 Inertia Response

與 Ocadmin 的 `return view(...)` 不同，ESS Controller 使用 `Inertia::render()`。

`Inertia::render()` 的第一個參數為頁面名稱，對應到 `Pages/` 目錄下的 React 元件。由於 `ess.tsx` 的 resolve 路徑已指向 Portal 內的 `Pages/`，**頁面名稱即為 `Pages/` 下的相對路徑**（不需加 `ESS/` 前綴）：

```php
// Modules/Dashboard/DashboardController.php
namespace App\Portals\ESS\Modules\Dashboard;

use App\Portals\ESS\Core\Controllers\EssController;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends EssController
{
    public function index(): Response
    {
        // 'Dashboard' → Pages/Dashboard.tsx
        return Inertia::render('Dashboard');
    }
}
```

```php
// Modules/Hrm/Employee/ProfileController.php
namespace App\Portals\ESS\Modules\Hrm\Employee;

use App\Portals\ESS\Core\Controllers\EssController;
use Inertia\Inertia;

class ProfileController extends EssController
{
    public function edit(Request $request)
    {
        // 'Hrm/Employee/Edit' → Pages/Hrm/Employee/Edit.tsx
        return Inertia::render('Hrm/Employee/Edit', [
            'employee' => $employee->only([...]),
        ]);
    }
}
```

> **慣例**：Controller 的 `Modules/` 路徑與 `Inertia::render()` 的頁面名稱、`Pages/` 的檔案路徑三者一致。例如 `Modules/Hrm/Employee/ProfileController` → `Inertia::render('Hrm/Employee/Edit')` → `Pages/Hrm/Employee/Edit.tsx`。

### HandleEssInertiaRequests 中介層

ESS 使用獨立的 Inertia 中介層，指定 ESS 專用的 root view（透過 view namespace），並共享多語資料：

```php
namespace App\Portals\ESS\Core\Middleware;

use Inertia\Middleware;

class HandleEssInertiaRequests extends Middleware
{
    protected $rootView = 'ess::ess';   // app/Portals/ESS/resources/views/ess.blade.php

    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth'    => ['user' => $request->user()],
            'locale'  => app()->getLocale(),
            'locales' => config('localization.locale_names'),
        ];
    }
}
```

### Vite 設定

`vite.config.js` 加入 `@tailwindcss/vite` 插件、ESS 進入點，並設定路徑別名方便 import：

```js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';
import tailwindcss from '@tailwindcss/vite';
import path from 'path';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/js/app.tsx',                      // 原有（Breeze 預設）
                'app/Portals/ESS/resources/js/ess.tsx',      // ESS Portal
            ],
            refresh: true,
        }),
        react(),
        tailwindcss(),
    ],
    resolve: {
        alias: {
            '@ess': path.resolve(__dirname, 'app/Portals/ESS/resources/js'),
        },
    },
});
```

> **Tailwind CSS 4**：不再需要 `tailwind.config.js` 和 `postcss.config.js`，改由 `@tailwindcss/vite` 插件處理。兩個進入點（`app.css` 與 `ess.css`）都使用 Tailwind v4 語法。

### ESS Root View（ess.blade.php）

`app/Portals/ESS/resources/views/ess.blade.php` — Inertia root template：

```blade
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title inertia>{{ config('app.name') }}</title>
    @routes
    @viteReactRefresh
    @vite(['app/Portals/ESS/resources/js/ess.tsx', "app/Portals/ESS/resources/js/Pages/{$page['component']}.tsx"])
    @inertiaHead
</head>
<body>
    @inertia
</body>
</html>
```

### ESS CSS 進入點（ess.css）

使用 Tailwind CSS 4 語法，透過 `@import` 載入 Tailwind、`@plugin` 載入插件：

```css
@import "tailwindcss";
@plugin "daisyui";
@plugin "@tailwindcss/forms";
```

### ESS React 進入點（ess.tsx）

```tsx
import '../css/ess.css';
import { createInertiaApp } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createRoot } from 'react-dom/client';

createInertiaApp({
    title: (title) => `${title} - ESS`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.tsx`,
            import.meta.glob('./Pages/**/*.tsx'),
        ),
    setup({ el, App, props }) {
        createRoot(el).render(<App {...props} />);
    },
    progress: { color: '#4B5563' },
});
```

由於進入點 `ess.tsx` 本身就在 `app/Portals/ESS/resources/js/` 下，`./Pages/` 的相對路徑自然指向同目錄的 `Pages/`。當 Controller 呼叫 `Inertia::render('Hrm/Employee/Edit')` 時，會自動解析為 `./Pages/Hrm/Employee/Edit.tsx`。

## 多語機制

ESS Portal 沿用與 Ocadmin 相同的 URL 多語機制（`SetLocale` middleware）：

- URL 格式：`/zh-hant/ess/dashboard`、`/en/ess/dashboard`
- 後端透過 `Inertia::share()` 傳遞 `locale` 與 `locales` 到前端
- 前端透過 `usePage().props.locale` 取得當前語系
- 語系切換：使用 Inertia `router.visit()` 導向新語系 URL

## 認證與權限

- ESS 與 Ocadmin 共用同一個 `users` 表與 `auth` guard
- ESS 路由群組加上 `auth` middleware，未登入導向 ESS 登入頁
- 資料範圍由 Spatie Permission 控制：
  - `ess_profile.*` — 僅存取自己的資料
  - `ess_team.*` — 存取同部門資料
  - `ess.*` — ESS 功能權限

## 與 Ocadmin 的隔離

兩個 Portal 完全獨立運作：

1. **路由隔離**：`/{locale}/admin/*` vs `/{locale}/ess/*`，各自的路由檔
2. **前端隔離**：Blade views vs React pages，各自獨立在 Portal 目錄內
3. **打包隔離**：各自的 Vite 進入點，產出獨立的 JS/CSS bundle
4. **中介層隔離**：Ocadmin 無 Inertia 中介層；ESS 使用 `HandleEssInertiaRequests`
5. **共用部分**：Models、Migration、Seeder、認證 guard、`SetLocale` middleware
