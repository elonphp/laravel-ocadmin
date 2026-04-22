# Portal 概述

---

## 一、什麼是 Portal

Portal 是系統的**應用入口**，每個 Portal 面向不同的使用者群體，擁有獨立的介面、角色與權限範圍。

### 1.1 概念

```
┌─────────────┐  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐
│   Ocadmin   │  │     ESS     │  │     Web     │  │     POS     │
│   Portal    │  │   Portal    │  │   Portal    │  │   Portal    │
│             │  │             │  │             │  │             │
│  公司內部    │  │  員工/主管   │  │  大眾/客戶   │  │  門市人員    │
│  後台管理    │  │  人力資源    │  │  官網前台    │  │  銷售系統    │
└─────────────┘  └─────────────┘  └─────────────┘  └─────────────┘
  role: admin.*    role: hrm.*      role: web.*      role: pos.*
```

每個 Portal：
- 有自己的**角色**（以 `role_prefix` 為前綴，如 `admin.*`、`hrm.*`）
- 有自己的**權限**（`{role_prefix}.{module}.{resource}.{action}`）
- 可以有獨立的**技術棧**（Blade、Inertia/React、純 API）
- 可以有獨立的**URL 前綴**與**認證方式**

### 1.2 Portal 列表

| Portal | url_prefix | role_prefix | 面向對象 | 狀態 |
|--------|------------|-------------|----------|------|
| Ocadmin | `admin` | `admin` | 公司內部職員 | 開發中 |
| ESS | `ess` | `hrm` | 員工 / 主管 | 已實作基礎 |
| Web | —（domain-based） | `web` | 大眾 / 客戶 | 待實作 |
| POS | 依專案定義 | 依專案定義 | 門市人員 | 待實作 |

> Portal 可依專案需求增減。以上為常見配置，並非全部都要實作。

---

## 二、config/portals.php

### 2.1 設定檔內容

```php
// config/portals.php
return [
    'global' => [
        'dir' => null,          // 全域設定，不對應實作目錄
    ],
    'ocadmin' => [
        'url_prefix'  => 'admin',
        'role_prefix' => 'admin',
        'dir'         => 'Ocadmin',
    ],
    'web' => [
        // url_prefix 省略：domain-based portal，不透過 path 區分
        'role_prefix' => 'web',
        'dir'         => 'Web',
    ],
];
```

### 2.2 欄位說明

| 欄位 | 用途 | 必填 | 說明 |
|------|------|------|------|
| array key | 內部識別碼，config 索引 | ✓ | 穩定不變，不影響 URL 或角色 |
| `url_prefix` | 路由前綴，決定 path-based URL | 選填 | 省略代表 domain-based |
| `role_prefix` | 角色/權限命名前綴 | ✓ | 所有角色以此為前綴 |
| `dir` | `app/Portals/` 下的實作目錄名稱 | ✓ | 一個 key 對應一個目錄 |

**設計原則：一個 key，一份設定，一個目錄。**

### 2.3 四個概念完全解耦

`url_prefix`、`role_prefix`、`dir` 各自獨立，可以不同，可以個別異動：

```php
// 情境 1：客戶要求 URL 改為 /backend，但角色不動
'ocadmin' => [
    'url_prefix'  => 'backend',   // URL 換掉
    'role_prefix' => 'admin',     // 角色不換，既有授權不受影響
    'dir'         => 'Ocadmin',
],

// 情境 2：大版本改版，新舊 Portal 並存過渡
'ocadmin' => [
    'url_prefix'  => 'ocadmin',   // 舊後台移至 /ocadmin
    'role_prefix' => 'admin',
    'dir'         => 'Ocadmin',
],
'ocadmin-v2' => [
    'url_prefix'  => 'admin',     // 新後台接管 /admin
    'role_prefix' => 'admin',     // 相同角色，明確宣告
    'dir'         => 'OcadminV2',
],

// 情境 3：domain-based Portal（官網）
'web' => [
    // url_prefix 省略，路由由程式另行處理
    'role_prefix' => 'web',
    'dir'         => 'Web',
],
```

### 2.4 如何讀取

