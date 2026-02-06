# Ocadmin common.js 說明

## 概述

`public/assets/ocadmin/javascript/common.js` 是 Ocadmin Portal 的核心前端腳本，源自 OpenCart 4.x 後台的 `common.js`，並針對 Laravel 框架進行重構。

**檔案位置：** `public/assets/ocadmin/javascript/common.js`
**來源：** OpenCart 4.x `backend/view/javascript/common.js`

---

## OpenCart 兩個版本的差異

OpenCart 前後台各有一份 `common.js`：

| 功能 | Backend (後台) | Catalog (前台) |
|------|:-:|:-:|
| getURLVar | ✅ | ✅ |
| Tooltip | ✅ | ✅ (實作略不同) |
| Pagination click | ✅ | ❌ |
| Alert Fade | ✅ | ✅ |
| Button plugin | ✅ | ✅ |
| decodeHTMLEntities | ✅ | ❌ |
| Observe (MutationObserver) | ✅ | ✅ |
| Chain class | ✅ | ✅ |
| Forms handler (AJAX) | ✅ | ✅ |
| Upload | ✅ | ✅ (選擇器略不同) |
| Download | ✅ | ❌ |
| Clear | ✅ | ❌ |
| Image Manager | ✅ | ❌ |
| Autocomplete | ✅ | ✅ (focusout 略不同) |
| Header notification | ✅ | ❌ |
| Menu | ✅ | ❌ |
| Language switcher | ✅ | ✅ |
| Currency 表單 | ❌ | ✅ (前台特有) |
| Product List/Grid | ❌ | ✅ (前台特有) |
| Modal link (agree terms) | ❌ | ✅ (前台特有) |
| Cookie Policy | ❌ | ✅ (前台特有) |

**結論：** Backend 是完整版 + 後台管理功能，Catalog 是精簡版 + 前台購物功能。本系統以 Backend 版為基礎。

---

## 功能區塊總覽

| # | 功能 | 行數範圍 | 來源 | 修改狀態 |
|---|------|:-------:|------|:-------:|
| 1 | `getURLVar()` | 1-23 | OpenCart | 保留原樣 |
| 2 | Tooltip | 27-37 | OpenCart | 保留原樣 |
| 3 | Button click 移除 tooltip | 39-41 | OpenCart | 保留原樣 |
| 4 | Pagination click | 43-51 | OpenCart | 保留原樣 |
| 5 | Alert Fade (3 秒淡出) | 54-60 | OpenCart | 保留原樣 |
| 6 | Button plugin (loading/reset) | 63-80 | OpenCart | 保留原樣 |
| 7 | `decodeHTMLEntities()` | 83-89 | OpenCart | 保留原樣 |
| 8 | Observe (MutationObserver) | 92-102 | OpenCart | 保留原樣 |
| 9 | Chain class (AJAX 佇列) | 105-136 | OpenCart | 保留原樣 |
| 10 | **`handleJsonResponse()`** | 139-175 | **新增** | 從 Forms 抽出 |
| 11 | **Forms handler** | 178-280 | OpenCart | **重構** |
| 12 | Upload | 283-347 | OpenCart | 保留原樣 (TODO) |
| 13 | Download | 349-357 | OpenCart | 保留原樣 (TODO) |
| 14 | Clear | 359-377 | OpenCart | 保留原樣 |
| 15 | Image Manager | 380-401 | OpenCart | 保留原樣 (TODO) |
| 16 | Autocomplete | 403-503 | OpenCart | 保留原樣 |
| 17 | Header notification | 507-523 | OpenCart | 保留原樣 |
| 18 | Menu | 526-548 | OpenCart | 保留原樣 |
| 19 | Language switcher | 550-577 | OpenCart | 保留原樣 (TODO) |

---

## 重構項目詳細說明

### 1. 抽出 handleJsonResponse()

**背景：** OpenCart 原始的成功/錯誤處理邏輯全部 inline 在 Forms handler 的 AJAX success callback 內，無法重用。

