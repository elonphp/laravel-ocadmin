# Ocadmin 檔案結構

## 概述

Ocadmin 是一個 Composer 套件，透過 `vendor:publish` 將檔案發佈到 Laravel 專案的 `app/Ocadmin` 目錄。

```
myproject/
├── vendor/
│   └── elonphp/
│       └── laravel-ocadmin-modules/   ← Composer 套件源碼
│           └── src/
│               ├── Core/
│               └── Modules/
├── app/
│   ├── Ocadmin/                       ← 發佈後的檔案（可客製化）
│   │   ├── Core/
│   │   └── Modules/
│   ├── Models/                        ← Laravel 原生
│   ├── Http/
│   └── Providers/
├── config/
└── ...
```

套件源碼位於 `vendor/` 中，發佈後的檔案位於 `app/Ocadmin/`，方便開發者客製化。

---

## 安裝與發佈

### 安裝套件

```bash
composer require elonphp/laravel-ocadmin-modules
```

### 發佈檔案

```bash
# 發佈所有檔案到 app/Ocadmin
php artisan vendor:publish --provider="Elonphp\Ocadmin\OcadminServiceProvider"

# 或選擇性發佈
php artisan vendor:publish --tag=ocadmin-core      # 僅 Core
php artisan vendor:publish --tag=ocadmin-modules   # 僅 Modules
php artisan vendor:publish --tag=ocadmin-config    # 僅設定檔
```

---

## 套件源碼結構

```
vendor/elonphp/laravel-ocadmin-modules/
├── src/
│   ├── OcadminServiceProvider.php       ← 套件入口
│   │
│   ├── Core/                            ← 框架核心
│   │   ├── Console/                     ← Artisan 命令
│   │   │   ├── InitCommand.php
│   │   │   ├── ListCommand.php
│   │   │   └── ModuleCommand.php
│   │   │
│   │   ├── Controllers/                 ← 基底/共用控制器
│   │   │   ├── Controller.php
│   │   │   ├── AuthController.php
│   │   │   └── DashboardController.php
│   │   │
│   │   ├── Middleware/                  ← 中間件
│   │   │   ├── SetLocale.php
│   │   │   ├── RedirectToLocale.php
│   │   │   └── LogRequest.php
│   │   │
│   │   ├── Providers/                   ← ServiceProvider
│   │   │   └── ModuleServiceProvider.php
│   │   │
│   │   ├── Support/                     ← 輔助類
│   │   │   └── ModuleLoader.php
│   │   │
│   │   ├── Traits/                      ← 共用 Traits
│   │   │
│   │   └── ViewComposers/               ← View Composers
│   │       └── MenuComposer.php
│   │
│   ├── Modules/                         ← 功能模組
│   │   └── SystemLog/
│   │       ├── SystemLogServiceProvider.php
│   │       ├── Controllers/
│   │       │   ├── LogController.php
│   │       │   └── ArchivedLogController.php
│   │       ├── Models/
│   │       │   └── Log.php
│   │       ├── Repositories/
│   │       │   └── LogRepository.php
│   │       ├── Services/
│   │       │   └── LogService.php
│   │       ├── Commands/
│   │       │   └── ArchiveLogs.php
│   │       ├── Config/
│   │       │   └── menu.php
│   │       ├── Routes/
│   │       │   └── routes.php
│   │       ├── Migrations/
│   │       │   └── 2100_01_01_000030_create_logs_table.php
│   │       └── resources/
│   │           ├── views/
│   │           │   ├── index.blade.php
│   │           │   ├── list.blade.php
│   │           │   ├── form.blade.php
│   │           │   ├── archived_index.blade.php
│   │           │   ├── archived_list.blade.php
│   │           │   └── archived_form.blade.php
│   │           └── lang/
│   │               ├── en/
│   │               └── zh_Hant/
│   │
│   ├── Support/                         ← 全域輔助類
│   │   └── LocaleHelper.php
│   │
│   └── helpers.php                      ← 全域函式
│
├── config/
│   └── ocadmin.php                      ← 套件設定檔
│
└── stubs/                               ← 程式碼模板
    └── module/
```

---

## 發佈後的結構

發佈後，檔案將複製到 `app/Ocadmin/`，命名空間自動轉換為 `App\Ocadmin`：

```
app/Ocadmin/
├── Core/
│   ├── Controllers/
│   ├── Middleware/
│   └── ...
├── Modules/
│   └── SystemLog/
│       └── ...
├── Support/
└── helpers.php
```

---

## 結構說明

### Core（框架核心）

Core 是 Ocadmin 運作的基礎設施，不包含業務邏輯。

| 目錄 | 說明 |
|------|------|
| `Console/` | Artisan 命令（`ocadmin:init`、`ocadmin:module` 等） |
| `Controllers/` | 基底控制器、認證、Dashboard |
| `Middleware/` | 語系設定、請求日誌等中間件 |
| `Providers/` | 模組基底 ServiceProvider |
| `Support/` | ModuleLoader 等輔助類 |
| `Traits/` | 共用 Traits |
| `ViewComposers/` | 選單等 View Composers |

