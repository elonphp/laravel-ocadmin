本來想套件化，後來覺得定位不明確。migration, model 要不要放在自己這樣？放棄。仍然續用原本的 laravel ocadmin 專案，不做成套件。

# Laravel Ocadmin Modules

模組化的 Laravel 系統管理後台框架。

## 特色

- **模組化架構** - 標準模組與客製模組分離管理
- **多語系路由** - URL 前綴式語系切換（`/{locale}/ocadmin/...`）
- **Laravel 風格** - 目錄結構遵循 Laravel 慣例
- **覆寫機制** - 視圖、路由、設定皆可在專案端覆寫
- **獨立更新** - 套件更新不影響專案客製化內容

## 文件

| 文件 | 說明 |
|------|------|
| [README.md](README.md) | 安裝與快速開始 |
| [docs/zh_Hant/localization-overview.md](docs/zh_Hant/localization-overview.md) | 多語系總覽 |
| [docs/zh_Hant/localization-url.md](docs/zh_Hant/localization-url.md) | 網址多語 |
| [docs/zh_Hant/localization-interface.md](docs/zh_Hant/localization-interface.md) | 介面多語 |
| [docs/zh_Hant/localization-content.md](docs/zh_Hant/localization-content.md) | 內容多語 |
| [docs/zh_Hant/modules.md](docs/zh_Hant/modules.md) | 模組開發指南 |
| [docs/zh_Hant/customization.md](docs/zh_Hant/customization.md) | 客製化機制 |

---

## 安裝

```bash
composer require elonphp/laravel-ocadmin-modules
```

安裝完成後**立即可用**，無需複製任何檔案。所有功能從 `vendor/` 載入。

---

## 按需發佈（Copy on Demand）

本套件採用「按需發佈」策略：

| 需求 | 指令 | 說明 |
|------|------|------|
| 純使用 | 無需任何指令 | 直接從 vendor 載入 |
| 覆寫設定 | `vendor:publish --tag=ocadmin-config` | 發佈到 `config/ocadmin.php` |
| 客製化 | `ocadmin:init` | 初始化 `app/Ocadmin/` 目錄結構 |

### 專案檔案結構（按需產生）

```
專案根目錄/
├── vendor/elonphp/laravel-ocadmin-modules/   # 套件（永遠存在）
│
├── config/
│   └── ocadmin.php                            # （可選）覆寫設定
│
└── app/
    └── Ocadmin/                               # （可選）客製化目錄
        ├── Modules/                           # 客製模組
        ├── Resources/views/                   # 覆寫視圖
        ├── Config/                            # 追加設定
        └── Routes/                            # 追加路由
```

所有客製化內容集中在 `app/Ocadmin/` 目錄下。

---

## 目錄結構

### src/Core/ 資料夾說明

| 資料夾 | 用途 | 說明 |
|--------|------|------|
| `Providers/` | 服務提供者 | 套件的主要進入點，註冊服務、路由、視圖等 |
| `Controllers/` | HTTP 控制器 | 核心控制器（登入、Dashboard 等） |
| `Middleware/` | 中間件 | 請求處理中間件（語系設定、重導向等） |
| `Support/` | 輔助類別 | 非 Controller/Middleware/Provider 的核心類別 |
| `ViewComposers/` | 視圖組合器 | 自動注入變數到視圖（如選單資料） |
| `Console/` | Artisan 指令 | CLI 指令（init、module、list） |
| `Traits/` | 共用 Traits | 可複用的 PHP Traits |

#### Support/ 資料夾內容

| 類別 | 用途 |
|------|------|
| `ModuleLoader.php` | 模組載入器：掃描、載入、註冊標準模組與客製模組 |

#### ViewComposers/ 資料夾內容

| 類別 | 用途 |
|------|------|
| `MenuComposer.php` | 選單組合器：自動注入 `$menus` 變數到 sidebar 視圖 |

### src/Support/ 資料夾說明

| 類別 | 用途 |
|------|------|
| `LocaleHelper.php` | 語系輔助：URL/內部格式轉換、語系設定、切換連結生成 |

### 套件結構（vendor，不可修改）

