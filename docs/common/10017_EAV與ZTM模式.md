# EAV 與 ZTM 模式

> **本文件僅供參考，目前評估不值得做。**
> 原因：對於欄位由開發者定義、變動頻率低的系統（如單一電商、同集團人資），使用 Migration 管理結構變更已足夠，EAV 反而增加複雜度。EAV + Ztm 適合的場景是 SaaS 多租戶、CMS 表單生成器等「欄位由終端使用者定義、開發者事先不知道有哪些欄位」的系統。

---

## 概述

本系統採用 **EAV**（Entity-Attribute-Value）模式處理動態擴展欄位，搭配 **Ztm 快取表**與**雙資料庫架構**，解決 EAV 的查詢效能問題。

**EAV 適用於：**

- 需要動態新增欄位，不想頻繁修改主表結構
- 需要支援多語系翻譯的欄位
- 欄位數量不固定，依業務需求擴展

**核心設計：**

- `meta_keys` — 統一的欄位定義表（basedata）
- `*_metas` — 各資料表的 EAV 擴展欄位（basedata）
- `ztm_*` — EAV 資料的扁平化快取表（cachedata），支援排序、JOIN、索引

> EAV 資料是**真正的資料**，存放在 basedata 資料庫，不可丟失。
> Ztm 快取表可隨時刪除重建。

本質上類似 CQRS（Command Query Responsibility Segregation）：寫入端用靈活的 EAV 結構，讀取端用高效的扁平表結構。

---

## 雙資料庫架構

### .env 設定

```env
# 主資料庫（basedata）
DB_CONNECTION=mysql
DB_DATABASE=ocadmin_eavztm

# 快取資料庫（cachedata）
DB_CACHEDATA_CONNECTION=cachedata
DB_CACHEDATA_DATABASE=ocadmin_eavztm_cache
```

### config/database.php

```php
'connections' => [
    'mysql' => [
        // 預設連線 — basedata
        'database' => env('DB_DATABASE', 'laravel'),
    ],
    'cachedata' => [
        // 快取連線 — 可重建的資料
        'database' => env('DB_CACHEDATA_DATABASE', 'cachedata'),
    ],
],
```

### 資料歸屬原則

| 資料庫 | Connection | 內容 | 性質 |
|--------|------------|------|------|
| basedata | `mysql`（預設） | 主表、`meta_keys`、`*_metas`、交易表 | 不可丟失，需備份 |
| cachedata | `cachedata` | `ztm_*` 快取表 | 可隨時刪除重建，不需備份 |

---

## 架構圖

```
basedata (ocadmin_eavztm)              cachedata (ocadmin_eavztm_cache)
connection: mysql                       connection: cachedata
════════════════════════════            ════════════════════════════

┌───────────────────────────────────────────────────┐
│                    meta_keys                       │
│  id │ name │ table_name │ data_type │ is_translatable │
└───────────────────────────────────────────────────┘
                        │
         ┌──────────────┼──────────────┐
         ▼              ▼              ▼
┌────────────────┐ ┌────────────────┐ ┌────────────────┐
│ sys_term_metas │ │inv_product_metas│ │  user_metas   │
├────────────────┤ ├────────────────┤ ├────────────────┤
│ term_id        │ │ product_id     │ │ user_id        │
│ key_id         │ │ key_id         │ │ key_id         │
│ locale         │ │ locale         │ │ locale         │
│ value          │ │ value          │ │ value          │
└────────────────┘ └────────────────┘ └────────────────┘
         │              │              │
         ▼              ▼              ▼
                                               cachedata
                                       ════════════════════
                                       ┌──────────────────┐
┌──────────────┐                       │  ztm_sys_terms   │
│  sys_terms   │                       ├──────────────────┤
│  (主表)      │─────────────────────▶ │ term_id          │
├──────────────┤                       │ locale           │
│ id           │                       │ display_name     │
│ taxonomy_id  │                       │ description      │
│ code         │                       │ icon             │
│ sort_order   │                       └──────────────────┘
│ is_active    │                           水平扁平化快取
└──────────────┘
     核心欄位
```

---

## 命名規則

