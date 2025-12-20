# 客製化機制

本文件說明如何客製化 Ocadmin 套件。

---

## 客製化類型

| 類型 | 方式 | 說明 |
|------|------|------|
| 設定覆寫 | `config/ocadmin.php` | 修改套件設定 |
| 視圖覆寫 | `app/Ocadmin/Resources/views/` | 覆寫套件視圖 |
| 路由追加 | `app/Ocadmin/Routes/routes.php` | 追加自訂路由 |
| 選單追加 | `app/Ocadmin/Config/menu.php` | 追加選單項目 |
| 模組新增 | `app/Ocadmin/Modules/` | 新增客製模組 |
| Model 替換 | `config/ocadmin.php` | 使用專案 Model |

---

## 初始化客製目錄

```bash
php artisan ocadmin:init
```

此指令會建立：

```
app/Ocadmin/
├── Modules/                    # 客製模組
├── Resources/
│   └── views/                  # 覆寫視圖
├── Config/
│   └── menu.php                # 追加選單
└── Routes/
    └── routes.php              # 追加路由
```

> 由於使用 `app/` 目錄，命名空間為 `App\Ocadmin\`，Laravel 預設的 PSR-4 已支援，無需修改 `composer.json`。

---

## 設定覆寫

### 發佈設定檔

```bash
php artisan vendor:publish --tag=ocadmin-config
```

### 設定檔位置

```
config/ocadmin.php
```

### 可覆寫設定

```php
<?php

return [
    // 路由前綴
    'prefix' => 'ocadmin',

    // 中介層
    'middleware' => ['web', 'auth', 'ocadmin.locale'],

    // 多語系
    'localization' => [
        'default' => 'zh-TW',
        'supported' => ['zh-TW', 'en'],
    ],

    // 啟用/停用標準模組
    'modules' => [
        'system-log' => true,
        'access-control' => true,
        'taxonomy' => false,        // 停用
    ],

    // Model 類別
    'models' => [
        'user' => App\Models\User::class,
    ],

    // 每頁筆數
    'pagination' => [
        'per_page' => 15,
    ],

    // 上傳設定
    'upload' => [
        'disk' => 'public',
        'max_size' => 10240,  // KB
        'allowed_types' => ['jpg', 'png', 'gif', 'pdf'],
    ],
];
```

---

## 視圖覆寫

### 視圖類型

| 類型 | 套件位置 | 覆寫位置 |
|------|----------|----------|
| 共用布局 | `vendor/.../resources/views/layouts/` | `app/Ocadmin/Resources/views/layouts/` |
| 共用元件 | `vendor/.../resources/views/components/` | `app/Ocadmin/Resources/views/components/` |
| 標準模組視圖 | `vendor/.../src/Modules/{Name}/Views/` | `app/Ocadmin/Resources/views/modules/{name}/` |

### 覆寫共用布局

```
# 套件
vendor/.../resources/views/layouts/app.blade.php

# 覆寫
app/Ocadmin/Resources/views/layouts/app.blade.php
```

### 覆寫 Partials

```
# 覆寫 sidebar
app/Ocadmin/Resources/views/layouts/partials/sidebar.blade.php

# 覆寫 header
app/Ocadmin/Resources/views/layouts/partials/header.blade.php
```

### 覆寫標準模組視圖

模組名稱使用 kebab-case：

```
# 覆寫 SystemLog 的 index
app/Ocadmin/Resources/views/modules/system-log/index.blade.php

# 覆寫 AccessControl 的 form
app/Ocadmin/Resources/views/modules/access-control/roles/form.blade.php
```

### 載入優先序

```
1. vendor/.../resources/views/              # 最低（套件預設）
2. vendor/.../src/Modules/{Name}/Views/     # 標準模組
3. app/Ocadmin/Resources/views/             # 最高（專案覆寫）
```

---

## 路由追加

### 路由檔案

```php
<?php
// app/Ocadmin/Routes/routes.php

use Illuminate\Support\Facades\Route;

// 追加路由會自動套用：
// - middleware: ['web', 'auth', 'ocadmin.locale']
// - prefix: {locale}/ocadmin
// - as: lang.ocadmin.