### Modules（功能模組）

Modules 是可插拔的業務功能，每個模組獨立運作。

| 目錄 | 說明 |
|------|------|
| `Controllers/` | 模組控制器 |
| `Models/` | 模組模型 |
| `Repositories/` | 資料存取層（選用） |
| `Services/` | 業務邏輯層（選用） |
| `Commands/` | 模組專屬 Artisan 命令 |
| `Config/` | 模組設定（選單等） |
| `Routes/` | 模組路由 |
| `Migrations/` | 資料庫遷移 |
| `resources/views/` | 視圖 |
| `resources/lang/` | 語系檔案 |

---

## 命名空間對應

### 套件源碼（vendor）

| 路徑 | 命名空間 |
|------|----------|
| `vendor/.../src/OcadminServiceProvider.php` | `Elonphp\Ocadmin` |
| `vendor/.../src/Core/Controllers/` | `Elonphp\Ocadmin\Core\Controllers` |
| `vendor/.../src/Modules/SystemLog/` | `Elonphp\Ocadmin\Modules\SystemLog` |

### 發佈後（app）

| 路徑 | 命名空間 |
|------|----------|
| `app/Ocadmin/Core/Controllers/` | `App\Ocadmin\Core\Controllers` |
| `app/Ocadmin/Core/Middleware/` | `App\Ocadmin\Core\Middleware` |
| `app/Ocadmin/Core/Support/` | `App\Ocadmin\Core\Support` |
| `app/Ocadmin/Modules/SystemLog/` | `App\Ocadmin\Modules\SystemLog` |
| `app/Ocadmin/Support/` | `App\Ocadmin\Support` |

---

## 模組規範

每個模組必須包含一個 ServiceProvider。

### 套件內的模組（vendor）

```php
<?php

namespace Elonphp\Ocadmin\Modules\SystemLog;

use Elonphp\Ocadmin\Core\Providers\ModuleServiceProvider;

class SystemLogServiceProvider extends ModuleServiceProvider
{
    protected string $moduleName = 'system-log';
    protected string $modulePath = __DIR__;

    public function boot(): void
    {
        $this->loadViews();
        $this->loadMigrations();
        $this->loadTranslations();
        $this->loadCommands();
    }
}
```

### 發佈後的模組（app）

```php
<?php

namespace App\Ocadmin\Modules\SystemLog;

use App\Ocadmin\Core\Providers\ModuleServiceProvider;

class SystemLogServiceProvider extends ModuleServiceProvider
{
    protected string $moduleName = 'system-log';
    protected string $modulePath = __DIR__;

    public function boot(): void
    {
        $this->loadViews();
        $this->loadMigrations();
        $this->loadTranslations();
        $this->loadCommands();
    }
}
```

### View 命名空間

模組視圖使用命名空間引用：

```php
return view('system-log::index');
return view('system-log::archived_form');
```

### 路由定義

模組路由在 `Routes/routes.php` 中定義，由 ModuleLoader 自動載入。

---

## Composer 設定

### 套件的 composer.json

```json
{
    "name": "elonphp/laravel-ocadmin-modules",
    "autoload": {
        "psr-4": {
            "Elonphp\\Ocadmin\\": "src/"
        },
        "files": [
            "src/helpers.php"
        ]
    }
}
```

### 發佈後需更新專案的 composer.json

```json
{
    "autoload": {
        "psr-4": {
            "App\\": "app/"
        },
        "files": [
            "app/Ocadmin/helpers.php"
        ]
    }
}
```

然後執行 `composer dump-autoload`。

---

## 為什麼使用 Core？

### 1. 框架與功能分離

```
app/Ocadmin/
├── Core/       ← 框架基礎設施（必要）
└── Modules/    ← 業務功能模組（可選）
```

- Core 是套件運作的必要條件
- Modules 依賴 Core，Core 不依賴 Modules
- 使用者可選擇性啟用 Modules

### 2. 支援客製化

發佈到 `app/Ocadmin/` 後，開發者可以：

- 直接修改 Core 或 Modules 的程式碼
- 新增自訂模組到 `app/Ocadmin/Modules/`
- 覆寫視圖、路由等設定

### 3. 語意明確

- `Core` = 核心框架，不可或缺
- `Modules` = 功能模組，可插拔
- `Support` = 輔助工具，全域使用

---

## 套件與發佈檔案對應

| 套件源碼（vendor） | 發佈後（app） |
|-------------------|---------------|
| `vendor/.../src/` | `app/Ocadmin/` |
| `Elonphp\Ocadmin\` | `App\Ocadmin\` |
| `vendor/.../src/Core/` | `app/Ocadmin/Core/` |
| `vendor/.../src/Modules/` | `app/Ocadmin/Modules/` |

發佈時套件會自動將命名空間前綴從 `Elonphp\Ocadmin\` 轉換為 `App\Ocadmin\`。