| 主表 (basedata) | Metas 表 (basedata) | 快取表 (cachedata) | 外鍵 |
|-----------------|--------------------|--------------------|------|
| `sys_terms` | `sys_term_metas` | `ztm_sys_terms` | `term_id` |
| `sys_taxonomies` | `sys_taxonomy_metas` | `ztm_sys_taxonomies` | `taxonomy_id` |
| `inv_products` | `inv_product_metas` | `ztm_inv_products` | `product_id` |
| `users` | `user_metas` | `ztm_users` | `user_id` |

- **Metas 表**：主表名單數化 + `_metas`
- **Ztm 表**：`ztm_` + 主表名
- **Ztm 表由 Artisan 命令動態建立**，不寫 migration

**Ztm 前綴設計理由：**

| 前綴 | 說明 |
|------|------|
| `z` | 字母排序中排最後，備份時易於識別與排除 |
| `tm` | **t**e**m**porary（暫時的），表示此表為快取，可隨時重建 |

---

## 資料表結構

### meta_keys 表（統一欄位定義）

所有 `*_metas` 表共用的欄位對照表，定義欄位名稱與資料類型。

```sql
CREATE TABLE meta_keys (
    id SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    table_name VARCHAR(30) NOT NULL,
    data_type VARCHAR(20) DEFAULT 'text',
    is_translatable TINYINT(1) DEFAULT 0,
    sort_order SMALLINT UNSIGNED DEFAULT 0,
    description VARCHAR(100) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    UNIQUE KEY (name, table_name)
);
```

**欄位說明：**

| 欄位 | 說明 |
|------|------|
| `name` | 欄位名稱，如 `display_name`、`description` |
| `table_name` | 所屬主表名（如 `sys_terms`、`inv_products`） |
| `data_type` | 資料類型（text, integer, decimal, boolean, json），用於驗證與轉型 |
| `is_translatable` | 是否支援多語，影響 locale 處理 |
| `sort_order` | 欄位在 ztm 快取表中的排列順序 |

### *_metas 表（EAV 擴展欄位）

每個主表對應一個 `*_metas` 表，儲存擴展欄位的鍵值對。

```sql
CREATE TABLE sys_term_metas (
    term_id BIGINT UNSIGNED NOT NULL,
    key_id SMALLINT UNSIGNED NOT NULL,
    locale VARCHAR(10) DEFAULT '',         -- 空字串=非多語
    value TEXT NULL,

    PRIMARY KEY (term_id, key_id, locale),
    FOREIGN KEY (term_id) REFERENCES sys_terms(id) ON DELETE CASCADE,
    FOREIGN KEY (key_id) REFERENCES meta_keys(id) ON DELETE CASCADE
);
```

### ztm_* 快取表（由命令動態建立）

快取表的欄位由 `meta_keys` 動態產生，存放於 cachedata。

```sql
CREATE TABLE ztm_sys_terms (
    term_id BIGINT UNSIGNED NOT NULL,
    locale VARCHAR(10) NOT NULL DEFAULT '',
    display_name TEXT NULL,     -- 由 meta_keys 動態產生
    description TEXT NULL,
    icon VARCHAR(50) NULL,
    PRIMARY KEY (term_id, locale),
    INDEX (display_name(100))
);
```

> 注意：cachedata 的 ztm 表**不建立 FK constraint** 指向 basedata，因為跨資料庫 FK 在 MySQL 中不可靠。由應用層（trait）保障資料一致性。

---

## 多語欄位處理

### locale 欄位規則

| locale 值 | 說明 |
|-----------|------|
| `''`（空字串） | 非多語欄位，所有語系共用 |
| `zh_Hant` | 繁體中文 |
| `en` | 英文 |

### 範例資料

