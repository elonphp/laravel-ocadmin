# OrmHelper 查詢輔助類別

## 概述

OrmHelper 是 Eloquent 查詢輔助類別，提供統一的查詢介面，自動處理篩選、排序、分頁。

檔案位置：`app/Helpers/Classes/OrmHelper.php`

---

## 多語模式

OrmHelper 支援三種多語處理模式，由 Model 的 `translation_mode` 屬性決定：

| 模式 | 常數 | 資料表 | 說明 |
|------|------|--------|------|
| 1 | `TRANSLATION_MODE_SUFFIX` | `xxx_translations` | 翻譯子表 JOIN（預設） |
| 2 | `TRANSLATION_MODE_EAV` | `xxx_metas` | 純 EAV（暫不實作） |
| 3 | `TRANSLATION_MODE_ZEAV` | `zeav_xxx` | EAV + 快取表 |

**mode=3** 時，OrmHelper 自動委派給 `OrmZeavHelper` 處理。

---

## 基本使用

### Controller 範例

```php
use App\Helpers\Classes\OrmHelper;

public function getList(Request $request): mixed
{
    $query = Product::query();
    $params = $request->all();

    // 自動處理 filter_*, equal_* 參數
    OrmHelper::prepare($query, $params);

    // 取得分頁結果
    return OrmHelper::getResult($query, $params);
}
```

### 參數選項

| 參數 | 說明 | 預設值 |
|------|------|--------|
| `filter_欄位名` | 彈性查詢（支援萬用字元） | — |
| `equal_欄位名` | 精確查詢 | — |
| `sort` | 排序欄位 | `id` |
| `order` | 排序方向 | `desc` |
| `per_page` / `limit` | 每頁筆數 | config 設定值 |
| `pagination` | 是否分頁 | `true` |
| `first` | 取得單筆 | `false` |
| `pluck` | 取得特定欄位 | — |
| `keyBy` | 結果以欄位為 key | — |

---

## filter_ 查詢語法

`filter_` 前綴支援萬用字元與運算子：

### 萬用字元

| 輸入值 | SQL | 說明 |
|-------|-----|------|
| `王` | `REGEXP '王'` | 包含「王」 |
| `王*` | `REGEXP '^王(.*)'` | 以「王」開頭 |
| `*明` | `REGEXP '(.*)明$'` | 以「明」結尾 |
| `王 明` | `REGEXP '王(.*)明'` | 空格轉 `(.*)`，匹配「王...明」 |

### 運算子

| 輸入值 | SQL | 說明 |
|-------|-----|------|
| `=` | `IS NULL OR = ''` | 空值 |
| `=王小明` | `= '王小明'` | 完全相等 |
| `<>` | `IS NOT NULL AND <> ''` | 非空值 |
| `<>王小明` | `<> '王小明'` | 不等於 |
| `>100` | `> 100` | 大於 |
| `<100` | `< 100` | 小於 |

---

## equal_ 查詢語法

`equal_` 前綴只做精確比對，不支援萬用字元：

| 輸入值 | SQL | 說明 |
|-------|-----|------|
| `active` | `= 'active'` | 完全相等 |
| `*` | （不加條件） | 顯示全部 |

---

## is_active 預設行為

OrmHelper 預設會加上 `is_active = 1` 條件。

```php
// 顯示全部（含停用）
$params['equal_is_active'] = '*';

// 只顯示停用
$params['equal_is_active'] = 0;
```

---

## ZEAV 模式（translation_mode = 3）

### Model 設定

```php
use App\Traits\HasZeavTable;

class Product extends Model
{
    use HasZeavTable;

    public $translation_mode = 3;
    public $translation_keys = ['name', 'description'];

    // 選用：meta_key_id 對照表（避免查詢資料庫）
    public $meta_keys = [
        'name' => 1,
        'description' => 2,
    ];
}
```

### 資料表關係

