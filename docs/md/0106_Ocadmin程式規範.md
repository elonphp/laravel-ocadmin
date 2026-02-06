# Ocadmin 程式規範

## 概述

本文件說明 Ocadmin Portal 的程式開發規範，以「權限管理」為範例。

---

## 架構原則

### 分層結構

```
Controller → Service → Model
    │           │
    │           └── 業務邏輯（寫入操作）
    └── HTTP 層（查詢、Transaction）
```

### 核心原則

| 原則 | 說明 |
|------|------|
| Controller : Service = 1 : 1 | 每個 Controller 對應一個 Service |
| Transaction | **一律寫在 Controller** |
| Service 職責 | 處理 `create`、`update`、`delete` 或複雜邏輯 |
| 查詢邏輯 | 簡單查詢直接寫在 Controller |

### 何時使用 Service

| Controller 方法 | 規則 | Service 方法 |
|-----------------|------|--------------|
| `store()` | **複雜邏輯調用 Service** | `create()` |
| `update()` | **複雜邏輯調用 Service** | `update()` |
| `destroy()` | **複雜邏輯調用 Service** | `delete()` |
| `getList()` 等查詢 | 直接操作 Model | — |

**簡單 CRUD**（如權限管理）不需要 Service，Controller 直接處理：

```php
// Controller 直接刪除
public function destroy(Permission $permission): JsonResponse
{
    $permission->delete();
    return response()->json(['success' => true, 'message' => $this->lang->text_success_delete]);
}
```

**複雜邏輯**（如員工管理）需要 Service 處理關聯資料、業務規則：

```php
// Service 處理複雜刪除
public function delete(Employee $employee): void
{
    if ($employee->contracts()->active()->exists()) {
        CustomException::fail('此員工仍有有效合約，無法刪除');
    }

    $employee->attendances()->delete();
    $employee->leaves()->delete();
    $employee->delete();
}
```

> `CustomException::fail()` 會拋出例外，由全域 handler 統一回傳 JSON 格式。詳見 [0108_例外處理.md](0108_例外處理.md)。

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
| `tab_` | Tab 標籤 | `tab_trans`（通常放 common） |

> **不使用 `entry_` 前綴。** 因為列表欄位標題與表單欄位標籤大部分相同，統一使用 `column_` 避免重複定義。篩選欄位標籤也直接複用 `column_*`。

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
                'href' => route('lang.ocadmin.system.permission.index'),
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
            (object)['text' => $this->lang->heading_title, 'href' => route('lang.ocadmin.system.permission.index')],
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
        $filter_data = $request->all();

        // 預設排序
        $filter_data['sort'] = $request->get('sort', 'name');
        $filter_data['order'] = $request->get('order', 'asc');

        // search 關鍵字查詢（優先處理，涵蓋的欄位從 filter_data 移除避免 prepare 重複處理）
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
        $permissions->withPath(route('lang.ocadmin.system.permission.list'));

        $data['lang'] = $this->lang;
        $data['permissions'] = $permissions;
        $data['pagination'] = $permissions->links('ocadmin::pagination.default');

        // 建構 URL 參數與排序連結
        $url = $this->buildUrlParams($request);
        $baseUrl = route('lang.ocadmin.system.permission.list');
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
            'replace_url' => route('lang.ocadmin.system.permission.edit', $permission),
            'form_action' => route('lang.ocadmin.system.permission.update', $permission),
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
$filter_data = $request->all();

// 設定預設排序
$filter_data['sort'] = $request->get('sort', 'name');
$filter_data['order'] = $request->get('order', 'asc');

// search 關鍵字查詢（視需要，必須在 prepare 之前）
// ... 詳見下方「search 關鍵字查詢」章節

// 自動處理剩餘的 filter_*, equal_* 及排序
OrmHelper::prepare($query, $filter_data);