```
sys_term_metas:
┌─────────┬────────┬─────────┬──────────┐
│ term_id │ key_id │ locale  │ value    │
├─────────┼────────┼─────────┼──────────┤
│ 1       │ 1      │ zh_Hant │ 飲料類   │  ← display_name (多語)
│ 1       │ 1      │ en      │ Beverages│  ← display_name (多語)
│ 1       │ 3      │         │ fa-coffee│  ← icon (非多語)
└─────────┴────────┴─────────┴──────────┘

ztm_sys_terms (快取表自動產生):
┌─────────┬─────────┬──────────────┬─────────────┬───────────┐
│ term_id │ locale  │ display_name │ description │ icon      │
├─────────┼─────────┼──────────────┼─────────────┼───────────┤
│ 1       │         │ null         │ null        │ fa-coffee │  ← 非多語列
│ 1       │ zh_Hant │ 飲料類       │ null        │ null      │
│ 1       │ en      │ Beverages    │ null        │ null      │
└─────────┴─────────┴──────────────┴─────────────┴───────────┘
```

讀取 profile 時，自動合併非多語列（`locale=''`）與當前語系列，語系值優先。

---

## 與 JSON extra 的分工

| 場景 | 使用方案 |
|------|----------|
| 需要排序、篩選 | EAV（搭配快取表） |
| 多語欄位 | EAV |
| 雜項設定、標籤 | JSON extra |
| 小型輔助資訊 | JSON extra |

```
sys_terms (主表)
├── id, taxonomy_id, code, sort_order  ← 核心欄位
├── extra JSON                          ← 雜項小資料
└── → sys_term_metas (EAV)              ← 擴展欄位
```

---

## 使用方式

### Model Trait

```php
class Term extends Model
{
    use HasEavMeta, HasZtmTable;

    protected $table = 'sys_terms';
    // ztm 表自動推導為 ztm_sys_terms，使用 cachedata connection
}
```

### EAV 讀寫（HasEavMeta）

```php
$term = Term::find(1);

// 多語欄位
$term->setMeta('display_name', '飲料類', 'zh_Hant');
$term->setMeta('display_name', 'Beverages', 'en');

// 非多語欄位（locale 自動判斷）
$term->setMeta('icon', 'fa-coffee');

// 批量寫入
$term->setMetas([
    'display_name' => '飲料類',
    'description' => '各式飲品',
], 'zh_Hant');

// 讀取
$term->getMeta('display_name');              // 當前語系
$term->getMeta('display_name', 'en');        // 指定語系
$term->hasMeta('icon');                      // 是否存在

// 刪除
$term->deleteMeta('icon');                   // 刪除所有語系
$term->deleteMeta('display_name', 'en');     // 刪除特定語系
```

### 快取表查詢（HasZtmTable）

#### profile 關聯

在 Model 中透過 `profile` 屬性存取 ztm 快取表資料：

```php
$term->profile;                  // 取得擴展屬性（合併非多語 + 當前語系）
$term->profile->display_name;    // 取得 display_name 欄位
$term->profile->icon;            // 取得 icon 欄位（非多語）
$term->getProfile('en');         // 指定語系
```

**選擇 `profile` 的理由：**
- **語意正確** — Profile 表示「實體的擴展描述/屬性」
- **Laravel 慣例** — `$user->profile` 是常見的關聯用法
- **API 一致性** — 相關方法統一以 Profile 結尾：`orderByProfile()`、`whereProfile()`

#### Scope 查詢

```php
// 排序
Term::orderByProfile('display_name', 'asc')->get();

// 篩選
Term::whereProfile('display_name', '飲料')->get();

// 模糊搜尋
Term::searchProfile('display_name', '飲料')->get();
```

### Artisan 命令

```bash
# 列出表名映射規則
php artisan ztm:sync --list

# 同步指定表結構
php artisan ztm:sync sys_terms

# 同步所有表結構
php artisan ztm:sync

# 重建模式（先 DROP 再重建）
php artisan ztm:sync sys_terms --rebuild
php artisan ztm:sync --rebuild

# 重建快取資料（從 *_metas 重新產生）
php artisan ztm:rebuild sys_terms
php artisan ztm:rebuild --all
```

---

## 核心類別

### ZtmTableHelper

表名映射輔助類。

**檔案位置：** `app/Helpers/Classes/ZtmTableHelper.php`

```php
ZtmTableHelper::getZtmTableName('sys_terms');      // 'ztm_sys_terms'
ZtmTableHelper::getForeignKeyName('sys_terms');     // 'term_id'
ZtmTableHelper::getMetasTableName('sys_terms');     // 'sys_term_metas'
ZtmTableHelper::getMainTableName('ztm_sys_terms');  // 'sys_terms'
ZtmTableHelper::getConnectionName();                // 'cachedata'
ZtmTableHelper::dataTypeToColumnType('integer');     // 'INT'
```

