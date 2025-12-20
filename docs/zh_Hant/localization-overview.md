# 多語系總覽

本套件支援三種多語系機制，可獨立或組合使用。

---

## 多語類型

| 類型 | 說明 | 範例 | 文件 |
|------|------|------|------|
| **網址多語** | URL 路徑包含語系前綴 | `/en/ocadmin/login` | [localization-url.md](localization-url.md) |
| **介面多語** | UI 文字翻譯（lang 資料夾） | 登入 / Login | [localization-interface.md](localization-interface.md) |
| **內容多語** | 資料庫內容翻譯 | 商品名稱多語版本 | [localization-content.md](localization-content.md) |

---

## 運作流程

```
使用者請求 /en/ocadmin/products
        │
        ▼
┌─────────────────────────────┐
│     網址多語 (URL)           │
│  從 URL 識別語系 → en        │
│  App::setLocale('en')       │
└─────────────────────────────┘
        │
        ▼
┌─────────────────────────────┐
│     介面多語 (Interface)     │
│  載入 lang/en/*.php         │
│  __('common.save') → Save   │
└─────────────────────────────┘
        │
        ▼
┌─────────────────────────────┐
│     內容多語 (Content)       │
│  查詢 product_translations  │
│  WHERE locale = 'en'        │
└─────────────────────────────┘
        │
        ▼
    回傳英文版頁面
```

---

## 設定總覽

```php
// config/ocadmin.php

return [
    'localization' => [
        // 網址多語
        'url' => [
            'enabled' => true,
            'prefix' => true,           // URL 顯示語系前綴
            'hide_default' => false,    // 隱藏預設語系前綴
        ],

        // 語系設定
        'default' => 'zh_Hant',
        'supported' => ['zh_Hant', 'en'],
        'names' => [
            'zh_Hant' => '繁體中文',
            'en' => 'English',
        ],

        // URL 與內部格式對應
        'url_mapping' => [
            'zh-hant' => 'zh_Hant',
            'en' => 'en',
        ],
    ],
];
```

---

## 使用情境

### 情境一：僅網址多語

```php
'localization' => [
    'url' => ['enabled' => true],
    'default' => 'zh_Hant',
    'supported' => ['zh_Hant'],  // 只支援一種語系
],
```

- URL：`/zh-hant/ocadmin/...`
- 介面：全部使用 zh_Hant
- 內容：無多語

### 情境二：網址 + 介面多語

```php
'localization' => [
    'url' => ['enabled' => true],
    'supported' => ['zh_Hant', 'en'],
],
```

- URL：`/zh-hant/...` 或 `/en/...`
- 介面：根據語系載入對應翻譯
- 內容：無多語

### 情境三：完整多語

```php
'localization' => [
    'url' => ['enabled' => true],
    'supported' => ['zh_Hant', 'en', 'ja'],
],
```

- URL：三種語系
- 介面：三種翻譯
- 內容：資料庫多語欄位

### 情境四：無 URL 前綴

```php
'localization' => [
    'url' => [
        'enabled' => true,
        'prefix' => false,  // 不顯示前綴
    ],
],
```

- URL：`/ocadmin/...`（無語系前綴）
- 語系：從 session 或 cookie 判斷
- 適用：單一語系或後台自動偵測

---

## 語系格式

### 格式規範

| 用途 | 格式 | 範例 | 說明 |
|------|------|------|------|
| URL | 全小寫 + hyphen | `zh-hant` | 網址友善 |
| 內部/資料庫 | 底線 + 混合大小寫 | `zh_Hant` | 可作為物件屬性 |
| Lang 資料夾 | 底線 + 混合大小寫 | `lang/zh_Hant/` | 同內部格式 |

### 為什麼用底線

```php
// ✅ 底線可作為物件屬性
$product->translations->zh_Hant

// ❌ 橫線會導致語法錯誤
$product->translations->zh-Hant  // 被解析為 zh 減 Hant
```

### 對照表

| URL 格式 | 內部格式 | 說明 |
|----------|----------|------|
| `zh-hant` | `zh_Hant` | 繁體中文 |
| `zh-hans` | `zh_Hans` | 簡體中文 |
| `en` | `en` | 英文 |
| `ja` | `ja` | 日文 |

### 轉換方法

```php
use Elonphp\LaravelOcadminModules\Support\LocaleHelper;

// URL → 內部
LocaleHelper::toInternal('zh-hant');  // 'zh_Hant'

// 內部 → URL
LocaleHelper::toUrl('zh_Hant');  // 'zh-hant'
```

---

## 路由名稱規範

本套件使用 `ocadmin.` 為路由前綴：

```php
// 路由名稱
ocadmin.dashboard
ocadmin.system.logs.index
ocadmin.access-control.roles.form

// 使用
route('ocadmin.dashboard')
ocadmin_route('dashboard')
```

> 不使用 `lang.` 前綴。多語由設定檔控制，程式碼不需變動。

---

## Helper 函數

```php
// 產生路由（自動處理語系）
ocadmin_route('dashboard')
ocadmin_route('system.logs.index', ['id' => 1])
ocadmin_route('dashboard', [], 'en')  // 指定語系

// 取得當前語系
ocadmin_locale()           // 'zh_Hant'（內部格式）
ocadmin_url_locale()       // 'zh-hant'（URL 格式）

// 語系切換連結
ocadmin_switch_locale_url('en')  // 切換到英文的 URL
```

---

## 相關文件

- [localization-url.md](localization-url.md) - 網址多語詳細說明
- [localization-interface.md](localization-interface.md) - 介面多語詳細說明
- [localization-content.md](localization-content.md) - 內容多語詳細說明

---

*文件版本：v1.1 - 更新語系格式規範（底線取代橫線）*