// 分頁結果
$result = OrmHelper::getResult($query, $filter_data);
```

### 參數命名規範

| 前綴 | 用途 | 範例 |
|-----|------|------|
| `filter_欄位名` | 彈性查詢（支援萬用字元、REGEXP） | `filter_name=mss*` |
| `equal_欄位名` | 精確查詢 | `equal_guard_name=web` |
| `sort` | 排序欄位 | `sort=name` |
| `order` | 排序方向 | `order=asc` |

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
$filter_data = $request->all();
$filter_data['sort'] = $request->get('sort', 'name');
$filter_data['order'] = $request->get('order', 'asc');

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

## 分頁規範

### 自訂分頁 View

使用 Bootstrap 5 分頁樣式，位於 `app/Portals/Ocadmin/Core/Views/pagination/default.blade.php`。

**不要**在 Blade 中直接呼叫 `{{ $items->links() }}`（預設為 Tailwind 樣式），改由 Controller 產生分頁 HTML 後傳遞到 View。

### Controller 產生分頁

在 `getList()` 中，`withPath()` 之後產生 `$data['pagination']`：

```php
$permissions = OrmHelper::getResult($query, $filter_data);
$permissions->withPath(route('lang.ocadmin.system.permission.list'));

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
    'replace_url' => route('lang.ocadmin.system.permission.edit', $permission),
    'form_action' => route('lang.ocadmin.system.permission.update', $permission),
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

> 驗證失敗、未認證、權限不足、業務邏輯錯誤等情況，全部由 `bootstrap/app.php` 的全域 handler 統一回傳。詳見 [0108_例外處理.md](0108_例外處理.md) 及 [0109_JSON回應格式.md](0109_JSON回應格式.md)。

### JSON 回應格式

統一格式（詳見 [0109_JSON回應格式.md](0109_JSON回應格式.md)）：

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

> 與 OpenCart 原始做法的差異及設計決策，詳見 [0107_Ocadmin-common.js說明.md](0107_Ocadmin-common.js說明.md)。

### 翻譯欄位的 Blade 寫法

翻譯欄位 ID 直接使用 `{{ $locale }}`，不需 `str_replace`。驗證錯誤由全域 handler 自動轉為扁平 key（如 `display_name-zh_Hant`）。

> 轉換流程詳見 [0107_Ocadmin-common.js說明.md](0107_Ocadmin-common.js說明.md)，全域 handler 邏輯見 [0109_例外處理.md](0109_例外處理.md)。

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

> 表單提交的完整運作流程（common.js 如何攔截、處理回應、標記錯誤），詳見 [0107_Ocadmin-common.js說明.md](0107_Ocadmin-common.js說明.md)。

- **不使用 `@error` Blade 指令**：改用 `<div id="error-xxx" class="invalid-feedback"></div>`
- **不使用 `redirect()`**：Controller 一律回傳 JSON
- **使用 `$request->validate()`**：驗證失敗自動拋出 `ValidationException`，由全域 handler 回傳 422 + 統一 JSON 格式（不需手動 `validator()->fails()` + 回傳 JSON）
- **新增後切換為編輯模式**：透過 `replace_url` 和 `form_action` 更新頁面狀態

---

## 視圖三層架構

### 運作流程

```
1. 使用者點擊「篩選」按鈕
2. JavaScript 收集表單參數，呼叫 /list 路由
3. Controller::list() → getList() 回傳 list.blade.php 的 HTML
4. JavaScript 將 HTML 替換到 #xxx-list 容器
5. 更新瀏覽器網址列（history.pushState）
```

### 三層分離

| 檔案 | 用途 |
|------|------|
| `index.blade.php` | 主視圖（頁面框架、篩選面板、JavaScript） |
| `list.blade.php` | 表格視圖（資料列表、分頁），由 `getList()` 渲染 |
| `form.blade.php` | 表單視圖（新增/編輯共用） |

### index.blade.php 重點

```blade
{{-- 列表區塊：輸出 getList() 渲染的 HTML --}}
<div id="permission-list" class="card-body">
    {!! $list !!}
</div>
```

```javascript
// AJAX 分頁 & 排序
$('#permission-list').on('click', 'thead a, .pagination a', function(e) {
    e.preventDefault();
    $('#permission-list').load($(this).attr('href'));
});

// 篩選 → 呼叫 /list 路由
$('#button-filter').on('click', function() {
    var url = '{{ route("lang.ocadmin.system.permission.list") }}?' + params.join('&');
    window.history.pushState({}, null, url.replace('/list?', '?'));
    $('#permission-list').load(url);
});
```

---

## 視圖布局規範

### 列表頁布局（index.blade.php）

**桌面版：** 篩選區塊在右側（3 欄），列表在左側（9 欄）

```
┌─────────────────────────────┬───────────────┐
│         權限列表              │     篩選      │
│         (col-lg-9)          │   (col-lg-3)  │
│                             │   order-last  │
└─────────────────────────────┴───────────────┘
```