Route::prefix('reports')->name('reports.')->group(function () {

    Route::get('/sales', [SalesReportController::class, 'index'])
        ->name('sales');

    Route::get('/inventory', [InventoryReportController::class, 'index'])
        ->name('inventory');
});
```

### 完整路由

```
GET /{locale}/ocadmin/reports/sales      → lang.ocadmin.reports.sales
GET /{locale}/ocadmin/reports/inventory  → lang.ocadmin.reports.inventory
```

### 無語系路由

若需要無語系前綴的路由，在檔案中自行定義完整路由：

```php
<?php
// app/Ocadmin/Routes/routes.php

use Illuminate\Support\Facades\Route;

// 這段會自動套用語系前綴
Route::get('/my-page', [MyController::class, 'index'])->name('my-page');

// 以下路由不會自動套用
Route::middleware(['web', 'auth'])
    ->prefix('ocadmin/api')
    ->as('ocadmin.api.')
    ->group(function () {
        Route::post('/upload', [UploadController::class, 'store'])->name('upload');
    });
```

---

## 選單追加

### 選單檔案

```php
<?php
// app/Ocadmin/Config/menu.php

return [
    // 新增選單群組
    [
        'group' => 'reports',
        'title' => '報表',
        'icon' => 'fa-solid fa-chart-bar',
        'priority' => 200,
        'items' => [
            [
                'title' => '銷售報表',
                'route' => 'lang.ocadmin.reports.sales',
                'icon' => 'fa-solid fa-dollar-sign',
            ],
            [
                'title' => '庫存報表',
                'route' => 'lang.ocadmin.reports.inventory',
                'icon' => 'fa-solid fa-warehouse',
            ],
        ],
    ],

    // 追加到現有群組
    [
        'group' => 'system',           // 使用現有群組 key
        'append' => true,              // 追加模式
        'items' => [
            [
                'title' => '自訂功能',
                'route' => 'lang.ocadmin.custom.feature',
            ],
        ],
    ],
];
```

### 選單欄位

```php
[
    'group' => 'unique_key',         // 群組唯一鍵
    'title' => '顯示名稱',            // 可用翻譯 key
    'icon' => 'fa-solid fa-xxx',     // Font Awesome 圖示
    'priority' => 100,               // 排序（越小越前）
    'permission' => 'view-reports',  // 權限（可選）
    'append' => false,               // true=追加到現有群組
    'items' => [
        [
            'title' => '子項目',
            'route' => 'lang.ocadmin.xxx',
            'icon' => 'fa-xxx',
            'permission' => 'xxx',
            'badge' => [             // 徽章（可選）
                'text' => 'NEW',
                'class' => 'bg-danger',
            ],
        ],
    ],
]
```

### 選單合併邏輯

```
1. 載入套件標準模組選單
2. 載入客製模組選單
3. 載入 app/Ocadmin/Config/menu.php
4. 處理 append 追加
5. 依 priority 排序
```

---

## Model 替換

### 設定方式

```php
// config/ocadmin.php
'models' => [
    'user' => App\Models\User::class,
    'setting' => App\Models\Setting::class,
],
```

### 使用方式

```php
// 取得 Model 類別
$userClass = ocadmin_model('user');
$user = $userClass::find(1);

// 直接查詢
$users = ocadmin_model('user')::where('active', true)->get();
```

### 預設 Model

| 名稱 | 預設類別 |
|------|----------|
| `user` | `App\Models\User` |
| `log` | `Elonphp\...\Models\Log` |
| `setting` | `Elonphp\...\Models\Setting` |
| `role` | `Spatie\...\Models\Role` |
| `permission` | `Spatie\...\Models\Permission` |

---

## 翻譯覆寫

### 發佈翻譯檔

```bash
php artisan vendor:publish --tag=ocadmin-lang
```

### 翻譯檔位置

```
resources/lang/vendor/ocadmin/
├── zh-TW/
│   ├── common.php
│   └── menu.php
└── en/
    ├── common.php
    └── menu.php
```

### 覆寫翻譯

```php
<?php
// resources/lang/vendor/ocadmin/zh-TW/common.php

return [
    'save' => '儲存變更',      // 覆寫套件預設
    'my_custom' => '自訂文字',  // 新增
];
```

---

## 靜態資源覆寫

### 發佈資源

```bash
php artisan vendor:publish --tag=ocadmin-assets
```

### 資源位置

```
public/vendor/ocadmin/
├── css/
│   └── app.css
├── js/
│   └── app.js
└── img/
    └── logo.png