**修改：** 將 Toast 彈窗 + 欄位錯誤標記邏輯抽出為獨立函式 `handleJsonResponse(json, element)`。

```javascript
function handleJsonResponse(json, element) {
    // 成功 → 綠色 Toast
    if (json['success'] === true && json['message']) { ... }

    // 失敗 → 紅色 Toast
    if (json['success'] === false && json['message']) { ... }

    // 欄位錯誤標記
    if (typeof json['errors'] == 'object') {
        for (var key in json['errors']) {
            $('#input-' + key).addClass('is-invalid');
            $('#error-' + key).html(json['errors'][key]).addClass('d-block');
        }
    }
}
```

**呼叫位置：**
- Forms handler 的 `success` callback（第 237 行）
- Forms handler 的 `error` callback（第 276 行）

### 2. 移除 replaceAll('_', '-')

**OpenCart 原始做法：**

```javascript
// 將 error key 的底線轉為橫線，對應 HTML ID
$('#input-' + key.replaceAll('_', '-')).addClass('is-invalid')
    .find('.form-control, .form-select, ...').addClass('is-invalid');
$('#error-' + key.replaceAll('_', '-')).html(json['error'][key]).addClass('d-block');
```

OpenCart 的 PHP error key 使用底線（如 `parent_id`），但 HTML ID 使用橫線（如 `input-parent-id`），因此 JS 需要做轉換。

**本系統決策：不做轉換，保留底線。**

| 比較 | OpenCart（橫線） | HRM2（底線） |
|------|-----------------|-------------|
| HTML ID | `input-parent-id` | `input-parent_id` |
| 雙擊選取 | 只能選到 `parent` 或 `id` | 選到 `parent_id` = 欄位名 |
| 可讀性 | 無法區分結構分隔與名稱內部 | `-` 是結構分隔，`_` 是名稱 |
| JS 轉換 | 需要 `replaceAll('_', '-')` | 不需轉換 |
| Blade 多語 | 需要 `str_replace('_', '-', $locale)` | 直接用 `{{ $locale }}` |
| HTML 合規 | 合規 | 合規（HTML5 僅禁止空格） |

> 程式碼中保留了 OpenCart 原始寫法作為已停用的註解，供日後參考。

### 3. 移除 .find() 鏈

**OpenCart 原始做法：** ID 放在外層容器 div 上，再用 `.find()` 往下找到 input 元素。

**本系統做法：** ID 直接放在 input/select 元素上，不需要容器 div 的 ID，不需要 `.find()` 鏈。

### 4. JSON 回應格式重構

**OpenCart 回應格式（全部 HTTP 200）：**

```javascript
// 成功
{ "success": "更新成功" }         // success 是字串

// 錯誤
{ "error": "權限不足" }           // error 是字串 → Toast
{ "error": { "warning": "...", "name": "..." } }  // error 是物件 → warning Toast + 欄位標記
```

**本系統統一格式：**

```javascript
// 成功（HTTP 200）
{ "success": true, "message": "更新成功" }

// 驗證失敗（HTTP 422）
{ "success": false, "message": "名稱為必填", "errors": { "name": "名稱為必填" } }

// 業務錯誤（HTTP 4xx）
{ "success": false, "message": "此部門仍有員工，無法刪除" }
```

> 詳見 [0110_JSON回應格式.md](0110_JSON回應格式.md)。

### 5. error callback 重寫

**背景：** OpenCart 所有 AJAX 一律回 HTTP 200，驗證錯誤透過 `json['error']` 字串傳遞，前端在 success callback 內判斷 error/success 決定彈窗樣式。error callback 只處理 5xx / 網路斷線，只做 console.log。

Laravel 遵循 HTTP 語意：驗證失敗回 422、成功回 200。jQuery 的 AJAX 在非 2xx 時走 error callback，因此本系統需在 error callback 中解析 responseText 並交給 handleJsonResponse() 處理。

