# Ocadmin 程式規範

## 目錄

- [概述](#概述)
- [架構原則](#架構原則)
  - [預設分層](#預設分層)
  - [何時抽出 Service](#何時抽出-service)
  - [不採用 Repository](#不採用-repository)
  - [範例：簡單 CRUD vs 複雜邏輯](#範例簡單-crud-vs-複雜邏輯)
- [Core 與 Module 架構](#core-與-module-架構)
  - [定位差異](#定位差異)
  - [檔案放置規則](#檔案放置規則)
  - [View Namespace 機制](#view-namespace-機制)
  - [新增模組步驟](#新增模組步驟)
- [目錄結構](#目錄結構)
  - [Controller 與 View 位置](#controller-與-view-位置)
- [基礎 Controller（OcadminController）](#基礎-controllerocadmincontroller)
  - [為什麼使用 middleware closure](#為什麼使用-middleware-closure)
  - [子類別覆寫 setLangFiles()](#子類別覆寫-setlangfiles)
- [多語系（Language Files）](#多語系language-files)
  - [語言檔目錄結構](#語言檔目錄結構)
  - [語言檔命名規範](#語言檔命名規範)
  - [語言 Key 命名慣例](#語言-key-命名慣例)
  - [TranslationBag 使用](#translationbag-使用)
  - [View 中使用 $lang](#view-中使用-lang)
- [View 資料傳遞規範](#view-資料傳遞規範)
- [麵包屑設定](#麵包屑設定)
- [Controller 範例](#controller-範例)
  - [完整範例：PermissionController](#完整範例permissioncontroller)
  - [關鍵模式](#關鍵模式)
- [OrmHelper 查詢輔助](#ormhelper-查詢輔助)
  - [基本使用](#基本使用)
  - [filterData() 白名單機制](#filterdata-白名單機制)
  - [參數命名規範](#參數命名規範)
  - [翻譯欄位自動處理](#翻譯欄位自動處理)
  - [需手動處理的情境](#需手動處理的情境)
  - [search 關鍵字查詢](#search-關鍵字查詢)
  - [OrmHelper::getResult() 分頁](#ormhelpergetresult-分頁)
- [列表排序與網址保留規範](#列表排序與網址保留規範)
  - [OcadminController 共用方法](#ocadmincontroller-共用方法)
  - [Controller getList() 使用方式](#controller-getlist-使用方式)
  - [index.blade.php AJAX 點擊更新網址](#indexbladephp-ajax-點擊更新網址)
  - [list.blade.php 編輯連結帶參數](#listbladephp-編輯連結帶參數)
  - [form.blade.php 返回連結帶參數](#formbladephp-返回連結帶參數)
- [分頁規範](#分頁規範)
  - [自訂分頁 View](#自訂分頁-view)
  - [Controller 產生分頁](#controller-產生分頁)
  - [Blade 輸出](#blade-輸出)
  - [AJAX 分頁攔截](#ajax-分頁攔截)
- [表單 AJAX 提交規範](#表單-ajax-提交規範)
  - [Controller 回應格式](#controller-回應格式)
  - [JSON 回應格式](#json-回應格式)
  - [表單 HTML 結構](#表單-html-結構)
  - [ID 命名規範](#id-命名規範)
  - [翻譯欄位的 Blade 寫法](#翻譯欄位的-blade-寫法)
  - [關鍵屬性](#關鍵屬性)
  - [儲存按鈕](#儲存按鈕)
  - [注意事項](#注意事項)
- [視圖規範](#視圖規範) → 詳見 [00008](00008_Ocadmin視圖規範.md)
- [路由規範](#路由規範)
  - [標準 CRUD 路由](#標準-crud-路由)
- [開發檢查清單](#開發檢查清單)
  - [Controller](#controller)
  - [視圖](#視圖)
  - [語言檔](#語言檔)
  - [路由](#路由)
- [相關文件](#相關文件)

---

## 概述

本文件說明 Ocadmin Portal 的程式開發規範，以「權限管理」為範例。

---

## 架構原則

> 本節為快速摘要。完整分層慣例、Service/Integrations/Model Scope 規範見 [10016_架構分層與Model職責.md](10016_架構分層與Model職責.md)。

### 預設分層

```
Controller → Model（Eloquent + Scope）
```

大多數 CRUD **不需要 Service**，Controller 直接使用 Eloquent 與 Model Scope。

### 何時抽出 Service

滿足以下任一條件才抽 `app\Services\{Entity}Service`（flat 結構、按業務實體命名，**不**與 Controller 1:1）：

| 觸發條件 | 範例 |
|---------|------|
| 兩個以上 Portal 共用同一業務動作 | Ocadmin 與 PosCateringV3 都能建單 → `OrderService::create()` |
| 單一動作涉及多 Model 且有交易/狀態機 | 建單同時寫 orders、order_products、扣庫存 |
| 業務邏輯需脫離 HTTP context 重用 | 排程任務、Queue Job 呼叫同一業務動作 |

**Transaction 寫在哪**：由「被呼叫的那層」負責。Controller 直接操作 Model 時，Transaction 寫在 Controller；邏輯抽到 Service 時，Transaction 包在 Service 內（`DB::transaction(fn() => $this->save(...))`），讓 Service 可被各 Portal、Queue、排程任意組合呼叫，不需靠 Controller 幫忙開 Transaction。

### 不採用 Repository

Eloquent 已是 Active Record + Query Builder：查詢用 Model Scope，寫入/業務用 Service，**不額外包 Repository 層**。完整說明見 [10016_架構分層與Model職責.md](10016_架構分層與Model職責.md#為什麼不採用-repository)。

### 範例：簡單 CRUD vs 複雜邏輯

**簡單 CRUD**（如權限管理）Controller 直接處理：

```php
public function destroy(Permission $permission): JsonResponse
{
    $permission->delete();
    return response()->json(['success' => true, 'message' => $this->lang->text_success_delete]);
}
```

**複雜邏輯**需抽 `app\Services\{Entity}Service`，Transaction 包在 Service 內：

```php
namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function cancel(Order $order): void
    {
        if ($order->isPayedOff()) {
            CustomException::fail('已結清訂單不可取消');
        }
        DB::transaction(function () use ($order) {
            $order->products()->each(fn($op) => $op->restock());
            $order->status_code = 'void';
            $order->save();
        });
    }
}
```

> `CustomException::fail()` 會拋出例外，由全域 handler 統一回傳 JSON 格式。詳見 [10013_例外處理.md](10013_例外處理.md)。

---

## Core 與 Module 架構

Ocadmin Portal 內部分為 **Core** 和 **Modules** 兩個區域，各有不同定位。

### 定位差異

| | Core | Modules |
|--|------|---------|
| **定位** | 系統層級、跨專案通用 | 業務模組，隨專案需求新增 |
| **穩定性** | 高，不常變動 | 中，隨業務需求調整 |
| **範例** | ACL（權限/角色/使用者）、Config（分類/詞彙）、System（參數/日誌/Schema）、Login、Layout | Dashboard、Corp（公司）、HRM（人資）、Organization |
| **跨專案** | 直接複用，不需修改 | 各專案可能不同 |

**判斷原則：** 如果該功能在任何使用此框架的專案都會用到，放 Core；如果只有特定專案需要，放 Modules。

### 檔案放置規則

```
app/Portals/Ocadmin/
├── Core/                                    ← 系統層級
│   ├── Controllers/
│   │   ├── OcadminController.php            # 基礎 Controller
│   │   ├── LoginController.php              # 登入
│   │   ├── Acl/                             # 存取控制
│   │   │   ├── PermissionController.php
│   │   │   ├── RoleController.php
│   │   │   └── UserController.php
│   │   ├── Config/                          # 組態管理
│   │   │   ├── TaxonomyController.php
│   │   │   └── TermController.php
│   │   └── System/                          # 系統管理
│   │       ├── SettingController.php
│   │       ├── LogController.php
│   │       └── SchemaController.php
│   ├── Views/
│   │   ├── layouts/                         # 共用布局
│   │   │   ├── app.blade.php
│   │   │   ├── auth.blade.php
│   │   │   └── partials/
│   │   ├── pagination/                      # 分頁模板
│   │   ├── acl/permission/                  # 對應 Controllers/Acl/
│   │   │   ├── index.blade.php
│   │   │   ├── list.blade.php
│   │   │   └── form.blade.php
│   │   ├── config/taxonomy/                 # 對應 Controllers/Config/
│   │   └── system/setting/                  # 對應 Controllers/System/
│   ├── ViewComposers/
│   │   ├── MenuComposer.php
│   │   └── LocaleComposer.php
│   └── Providers/
│       └── OcadminServiceProvider.php
│
├── Modules/                                 ← 業務模組
│   ├── Dashboard/
│   │   ├── DashboardController.php
│   │   └── Views/
│   │       └── index.blade.php
│   ├── Corp/
│   │   └── Company/
│   │       ├── CompanyController.php
│   │       └── Views/
│   ├── Hrm/
│   │   └── Employee/
│   │       ├── EmployeeController.php
│   │       └── Views/
│   └── Organization/
│       ├── OrganizationController.php
│       └── Views/
│
└── routes/
    └── ocadmin.php                          # 所有路由集中定義
```

### View Namespace 機制

`OcadminServiceProvider` 自動註冊 View namespace：

| 區域 | 註冊方式 | Namespace | 使用範例 |
|------|---------|-----------|---------|
| Core | 手動註冊 | `ocadmin::` | `view('ocadmin::acl.permission.index')` |
| Modules | 自動掃描 | `ocadmin.{module}::` | `view('ocadmin.dashboard::index')` |

```php
// OcadminServiceProvider.php
View::addNamespace('ocadmin', $basePath . '/Core/Views');        // Core
$this->loadModuleViews($basePath . '/Modules', '');              // Modules（自動掃描）
```

Modules 目錄下的所有含 `Views/` 子目錄的模組會被自動註冊，不需要手動設定。

### 新增模組步驟

新增一個 Ocadmin 功能模組（以 Core/System 下的「Schema 管理」為例）：

1. **Controller** — 建立 `Core/Controllers/System/SchemaController.php`，繼承 `OcadminController`
2. **Views** — 建立 `Core/Views/system/schema/` 目錄，放入 `index.blade.php`、`list.blade.php`、`form.blade.php`
3. **語系檔** — 建立 `lang/zh_Hant/system/schema.php`
4. **路由** — 在 `routes/ocadmin.php` 的 `system` 群組內新增路由
5. **選單** — 在 `MenuComposer::buildMenus()` 新增選單項目

> 各步驟的詳細規範見後續章節。

---

## 目錄結構

### Controller 與 View 位置

```
app/Portals/Ocadmin/
├── Core/
│   ├── Controllers/
│   │   ├── OcadminController.php   # Portal 基礎 Controller
│   │   ├── Acl/
│   │   │   └── PermissionController.php
│   │   └── Config/
│   │       ├── TaxonomyController.php
│   │       └── TermController.php
│   ├── Views/
│   │   ├── layouts/
│   │   │   ├── app.blade.php       # 主布局
│   │   │   └── partials/
│   │   ├── acl/
│   │   │   └── permission/
│   │   │       ├── index.blade.php
│   │   │       ├── list.blade.php
│   │   │       └── form.blade.php
│   │   └── config/
│   │       ├── taxonomy/
│   │       └── term/
│   └── ViewComposers/
│       └── MenuComposer.php
├── Modules/                         # 業務模組
│   ├── Dashboard/
│   └── System/
└── routes/
    └── ocadmin.php
```

---

## 基礎 Controller（OcadminController）

每個 Portal 有自己的基礎 Controller。Ocadmin 使用 `OcadminController`：

```php
namespace App\Portals\Ocadmin\Core\Controllers;

use App\Libraries\TranslationLibrary;
use Illuminate\Routing\Controller as BaseController;

class OcadminController extends BaseController
{
    protected array $breadcrumbs = [];
    protected $lang;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->getLang($this->setLangFiles());
            $this->setBreadcrumbs();
            return $next($request);
        });
    }

    protected function setLangFiles(): array
    {
        return ['common'];
    }

    protected function getLang(string|array $groups): void
    {
        if (!isset($this->lang)) {
            $this->lang = app(TranslationLibrary::class)->load($groups);
        }
    }

    protected function setBreadcrumbs(): void { }

    /**
     * 從 Request 取得白名單過濾參數
     *
     * 共用參數（search, sort, order, page, limit, per_page）自動允許，
     * 各 Controller 只需指定額外允許的 filter_* / equal_* 欄位。
     */
    protected function filterData(Request $request, array $allowedFilters = []): array
    {
        return $request->only(array_merge(
            ['search', 'sort', 'order', 'page', 'limit', 'per_page'],
            $allowedFilters
        ));
    }

    protected function buildUrlParams(Request $request): string { /* ... */ }
}
```

### 為什麼使用 middleware closure

`getLang()` 必須在 middleware closure 內呼叫，**不能**寫在 `__construct()` 本體：

```
請求生命週期：
Constructor → Route Middleware（SetLocale 設定語系）→ Controller Middleware closure → Action
```

在 `__construct()` 時 `app()->getLocale()` 尚未被 `SetLocale` middleware 設定，語言檔會讀取錯誤的語系。middleware closure 在 route middleware 之後執行，確保語系正確。

### 子類別覆寫 setLangFiles()

子類別覆寫 `setLangFiles()` 指定語言檔，**後者覆蓋前者**：

```php
class PermissionController extends OcadminController
{
    protected function setLangFiles(): array
    {
        return ['common', 'ocadmin/acl/permission'];
    }
}
```

| 順序 | 語言檔 | 說明 |
|------|--------|------|
| 1 | `common` | 共用翻譯（按鈕、欄位、通用文字） |
| 2 | `ocadmin/acl/permission` | 模組翻譯（覆蓋同名 key） |

---

## 多語系（Language Files）

### 語言檔目錄結構

```
lang/
├── zh_Hant/                       # 繁體中文
│   ├── common.php                 # 共用翻譯
│   └── ocadmin/
│       ├── acl/
│       │   └── permission.php     # 權限管理
│       └── config/
│           ├── taxonomy.php       # 分類管理
│           └── term.php           # 詞彙管理
└── en/                            # English（未來擴充）
    └── common.php
```

### 語言檔命名規範

| 類型 | 路徑 | 範例 |
|------|------|------|
| 共用 | `lang/{locale}/common.php` | `common` |
| 模組 | `lang/{locale}/ocadmin/{category}/{module}.php` | `ocadmin/acl/permission` |

### 語言 Key 命名慣例

| 前綴 | 用途 | 範例 |
|------|------|------|
| `heading_` | 頁面標題 | `heading_title` |
| `text_` | 一般文字、訊息 | `text_list`, `text_success_add` |
| `column_` | 欄位標籤（列表、表單、篩選共用） | `column_name`, `column_display_name` |
| `placeholder_` | 輸入提示 | `placeholder_name` |
| `help_` | 欄位說明 | `help_name` |
| `error_` | 錯誤訊息 | `error_has_roles` |
| `button_` | 按鈕文字 | `button_save`（通常放 common） |
| `tab_` | Tab 標籤 | `tab_basic`、`tab_trans`（共用兩個放 default.php，模組自訂可放各自的 lang 檔） |

> **不使用 `entry_` 前綴。** 因為列表欄位標題與表單欄位標籤大部分相同，統一使用 `column_` 避免重複定義。篩選欄位標籤也直接複用 `column_*`。

#### Tab 標籤統一使用 `tab_basic` / `tab_trans`

全後台規範**只標準化兩個 tab key**（其它 tab 名由各模組自訂、不統一）：

| Tab ID | Lang Key | 中文 | 用途 |
|--------|----------|------|------|
| `tab-basic` | `tab_basic` | 基本資料 | 該表的核心欄位（status / sort / 各種設定 switch …） |
| `tab-trans` | `tab_trans` | 多語資料 | 翻譯欄位（name / description / SEO meta …） |

兩個 key **集中放在 `lang/zh_Hant/admin/default.php`**，模組 lang 檔不要再重複定義。

**禁止使用：**
- `tab-data` / `tab_data` — 語意太籠統（`tab-trans` 也是 data），新代碼一律用 `tab-basic`
- `tab-general` / `tab_general` — OpenCart 4.x 的命名遺留（其原意是「General Information = 對外行銷文案，自然多語化」），跟我們業務系統的「基本資料」概念不對等，造成不同模組語意飄移：
  - OpenCart 自身：`tab-general` = 多語名稱描述、`tab-data` = SKU/價格/庫存
  - 本系統若用：`tab-general` 容易在不同模組指向不同東西（有人當「基本」有人當「多語」）
  - 結論：拋棄 OpenCart 此命名，改用 `tab-basic` + `tab-trans` 二分

**Blade 寫法範例：**

```blade
<ul class="nav nav-tabs">
    <li class="nav-item"><a href="#tab-basic" data-bs-toggle="tab" class="nav-link active">{{ $lang->tab_basic }}</a></li>
    <li class="nav-item"><a href="#tab-trans" data-bs-toggle="tab" class="nav-link">{{ $lang->tab_trans }}</a></li>
</ul>
<div class="tab-content">
    <div id="tab-basic" class="tab-pane active">{{-- 基本欄位 --}}</div>
    <div id="tab-trans" class="tab-pane">{{-- 多語欄位 --}}</div>
</div>
```

**何時不分 tab：**

當「多語欄位只有一個 `name`」這類極簡情境，沒必要拆 tab；改用 input-group 內嵌進「基本資料」即可（仿 `catalog/option/form.blade.php`）：

```blade
<div class="row mb-3 required">
    <label class="col-sm-2 col-form-label">{{ $lang->column_name }}</label>
    <div class="col-sm-10">
        @foreach($locales as $locale)
        <div class="input-group mb-1">
            <span class="input-group-text">{{ $localeNames[$locale] ?? $locale }}</span>
            <input type="text" name="translations[{{ $locale }}][name]" ... class="form-control">
        </div>
        @endforeach
    </div>
</div>
```

只有當多語欄位 ≥ 2（如 name + description + meta_title + ...）才值得拆獨立的 `tab-trans`。

**模組自訂 tab 不在規範範圍：**

像 `tab-users`（單據分類授權）、`tab-option`（商品選項）、`tab-image`（圖片）等屬於該模組自身的 tab，命名與 lang key 由模組自行決定（如 `tab_users`、`tab_option`、`tab_image`），**不集中**到 default.php。

### TranslationBag 使用

`$this->lang` 是 `TranslationBag` 物件，支援物件式存取：

```php
// 讀取
$this->lang->heading_title;        // 魔術方法 __get
$this->lang->get('heading_title'); // 等同上方

// 設定（覆蓋或動態新增）
$this->lang->text_custom = '自訂文字';
$this->lang->set('text_custom', '自訂文字');

// 批次合併
$this->lang->merge(['key1' => 'value1', 'key2' => 'value2']);
```

### View 中使用 $lang

Controller 傳遞 `$lang` 到 View，View 中以 `$lang->key` 存取：

```blade
{{-- 文字 --}}
<h1>{{ $lang->heading_title }}</h1>

{{-- 按鈕 --}}
<button title="{{ $lang->button_save }}">

{{-- 欄位標籤（列表、表單、篩選共用） --}}
<label>{{ $lang->column_name }}</label>

{{-- 分頁統計（PHP sprintf） --}}
{!! sprintf($lang->text_showing, $permissions->firstItem() ?? 0, $permissions->lastItem() ?? 0, $permissions->total()) !!}

{{-- JS 中使用（注意：透過 Blade 輸出） --}}
<script>
    alert('{{ $lang->error_select_delete }}');
    confirm('{{ $lang->text_confirm_batch_delete }}'.replace('%s', selected.length));
</script>
```

---

## View 資料傳遞規範

Controller 傳遞資料到 View 時，使用 `$data['key'] = value` **逐行指定**：

```php
// ✓ 正確寫法：逐行指定
public function index(Request $request): View
{
    $data['lang'] = $this->lang;
    $data['breadcrumbs'] = $this->breadcrumbs;
    $data['list'] = $this->getList($request);

    return view('ocadmin::acl.permission.index', $data);
}

// ✗ 不使用 compact()
return view('ocadmin::acl.permission.index', compact('permissions'));
```

**必須傳遞的共用資料：**

| Key | 說明 | 來源 |
|-----|------|------|
| `lang` | 多語翻譯物件（`TranslationBag`） | `$this->lang` |
| `breadcrumbs` | 麵包屑（含 View 的方法才需要） | `$this->breadcrumbs` |

**原因：**
- 明確列出傳遞的變數，易於追蹤
- 避免 `compact()` 變數名稱打錯造成的隱性錯誤
- 方便統一加入 `lang`、`breadcrumbs` 等共用資料

---

## 麵包屑設定

每個 Controller 需覆寫 `setBreadcrumbs()` 方法：

```php
class PermissionController extends OcadminController
{
    protected function setBreadcrumbs(): void
    {
        $this->breadcrumbs = [
            (object)[
                'text' => $this->lang->text_home,
                'href' => route('lang.ocadmin.dashboard'),
            ],
            (object)[
                'text' => $this->lang->text_system,
                'href' => 'javascript:void(0)',
            ],
            (object)[
                'text' => $this->lang->heading_title,
                'href' => route('lang.ocadmin.system.permissions.index'),
            ],
        ];
    }
}
```

**注意：** `setBreadcrumbs()` 在 middleware closure 中於 `getLang()` 之後呼叫，因此可安全使用 `$this->lang`。

| 欄位 | 說明 |
|------|------|
| `text` | 顯示文字 |
| `href` | 連結網址（中間節點使用 `javascript:void(0)`） |

---

## Controller 範例

### 完整範例：PermissionController

```php
<?php

namespace App\Portals\Ocadmin\Core\Controllers\Acl;

use App\Helpers\Classes\LocaleHelper;
use App\Helpers\Classes\OrmHelper;
use App\Models\Acl\Permission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Portals\Ocadmin\Core\Controllers\OcadminController;
use Spatie\Permission\PermissionRegistrar;

class PermissionController extends OcadminController
{
    protected function setLangFiles(): array
    {
        return ['common', 'ocadmin/acl/permission'];
    }

    protected function setBreadcrumbs(): void
    {
        $this->breadcrumbs = [
            (object)['text' => $this->lang->text_home, 'href' => route('lang.ocadmin.dashboard')],
            (object)['text' => $this->lang->text_system, 'href' => 'javascript:void(0)'],
            (object)['text' => $this->lang->heading_title, 'href' => route('lang.ocadmin.system.permissions.index')],
        ];
    }

    /**
     * 列表頁（初始載入）
     */
    public function index(Request $request): View
    {
        $data['lang'] = $this->lang;
        $data['breadcrumbs'] = $this->breadcrumbs;
        $data['list'] = $this->getList($request);

        // JavaScript 用 URL（避免在 Blade JS 區塊內呼叫 route()）
        $data['list_url']         = route('lang.ocadmin.system.permissions.list');
        $data['index_url']        = route('lang.ocadmin.system.permissions.index');
        $data['add_url']          = route('lang.ocadmin.system.permissions.create');
        $data['batch_delete_url'] = route('lang.ocadmin.system.permissions.batch-delete');

        return view('ocadmin::acl.permission.index', $data);
    }

    /**
     * AJAX 入口（列表刷新）
     */
    public function list(Request $request): string
    {
        return $this->getList($request);
    }

    /**
     * 核心查詢邏輯
     */
    protected function getList(Request $request): string
    {
        $query = Permission::with('translations');
        $filter_data = $this->filterData($request);

        // 預設排序
        $filter_data['sort'] = $request->query('sort', 'name');
        $filter_data['order'] = $request->query('order', 'asc');

        // search 關鍵字查詢（優先處理，涵蓋的欄位從 filter_data 移除避免 prepare 重複處理）
        //
        // ⚠ 效能注意：filterOrEqualColumn 使用 REGEXP 比對，MySQL 的 B-tree 索引對 REGEXP 無效，
        //   因此對這些欄位加索引並不會加速查詢。在目前資料量下，全表掃描的成本可接受。
        //   若未來資料量大到查詢明顯變慢，應考慮：
        //   - MySQL FULLTEXT 全文索引（適合純文字模糊搜尋，但不支援萬用字元語法）
        //   - Laravel Scout + Meilisearch（獨立搜尋引擎，支援中文分詞、模糊比對、權重排序）
        if ($request->filled('search')) {
            $search = $request->search;
            $locale = app()->getLocale();

            $query->where(function ($q) use ($search, $locale) {
                OrmHelper::filterOrEqualColumn($q, 'filter_name', $search);

                $q->orWhereHas('translations', function ($tq) use ($search, $locale) {
                    $tq->where('locale', $locale);
                    $tq->where(function ($sq) use ($search) {
                        OrmHelper::filterOrEqualColumn($sq, 'filter_display_name', $search);
                        $sq->orWhere(function ($sq2) use ($search) {
                            OrmHelper::filterOrEqualColumn($sq2, 'filter_note', $search);
                        });
                    });
                });
            });

            unset($filter_data['search'], $filter_data['filter_name'], $filter_data['filter_display_name'], $filter_data['filter_note']);
        }

        // OrmHelper 處理剩餘的 filter_*, equal_* 及排序
        OrmHelper::prepare($query, $filter_data);

        // 分頁結果
        $permissions = OrmHelper::getResult($query, $filter_data);
        $permissions->withPath(route('lang.ocadmin.system.permissions.list'));

        $data['lang'] = $this->lang;
        $data['permissions'] = $permissions;
        $data['pagination'] = $permissions->links('ocadmin::pagination.default');

        // 建構 URL 參數與排序連結
        $url = $this->buildUrlParams($request);
        $baseUrl = route('lang.ocadmin.system.permissions.list');
        $data['sort'] = $filter_data['sort'];
        $data['order'] = $filter_data['order'];
        $nextOrder = ($data['order'] == 'asc') ? 'desc' : 'asc';

        $data['sort_name'] = $baseUrl . "?sort=name&order={$nextOrder}" . str_replace('?', '&', $url);
        $data['sort_display_name'] = $baseUrl . "?sort=display_name&order={$nextOrder}" . str_replace('?', '&', $url);

        return view('ocadmin::acl.permission.list', $data)->render();
    }

    /**
     * 新增表單
     */
    public function create(): View
    {
        $data['lang'] = $this->lang;
        $data['breadcrumbs'] = $this->breadcrumbs;
        $data['permission'] = new Permission();

        // 表單用 URL（避免在 Blade 內呼叫 route()）
        $data['save_url'] = route('lang.ocadmin.system.permissions.store');
        $data['back_url'] = route('lang.ocadmin.system.permissions.index');

        return view('ocadmin::acl.permission.form', $data);
    }

    /**
     * 編輯表單
     */
    public function edit(Permission $permission): View
    {
        $data['lang'] = $this->lang;
        $data['breadcrumbs'] = $this->breadcrumbs;
        $data['permission'] = $permission;

        // 編輯時 save_url 指向 update 路由
        $data['save_url'] = route('lang.ocadmin.system.permissions.update', $permission);
        $data['back_url'] = route('lang.ocadmin.system.permissions.index');

        return view('ocadmin::acl.permission.form', $data);
    }

    /**
     * 儲存新資料（回傳 JSON）
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:acl_permissions,name',
            'guard_name' => 'nullable|string|max:50',
            // 翻譯欄位...
        ]);

        $permission = Permission::create($validated);
        $permission->saveTranslations($validated['translations']);

        return response()->json([
            'success' => true,
            'message' => $this->lang->text_success_add,
            'replace_url' => route('lang.ocadmin.system.permissions.edit', $permission),
            'form_action' => route('lang.ocadmin.system.permissions.update', $permission),
        ]);
    }

    /**
     * 更新資料（回傳 JSON）
     */
    public function update(Request $request, Permission $permission): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:acl_permissions,name,' . $permission->id,
            'guard_name' => 'nullable|string|max:50',
            // 翻譯欄位...
        ]);

        $permission->update($validated);
        $permission->saveTranslations($validated['translations']);

        return response()->json([
            'success' => true,
            'message' => $this->lang->text_success_edit,
        ]);
    }
}
```

### 關鍵模式

| 方法 | 回傳類型 | 說明 |
|------|----------|------|
| `index()` | `View` | 初始載入，呼叫 `getList()` |
| `list()` | `string` | AJAX 入口，回傳 HTML 片段 |
| `getList()` | `string` | 核心查詢，渲染 `list.blade.php` |
| `create()` / `edit()` | `View` | 表單頁 |
| `store()` / `update()` | `JsonResponse` | AJAX 提交，回傳 JSON |
| `destroy()` | `JsonResponse` | 刪除，回傳 JSON |

---

## OrmHelper 查詢輔助

### 概述

`OrmHelper`（`App\Helpers\Classes\OrmHelper`）是 Eloquent 查詢輔助類別，自動處理篩選、排序、分頁。在 `getList()` 中使用。

### 基本使用

```php
$query = Permission::with('translations');

// 白名單取得過濾參數（只允許指定的 filter_*/equal_* 欄位）
$filter_data = $this->filterData($request);

// 設定預設排序
$filter_data['sort'] = $request->query('sort', 'name');
$filter_data['order'] = $request->query('order', 'asc');

// search 關鍵字查詢（視需要，必須在 prepare 之前）
// ... 詳見下方「search 關鍵字查詢」章節

// 自動處理剩餘的 filter_*, equal_* 及排序
OrmHelper::prepare($query, $filter_data);

// 分頁結果
$result = OrmHelper::getResult($query, $filter_data);
```

### filterData() 白名單機制

`OcadminController::filterData()` 使用 `$request->only()` 取代 `$request->all()`，防止使用者自行注入未預期的 `filter_*` / `equal_*` 參數查詢任意欄位（例如 `equal_password` 探測密碼）。

**共用參數**自動允許，不需指定：

| 參數 | 說明 |
|------|------|
| `search` | 關鍵字搜尋（不以 `filter_`/`equal_` 開頭，OrmHelper 不會處理） |
| `sort`, `order` | 排序（OrmHelper 內部會驗證欄位是否存在） |
| `page`, `limit`, `per_page` | 分頁 |

**各 Controller 只需指定額外允許的 `filter_*` / `equal_*` 欄位：**

```php
// 無額外篩選欄位
$filter_data = $this->filterData($request);

// 指定允許的篩選欄位
$filter_data = $this->filterData($request, ['filter_model', 'equal_status', 'equal_is_active']);
```

**使用範例：**

```php
// ProductController — 允許 filter_model、equal_status、equal_is_active
$filter_data = $this->filterData($request, ['filter_name', 'filter_model', 'equal_status', 'equal_is_active']);

// TermController — 允許依分類篩選
$filter_data = $this->filterData($request, ['equal_taxonomy_id', 'equal_is_active']);

// LogController — 允許入口、方法、狀態篩選及日期範圍
$filter_data = $this->filterData($request, ['equal_portal', 'equal_method', 'equal_status', 'filter_date_start', 'filter_date_end']);

// PermissionController — 無額外篩選
$filter_data = $this->filterData($request);
```

### 參數命名規範

| 前綴 | 用途 | 範例 |
|-----|------|------|
| `filter_欄位名` | 彈性查詢（支援萬用字元、REGEXP） | `filter_name=mss*` |
| `equal_欄位名` | 精確查詢 | `equal_guard_name=web` |
| `sort` | 排序欄位 | `sort=name` |
| `order` | 排序方向 | `order=asc` |

> **欄位命名規則**：文字搜尋用 `filter_`；ID 或固定選項（下拉選單）用 `equal_`，JS 直接傳值，OrmHelper 自動做完全比對，**不需**在值前手動加 `=` 前綴。
>
> ```javascript
> // ✅ 正確：equal_ 前綴，直接傳 ID
> params.set('equal_category_id', categoryId);
>
> // ❌ 錯誤：filter_ 前綴搭配手動 = 前綴（舊寫法，已廢棄）
> params.set('filter_category_id', '=' + categoryId);
> ```

### 翻譯欄位自動處理

如果 Model 使用 `HasTranslation` trait 並定義了 `$translatedAttributes`，OrmHelper 會**自動處理翻譯欄位**：

- **篩選**：偵測 `filter_display_name` 等參數，自動透過 `whereHas('translations')` 查詢翻譯子表，限定當前語系，並複用 `filterOrEqualColumn()` 的運算符支援（REGEXP、`*`、`<>` 等）
- **排序**：偵測 `sort=display_name`，自動用子查詢到翻譯表排序，限定當前語系

```php
// Model 定義
class Permission extends SpatiePermission
{
    use HasTranslation;

    protected array $translatedAttributes = ['display_name', 'note'];
}

// Controller 中完全不需要手動處理翻譯篩選
$query = Permission::with('translations');
$filter_data = $this->filterData($request, ['filter_name', 'filter_display_name']);
$filter_data['sort'] = $request->query('sort', 'name');
$filter_data['order'] = $request->query('order', 'asc');

// filter_name → 主表自動處理
// filter_display_name → 翻譯表自動處理
OrmHelper::prepare($query, $filter_data);
```

### 需手動處理的情境

| 情境 | 說明 | 範例 |
|------|------|------|
| 關聯查詢 | 非翻譯的其他關聯表 | `filter_role_id` → `whereHas('roles', ...)` |
| 跨表搜尋 | 多欄位 OR 查詢 | `search` → 手動組合 `where(function($q) {...})` |

### search 關鍵字查詢

**必須在 `OrmHelper::prepare()` 之前處理**，並將 search 涵蓋的欄位從 `$filter_data` 中移除，避免 `prepare()` 重複處理相同欄位。

```php
// search 關鍵字查詢（優先處理，涵蓋的欄位從 filter_data 移除避免 prepare 重複處理）
if ($request->filled('search')) {
    $search = $request->search;
    $locale = app()->getLocale();

    $query->where(function ($q) use ($search, $locale) {
        // 主表欄位
        OrmHelper::filterOrEqualColumn($q, 'filter_name', $search);

        // 翻譯欄位（OR）
        $q->orWhereHas('translations', function ($tq) use ($search, $locale) {
            $tq->where('locale', $locale);
            $tq->where(function ($sq) use ($search) {
                OrmHelper::filterOrEqualColumn($sq, 'filter_display_name', $search);
                $sq->orWhere(function ($sq2) use ($search) {
                    OrmHelper::filterOrEqualColumn($sq2, 'filter_note', $search);
                });
            });
        });
    });

    // 移除 search 涵蓋的欄位，避免 prepare() 重複處理
    unset($filter_data['search'], $filter_data['filter_name'], $filter_data['filter_display_name'], $filter_data['filter_note']);
}

// OrmHelper 處理剩餘的 filter_*, equal_* 及排序
OrmHelper::prepare($query, $filter_data);
```

**重點：**

| 項目 | 說明 |
|------|------|
| 放置位置 | **`OrmHelper::prepare()` 之前** |
| 主表欄位 | 直接使用 `OrmHelper::filterOrEqualColumn($q, 'filter_欄位', $search)` |
| 翻譯欄位 | 透過 `orWhereHas('translations')` 並限定 `locale` |
| 多欄位邏輯 | 外層 `where()` 包裹，各欄位間使用 `orWhere` / `orWhereHas` |
| 清除參數 | `unset` search 涵蓋的所有欄位（`search`、`filter_name`、`filter_display_name`、`filter_note`），避免 `prepare()` 重複處理 |

### OrmHelper::getResult() 分頁

分頁每頁筆數讀取 `config('settings.config_admin_pagination_limit', 10)`，也可在 `$filter_data` 中指定 `limit`。

---

## 列表排序與網址保留規範

### 概述

列表頁的排序（sort/order）與分頁（page）狀態必須保留在瀏覽器網址中，讓使用者：
1. 點擊排序/分頁後，瀏覽器網址同步更新
2. 進入編輯表單後，返回按鈕能回到原本的篩選、排序、頁數狀態

### OcadminController 共用方法

`buildUrlParams()` 只包含篩選參數（filter_*/equal_*/search/limit），**不包含** sort/order/page，避免排序連結重複。

新增 `buildEditUrlParams()` 方法，將 filter + sort/order/page 合併，供編輯連結使用：

```php
// OcadminController.php
protected function buildEditUrlParams(Request $request): string
{
    $url = $this->buildUrlParams($request);

    $extra = [];
    if ($request->filled('sort')) {
        $extra[] = 'sort=' . urlencode($request->sort);
    }
    if ($request->filled('order')) {
        $extra[] = 'order=' . urlencode($request->order);
    }
    if ($request->filled('page') && (int) $request->page > 1) {
        $extra[] = 'page=' . (int) $request->page;
    }

    if (empty($extra)) return $url;
    $extraStr = implode('&', $extra);
    return $url ? $url . '&' . $extraStr : '?' . $extraStr;
}
```

### Controller getList() 使用方式

```php
$url = $this->buildUrlParams($request);          // 篩選參數（排序連結用）
$data['urlParams'] = $this->buildEditUrlParams($request); // 篩選 + sort/order/page（編輯連結用）

// 排序連結使用 $url（不含 sort/order，避免重複）
$data['sort_name'] = $baseUrl . "?sort=name&order={$nextOrder}" . str_replace('?', '&', $url);
```

| 方法 | 用途 | 包含參數 |
|------|------|----------|
| `buildUrlParams()` | 排序連結 | filter/equal/search/limit |
| `buildEditUrlParams()` | 編輯連結、返回連結 | 上述 + sort/order/page |

### index.blade.php AJAX 點擊更新網址

排序與分頁的 AJAX 載入後，必須用 `pushState` 同步瀏覽器網址：

```javascript
$('#xxx-list').on('click', 'thead a, .pagination a', function(e) {
    e.preventDefault();
    var href = $(this).attr('href');
    $('#xxx-list').load(href);
    window.history.pushState({}, null, href.replace(/\/list\b/, ''));
});
```

`href.replace(/\/list\b/, '')` 將 AJAX 的 `/list` 路由轉換為 `index` 路由的網址格式。

### list.blade.php 編輯連結帶參數

```blade
<a href="{{ route('lang.ocadmin.xxx.edit', $item) . $urlParams }}">
```

### form.blade.php 返回連結帶參數

```blade
<a href="{{ route('lang.ocadmin.xxx.index') . (request()->getQueryString() ? '?' . request()->getQueryString() : '') }}">
```

進入表單時 URL 帶有 sort/order/page 等參數，`request()->getQueryString()` 自動將它們帶回列表頁。

---

## 分頁規範

### 自訂分頁 View

使用 Bootstrap 5 分頁樣式，位於 `app/Portals/Ocadmin/Core/Views/pagination/default.blade.php`。

**不要**在 Blade 中直接呼叫 `{{ $items->links() }}`（預設為 Tailwind 樣式），改由 Controller 產生分頁 HTML 後傳遞到 View。

### Controller 產生分頁

在 `getList()` 中，`withPath()` 之後產生 `$data['pagination']`：

```php
$permissions = OrmHelper::getResult($query, $filter_data);
$permissions->withPath(route('lang.ocadmin.system.permissions.list'));

$data['permissions'] = $permissions;
$data['pagination'] = $permissions->links('ocadmin::pagination.default');
```

### Blade 輸出

在 `list.blade.php` 中使用 `{!! $pagination !!}` 輸出：

```blade
<div class="row">
    <div class="col-sm-6 text-start">{!! $pagination !!}</div>
    <div class="col-sm-6 text-end">{!! sprintf($lang->text_showing, $items->firstItem() ?? 0, $items->lastItem() ?? 0, $items->total()) !!}</div>
</div>
```

### AJAX 分頁攔截

`index.blade.php` 的 JavaScript 攔截 `.pagination a` 的點擊事件，改用 AJAX 載入：

```javascript
$('#permission-list').on('click', 'thead a, .pagination a', function(e) {
    e.preventDefault();
    $('#permission-list').load($(this).attr('href'));
});
```

| 項目 | 說明 |
|------|------|
| 分頁 View | `ocadmin::pagination.default`（Bootstrap 5） |
| Controller 職責 | 產生 `$data['pagination']`，View 不需知道用哪個分頁模板 |
| Blade 輸出 | `{!! $pagination !!}`（unescaped，因為是 HTML） |
| 預設分頁 `->links()` | **禁止**在 Blade 中直接使用（Tailwind 樣式不相容） |

---

## 表單 AJAX 提交規範

### 概述

Ocadmin 的表單頁面採用 AJAX 方式提交，**儲存成功後仍留在表單頁**，並顯示成功訊息。由 `common.js` 統一處理 `data-oc-toggle="ajax"` 表單。

### Controller 回應格式

Controller **只需處理成功回應**，錯誤由全域 handler 統一處理：

```php
// 驗證：直接使用 $request->validate()
// 驗證失敗時自動拋出 ValidationException，由全域 handler 回傳 422 + 統一 JSON 格式
$validated = $request->validate($rules);

// 新增成功
return response()->json([
    'success' => true,
    'message' => $this->lang->text_success_add,
    'replace_url' => route('lang.ocadmin.system.permissions.edit', $permission),
    'form_action' => route('lang.ocadmin.system.permissions.update', $permission),
]);

// 更新成功
return response()->json([
    'success' => true,
    'message' => $this->lang->text_success_edit,
]);

// 刪除成功
return response()->json([
    'success' => true,
    'message' => $this->lang->text_success_delete,
]);
```

> 驗證失敗、未認證、權限不足、業務邏輯錯誤等情況，全部由 `bootstrap/app.php` 的全域 handler 統一回傳。詳見 [10013_例外處理.md](10013_例外處理.md) 及 [10014_JSON回應格式.md](10014_JSON回應格式.md)。

### JSON 回應格式

統一格式（詳見 [10014_JSON回應格式.md](10014_JSON回應格式.md)）：

```json
{
    "success": true/false,
    "message": "顯示文字",
    "errors": { "欄位": "錯誤訊息" },
    "data": { ... }
}
```

| 欄位 | 類型 | 必要 | 說明 |
|------|------|------|------|
| `success` | boolean | 必要 | 成功/失敗的唯一判斷依據，前端據此決定 Toast 顏色 |
| `message` | string | 必要 | 顯示給用戶的文字 |
| `errors` | object | 選填 | 欄位驗證錯誤（`{ field: message }`），僅驗證失敗時出現 |
| `data` | any | 選填 | 資料載荷（API 回傳資料用） |

**Ocadmin 專屬欄位（UI 行為控制）：**

| 欄位 | 類型 | 說明 |
|------|------|------|
| `redirect` | string | 全頁跳轉 URL |
| `replace_url` | string | 更新瀏覽器網址列（新增→編輯轉換用） |
| `form_action` | string | 更新表單 action URL（搭配 `replace_url` 使用） |

### 表單 HTML 結構

```blade
<form id="form-permission"
      action="{{ $permission->exists ? route('...update', $permission) : route('...store') }}"
      method="post"
      data-oc-toggle="ajax">
    @csrf
    @if($permission->exists)
    @method('PUT')
    @endif

    <div class="row mb-3 required">
        <label for="input-name" class="col-sm-2 col-form-label">{{ $lang->column_name }}</label>
        <div class="col-sm-10">
            <input type="text" name="name" value="..." placeholder="{{ $lang->placeholder_name }}" id="input-name" class="form-control">
            <div id="error-name" class="invalid-feedback"></div>
        </div>
    </div>

    <div class="row mb-3">
        <label for="input-parent_id" class="col-sm-2 col-form-label">{{ $lang->column_parent }}</label>
        <div class="col-sm-10">
            <select name="parent_id" id="input-parent_id" class="form-select">...</select>
            <div id="error-parent_id" class="invalid-feedback"></div>
        </div>
    </div>
</form>
```

### ID 命名規範

| 元素 | ID 格式 | 範例 |
|------|---------|------|
| 輸入欄位 | `input-{column_name}` | `id="input-parent_id"` |
| 錯誤訊息 | `error-{column_name}` | `id="error-parent_id"` |
| 外層容器 (row) | 無 ID | — |

**`-` 是結構分隔符，`_` 保留在欄位名稱內。** ID 中的 `{column_name}` 就是 `name` 屬性的值（即資料庫欄位名）。

> 與 OpenCart 原始做法的差異及設計決策，詳見 [00004_Ocadmin-common.js說明.md](00004_Ocadmin-common.js說明.md)。

### 翻譯欄位的 Blade 寫法

翻譯欄位 ID 直接使用 `{{ $locale }}`，不需 `str_replace`。驗證錯誤由全域 handler 自動轉為扁平 key（如 `display_name-zh_Hant`）。

> 轉換流程詳見 [00004_Ocadmin-common.js說明.md](00004_Ocadmin-common.js說明.md)，全域 handler 邏輯見 [10013_例外處理.md](10013_例外處理.md)。

```blade
<div class="row mb-3 required">
    <label for="input-display_name-{{ $locale }}" class="col-sm-2 col-form-label">{{ $lang->column_display_name }}</label>
    <div class="col-sm-10">
        <input ... id="input-display_name-{{ $locale }}" class="form-control">
        <div id="error-display_name-{{ $locale }}" class="invalid-feedback"></div>
    </div>
</div>
```

### 關鍵屬性

| 屬性 | 說明 |
|------|------|
| `data-oc-toggle="ajax"` | 啟用 AJAX 表單提交（`common.js` 攔截） |
| `id="form-xxx"` | 表單 ID（對應儲存按鈕的 `form` 屬性） |

### 儲存按鈕

```blade
<button type="submit" form="form-permission" class="btn btn-primary">
    <i class="fa-solid fa-save"></i>
</button>
```

使用 `form="form-permission"` 屬性關聯表單，按鈕可放在表單外部（如頁首工具列）。

### 注意事項

> 表單提交的完整運作流程（common.js 如何攔截、處理回應、標記錯誤），詳見 [00004_Ocadmin-common.js說明.md](00004_Ocadmin-common.js說明.md)。

- **不使用 `@error` Blade 指令**：改用 `<div id="error-xxx" class="invalid-feedback"></div>`
- **不使用 `redirect()`**：Controller 一律回傳 JSON
- **使用 `$request->validate()`**：驗證失敗自動拋出 `ValidationException`，由全域 handler 回傳 422 + 統一 JSON 格式（不需手動 `validator()->fails()` + 回傳 JSON）
- **新增後切換為編輯模式**：透過 `replace_url` 和 `form_action` 更新頁面狀態

---

## 視圖規範

視圖三層架構、列表頁 / 表單頁布局、共用 partial（核准印章 / 狀態快速操作）、AJAX 機制、多語慣例 → 詳見 [00008_Ocadmin視圖規範](00008_Ocadmin視圖規範.md)。

該文件涵蓋：
- 視圖三層架構（index / list / form）
- 列表頁規範（篩選 sidebar、啟用篩選欄位、狀態多選、重設 / 清除 / 搜尋按鈕、placeholder 慣例）
- 表單頁規範（card 結構、nav-tabs 位置、欄位 col-sm-2/10、approval_stamp / approval_buttons partial、isEditable 機制）
- AJAX 互動規範
- 多語規範（default.php 共用 key）
- 開發檢查清單

---


## 路由規範

### 標準 CRUD 路由

```php
Route::prefix('permission')->name('permission.')->group(function () {
    Route::get('/', [PermissionController::class, 'index'])->name('index');
    Route::get('/list', [PermissionController::class, 'list'])->name('list');
    Route::get('/create', [PermissionController::class, 'create'])->name('create');
    Route::post('/', [PermissionController::class, 'store'])->name('store');
    Route::get('/{permission}/edit', [PermissionController::class, 'edit'])->name('edit');
    Route::put('/{permission}', [PermissionController::class, 'update'])->name('update');
    Route::delete('/{permission}', [PermissionController::class, 'destroy'])->name('destroy');
    Route::post('/batch-delete', [PermissionController::class, 'batchDelete'])->name('batch-delete');
});
```

路由名稱格式：`lang.ocadmin.{category}.{resource}.{action}`

---

## 開發檢查清單

### Controller

- [ ] 繼承 `App\Portals\Ocadmin\Core\Controllers\OcadminController`
- [ ] 覆寫 `setLangFiles()` 指定語言檔（`['common', 'ocadmin/xxx/yyy']`）
- [ ] 覆寫 `setBreadcrumbs()` 設定麵包屑（使用 `$this->lang->xxx`）
- [ ] `index()` 使用 `$data['key'] = value` 逐行指定，包含 `$data['lang'] = $this->lang`，呼叫 `getList()`
- [ ] `index()` 預定義 JavaScript 所需的路由 URL（`$data['list_url']`、`$data['index_url']`、`$data['batch_delete_url']` 等），**禁止在 Blade `<script>` 內直接呼叫 `route()`**
- [ ] `list()` 作為 AJAX 入口
- [ ] `getList()` 使用 `$this->filterData($request, [...])` 取得白名單參數，**禁止 `$request->all()`**
- [ ] `getList()` 處理篩選、排序、分頁，回傳 `string`，包含 `$data['lang'] = $this->lang`
- [ ] `getList()` 產生 `$data['pagination'] = $items->links('ocadmin::pagination.default')`，不在 Blade 直接呼叫 `->links()`
- [ ] `getList()` 如需關鍵字搜尋，在 `prepare()` 之前處理，完成後 `unset` 涵蓋的欄位避免重複處理
- [ ] `create()` / `edit()` 預定義 `$data['save_url']`（create → store 路由、edit → update 路由）和 `$data['back_url']`（index 路由），**禁止在 Blade 內用三元運算子組合 `route()`**
- [ ] `store()` / `update()` 使用 `$request->validate()` 驗證，回傳 `JsonResponse`（`success: true, message: '...'`）
- [ ] `destroy()` / `batchDelete()` 回傳 `JsonResponse`（`success: true/false, message: '...'`）
- [ ] View 資料使用 `$data['key'] = value` 逐行指定，**禁止硬編碼中文**
- [ ] 成功/錯誤訊息使用 `$this->lang->xxx`

### 視圖

- [ ] 三層分離：`index.blade.php` + `list.blade.php` + `form.blade.php`
- [ ] 所有文字使用 `$lang->xxx`，**禁止硬編碼中文**
- [ ] 表單使用 `data-oc-toggle="ajax"` AJAX 提交
- [ ] 錯誤使用 `<div id="error-xxx" class="invalid-feedback"></div>`
- [ ] 列表使用 AJAX 刷新（`/list` 路由），支援分頁與排序
- [ ] 分頁使用 `{!! $pagination !!}` 輸出（Controller 產生，非 Blade 直接呼叫）
- [ ] 篩選按鈕順序：重設（左）→ 清除（中）→ 篩選（右）
- [ ] index 使用 `{!! $list !!}` 輸出 getList() 結果，**禁止 `@include`**
- [ ] index `<script>` 內的路由 URL 使用 Controller 傳入的 `$xxx_url` 變數，**禁止直接呼叫 `route()`**

### 語言檔

- [ ] 建立模組語言檔 `lang/zh_Hant/ocadmin/{category}/{module}.php`
- [ ] 共用翻譯放 `lang/zh_Hant/common.php`，模組專屬翻譯放模組語言檔
- [ ] Key 命名遵循前綴慣例（`heading_`、`text_`、`column_`、`placeholder_`、`help_`、`error_`），**不使用 `entry_`**

### 路由

- [ ] 包含 `/list` AJAX 路由
- [ ] 路由名稱符合 `lang.ocadmin.{category}.{resource}.*` 格式

---

## 相關文件

- [00004_Ocadmin-common.js說明.md](00004_Ocadmin-common.js說明.md) — common.js 功能說明、表單提交流程、Upload/Download/Clear
- [00008_Ocadmin視圖規範.md](00008_Ocadmin視圖規範.md) — 視圖三層架構、列表頁 / 表單頁布局、共用 partial、多語慣例
- [10013_例外處理.md](10013_例外處理.md) — 全域例外 handler、CustomException
- [10014_JSON回應格式.md](10014_JSON回應格式.md) — 統一 JSON 回應格式定義
- [10016_架構分層與Model職責.md](10016_架構分層與Model職責.md) — 架構分層（Controller / Service / Integrations 定位、為何不用 Repository）、Model Scope 複雜查詢設計、Model::defaults() 預設值規範

---

*文件版本：v1.7*
*更新日期：2026-04-24*