```php
// 取得 Ocadmin 的 url_prefix
config('portals.ocadmin.url_prefix')  // 'admin'

// 取得所有 portal（排除 global）
collect(config('portals'))->except('global')
```

---

## 三、角色與權限的 Portal 前綴

角色與權限皆帶有 `role_prefix`，確保不同入口之間的隔離性：

| 項目 | 格式 | 範例 |
|------|------|------|
| 角色 | `{role_prefix}.{role_name}` | `hrm.hr_manager`, `admin.operator` |
| 權限 | `{role_prefix}.{module}.{resource}.{action}` | `hrm.mss.employee.access`, `admin.config.setting.modify` |
| 全域角色（例外） | `{role_name}` | `super_admin`（跨 Portal） |

### 3.1 Portal 存取判斷

```php
// User Model
public function hasPortalRole(string $rolePrefix): bool
{
    if ($this->hasRole('super_admin')) {
        return true;
    }

    return $this->roles->contains(
        fn ($role) => str_starts_with($role->name, $rolePrefix . '.')
    );
}

$user->hasPortalRole('admin');  // 是否可進入 Ocadmin
$user->hasPortalRole('hrm');    // 是否可進入 ESS/HRM Portal
```

### 3.2 Middleware

```php
// 路由級 Portal 守門，參數為 role_prefix
Route::middleware(['auth', 'requirePortalRole:admin'])->group(/* Ocadmin 路由 */);
Route::middleware(['auth', 'requirePortalRole:hrm'])->group(/* ESS 路由 */);
```

---

## 四、URL 結構設計原則

### 4.1 基本結構

```
/{locale}/{url_prefix}/{module}/{resource}
```

- `{locale}`：語系前綴，如 `zh-hant`、`en`。即使單語系專案也保留，以便未來擴充。
- `{url_prefix}`：Portal 識別，來自 `config/portals.php` 的 `url_prefix`。
- 其後為各 Portal 自行定義的路由結構。

### 4.2 不建議將業務識別碼放入 URL 前綴

品牌、門市等業務識別碼**不應**放入 URL 前綴（如 `/{locale}/freshbite/pos`）：

- 品牌是登入後的 session 上下文，不是應用入口的識別符
- 品牌更名時，URL 需跟著改
- 管理後台（admin）本來就跨品牌，無法對應單一品牌

**正確做法：** URL 只到 portal 層級，品牌由 session/auth 決定。

### 4.3 各 Portal 各自有登入頁（建議）

```
/{locale}/admin/login       ← Ocadmin
/{locale}/ess/login         ← ESS
/{locale}/pos/login         ← POS
```

優點：各 Portal 完全自治，設備（門市平板）可直接書籤到正確入口。

**Auth Guard：** 多個 Portal 可共用同一個 guard（如 `web`），差異在登入後的 `role_prefix` 驗證。

---

## 五、各 Portal 技術選型

不同 Portal 的使用者情境不同，技術棧隨之調整：

| Portal | 使用者 | 裝置 | 介面特性 | 技術選型 |
|--------|--------|------|----------|---------|
| Ocadmin | 管理人員 | 桌機 | 資料密集、表格多、表單多 | Blade + jQuery + Bootstrap 5 |
| ESS | 員工/主管 | 桌機/手機 | 互動豐富、表單複雜 | Inertia + React + Tailwind CSS |
| POS | 門市店員 | 平板 | 大按鈕、觸控優化、快速結帳 | Blade + Tailwind CSS + DaisyUI |
| Web | 大眾/客戶 | 各種 | SEO 重要、靜態為主 | 另評估（Next.js 或純 API） |

**CSS 框架衝突是隔離 Portal 的主要技術理由：** Ocadmin 的 Bootstrap 5 與 POS 的 Tailwind CSS 的 class 命名（`.container`、`.row`、`.btn`）會互相干擾，必須在 Portal 層完全隔離。

---

## 六、Portal 間的共用資源

Portal 之間**共用後端資源，隔離前端資源**：