**OpenCart 原始寫法（已停用）：**

```javascript
error: function(xhr, ajaxOptions, thrownError) {
    console.log(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
}
```

**本系統寫法：**

```javascript
error: function (xhr, ajaxOptions, thrownError) {
    console.log(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);

    $('.alert-dismissible').remove();
    $(element).find('.is-invalid').removeClass('is-invalid');
    $(element).find('.invalid-feedback').removeClass('d-block');

    var json = {};
    try {
        json = JSON.parse(xhr.responseText);
    } catch (e) {
        json = { success: false, message: '發生錯誤，請稍後再試' };
    }

    handleJsonResponse(json, element);
}
```

### 6. 新增 replace_url / form_action 機制

OpenCart 新增資料後會 redirect 到編輯頁（整頁跳轉）。本系統改為**留在當前頁**，透過 `replace_url` 更新網址列、`form_action` 更新表單 action，搭配自動注入 `_method=PUT`，實現新增→編輯的無刷新切換。

```javascript
if (json['replace_url']) {
    window.history.pushState(null, null, json['replace_url']);

    if (json['form_action']) {
        $(element).attr('action', json['form_action']);

        // 新增 _method=PUT（Laravel 需要）
        if ($(element).find('input[name="_method"]').length === 0) {
            $(element).prepend('<input type="hidden" name="_method" value="PUT">');
        }
    }
}
```

### 7. 移除 debug console.log

OpenCart 原始的 Forms handler 有 7 個 debug log（`console.log(e)`, `console.log(element)`, `console.log('action ' + action)` 等）。本系統僅保留 `console.log(json)` 供開發階段觀察回應內容。

---

## 表單提交運作流程

### 整體流程

```
1. 使用者點擊儲存按鈕（button[form="form-xxx"]）
2. common.js 攔截 form submit（data-oc-toggle="ajax"），改用 AJAX 發送
3. Controller 驗證 → 成功回 200 JSON / 驗證失敗拋 422
4. jQuery AJAX：
   - HTTP 200 → success callback
   - HTTP 4xx/5xx → error callback（解析 responseText 為 JSON）
5. 兩個 callback 都呼叫 handleJsonResponse(json, element)：
   - success === true → 綠色 Toast
   - success === false → 紅色 Toast
   - errors → 標記各欄位 is-invalid
6. success callback 額外處理：
   - redirect → 全頁跳轉
   - replace_url + form_action → 新增→編輯無刷新切換
   - data-oc-load + data-oc-target → 局部刷新
```

### 翻譯欄位錯誤處理

翻譯欄位的驗證 key 為巢狀格式（如 `translations.zh_Hant.display_name`），由全域 handler（`bootstrap/app.php`）自動轉為扁平 key，common.js 直接使用，不需任何轉換。

**轉換流程：**

| 步驟 | 值 | 說明 |
|------|-----|------|
| 1. Laravel 驗證 key | `translations.zh_Hant.display_name` | 原始巢狀格式 |
| 2. 全域 handler 轉換 | `display_name-zh_Hant` | `{column}-{locale}` |
| 3. common.js 直接使用 | `display_name-zh_Hant` | 不轉換，保留底線 |
| 4. 對應 DOM ID | `#input-display_name-zh_Hant` | Blade 中的 ID |

> 全域 handler 的轉換邏輯位於 `bootstrap/app.php` 的 ValidationException 處理區段。詳見 [0109_例外處理.md](0109_例外處理.md)。

對應 Blade 中的 ID（直接使用 `$locale`，不需 `str_replace`）：

```blade
<div class="row mb-3 required">
    <label for="input-display_name-{{ $locale }}" class="col-sm-2 col-form-label">{{ $lang->column_display_name }}</label>
    <div class="col-sm-10">
        <input ... id="input-display_name-{{ $locale }}" class="form-control">
        <div id="error-display_name-{{ $locale }}" class="invalid-feedback"></div>
    </div>
</div>
```

---

## Upload / Download / Clear 三件組