```
vendor/elonphp/laravel-ocadmin-modules/
├── src/
│   ├── Core/                           # 核心框架
│   │   ├── Providers/                  # 服務提供者
│   │   │   └── OcadminServiceProvider.php
│   │   ├── Controllers/                # HTTP 控制器
│   │   │   ├── Controller.php
│   │   │   ├── AuthController.php
│   │   │   └── DashboardController.php
│   │   ├── Middleware/                 # 中間件
│   │   │   ├── SetLocale.php
│   │   │   └── RedirectToLocale.php
│   │   ├── Support/                    # 輔助/支援類別
│   │   │   └── ModuleLoader.php
│   │   ├── ViewComposers/              # 視圖組合器
│   │   │   └── MenuComposer.php
│   │   ├── Console/                    # Artisan 指令
│   │   │   ├── InitCommand.php
│   │   │   ├── ModuleCommand.php
│   │   │   └── ListCommand.php
│   │   └── Traits/                     # 共用 Traits
│   │
│   ├── Support/                        # 全域輔助類別
│   │   └── LocaleHelper.php
│   │
│   └── Modules/                        # 標準模組
│       ├── SystemLog/
│       ├── AccessControl/
│       ├── Taxonomy/
│       ├── Localization/
│       ├── Setting/
│       └── MetaKey/
│
├── config/
│   └── ocadmin.php
│
├── resources/
│   └── views/
│       ├── layouts/
│       │   ├── app.blade.php
│       │   └── partials/
│       ├── components/
│       └── errors/
│
├── routes/
│   └── ocadmin.php
│
├── database/
│   └── migrations/
│
└── stubs/                              # 骨架範本
    └── module/
```

### 專案結構（app/Ocadmin/，按需建立）

執行 `php artisan ocadmin:init` 後產生：

```
app/
└── Ocadmin/
    ├── Modules/                        # 客製模組目錄
    │   └── {ModuleName}/               # 由 ocadmin:module 建立
    │       ├── Controllers/
    │       ├── Models/
    │       ├── Views/
    │       ├── Routes/
    │       │   └── routes.php
    │       ├── Config/
    │       │   └── menu.php
    │       └── module.json
    │
    ├── Resources/
    │   └── views/                      # 覆寫視圖
    │       ├── layouts/                # 覆寫共用布局
    │       ├── components/             # 覆寫共用元件
    │       └── modules/                # 覆寫標準模組視圖
    │           └── system-log/
    │
    ├── Config/                         # 追加設定
    │   └── menu.php                    # 追加選單
    │
    └── Routes/                         # 追加路由
        └── routes.php
```

---

## 載入優先序

### 模組載入順序

```
1. vendor/.../src/Core/              # 核心框架
2. vendor/.../src/Modules/           # 標準模組（套件內建）
3. app/Ocadmin/Modules/              # 客製模組（專案自訂）
```

### 視圖優先序（後者覆寫前者）

```
1. vendor/.../resources/views/              # 套件共用視圖
2. vendor/.../src/Modules/{Name}/Views/     # 標準模組視圖
3. app/Ocadmin/Resources/views/             # 專案覆寫（優先）
4. app/Ocadmin/Modules/{Name}/Views/        # 客製模組視圖
```

### 設定優先序

```
1. vendor/.../config/ocadmin.php            # 套件預設
2. config/ocadmin.php                       # 專案覆寫（mergeConfigFrom）
```

---

## 建立客製模組

使用 Artisan 指令：

```bash
php artisan ocadmin:module Inventory
```

生成結構：

```
app/Ocadmin/Modules/Inventory/
├── Controllers/
│   └── InventoryController.php
├── Models/
├── Views/
│   └── index.blade.php
├── Routes/
│   └── routes.php
├── Config/
│   └── menu.php
└── module.json
```

### module.json 格式

```json
{
    "name": "Inventory",
    "description": "庫存管理模組",
    "priority": 50,
    "enabled": true
}
```

- `priority`: 載入順序，數字越小越先載入
- `enabled`: 是否啟用

---

## 覆寫機制

### 視圖類型