```
products (主表)
├── product_metas (EAV 真實資料)
│     ├── product_id
│     ├── meta_key_id
│     ├── locale
│     └── meta_value
└── zeav_products (快取表)
      ├── product_id
      ├── locale
      ├── name
      └── description
```

### 自動 JOIN

當查詢條件或排序涉及 `translation_keys` 欄位時，自動 LEFT JOIN `zeav_xxx` 表：

```sql
SELECT products.*
FROM products
LEFT JOIN zeav_products AS zp
    ON products.id = zp.product_id
    AND zp.locale = 'zh-TW'
WHERE zp.name REGEXP '關鍵字'
ORDER BY zp.name ASC
```

### Controller 使用

Controller 程式碼不需更改，OrmHelper 自動判斷並委派：

```php
// 查詢（與 mode=1 寫法相同）
$query = Product::query();
OrmHelper::prepare($query, $params);
$result = OrmHelper::getResult($query, $params);

// 儲存（使用 HasZeavTable trait）
$product->saveMetas($request->all());
```

---

## OrmZeavHelper

`OrmZeavHelper` 是 ZEAV 模式的專用輔助類別，由 OrmHelper 自動呼叫。

檔案位置：`app/Helpers/Classes/OrmZeavHelper.php`

### 主要方法

| 方法 | 說明 |
|------|------|
| `prepare()` | 自動 JOIN zeav 表並套用篩選 |
| `getResult()` | 取得查詢結果（支援翻譯欄位排序） |
| `getZeavTableName()` | 取得 zeav 表名（products → zeav_products） |
| `getMetaTableName()` | 取得 metas 表名（products → product_metas） |
| `getForeignKeyName()` | 取得外鍵名稱（products → product_id） |

---

## HasZeavTable Trait

提供 Model 操作 EAV 資料並同步快取表的方法。

檔案位置：`app/Traits/HasZeavTable.php`

### 關聯方法

| 方法 | 說明 |
|------|------|
| `metas()` | HasMany 關聯到 xxx_metas |
| `zeavProfile()` | HasOne 當前語系的 zeav 資料 |
| `zeavProfiles()` | HasMany 所有語系的 zeav 資料 |

### 讀取方法

| 方法 | 說明 |
|------|------|
| `getMeta($key, $locale)` | 取得單一 meta 值 |
| `getMetas($keys, $locale)` | 取得多個 meta 值 |
| `toArrayWithMetas($locale)` | 取得含 metas 的完整陣列 |

### 寫入方法

| 方法 | 說明 |
|------|------|
| `setMeta($key, $value, $locale)` | 設定單一 meta |
| `setMetas($data, $locale)` | 批次設定 metas（自動同步 zeav） |
| `saveMetas($data, $locale)` | 從請求資料提取並儲存 metas |
| `syncToZeav($locale)` | 同步資料到 zeav 快取表 |

### 快取管理

| 方法 | 說明 |
|------|------|
| `deleteZeav()` | 刪除 zeav 快取 |
| `rebuildZeav()` | 重建 zeav 快取 |

### 使用範例

```php
// 讀取
$name = $product->getMeta('name', 'zh-TW');
$metas = $product->getMetas(['name', 'description']);

// 寫入（自動同步 zeav）
$product->setMetas([
    'name' => ['zh-TW' => '產品名稱', 'en' => 'Product Name'],
    'description' => ['zh-TW' => '描述', 'en' => 'Description'],
]);

// 從 Controller 儲存
$product->saveMetas($request->all());
```

---

## 注意事項

1. **REGEXP vs LIKE**：OrmHelper 使用 MySQL REGEXP，萬用字元是 `*` 而非 `%`
2. **特殊字元**：`(`、`)`、`+` 會自動跳脫
3. **預設 is_active**：自動加上 `is_active = 1`，設 `*` 可取消
4. **zeav 快取**：寫入 metas 後自動同步，可用 `rebuildZeav()` 重建

---

*文件版本：v1.0*
*更新日期：2025-12-22*