### ZtmTableSyncService

快取表結構同步服務。

**檔案位置：** `app/Services/System/Database/ZtmTableSyncService.php`

```php
$syncService = app(ZtmTableSyncService::class);
$syncService->syncTableStructure('sys_terms');   // 同步表結構（新增/移除欄位）
$syncService->syncAll();                         // 同步所有表結構
$syncService->rebuildTable('sys_terms');          // 重建單一表快取資料
$syncService->rebuildAll();                       // 重建所有快取資料
$syncService->syncEntityRow('sys_terms', 1);     // 同步單一實體的快取列
```

### Trait

| Trait | 檔案位置 | 用途 |
|-------|---------|------|
| HasEavMeta | `app/Traits/HasEavMeta.php` | setMeta / getMeta / deleteMeta / setMetas / getAllMetas |
| HasZtmTable | `app/Traits/HasZtmTable.php` | profile 屬性、orderByProfile / whereProfile / searchProfile scope |

---

## 同步機制

### 觸發時機

| 事件 | 動作 |
|------|------|
| `meta_keys` 新增欄位 | `ztm:sync` → ALTER TABLE ADD COLUMN（cachedata） |
| `meta_keys` 刪除欄位 | `ztm:sync` → ALTER TABLE DROP COLUMN（cachedata） |
| `*_metas` 資料變更 | `setMeta()` / `deleteMeta()` 自動同步 cachedata 對應的快取表單筆記錄 |

### 自動同步

每次 `setMeta()` / `deleteMeta()` 會同步寫入兩個資料庫：

```
setMeta('icon', 'fa-code')
  ├─ 1. INSERT/UPDATE sys_term_metas   ← basedata
  └─ 2. DELETE + INSERT ztm_sys_terms  ← cachedata（同一個 request）
```

cachedata 寫入失敗不影響資料完整性，可用 `ztm:rebuild` 修復。

### 手動重建

```bash
# 重建單一表的快取
php artisan ztm:rebuild sys_terms

# 重建所有快取表
php artisan ztm:rebuild --all
```

---

## 備份策略

雙資料庫架構大幅簡化備份流程：

```bash
# 只備份 basedata，跳過整個 cachedata
mysqldump ocadmin_eavztm > backup.sql
```

還原後重建快取：

```bash
mysql ocadmin_eavztm < backup.sql
php artisan ztm:rebuild --all
```

---

## EAV vs 快取表比較

| 項目 | EAV (*_metas) | 快取表 (ztm_*) |
|------|---------------|----------------|
| 資料庫 | basedata | cachedata |
| 資料儲存 | 鍵值對，垂直結構 | 扁平化，水平結構 |
| 排序 | 複雜，需子查詢 | 標準 SQL |
| JOIN | 複雜 | 標準 SQL |
| 索引 | 無法有效索引 | 可直接建索引 |
| 是否可重建 | 否（主要資料） | 是（快取） |

---

## 跨庫注意事項

- MySQL 跨庫 JOIN：`SELECT * FROM ocadmin_eavztm.sys_terms t JOIN ocadmin_eavztm_cache.ztm_sys_terms z ON ...`
- 同一 MySQL instance 的不同 database 可以直接跨庫 JOIN，不需特殊設定
- cachedata 的 ztm 表**不建立 FK constraint** 指向 basedata

---

## 注意事項

1. **快取表可重建** — 真正的資料在 `*_metas`（basedata），快取表隨時可刪除重建
2. **跨庫不建 FK** — cachedata 的 ztm 表不對 basedata 建 FK constraint
3. **locale 空字串** — 非多語欄位使用 `locale = ''`，不是 NULL
4. **資料類型** — `value` 欄位為 TEXT，應用層負責類型轉換
5. **查詢效能** — EAV 直接查詢效能較差，搭配快取表（ztm）使用
6. **自動同步** — 透過 trait 自動同步，無需手動維護
7. **Connection 指定** — ztm 相關的 Model / Migration 必須指定 `cachedata` connection