| 類型 | 套件位置 | 覆寫位置 |
|------|----------|----------|
| 共用布局 | `vendor/.../resources/views/layouts/` | `app/Ocadmin/Resources/views/layouts/` |
| 共用元件 | `vendor/.../resources/views/components/` | `app/Ocadmin/Resources/views/components/` |
| 標準模組視圖 | `vendor/.../src/Modules/{Name}/Views/` | `app/Ocadmin/Resources/views/modules/{name}/` |
| 客製模組視圖 | N/A | `app/Ocadmin/Modules/{Name}/Views/` |

### 覆寫共用布局

在 `app/Ocadmin/Resources/views/layouts/` 下建立對應的視圖：

```
# 套件視圖
vendor/.../resources/views/layouts/app.blade.php

# 專案覆寫
app/Ocadmin/Resources/views/layouts/app.blade.php
```

### 覆寫標準模組視圖

在 `app/Ocadmin/Resources/views/modules/{module-name}/` 下建立：

```
# 套件視圖
vendor/.../src/Modules/SystemLog/Views/index.blade.php

# 專案覆寫
app/Ocadmin/Resources/views/modules/system-log/index.blade.php
```

> 模組名稱使用 kebab-case（如 `system-log`）

### 覆寫設定

```bash
php artisan vendor:publish --tag=ocadmin-config
```

設定檔會複製到 `config/ocadmin.php`，修改後會覆寫套件預設值。

### 追加路由（需先執行 ocadmin:init）

在 `app/Ocadmin/Routes/routes.php` 中定義：

```php
<?php

use Illuminate\Support\Facades\Route;
use App\Ocadmin\Modules\Report\Controllers\ReportController;

Route::prefix('report')->name('report.')->group(function () {
    Route::get('/', [ReportController::class, 'index'])->name('index');
});
```

### 追加選單（需先執行 ocadmin:init）

在 `app/Ocadmin/Config/menu.php` 中定義：

```php
<?php

return [
    [
        'group' => 'custom',
        'title' => '自訂功能',
        'icon' => 'fa-solid fa-cog',
        'children' => [
            [
                'title' => '報表',
                'route' => 'report.index',
            ],
        ],
    ],
];
```

---

## 視圖架構

### 完整視圖路徑

```
vendor/elonphp/laravel-ocadmin-modules/
├── resources/views/                        # 套件共用視圖
│   ├── layouts/
│   │   ├── app.blade.php                   # 主框架
│   │   ├── auth.blade.php                  # 登入頁框架
│   │   └── partials/
│   │       ├── header.blade.php
│   │       ├── sidebar.blade.php
│   │       └── footer.blade.php
│   ├── components/                         # 共用元件
│   │   ├── card.blade.php
│   │   ├── modal.blade.php
│   │   └── table.blade.php
│   └── errors/
│
├── src/Modules/
│   └── SystemLog/
│       └── Views/                          # 標準模組視圖
│           ├── index.blade.php
│           └── form.blade.php

---

app/Ocadmin/                                # 專案客製化
├── Modules/
│   └── Inventory/
│       └── Views/                          # 客製模組視圖
│           └── index.blade.php
│
└── Resources/views/                        # 覆寫套件視圖
    ├── layouts/                            # 覆寫共用布局
    │   └── app.blade.php
    ├── components/                         # 覆寫共用元件
    │   └── card.blade.php
    └── modules/                            # 覆寫標準模組視圖
        └── system-log/
            └── index.blade.php
```

### Blade 引用方式

```php
{{-- 引用共用布局 --}}
@extends('ocadmin::layouts.app')

{{-- 引用共用元件 --}}
@include('ocadmin::components.card')

{{-- 引用 partials --}}
@include('ocadmin::layouts.partials.sidebar')
```

### 設計原則

| 目的 | 位置 |
|------|------|
| 新增客製模組 | `app/Ocadmin/Modules/{Name}/Views/` |
| 覆寫共用布局/元件 | `app/Ocadmin/Resources/views/layouts/` 或 `components/` |
| 覆寫標準模組視圖 | `app/Ocadmin/Resources/views/modules/{name}/` |