| 資源 | 共用 | 說明 |
|------|------|------|
| `app/Models/` | ✓ | 所有 Portal 使用相同的 Eloquent Model |
| `app/Services/` | ✓（跨 Portal 共用的 Service） | Portal 專屬邏輯放在各自的 Portal 目錄 |
| `app/Helpers/` | ✓ | OrmHelper、DateHelper 等工具 |
| `database/migrations/` | ✓ | 資料表統一管理 |
| `lang/` | ✓（共用語言檔） | 各 Portal 有自己的語言檔目錄 |
| Blade Views | ✗ | 各 Portal 完全獨立 |
| 前端資源（JS/CSS） | ✗ | 各 Portal 獨立的 Vite 進入點與 bundle |
| 路由檔 | ✗ | 各 Portal 有獨立的 routes/ 目錄 |

---

## 七、新增 Portal 完整步驟

以新增 `POS` Portal 為例：

### Step 1：`config/portals.php` 登記

```php
'pos' => [
    'url_prefix'  => 'pos',
    'role_prefix' => 'pos',
    'dir'         => 'Pos',
],
```

### Step 2：建立目錄結構

```
app/Portals/Pos/
├── Core/
│   ├── Controllers/
│   │   └── PosController.php       ← 基底 Controller
│   └── Providers/
│       └── PosServiceProvider.php  ← 路由與視圖註冊
├── Modules/                        ← 功能模組
│   └── Sale/
│       └── OrderController.php
├── resources/
│   └── views/                      ← Blade 視圖（或 Inertia root view）
└── routes/
    └── pos.php
```

### Step 3：建立 ServiceProvider

```php
namespace App\Portals\Pos\Core\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class PosServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Route::middleware(['web'])
            ->group(app_path('Portals/Pos/routes/pos.php'));

        $this->loadViewsFrom(app_path('Portals/Pos/resources/views'), 'pos');
    }
}
```

### Step 4：在 `bootstrap/providers.php` 註冊

```php
return [
    App\Providers\AppServiceProvider::class,
    App\Portals\Ocadmin\Core\Providers\OcadminServiceProvider::class,
    App\Portals\Pos\Core\Providers\PosServiceProvider::class,  // 新增
];
```

### Step 5：設定路由（pos.php）

```php
use App\Portals\Pos\Modules\Sale\OrderController;

Route::group([
    'prefix'     => '{locale}/pos',
    'as'         => 'lang.pos.',
    'middleware' => ['setLocale', 'auth', 'requirePortalRole:pos'],
], function () {
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
});
```

### Step 6：建立基底 Controller

```php
namespace App\Portals\Pos\Core\Controllers;

use App\Http\Controllers\Controller;

abstract class PosController extends Controller
{
    // Portal 共用邏輯
}
```

### 新增 Portal Checklist

- [ ] `config/portals.php` 登記 `url_prefix`、`role_prefix`、`dir`
- [ ] 建立 `app/Portals/{Dir}/` 目錄結構
- [ ] 建立 `ServiceProvider`，註冊路由與視圖
- [ ] 在 `bootstrap/providers.php` 加入 ServiceProvider
- [ ] 建立基底 Controller（繼承 `App\Http\Controllers\Controller`）
- [ ] 建立路由檔，套用 `requirePortalRole:{role_prefix}` middleware
- [ ] 為 Portal 使用者建立角色（`{role_prefix}.{role_name}`）

---

## 八、Portal 總覽

| | Ocadmin | ESS | Web | POS |
|---|---------|-----|-----|-----|
| **url_prefix** | `admin` | `ess` | —（domain-based） | 依專案 |
| **role_prefix** | `admin` | `hrm` | `web` | 依專案 |
| **面向** | 內部職員 | 員工/主管 | 大眾/客戶 | 門市人員 |
| **技術** | Blade + Bootstrap | Inertia + React | TBD | Blade + Tailwind |
| **角色** | `super_admin`, `admin.*` | `hrm.*` | `web.*` | 依專案 |
| **狀態** | 開發中 | 已實作基礎 | 待實作 | 待實作 |

---

## 相關文件

- [10000_系統架構.md](10000_系統架構.md) — 全系統架構總覽
- [10007_權限機制.md](10007_權限機制.md) — 角色/權限命名規範、Spatie 設定、權限檢查方式
- [00003_Ocadmin程式規範.md](00003_Ocadmin程式規範.md) — Ocadmin Portal 完整開發規範