### 運作機制

Upload、Download、Clear 是一組搭配使用的 UI 元件，用於**檔案附件欄位**：

```
[ 隱藏 input (存 code) ]
[ Upload 按鈕 ] [ Download 按鈕 ] [ Clear 按鈕 ]
```

### 流程

```
1. Upload：使用者選檔 → AJAX 上傳 → 伺服器回傳 code（唯一識別碼）
           → 存入隱藏 input → 啟用 Download 和 Clear 按鈕

2. Download：讀取隱藏 input 的 code → 導向下載 URL → 預覽/下載已上傳的檔案

3. Clear：清空隱藏 input → 停用 Download 和 Clear 按鈕
         → 如果是圖片，還原 placeholder 縮圖
```

### Upload 詳細說明

```javascript
$(document).on('click', '[data-oc-toggle=\'upload\']', function () { ... });
```

| data 屬性 | 說明 |
|-----------|------|
| `data-oc-toggle="upload"` | 標記為上傳按鈕 |
| `data-oc-url` | 上傳目標 URL |
| `data-oc-target` | 存放 code 的隱藏 input 選擇器 |
| `data-oc-size-max` | 最大檔案大小（KB） |
| `data-oc-size-error` | 超過大小時的錯誤訊息 |

**流程：**
1. 動態建立隱藏的 `<form enctype="multipart/form-data">`
2. 觸發 file input 的 click 事件，開啟檔案選擇器
3. 使用者選檔後，檢查檔案大小限制
4. 以 `FormData` 上傳，伺服器回傳 `{ code: "唯一識別碼" }`
5. 將 code 寫入 `data-oc-target` 指定的隱藏 input
6. 啟用同層的 Download 和 Clear 按鈕

### Download 詳細說明

```javascript
$(document).on('click', '[data-oc-toggle=\'download\']', function (e) { ... });
```

| data 屬性 | 說明 |
|-----------|------|
| `data-oc-toggle="download"` | 標記為下載按鈕 |
| `data-oc-target` | 存放 code 的隱藏 input 選擇器 |

讀取隱藏 input 中的 code，導向下載路由，觸發瀏覽器下載。

### Clear 詳細說明

```javascript
$(document).on('click', '[data-oc-toggle=\'clear\']', function () { ... });
```

| data 屬性 | 說明 |
|-----------|------|
| `data-oc-toggle="clear"` | 標記為清除按鈕 |
| `data-oc-target` | 要清空的 input 選擇器 |
| `data-oc-thumb` | 縮圖 img 選擇器（圖片類用） |

**行為：**
- 清空 `data-oc-target` 指定的 input 值
- 如有 `data-oc-thumb`，將縮圖還原為 `data-oc-placeholder` 的預設圖
- 停用同層的 Download 和 Clear 按鈕

### Blade 使用範例

```blade
{{-- 附件欄位 --}}
<div class="row mb-3">
    <label class="col-sm-2 col-form-label">{{ $lang->column_attachment }}</label>
    <div class="col-sm-10">
        <input type="hidden" name="attachment_code" id="input-attachment_code" value="{{ $contract->attachment_code }}">
        <button type="button"
            data-oc-toggle="upload"
            data-oc-url="{{ route('lang.ocadmin.tool.upload') }}"
            data-oc-target="#input-attachment_code"
            data-oc-size-max="10240"
            data-oc-size-error="{{ $lang->error_file_size }}"
            class="btn btn-outline-primary btn-sm">
            <i class="fa-solid fa-upload"></i> {{ $lang->button_upload }}
        </button>
        <button type="button"
            data-oc-toggle="download"
            data-oc-target="#input-attachment_code"
            class="btn btn-outline-info btn-sm"
            {{ $contract->attachment_code ? '' : 'disabled' }}>
            <i class="fa-solid fa-download"></i> {{ $lang->button_download }}
        </button>
        <button type="button"
            data-oc-toggle="clear"
            data-oc-target="#input-attachment_code"
            class="btn btn-outline-danger btn-sm"
            {{ $contract->attachment_code ? '' : 'disabled' }}>
            <i class="fa-solid fa-times"></i> {{ $lang->button_clear }}
        </button>
    </div>
</div>
```