---

## 標準模組

| 模組 | 說明 |
|------|------|
| SystemLog | 系統日誌查詢 |
| AccessControl | 角色與權限管理 |
| Taxonomy | 分類法（Categories, Tags） |
| Localization | 多語系管理 |
| Setting | 系統參數設定 |
| MetaKey | EAV 欄位定義 |

---

## Artisan 指令

### 套件指令

```bash
# 初始化客製模組目錄結構
php artisan ocadmin:init
```

建立 `app/Ocadmin/` 目錄骨架。由於在 `app/` 目錄下，Laravel 的 PSR-4 自動載入會自動處理 `App\Ocadmin\` 命名空間。

---

```bash
# 建立新的客製模組
php artisan ocadmin:module {name}

# 範例
php artisan ocadmin:module Inventory
```

在 `app/Ocadmin/Modules/` 下建立完整的模組結構。

---

```bash
# 列出所有已載入的模組
php artisan ocadmin:list
```

輸出範例：
```
+----------------+----------+----------+--------+
| Module         | Source   | Priority | Status |
+----------------+----------+----------+--------+
| SystemLog      | package  | 10       | active |
| AccessControl  | package  | 20       | active |
| Taxonomy       | package  | 30       | active |
| Inventory      | custom   | 50       | active |
+----------------+----------+----------+--------+
```

---

### Laravel 發佈指令

```bash
# 發佈設定檔
php artisan vendor:publish --tag=ocadmin-config
# → config/ocadmin.php

# 發佈 migrations
php artisan vendor:publish --tag=ocadmin-migrations
# → database/migrations/

# 發佈靜態資源
php artisan vendor:publish --tag=ocadmin-assets
# → public/vendor/ocadmin/

# 發佈全部資源
php artisan vendor:publish --provider="Elonphp\LaravelOcadminModules\Core\Providers\OcadminServiceProvider"
```

---

### 發佈標籤一覽

| Tag | 目標路徑 | 說明 |
|-----|----------|------|
| `ocadmin-config` | `config/ocadmin.php` | 設定檔 |
| `ocadmin-migrations` | `database/migrations/` | 資料庫遷移 |
| `ocadmin-assets` | `public/vendor/ocadmin/` | 靜態資源（CSS/JS/圖片） |

> 視圖覆寫請使用 `app/Ocadmin/Resources/views/`，不需發佈。

---

## 前端資源

### 發佈資源

將前端資源（CSS、JS、圖片）發佈到專案：

```bash
php artisan vendor:publish --tag=ocadmin-assets
```

資源會發佈到 `public/vendor/ocadmin/`：

```
public/vendor/ocadmin/
├── css/
├── images/
├── js/
└── vendor/
```

### 在 Blade 模板中使用

使用 `ocadmin_asset()` 輔助函式引用資源：

```blade
{{-- CSS --}}
<link rel="stylesheet" href="{{ ocadmin_asset('css/stylesheet.css') }}">

{{-- JavaScript --}}
<script src="{{ ocadmin_asset('js/common.js') }}"></script>

{{-- 圖片 --}}
<img src="{{ ocadmin_asset('images/logo.png') }}">
```

### 客製化樣式

如需自訂樣式，請放在 `public/ocadmin/`（與發佈資源分開）：

```
public/
├── vendor/ocadmin/        ← 套件發佈的資源（.gitignore 忽略）
│   ├── css/
│   ├── js/
│   └── ...
│
└── ocadmin/               ← 自訂資源（可提交到版本控制）
    └── css/
        └── custom.css
```

在 layout 中，自訂樣式載入於套件樣式之後：

```blade
{{-- 套件樣式 --}}
<link rel="stylesheet" href="{{ ocadmin_asset('css/stylesheet.css') }}">

{{-- 自訂樣式（覆蓋） --}}
<link rel="stylesheet" href="{{ asset('ocadmin/css/custom.css') }}">
```

### .gitignore 設定

發佈的資源不應提交到版本控制。請在專案的 `.gitignore` 加入：

```gitignore
/public/vendor
```

> **注意：** `public/ocadmin/`（自訂資源）不應加入 `.gitignore`，這是專案的客製化內容。

---

## 設定

`config/ocadmin.php`:

```php
<?php

