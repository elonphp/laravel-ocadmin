# Ocadmin 視圖規範

本文涵蓋 Ocadmin 後台與 POS Portal 共用的 Blade 視圖規範：三層架構、列表頁 / 表單頁布局、共用 partial、AJAX 機制、多語慣例。

> 本文從 `00003_Ocadmin程式規範.md` 抽出 + 補強，目標是讓新模組能直接套範本而不犯既有錯誤。

## 目錄

- [視圖三層架構](#視圖三層架構)
- [共用 layout 與 partial](#共用-layout-與-partial)
- [列表頁規範](#列表頁規範)
- [表單頁規範](#表單頁規範)
- [AJAX 規範](#ajax-規範)
- [多語規範](#多語規範)
- [共用 JS（common.js）](#共用-jscommonjs)
- [開發檢查清單](#開發檢查清單)
- [相關文件](#相關文件)

---

## 視圖三層架構

### 運作流程

```
1. 使用者點擊「搜尋」按鈕
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
| `form.blade.php` | 表單視圖（新增 / 編輯共用） |

### AJAX 互動機制

#### 列表查詢（使用 AJAX）

列表頁採用 **AJAX 載入機制**，所有篩選、排序、分頁操作都透過 AJAX 呼叫 `/list` 路由，取得 `list.blade.php` 的 HTML 片段，動態替換到 `#xxx-list` 容器中，**無需重新整理頁面**。

| 操作 | 觸發方式 | AJAX 目標 | 效果 |
|------|----------|-----------|------|
| 搜尋 | 點擊「搜尋」按鈕 | `/list?filter_*=...&equal_*=...` | 更新列表容器 |
| 排序 | 點擊表頭排序連結 | `/list?sort=...&order=...` | 更新列表容器 |
| 分頁 | 點擊分頁連結 | `/list?page=...` | 更新列表容器 |

#### 表單儲存（使用 AJAX）

表單頁採用 **AJAX 提交機制**，透過 `data-oc-toggle="ajax"` 屬性啟用，由 `common.js` 統一處理：

- **儲存成功**：留在表單頁，顯示成功訊息，表單 action 自動更新為編輯路由
- **驗證失敗**：留在表單頁，顯示錯誤訊息於對應欄位下方
- **無需手動編寫** AJAX 程式碼，只需在 `<form>` 加上 `data-oc-toggle="ajax"`

> **完整範例參考**：`app/Portals/Ocadmin/Core/Views/acl/user/` 目錄下的 `index.blade.php`、`list.blade.php`、`form.blade.php`。

### index.blade.php 重點

```blade
{{-- 列表區塊：輸出 getList() 渲染的 HTML --}}
<div id="permission-list" class="card-body">
    {!! $list !!}
</div>
```

> **禁止** `@include('ocadmin::xxx.list')`：`@include` 會與父視圖共享變數作用域，但 `getList()` 的 `$data` 陣列（如 `$settings`、`$sort_*`、`$pagination`）僅在 `view()->render()` 時傳入 `list.blade.php`，不存在於 `index` 的作用域中，導致 Undefined variable 錯誤。必須使用 `{!! $list !!}` 輸出 `getList()` 已渲染的 HTML 字串。

```javascript
var listUrl  = '{{ $list_url }}';
var indexUrl = '{{ $index_url }}';
var batchDeleteUrl = '{{ $batch_delete_url }}';

// AJAX 分頁 & 排序
$('#permission-list').on('click', 'thead a, .pagination a', function(e) {
    e.preventDefault();
    var href = $(this).attr('href');
    $('#permission-list').load(href);
    window.history.pushState({}, null, href.replace(/\/list\b/, ''));
});
```

> **禁止**在 Blade 的 `<script>` 區塊內直接呼叫 `{{ route('...') }}`。路由 URL 應在 Controller 的 `index()` 中以 `$data['xxx_url']` 預先定義，Blade 以 `{{ $xxx_url }}` 輸出。原因：
> - `'{{ route('lang.ocadmin...') }}'` 多層引號嵌套，容易出錯且難以閱讀
> - IDE 無法正確解析 `<script>` 內的 Blade 語法，破壞 JavaScript 語法高亮
> - 同一路由在多處重複書寫，改路由名稱時容易遺漏

### list.blade.php 重點

**排序連結**：使用 Controller 傳來的 `$sort_*` 變數，不要在 Blade 中組裝 URL

```blade
<thead>
    <tr>
        <th>
            <a href="{{ $sort_username }}" @class([$order => $sort === 'username'])>
                {{ $lang->column_username }}
            </a>
        </th>
    </tr>
</thead>
```

**分頁輸出**：使用 Controller 傳來的 `$pagination` 變數

```blade
<div class="row">
    <div class="col-sm-6 text-start">{!! $pagination !!}</div>
    <div class="col-sm-6 text-end">
        {!! sprintf($lang->text_showing, $users->firstItem() ?? 0, $users->lastItem() ?? 0, $users->total()) !!}
    </div>
</div>
```

| 重點 | 說明 |
|------|------|
| 使用 `$sort_*` | Controller 已建構完整的排序 URL（含現有篩選參數） |
| 使用 `{!! $pagination !!}` | Controller 已產生 Bootstrap 5 分頁 HTML |
| **禁止** `{{ $users->links() }}` | 會產生 Tailwind 樣式，與系統不相容 |
| **禁止**在 Blade 組裝 URL | 會遺漏篩選參數，導致排序時篩選條件消失 |

---

## 共用 layout 與 partial

### Layout

| 檔案 | 用途 |
|------|------|
| `layouts/app.blade.php` | 後台主框架（含 sidebar、header、footer） |
| `layouts/auth.blade.php` | 登入頁簡化框架 |

### Partials

| Partial | 用途 |
|---------|------|
| `layouts/partials/header.blade.php` | 頂部 nav（含品牌切換、語系、使用者選單） |
| `layouts/partials/sidebar.blade.php` | 側邊主選單 |
| `layouts/partials/footer.blade.php` | 底部 |
| `layouts/partials/approval_stamp.blade.php` | 核准 / 作廢印章 |
| `layouts/partials/approval_buttons.blade.php` | 狀態快速操作按鈕 |

### approval_stamp 用法

父層加 `position-relative`，include 後依 status value 渲染印章：

```blade
<div class="container-fluid position-relative">
    @include('ocadmin::layouts.partials.approval_stamp', ['stamp' => $order->status?->value])
    ...
</div>
```

支援的 stamp 值：`'approved'` → 紅圈「核」、`'void'` → 灰圈「廢」、其它 / null → 不渲染。

CSS 在 `public/assets/ocadmin/stylesheet/stylesheet.css` 全域定義。

### approval_buttons 用法

放在 form 右上角 button group 內，依 status 渲染對應的快速操作按鈕：

```blade
<div class="float-end">
    @if($order->exists)
        @include('ocadmin::layouts.partials.approval_buttons', [
            'status'    => $order->status?->value,
            'statusUrl' => route('lang.ocadmin.inventory.purchasing-order.update-status', $order),
        ])
    @endif
    {{-- 儲存按鈕、返回按鈕等 --}}
</div>
```

| 當前 status | 顯示按鈕 | 轉到 |
|---|---|---|
| draft | 不渲染（透過表單 status select + 儲存一起送，避免欄位編輯遺失） | — |
| confirmed | 「核准」 + 「退回草稿」 | approved / draft |
| approved | 「取消核准」 | confirmed |

JS handler `.btn-status-change` 在 `common.js` 委派監聽，POST `{ status: 'xxx' }` 到 `statusUrl`，**不送表單資料**。

---

## 列表頁規範

### 容器布局（index.blade.php）

**桌面版**：篩選 sidebar 在右（3 欄），列表在左（9 欄）

```
┌─────────────────────────────┬───────────────┐
│         資料列表              │     搜尋      │
│         (col-lg-9)          │   (col-lg-3)  │
│                             │   order-last  │
└─────────────────────────────┴───────────────┘
```

**手機版**：篩選 sidebar 隱藏，由 page-header 的 Filter 按鈕 toggle 顯示

| 類別 | 作用 |
|------|------|
| `order-lg-last` | 桌面版放右側 |
| `d-none d-lg-block` | 手機版隱藏，桌面版顯示 |
| `d-lg-none` | 只在手機版顯示（page-header 的 Filter 按鈕） |

### 篩選區塊（form#form-filter）

#### 啟用篩選欄位（基本資料類列表必備）

凡是 model 有 `is_active` 欄位的列表，篩選區一律加：

```blade
<div class="mb-3">
    <label class="form-label">{{ $lang->column_active }}</label>
    <select name="equal_is_active" id="input-equal-is-active" class="form-select">
        <option value="*">{{ $lang->text_all }}</option>
        <option value="1" {{ request('equal_is_active', '1') === '1' ? 'selected' : '' }}>{{ $lang->text_yes }}</option>
        <option value="0" {{ request('equal_is_active') === '0' ? 'selected' : '' }}>{{ $lang->text_no }}</option>
    </select>
</div>
```

- Label：`column_active` = 「啟用」
- 選項：`text_yes` = 「是」、`text_no` = 「否」、`text_all` = 「-- 全部 --」
- 第一項用 `value="*"`：因為 `OrmHelper::applyFilters()` 預設 `equal_is_active = 1`（只看啟用），需明確送 `*` 才能 unset 看全部

#### 狀態多選（workflow 類）

對於有 workflow 狀態的單據（採購單、訂單、案件等），篩選區用 **Select2 multi-select**：

```blade
@php
    $rawStatusesParam = request()->query('equal_statuses');
    // 有帶參數（即使空陣列）→ 尊重；沒帶 → 套預設（排除終態）
    $selectedStatuses = is_array($rawStatusesParam)
        ? $rawStatusesParam
        : ['draft', 'confirmed', 'approved'];
@endphp
{{-- hidden：確保 select 全部未選時仍會送 equal_statuses[]= ，伺服器才能區分「未送」vs「送出但空」 --}}
<input type="hidden" name="equal_statuses[]" value=""/>
<select name="equal_statuses[]" id="input-equal-statuses" class="form-select" multiple>
    @foreach($status_options as $opt)
    <option value="{{ $opt['value'] }}" @if(in_array($opt['value'], $selectedStatuses, true)) selected @endif>{{ $opt['label'] }}</option>
    @endforeach
</select>
```

```javascript
$('#input-equal-statuses').select2({
    width: '100%',
    placeholder: '{{ $lang->text_all }}',
    allowClear: true,
});
```

- 預設選擇排除終態（如 `void`、`closed`）— 一般使用者不需要看作廢的單
- 使用 `<input type="hidden" name="equal_statuses[]" value=""/>`：clear 後仍送 `equal_statuses[]=`，後端才能區分「首次進入套預設」vs「使用者刻意清空 = 顯示全部」

對應 controller 處理：

```php
$rawStatuses = $request->query('equal_statuses');
if (is_array($rawStatuses)) {
    $statuses = array_values(array_filter($rawStatuses, fn ($s) => $s !== '' && $s !== null));
    if (! empty($statuses)) {
        $query->whereIn('status', $statuses);
    }
} else {
    $query->whereIn('status', ['draft', 'confirmed', 'approved']);
}
```

#### 第一項 placeholder 慣例

| 場景 | value | 顯示文字 | lang key |
|------|-------|---------|---------|
| 一般 filter（無 server 端預設） | `""` | 「-- 全部 --」 | `text_all` |
| `equal_is_active` filter | `"*"` | 「-- 全部 --」 | `text_all` |
| 表單欄位（form select） | `""` | 「-- 請選擇 --」 | `text_please_select` |

`text_all` 與 `text_please_select` 已內含 dashes，HTML 不要再外加 `-- ... --`。

#### 三個按鈕：重設 / 清除 / 搜尋

```blade
<div class="text-end">
    <button type="reset" id="button-reset" class="btn btn-light d-none d-xl-inline-block">
        <i class="fa-solid fa-rotate"></i> {{ $lang->button_reset }}
    </button>
    <button type="button" id="button-clear" class="btn btn-light">
        <i class="fa-solid fa-eraser"></i> {{ $lang->button_clear }}
    </button>
    <button type="button" id="button-filter" class="btn btn-light">
        <i class="fa-solid fa-filter"></i> {{ $lang->button_filter }}
    </button>
</div>
```

| 按鈕 | id | type | 功能 |
|------|-----|------|------|
| 重設 | `button-reset` | `reset` | 恢復表單至頁面載入時的預設值（如 is_active 回到「啟用」），並重新載入列表 |
| 清除 | `button-clear` | `button` | 清空所有篩選條件（select 重設第一項 = 全部），載入無篩選的完整列表 |
| 搜尋 | `button-filter` | `button` | 依目前表單欄位值篩選列表 |

**重設按鈕加 `d-none d-xl-inline-block`**：< 1200px 隱藏（包含 col-lg-3 narrow sidebar 模式），避免第三顆按鈕換行。

對應 JavaScript：

```javascript
// 推薦：用 FormData iterate，自動處理 [] 陣列鍵與 select2 multi
function getFilterQuery() {
    var params = new URLSearchParams();
    var fd = new FormData(document.getElementById('form-filter'));
    for (var pair of fd.entries()) {
        var key = pair[0], val = pair[1];
        // 陣列型欄位（key 以 [] 結尾）即使空值也保留，讓伺服器分辨「未送」vs「送出但空」
        if (key.endsWith('[]')) {
            params.append(key, val);
        } else if (val !== '*' && val !== '' && val !== null) {
            params.append(key, val);
        }
    }
    return params.toString();
}

$('#button-filter').on('click', function() {
    var qs = getFilterQuery();
    var url = listUrl + (qs ? '?' + qs : '');
    window.history.pushState({}, null, url.replace(/\/list\b/, ''));
    $('#xxx-list').load(url);
});

$('#button-reset').on('click', function() {
    // type="reset" 觸發瀏覽器原生表單重設後再呼叫篩選
    setTimeout(function() { $('#button-filter').trigger('click'); }, 10);
});

$('#button-clear').on('click', function() {
    $('#form-filter').find('input[type="text"]').val('');
    $('#form-filter').find('select:not([multiple])').each(function() {
        // selectedIndex=0 確保預設選第一項（不論 value 是 "" 或 "*"）
        this.selectedIndex = 0;
        $(this).trigger('change.select2');
    });
    // Select2 multi-select 用 val(null)
    $('#input-equal-statuses').val(null).trigger('change');
    $('#xxx-list').load(listUrl);
    window.history.pushState({}, null, indexUrl);
});
```

> 不要用 `$(this).val('')` — 會打不到 `value="*"` 的選項；改用 `selectedIndex = 0` 即可。

### 列表區塊（list.blade.php）

#### 共用結構

```blade
<div class="table-responsive">
    <table class="table table-bordered table-hover">
        <thead>
            <tr>
                <th class="text-center" style="width: 1px;">
                    <input type="checkbox" onclick="..." class="form-check-input">
                </th>
                {{-- 排序欄位用 $sort_xxx --}}
                <th><a href="{{ $sort_code }}" @class([$order => $sort === 'code'])>{{ $lang->column_code }}</a></th>
                {{-- 不可排序欄位純文字 --}}
                <th>{{ $lang->column_xxx }}</th>
                <th class="text-end">{{ $lang->column_action }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $item)
            <tr>
                <td class="text-center"><input type="checkbox" name="selected[]" value="{{ $item->id }}" class="form-check-input"></td>
                {{-- 欄位內容 --}}
                <td class="text-end">
                    <a href="{{ route('...edit', $item) . $urlParams }}" data-bs-toggle="tooltip" title="{{ $lang->button_edit }}" class="btn btn-primary btn-sm">
                        <i class="fa-solid fa-pencil"></i>
                    </a>
                </td>
            </tr>
            @empty
            <tr><td colspan="N" class="text-center">{{ $lang->text_no_data }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
```

#### 列表欄位慣例

- **不顯示 ID 欄**（除非單據需要明顯辨識，但通常用 code 即可）
- 操作欄 `text-end`，內含「編輯」按鈕（pencil icon）
- 狀態欄用 badge：

| 狀態 | 顏色 |
|---|---|
| draft / 草稿 | `bg-secondary` 灰 |
| confirmed / 已確認 | `bg-info` 藍 |
| approved / 已核准 | `bg-success` 綠 |
| void / 作廢 | `bg-danger` 紅 |
| done / 已完成 | `bg-success` 綠 |

---

## 表單頁規範

### 整體結構

```blade
@section('content')
<div id="content">
    {{-- Page header：標題 + 動作按鈕 --}}
    <div class="page-header">
        <div class="container-fluid">
            <div class="float-end">
                {{-- 1. 狀態快速操作（confirmed / approved 才顯示） --}}
                @if($order->exists)
                    @include('ocadmin::layouts.partials.approval_buttons', [
                        'status'    => $order->status?->value,
                        'statusUrl' => route('...update-status', $order),
                    ])
                @endif
                {{-- 2. 儲存按鈕（依 isEditable() 條件） --}}
                @if(! $order->exists || $order->status?->isEditable())
                <button type="submit" form="form-xxx" class="btn btn-primary">
                    <i class="fa-solid fa-save"></i>
                </button>
                @endif
                {{-- 3. 返回 --}}
                <a href="{{ $back_url }}" class="btn btn-secondary"><i class="fa-solid fa-reply"></i></a>
            </div>
            <h1>{{ $order->exists ? $lang->text_edit : $lang->text_add }}</h1>
        </div>
    </div>

    <div class="container-fluid position-relative">
        {{-- 印章（已核准 / 已作廢） --}}
        @include('ocadmin::layouts.partials.approval_stamp', ['stamp' => $order->status?->value])

        {{-- 外層單一 .card 包整個 form --}}
        <div class="card">
            <div class="card-body">
                {{-- nav-tabs 在 form 外面 --}}
                <ul class="nav nav-tabs">
                    <li class="nav-item"><a href="#tab-basic" data-bs-toggle="tab" class="nav-link active">{{ $lang->tab_basic }}</a></li>
                    <li class="nav-item"><a href="#tab-other" data-bs-toggle="tab" class="nav-link">...</a></li>
                </ul>

                <form action="{{ $save_url }}" method="post" id="form-xxx" data-oc-toggle="ajax">
                    @csrf
                    @if($order->exists)
                    @method('PUT')
                    @endif

                    {{-- 非 Draft 時整個表單欄位 disabled --}}
                    <fieldset @disabled($order->exists && ! $order->status?->isEditable())>
                    <div class="tab-content">
                        {{-- 每個 tab-pane 內不再包 card --}}
                        <div class="tab-pane fade show active" id="tab-basic">
                            ...欄位...
                        </div>
                        <div class="tab-pane fade" id="tab-other">
                            ...
                        </div>
                    </div>
                    </fieldset>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
```

**結構重點：**

| 規範 | 說明 |
|---|---|
| 外層單一 `.card > .card-body` | 包住 nav-tabs + form |
| nav-tabs 在 `<form>` 外 | OpenCart 4 慣例 |
| tab-pane 內**不要**再包 card | 避免雙層 card 視覺重疊與多餘 padding |
| 容器 `position-relative` | 為印章絕對定位提供 anchor |

### 動作按鈕順序（page-header float-end，左 → 右）

1. **狀態快速操作** (approval_buttons partial) — 僅 existing record + 適用 status 才顯示
2. **儲存按鈕** — `isEditable()` 才顯示
3. **返回按鈕** — 永遠顯示

### 欄位排版

**一律 col-sm-2 (label) + col-sm-10 (input wrapper)**，補滿整列：

```blade
<div class="row mb-3 required">
    <label for="input-name" class="col-sm-2 col-form-label">{{ $lang->column_name }}</label>
    <div class="col-sm-10">
        <input type="text" name="name" id="input-name" class="form-control">
        <div id="error-name" class="invalid-feedback"></div>
    </div>
</div>
```

多欄位並排：col-sm-10 內再 row + col-sm-N

```blade
<div class="row mb-3">
    <label class="col-sm-2 col-form-label">{{ $lang->column_invoice }}</label>
    <div class="col-sm-10">
        <div class="row g-2">
            <div class="col-sm-3">
                <select name="invoice_type" class="form-select">...</select>
            </div>
            <div class="col-sm-7">
                <input type="text" name="invoice_num" class="form-control">
            </div>
        </div>
    </div>
</div>
```

| 類別 | 作用 |
|------|------|
| `row mb-3` | 一列一欄位 |
| `col-sm-2 col-form-label` | 標籤（左側） |
| `col-sm-10` | 輸入欄位容器（右側） |
| `required` | 必填欄位標記（加在 row 上） |

### 下拉選單慣例

```blade
<select name="store_id" class="form-select">
    <option value="">{{ $lang->text_please_select }}</option>
    @foreach($store_options as $id => $name)
    <option value="{{ $id }}" {{ old('store_id', $order->store_id) == $id ? 'selected' : '' }}>{{ $name }}</option>
    @endforeach
</select>
```

- 第一項一律 `value=""` + `text_please_select`（不論是否必填）
- selected 預設值用 `old()` 並 fallback 到 model 值
- 模組特定 placeholder（`text_select_brand`、`text_select_company` 等）若有具體名詞可優先使用，UX 比 generic 好

### 狀態與權限機制

| Status | 欄位可編輯？ | 儲存按鈕 | 快速操作按鈕 | 印章 |
|---|---|---|---|---|
| 新增（無 status） | ✅ 是 | 顯示 | — | — |
| Draft | ✅ 是 | 顯示 | 無（透過表單 status select 一起送） | — |
| Confirmed | ❌ fieldset disabled | 隱藏 | 核准 / 退回草稿 | — |
| Approved | ❌ fieldset disabled | 隱藏 | 取消核准 | 紅圈「核」 |
| Void | ❌ fieldset disabled | 隱藏 | — | 灰圈「廢」 |

**關鍵原則：Draft 不顯示快速操作按鈕**

> Draft 狀態欄位可編輯，若使用者編輯後按「確認」按鈕，純狀態 endpoint 不會持久化欄位變更，導致資料遺失。Draft → Confirmed 必須透過表單的 status select + 儲存按鈕，原子化更新內容與狀態。

對應的 enum 方法：

```php
// PurchasingOrderStatus enum
public function isEditable(): bool {
    return $this === self::Draft;
}
```

view 條件：

```blade
{{-- 儲存按鈕 --}}
@if(! $order->exists || $order->status?->isEditable())
    <button type="submit" form="form-xxx">儲存</button>
@endif

{{-- 整個表單 fieldset --}}
<fieldset @disabled($order->exists && ! $order->status?->isEditable())>
    ...
</fieldset>
```

### 表單 AJAX 提交

```blade
<form action="{{ $save_url }}" method="post" id="form-xxx" data-oc-toggle="ajax">
    @csrf
    @if($model->exists)
    @method('PUT')
    @endif
    ...
</form>
```

`$save_url` 和 `$back_url` 由 Controller 的 `create()` / `edit()` 預先定義，`create()` 指向 `store` 路由，`edit()` 指向 `update` 路由。**禁止**在 Blade 內使用三元運算子組合 `route()`。

| 重點 | 說明 |
|------|------|
| `data-oc-toggle="ajax"` | 啟用 AJAX 提交，由 `common.js` 統一處理 |
| `action` | 新增用 `store` 路由，編輯用 `update` 路由 |
| `method="post"` + `@method('PUT')` | 編輯時使用 PUT method |
| **無需額外 JS** | 不需要手動編寫 AJAX 程式碼 |

#### AJAX 提交流程

1. **提交表單** → `common.js` 攔截 submit 事件
2. **驗證失敗** → 錯誤訊息顯示於對應欄位下方（`#error-欄位名` 元素）
3. **儲存成功** → 顯示成功訊息，停留在表單頁
4. **新增成功** → 自動更新表單 action 為編輯路由，下次儲存變為更新操作

---

## AJAX 規範

### 列表 ajax 載入

- form 內 thead 排序連結 + pagination 連結委派監聽 `click`，`load(href)` 替換到列表容器
- `pushState` 同步 URL（移除 `/list` 段，讓 URL 看起來像主頁面）

### 表單 ajax 提交

- form 加 `data-oc-toggle="ajax"`，`common.js` 統一處理
- 錯誤訊息進 `#error-欄位名` + `.invalid-feedback`（Bootstrap 5 樣式）

### CSRF token

- HTML head 已含 `<meta name="csrf-token" content="...">`
- JS 取用：`$('meta[name="csrf-token"]').attr('content')`

---

## 多語規範

### default.php 共用 key

`lang/zh_Hant/admin/default.php` 為共用 key，**模組 lang 檔不要重複定義**已存在的 key（會 override）。

| key | 值 | 用途 |
|---|---|---|
| `column_active` | 啟用 | 啟用篩選欄位 label |
| `column_status` | 狀態 | workflow 狀態欄位 label |
| `text_yes` | 是 | 啟用篩選 option 1 |
| `text_no` | 否 | 啟用篩選 option 0 |
| `text_all` | -- 全部 -- | filter 第一項 placeholder（含 dashes） |
| `text_please_select` | -- 請選擇 -- | form select 第一項 placeholder（含 dashes） |
| ~~`text_select`~~ | 請選擇 | @deprecated（無 dashes，不再使用） |
| `text_enabled` / `text_disabled` | 啟用 / 停用 | 列表 cell 顯示用（form-switch） |
| `button_filter` | 搜尋 | 篩選送出按鈕 |
| `button_reset` | 重設 | 篩選重設按鈕 |
| `button_clear` | 清除 | 篩選清除按鈕 |
| `button_save` / `button_back` | 儲存 / 返回 | 表單按鈕 |

### 模組 lang 檔

- 路徑：`lang/zh_Hant/admin/{module}.php` 或 `lang/zh_Hant/admin/{module}/{file}.php`
- 模組特定 placeholder：`text_select_brand`、`text_select_company`、`placeholder_search` 等
- **不重複** default.php 已有的 key

---

## 共用 JS（common.js）

`public/assets/ocadmin/javascript/common.js` 提供全站通用功能：

| 功能 | 觸發方式 |
|------|----------|
| AJAX 表單提交 | `<form data-oc-toggle="ajax">` |
| AJAX 連結 | `<a data-oc-toggle="ajax">` |
| 狀態快速變更 | `<button class="btn-status-change" data-url="..." data-target-status="..." data-confirm-text="...">` |
| autocomplete=off | 自動補在所有 form / select（避免 F5 殘留） |

詳細 API 見 `00004_Ocadmin-common.js說明.md`。

---

## 開發檢查清單

### 新列表頁

- [ ] index / list / form 三層分離
- [ ] 篩選 sidebar 採 `col-lg-3 order-lg-last` + `d-none d-lg-block`
- [ ] 啟用篩選欄位（基本資料類）：`column_active` + `text_yes`/`text_no`
- [ ] workflow 狀態用 select2 multi-select，預設排除終態
- [ ] filter 第一項用 `text_all`（不含外層 dashes）
- [ ] 重設按鈕加 `d-none d-xl-inline-block`
- [ ] 清除按鈕用 `selectedIndex = 0`
- [ ] thead / pagination 連結用 ajax `load()` + `pushState`
- [ ] 排序連結用 `$sort_*` 變數
- [ ] 分頁器用 `ocadmin::pagination.default`
- [ ] 不顯示 ID 欄

### 新表單頁

- [ ] page-header float-end 順序：approval_buttons → 儲存 → 返回
- [ ] 容器 `container-fluid position-relative`
- [ ] include `approval_stamp` partial
- [ ] 外層單一 `.card > .card-body` 包 nav-tabs + form
- [ ] nav-tabs 在 `<form>` 外，tab-pane 內不要再包 card
- [ ] 欄位 `col-sm-2` (label) + `col-sm-10` (input wrapper)
- [ ] form select 第一項用 `text_please_select`
- [ ] form `data-oc-toggle="ajax"`，錯誤訊息 `#error-xxx`
- [ ] workflow 類用 `approval_buttons` partial + `<fieldset @disabled(...)>`
- [ ] 儲存按鈕條件：`! $exists || $status?->isEditable()`

---

## 相關文件

- [00003 Ocadmin 程式規範](00003_Ocadmin程式規範.md) — 總覽、Controller、OrmHelper
- [00004 Ocadmin common.js 說明](00004_Ocadmin-common.js說明.md)
- [10011 選單機制](10011_選單機制.md)
- [10005 Portal 使用者記錄](10005_Portal使用者記錄.md)