### 本系統的使用場景

| 功能 | 使用元件 | 說明 |
|------|---------|------|
| 員工大頭照 | Image Manager | 圖片選擇器，有縮圖預覽 |
| 公司 Logo | Image Manager | 同上 |
| 員工證件掃描 | Upload + Download + Clear | 非圖片附件 |
| 合約附件 | Upload + Download + Clear | PDF/Word 文件 |
| 薪資單 PDF | Upload + Download + Clear | 自動產生或手動上傳 |

> Image Manager 用於「圖片」類欄位（有縮圖預覽），Upload/Download/Clear 三件組用於「檔案」類欄位（無預覽，只有上傳/下載/清除）。

---

## Image Manager

```javascript
$(document).on('click', '[data-oc-toggle=\'image\']', function (e) { ... });
```

| data 屬性 | 說明 |
|-----------|------|
| `data-oc-toggle="image"` | 標記為圖片選擇按鈕 |
| `data-oc-target` | 存放圖片路徑的隱藏 input 選擇器 |
| `data-oc-thumb` | 縮圖 img 選擇器 |

點擊後 AJAX 載入檔案管理器 Modal，使用者在 Modal 中選擇/上傳圖片後，回填路徑到隱藏 input 並更新縮圖。

---

## 待改造項目（TODO）

以下功能保留了 OpenCart 的硬編碼 URL，目前不影響運作（尚無頁面使用），但啟用時需改為 Laravel 路由：

| 功能 | 目前 URL | 需改為 |
|------|----------|--------|
| Download | `index.php?route=tool/upload.download&user_token=...` | Laravel 路由 |
| Image Manager | `index.php?route=common/filemanager&user_token=...` | Laravel 路由 |
| Language switcher | `index.php?route=common/language.save&user_token={{ user_token }}` | Laravel 路由（含 Twig → Blade） |

啟用時機：
- **Download / Upload**：建置合約管理、員工文件上傳等附件功能時
- **Image Manager**：建置員工大頭照、公司 Logo 等圖片欄位時
- **Language switcher**：Ocadmin 啟用多語切換功能時

> 這些功能的 JS 邏輯本身不需大改，主要是 URL 改為 Laravel route、認證方式從 `user_token` 改為 Laravel session/middleware。Upload 的後端需建立對應的 Controller 和 Storage 處理。

---

## 其他保留功能說明

### Autocomplete

jQuery 自動完成外掛，用於搜尋框。支援分類分組顯示、150ms 延遲請求、dropdown 下拉選單。

**使用方式：**

```javascript
$('#input-search').autocomplete({
    source: function(request, response) {
        $.ajax({
            url: '搜尋 API URL',
            data: { search: request },
            success: function(json) { response(json); }
        });
    },
    select: function(item) {
        // item = { value: '...', label: '...' }
    }
});
```

### Chain class

AJAX 佇列管理，確保多個 AJAX 呼叫依序執行。用於需要保證順序的批次操作。

### Alert Fade

`#alert` 區域新增 alert 時，自動在 3 秒後開始淡出，再 3 秒後移除 DOM 元素。

### Button plugin

為按鈕提供 loading 狀態（轉圈圖示 + disabled）和 reset 狀態（恢復原始內容）。在 AJAX 的 `beforeSend` / `complete` 中使用。

---

## 相關文件

- [0106_Ocadmin程式規範.md](0106_Ocadmin程式規範.md) — 表單 AJAX 規範、ID 命名規範
- [0109_例外處理.md](0109_例外處理.md) — 全域例外 handler
- [0110_JSON回應格式.md](0110_JSON回應格式.md) — 統一 JSON 回應格式定義

---

*文件版本：v1.0*
*建立日期：2026-02-07*