return [
    // 路由前綴
    'prefix' => 'ocadmin',

    // 中介層
    'middleware' => ['web', 'auth', 'locale'],

    // 預設語系
    'locale' => 'zh-TW',

    // 啟用的標準模組
    'modules' => [
        'system-log' => true,
        'access-control' => true,
        'taxonomy' => true,
        'localization' => true,
        'setting' => true,
        'meta-key' => true,
    ],

    // 客製模組路徑
    'custom_modules_path' => app_path('Ocadmin/Modules'),

    // Model 類別設定
    // 可指定使用專案的 Model 或套件的 Model
    'models' => [
        'user' => App\Models\User::class,
        // 'log' => Elonphp\LaravelOcadminModules\Models\Log::class,
        // 'setting' => Elonphp\LaravelOcadminModules\Models\Setting::class,
    ],
];
```

---

## Model 設定

### Model 來源

| 來源 | 命名空間 | 說明 |
|------|----------|------|
| 專案 Model | `App\Models\` | 專案既有的 Model |
| 套件 Model | `Elonphp\LaravelOcadminModules\Models\` | 套件內建的 Model |
| 客製模組 Model | `App\Ocadmin\Modules\{Name}\Models\` | 客製模組的 Model |

### 指定使用專案 Model

當套件與專案有相同用途的 Model 時（如 `User`），透過設定檔指定：

```php
// config/ocadmin.php
'models' => [
    'user' => App\Models\User::class,           // 使用專案的 User
    'setting' => App\Models\Setting::class,     // 使用專案的 Setting
],
```

### 使用套件 Model

不設定則使用套件預設：

```php
// config/ocadmin.php
'models' => [
    'user' => App\Models\User::class,
    // 'log' 未設定，使用套件預設
],
```

### 套件內建 Model

| Model | 用途 | 預設類別 |
|-------|------|----------|
| `user` | 使用者 | `App\Models\User` |
| `log` | 系統日誌 | `Elonphp\...\Models\Log` |
| `setting` | 系統設定 | `Elonphp\...\Models\Setting` |
| `role` | 角色 | `Spatie\...\Models\Role` |
| `permission` | 權限 | `Spatie\...\Models\Permission` |

### 在程式中使用

```php
use Elonphp\LaravelOcadminModules\Facades\Ocadmin;

// 取得 Model 類別
$userClass = Ocadmin::model('user');
$user = $userClass::find(1);

// 或使用 helper
$user = ocadmin_model('user')::find(1);
$logs = ocadmin_model('log')::latest()->get();
```

### 客製模組的 Model

客製模組的 Model 放在模組內，直接使用完整命名空間：

```php
// app/Ocadmin/Modules/Inventory/Models/Product.php
namespace App\Ocadmin\Modules\Inventory\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'pos_products';
}
```

```php
// 使用
use App\Ocadmin\Modules\Inventory\Models\Product;

$products = Product::all();
```

---

## 命名空間

| 位置 | 命名空間 | 範例 |
|------|----------|------|
| 套件核心 | `Elonphp\LaravelOcadminModules\Core\` | `Core\Controllers\Controller` |
| 標準模組 | `Elonphp\LaravelOcadminModules\Modules\{Name}\` | `Modules\SystemLog\Controllers\LogController` |
| 客製模組 | `App\Ocadmin\Modules\{Name}\` | `App\Ocadmin\Modules\Inventory\Controllers\ProductController` |

> 由於 `app/Ocadmin/` 位於 Laravel 的 `app/` 目錄下，`App\Ocadmin\` 命名空間會自動被 Laravel 的 PSR-4 自動載入處理，無需修改 `composer.json`。

---

## 系統需求

- PHP >= 8.2
- Laravel >= 11.0
- spatie/laravel-permission >= 6.0

---

## 授權

Proprietary License

---

## 版本紀錄

### v1.0.0 (規劃中)
- 初始版本
- 核心框架
- 標準模組
- 客製化機制