---

## 適用場景評估

| 場景 | 是否適合 EAV + Ztm | 建議做法 |
|------|-------------------|----------|
| SaaS 多租戶（各客戶自定義欄位） | 適合 | EAV + Ztm |
| CMS / 表單生成器 | 適合 | EAV + Ztm |
| 醫療/研究系統（不同案件不同欄位） | 適合 | EAV + Ztm |
| 單一電商 | 不適合 | Migration |
| 同集團人資系統 | 不適合 | Migration |
| 欄位固定、變動少的後台 | 不適合 | Migration |

**判斷準則：欄位是否由終端使用者定義、開發者事先無法預知。**
如果欄位由開發者定義且可預期，Migration 是最直接的做法。

---

## 參考案例：WordPress 與 WooCommerce

WordPress 是 EAV 模式最廣泛部署的實例，核心圍繞四組 meta 表建構：

| 主表 | Meta 表 | 外鍵 |
|------|---------|------|
| `wp_posts` | `wp_postmeta` | `post_id` |
| `wp_users` | `wp_usermeta` | `user_id` |
| `wp_terms` | `wp_termmeta` | `term_id` |
| `wp_comments` | `wp_commentmeta` | `comment_id` |

```sql
-- WordPress 的 postmeta 結構
CREATE TABLE wp_postmeta (
    meta_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    post_id BIGINT UNSIGNED NOT NULL,
    meta_key VARCHAR(255),
    meta_value LONGTEXT
);
```

### 與本系統設計的比較

| 面向 | WordPress | 本系統 EAV + Ztm |
|------|-----------|-------------------|
| Key 定義 | 直接存字串 `meta_key` | 正規化到 `meta_keys` 表，用 `key_id` 參照 |
| 多語 | 無原生支援，依賴 plugin（WPML / Polylang） | 原生 `locale` 欄位 |
| 資料類型 | 全部 `LONGTEXT`，無型別資訊 | `meta_keys.data_type` 有型別定義 |
| 查詢效能 | 直接查 meta 表，大量資料時效能差 | Ztm 快取表解決排序與篩選 |
| PK 設計 | 自增 `meta_id`（允許同 key 多筆） | 複合 PK（不允許重複） |

### WordPress 為何能靠 EAV 運作

- **Plugin 生態的核心需求** — 任何 plugin 都能 `add_post_meta('my_field', $value)`，不需改 schema。正是「欄位由第三方定義、開發者事先無法預知」的場景
- **多數站台規模不大** — 部落格、小型企業站，幾百篇文章，EAV 效能問題感覺不到

### WooCommerce 的教訓

WooCommerce 早期**完全依賴 `wp_postmeta`** 儲存商品資料，一個商品可能產生 40～100+ 筆 meta（價格、庫存、重量、SKU、圖片、變體……）。商品列表排序、價格範圍篩選都需要多重 `JOIN wp_postmeta`，幾千筆商品就開始出現效能瓶頸。

WooCommerce 在 2022 年推出 **HPOS（High-Performance Order Storage）**，將訂單從 `wp_posts` + `wp_postmeta` 搬遷至專屬扁平表（`wp_wc_orders`）。商品方面也在進行類似的 Product Tables 重構。

```
WooCommerce 的演化路徑：
  wp_posts + wp_postmeta（純 EAV）
  → 效能瓶頸
  → wp_wc_orders 扁平表  ≈ 本系統的 ztm 快取表概念
```

**結論：** WordPress/WooCommerce 的歷程驗證了本文件的判斷 — EAV 適合「欄位由外部定義」的 CMS/Plugin 場景；當用於欄位固定的電商（價格、庫存、SKU 是已知欄位），最終仍須回歸扁平表。本系統的 Ztm 設計等同於一步到位地解決 WooCommerce 花了近十年才著手重構的問題。

### WooCommerce 回歸扁平表 = 回歸傳統電商結構

WooCommerce 從 EAV 搬遷到專屬扁平表，本質上就是回到 OpenCart 等傳統電商一直在用的結構：