**手機版：** 篩選區塊隱藏，顯示 Filter 按鈕切換

| 類別 | 作用 |
|------|------|
| `order-lg-last` | 桌面版放右側 |
| `d-none d-lg-block` | 手機版隱藏，桌面版顯示 |
| `d-lg-none` | 只在手機版顯示（Filter 按鈕） |

### 篩選按鈕順序

篩選區塊底部有三個按鈕，**重設在左、清除在中、篩選在右**：

| 按鈕 | id | type | icon | 功能 |
|------|-----|------|------|------|
| 重設 | `button-reset` | `reset` | `fa-solid fa-rotate` | 恢復表單至頁面載入時的預設值（如 is_active 回到「啟用」），並重新載入列表 |
| 清除 | `button-clear` | `button` | `fa-solid fa-eraser` | 清空所有篩選條件（含預設值），載入無篩選的完整列表 |
| 篩選 | `button-filter` | `button` | `fa-solid fa-filter` | 依目前表單欄位值篩選列表 |

```blade
<div class="text-end">
    <button type="reset" id="button-reset" class="btn btn-light"><i class="fa-solid fa-rotate"></i> {{ $lang->button_reset }}</button>
    <button type="button" id="button-clear" class="btn btn-light"><i class="fa-solid fa-eraser"></i> {{ $lang->button_clear }}</button>
    <button type="button" id="button-filter" class="btn btn-light"><i class="fa-solid fa-filter"></i> {{ $lang->button_filter }}</button>
</div>
```

對應的 JavaScript：

```javascript
// 重設（恢復預設篩選條件）
$('#button-reset').on('click', function() {
    // type="reset" 會先觸發瀏覽器原生表單重設
    setTimeout(function() { $('#button-filter').trigger('click'); }, 10);
});

// 清除（移除所有篩選條件）
$('#button-clear').on('click', function() {
    $('#form-filter').find('input[type="text"]').val('');
    $('#form-filter').find('select').each(function() { $(this).prop('selectedIndex', 0); });
    // 若表格有 is_active 欄位，需帶 equal_is_active=* 以覆蓋 OrmHelper 預設
    var url = listUrl + '?equal_is_active=*';
    window.history.pushState({}, null, indexUrl);
    $(listContainer).load(url);
});
```

### 表單頁布局（form.blade.php）

**一列一欄位**，使用水平表單（horizontal form）：

| 類別 | 作用 |
|------|------|
| `row mb-3` | 一列一欄位 |
| `col-sm-2 col-form-label` | 標籤（左側） |
| `col-sm-10` | 輸入欄位容器（右側） |
| `required` | 必填欄位標記（加在 row 上） |

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
- [ ] `list()` 作為 AJAX 入口
- [ ] `getList()` 處理篩選、排序、分頁，回傳 `string`，包含 `$data['lang'] = $this->lang`
- [ ] `getList()` 產生 `$data['pagination'] = $items->links('ocadmin::pagination.default')`，不在 Blade 直接呼叫 `->links()`
- [ ] `getList()` 如需關鍵字搜尋，在 `prepare()` 之前處理，完成後 `unset` 涵蓋的欄位避免重複處理
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
- [ ] index 使用 `{!! $list !!}` 輸出 getList() 結果

### 語言檔

- [ ] 建立模組語言檔 `lang/zh_Hant/ocadmin/{category}/{module}.php`
- [ ] 共用翻譯放 `lang/zh_Hant/common.php`，模組專屬翻譯放模組語言檔
- [ ] Key 命名遵循前綴慣例（`heading_`、`text_`、`column_`、`placeholder_`、`help_`、`error_`），**不使用 `entry_`**

### 路由

- [ ] 包含 `/list` AJAX 路由
- [ ] 路由名稱符合 `lang.ocadmin.{category}.{resource}.*` 格式

---

## 相關文件

- [0107_Ocadmin-common.js說明.md](0107_Ocadmin-common.js說明.md) — common.js 功能說明、表單提交流程、Upload/Download/Clear
- [0109_例外處理.md](0109_例外處理.md) — 全域例外 handler、CustomException
- [0110_JSON回應格式.md](0110_JSON回應格式.md) — 統一 JSON 回應格式定義

---

*文件版本：v1.5*
*更新日期：2026-02-07*