```

### 自訂 CSS

```php
{{-- layouts/app.blade.php --}}

{{-- 套件 CSS --}}
<link rel="stylesheet" href="{{ asset('vendor/ocadmin/css/app.css') }}">

{{-- 專案自訂 CSS（覆寫） --}}
@if (file_exists(public_path('css/ocadmin-custom.css')))
    <link rel="stylesheet" href="{{ asset('css/ocadmin-custom.css') }}">
@endif
```

---

## Middleware 擴充

### 新增 Middleware

```php
<?php
// app/Ocadmin/Middleware/CheckSubscription.php

namespace App\Ocadmin\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckSubscription
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->user()->hasActiveSubscription()) {
            return redirect()->route('ocadmin.subscription.required');
        }

        return $next($request);
    }
}
```

### 註冊 Middleware

```php
// app/Providers/AppServiceProvider.php

public function boot(): void
{
    $router = app('router');
    $router->aliasMiddleware('subscription', \App\Ocadmin\Middleware\CheckSubscription::class);
}
```

### 使用

```php
// app/Ocadmin/Routes/routes.php

Route::middleware(['subscription'])->group(function () {
    Route::get('/premium', [PremiumController::class, 'index']);
});
```

---

## ServiceProvider 擴充

### 建立 Provider

```php
<?php
// app/Ocadmin/Providers/CustomServiceProvider.php

namespace App\Ocadmin\Providers;

use Illuminate\Support\ServiceProvider;

class CustomServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // 綁定服務
        $this->app->singleton(MyService::class, function ($app) {
            return new MyService($app['config']['my_config']);
        });
    }

    public function boot(): void
    {
        // 載入自訂設定
        $this->mergeConfigFrom(
            app_path('Ocadmin/Config/custom.php'),
            'ocadmin.custom'
        );
    }
}
```

### 註冊 Provider

```php
// config/app.php
'providers' => [
    // ...
    App\Ocadmin\Providers\CustomServiceProvider::class,
],
```

---

## 事件監聽

### 套件事件

| 事件 | 說明 |
|------|------|
| `OcadminBooted` | 套件啟動完成 |
| `ModulesLoaded` | 模組載入完成 |
| `MenuBuilt` | 選單建立完成 |
| `UserLoggedIn` | 使用者登入 |

### 監聽事件

```php
<?php
// app/Listeners/OnOcadminBooted.php

namespace App\Listeners;

use Elonphp\LaravelOcadminModules\Events\OcadminBooted;

class OnOcadminBooted
{
    public function handle(OcadminBooted $event): void
    {
        // 自訂邏輯
        logger('Ocadmin booted at ' . now());
    }
}
```

```php
// app/Providers/EventServiceProvider.php

protected $listen = [
    \Elonphp\LaravelOcadminModules\Events\OcadminBooted::class => [
        \App\Listeners\OnOcadminBooted::class,
    ],
];
```

---

## 覆寫 Controller

### 方式一：路由覆寫

定義相同路由，優先使用專案 Controller：

```php
// app/Ocadmin/Routes/routes.php

use App\Ocadmin\Controllers\CustomLogController;

// 覆寫 SystemLog 路由
Route::prefix('system/logs')->name('system.logs.')->group(function () {
    Route::get('/', [CustomLogController::class, 'index'])->name('index');
});
```

### 方式二：繼承擴充

```php
<?php
// app/Ocadmin/Controllers/CustomLogController.php

namespace App\Ocadmin\Controllers;

use Elonphp\LaravelOcadminModules\Modules\SystemLog\Controllers\LogController;

class CustomLogController extends LogController
{
    public function index(Request $request)
    {
        // 自訂邏輯
        $this->customLogic();

        // 呼叫父類別
        return parent::index($request);
    }

    protected function customLogic(): void
    {
        // ...
    }
}
```

---

## 最佳實踐

### 1. 最小化覆寫

只覆寫必要的部分，保持與套件的相容性。

### 2. 使用設定檔

優先使用設定檔調整行為，而非覆寫程式碼。

### 3. 版本控制

`app/Ocadmin/` 目錄位於 `app/` 內，預設會加入版本控制。

### 4. 升級注意

套件升級時：
- 檢查 CHANGELOG
- 比對設定檔變更
- 測試覆寫的視圖是否相容

---

*文件版本：v1.0*