```
傳統電商（OpenCart 式）
  products, product_options, product_option_values
  orders, order_products, order_product_options
  → 每種資料有專屬表，結構清晰，正常 SQL 就能排序、篩選、JOIN

        ↓ WordPress 覺得這樣太死板

WordPress / WooCommerce（EAV 式）
  wp_posts + wp_postmeta 搞定一切
  → 靈活，一張 meta 表打天下
  → 但效能差、查詢複雜、debug 困難

        ↓ 撐了近十年，撐不住了

WooCommerce HPOS / Product Tables（回歸扁平表）
  wp_wc_orders, wp_wc_products（規劃中）
  → 本質上就是回到傳統電商的專屬表思路
```

繞了一大圈，又回到起點。

### WordPress 的快取策略：單一資料庫 + 應用層快取

值得注意的是，WordPress 解決 EAV 效能問題的方式是**應用層快取**，而非雙資料庫：

```
WordPress：一個資料庫，一份資料
  wp_posts + wp_postmeta（EAV）
  → 效能不夠？
  → Object Cache（Redis / Memcached / 檔案快取）
  → wp_cache_get() / wp_cache_set()
```

一個 post 的所有 meta 一次撈完、快取成 array，下次讀取不查 DB。資料永遠只有一份，沒有同步問題。

相較之下，本系統的 Ztm 雙資料庫設計雖然分離更乾淨，但代價也更高：

| 問題 | 說明 |
|------|------|
| 資料冗餘 | 同樣的資料存兩份（basedata + cachedata） |
| 同步機制 | 每次寫入要同步兩邊，寫入成本翻倍 |
| 一致性風險 | cachedata 寫入失敗就不一致，需要 `ztm:rebuild` 修復 |
| 維護負擔 | 兩個 DB connection、跨庫 JOIN、不能建 FK |
| 開發者心智負擔 | 新人要理解「為什麼有兩個資料庫」 |

即使真的需要 EAV，WordPress 證明了用單一資料庫 + 應用層快取就能應付絕大多數場景，不需要搞到雙資料庫。

---

## 結論：為什麼 EAV + Ztm 不值得做

### EAV 的查詢代價

| 操作 | 傳統扁平表 | EAV |
|------|-----------|-----|
| SELECT 一筆商品完整資料 | 1 條 query | N 條 JOIN 或 N+1 query |
| ORDER BY price | `ORDER BY price` | 子查詢 JOIN meta WHERE meta_key='price' |
| WHERE price > 100 AND stock > 0 | 兩個欄位條件 | 兩層 JOIN meta，各帶條件 |
| 加欄位 | ALTER TABLE / migration | INSERT 一筆 meta，不用改結構 |
| Debug 看一筆資料 | 看一筆 row 就懂 | 要拼湊十幾筆 meta 才知道完整資料 |

EAV 唯一的優勢是「加欄位不用 ALTER TABLE」，但在 Laravel migration 管理下這幾乎不算優勢 — `php artisan make:migration` 就搞定了。

### 不值得做的理由總結

1. **本系統欄位由開發者定義，可預期** — 不存在「終端使用者自訂欄位」的需求，Migration 完全足夠
2. **WooCommerce 的教訓** — EAV 模式最大規模的實踐者，花了近十年最終仍回歸傳統扁平表，證明 EAV 用於結構固定的資料是錯誤的方向
3. **Ztm 雙資料庫增加複雜度** — 為了解決 EAV 的效能問題而引入的 Ztm 快取表，帶來資料冗餘、同步機制、一致性風險等額外負擔
4. **即使需要 EAV，也不需要雙資料庫** — WordPress 用單一資料庫 + 應用層快取（Redis / Object Cache）就能應付，方案更簡單
5. **本專案靈感來自 OpenCart** — OpenCart 本身就是傳統扁平表設計，這條路從一開始就是對的，不需要繞去 EAV 再繞回來

**EAV 不是不合時宜，而是適用範圍極窄** — 僅適合 SaaS 多租戶、CMS Plugin 生態、表單生成器等「欄位由終端使用者定義、開發者事先無法預知」的場景。對於本系統，傳統的專屬表就是最好的方案。

---

*文件版本：v1.2*
*建立日期：2026-04-15*
*來源分支：branchEavZtm（技術研究用，未合併至 main）*
