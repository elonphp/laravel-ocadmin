# 資料庫 Schema 同步

> **⛔ 此計劃不適用** — 原始動機（供 OrmHelper 讀取欄位定義）已不成立，OrmHelper 已改為直接查詢 DB `INFORMATION_SCHEMA`，不依賴 schema 檔案。此外機制本身存在多項根本性缺陷，詳見文末「[問題反思](#問題反思)」。

## 設計動機

### 傳統 Migration 的痛點

Laravel Migration 是「**記錄歷史步驟**」的模式 — 每次欄位異動都產生一支新檔案：

```
database/migrations/
├── 2025_01_01_create_orders_table.php
├── 2025_03_15_add_note_to_orders.php
├── 2025_06_20_add_status_to_orders.php
├── 2025_09_01_change_total_precision_in_orders.php
├── 2025_11_10_add_shipping_fee_to_orders.php
└── ...永遠累積
```

| 痛點 | 說明 |
|------|------|
| 檔案持續累積 | 專案越來越多歷史步驟檔，但只有最新狀態有意義 |
| 多環境同步麻煩 | 本地、開發區、正式區各跑各的 migration，容易漏跑或順序錯誤 |
| 難以一眼看出現況 | 要讀完所有 migration 才知道表現在長什麼樣 |
| 歷史應該交給 git | 程式碼的修改只保留當前版本，歷史在 git 裡；資料表結構也應如此 |

### 本系統的做法：宣告目標狀態，自動 diff

```
不記錄「做過什麼」，而是宣告「現在應該長什麼樣」。
系統自動比對現有資料庫，產生並執行 ALTER 語句。
```

---

## 架構總覽

```
database/
├── migrations/                ← 僅保留 Laravel 框架自己的表
│   ├── create_sessions_table.php
│   ├── create_cache_table.php
│   └── create_jobs_table.php
│
└── schema/                    ← 業務表的宣告式結構定義
    ├── tables/
    │   ├── cfg_terms.php
    │   ├── cfg_taxonomies.php
    │   ├── hrm_employees.php
    │   ├── hrm_departments.php
    │   ├── sal_orders.php
    │   └── sal_order_items.php
    │
    └── transitions/           ← 資料轉換腳本（僅在需要時建立）
        └── 20260301_split_customer_name.php
```

### 分工原則

| 類別 | 管理方式 | 說明 |
|------|---------|------|
| Laravel 框架表 | `migrations/` | `sessions`、`cache`、`jobs` 等，照 Laravel 原生方式 |
| 業務表結構 | `schema/tables/` | 一張表一支檔案，宣告現在的結構 |
| 資料轉換 | `schema/transitions/` | 欄位搬移、值轉換、重新計算等，偶爾才需要 |

---

## Schema 定義格式

### 基本範例

```php
// database/schema/tables/sal_orders.php

return [
    'comment' => '銷售訂單',

    'columns' => [
        'id'          => 'bigint|unsigned|auto_increment|primary',
        'order_no'    => 'varchar:30|index|comment:訂單編號',
        'customer_id' => 'bigint|unsigned|foreign:sal_customers.id',
        'total'       => 'decimal:13,4|default:0',
        'note'        => 'text|nullable',
        'status'      => 'tinyint|unsigned|default:0|comment:0=草稿 1=已確認 2=已完成',
        'ordered_at'  => 'datetime|nullable',
        'created_at'  => 'timestamp|nullable',
        'updated_at'  => 'timestamp|nullable',
    ],

    'indexes' => [
        'idx_status_ordered' => ['status', 'ordered_at'],
    ],

    'unique' => [
        'uq_order_no' => ['order_no'],
    ],
];
```

### 欄位定義語法

用 `|` 分隔屬性，`:` 帶參數：

```
類型:參數|修飾符|修飾符:參數

範例：
  varchar:100              → VARCHAR(100)
  decimal:13,4             → DECIMAL(13,4)
  bigint|unsigned          → BIGINT UNSIGNED
  varchar:50|nullable      → VARCHAR(50) NULL
  tinyint|default:0        → TINYINT DEFAULT 0
  bigint|unsigned|foreign:customers.id  → BIGINT UNSIGNED + 外鍵
  varchar:30|index         → VARCHAR(30) + 單欄索引
  text|nullable|comment:備註 → TEXT NULL COMMENT '備註'
```

### 支援的欄位類型

| 分類 | 類型 |
|------|------|
| 整數 | `tinyint` `smallint` `mediumint` `int` `bigint` |
| 浮點 | `decimal` `float` `double` |
| 字串 | `char` `varchar` `tinytext` `text` `mediumtext` `longtext` |
| 日期 | `date` `time` `datetime` `timestamp` `year` |
| 其它 | `json` `boolean` `enum` `binary` `blob` |

### 支援的修飾符

| 修飾符 | 說明 | 範例 |
|--------|------|------|
| `unsigned` | 無符號 | `int\|unsigned` |
| `nullable` | 允許 NULL | `text\|nullable` |
| `default:值` | 預設值 | `tinyint\|default:0` |
| `index` | 單欄索引 | `varchar:30\|index` |
| `unique` | 單欄唯一 | `varchar:50\|unique` |
| `foreign:表.欄` | 外鍵 | `bigint\|unsigned\|foreign:customers.id` |
| `comment:說明` | 欄位註解 | `status\|comment:狀態` |
| `auto_increment` | 自動遞增 | `bigint\|unsigned\|auto_increment` |
| `primary` | 主鍵 | `bigint\|unsigned\|auto_increment\|primary` |
| `after:欄位` | 指定位置（ALTER 時） | `varchar:50\|after:name` |

### 含多語翻譯的表

翻譯表跟主表定義在同一支檔案：

```php
// database/schema/tables/cfg_terms.php

return [
    'comment' => '詞彙',

    'columns' => [
        'id'          => 'bigint|unsigned|auto_increment|primary',
        'taxonomy_id' => 'bigint|unsigned|foreign:cfg_taxonomies.id',
        'parent_id'   => 'bigint|unsigned|nullable|default:0',
        'code'        => 'varchar:50|index',
        'sort_order'  => 'smallint|unsigned|default:0',
        'is_enabled'  => 'boolean|default:1',
        'created_at'  => 'timestamp|nullable',
        'updated_at'  => 'timestamp|nullable',
    ],

    // 翻譯欄位 → 自動產生 cfg_term_translations 表
    'translations' => [
        'name'        => 'varchar:200',
        'description' => 'text|nullable',
    ],
];
```

系統會自動產生對應的翻譯表：

```sql
CREATE TABLE cfg_term_translations (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    term_id     BIGINT UNSIGNED NOT NULL,
    locale      VARCHAR(10) NOT NULL,
    name        VARCHAR(200) NOT NULL,
    description TEXT NULL,
    UNIQUE KEY uq_term_locale (term_id, locale),
    FOREIGN KEY (term_id) REFERENCES cfg_terms(id) ON DELETE CASCADE
);
```

---

## 同步指令

### `php artisan db:sync`

比對 `database/schema/tables/` 與實際資料庫結構，產生並執行 ALTER 語句。

```bash
# 預覽差異（不執行）
php artisan db:sync --dry-run

# 執行同步
php artisan db:sync

# 只同步指定表
php artisan db:sync --table=sal_orders

# 顯示詳細 SQL
php artisan db:sync --verbose
```

### 預覽輸出範例

```
$ php artisan db:sync --dry-run

Comparing schema definitions with database...

[sal_orders]
  + ADD COLUMN note TEXT NULL AFTER total
  + ADD COLUMN status TINYINT UNSIGNED DEFAULT 0 AFTER note
  ~ MODIFY COLUMN total DECIMAL(13,4) → DECIMAL(15,4)

[hrm_employees]
  + ADD COLUMN emergency_contact VARCHAR(100) NULL AFTER phone
  - DROP COLUMN fax

[cfg_terms]
  (no changes)

Summary: 2 tables need changes, 1 table up to date.
Run without --dry-run to apply.
```

### 同步邏輯

```
讀取 schema/tables/*.php
       │
       ▼
查詢 INFORMATION_SCHEMA（目前資料庫結構）
       │
       ▼
逐表比對
  ├─ 表不存在 → CREATE TABLE
  ├─ 欄位不存在 → ALTER TABLE ADD COLUMN
  ├─ 欄位類型/屬性不同 → ALTER TABLE MODIFY COLUMN
  ├─ 索引/外鍵不同 → ADD/DROP INDEX
  └─ 多餘欄位 → 提示警告（不自動刪除，需加 --drop-columns 明確指定）
       │
       ▼
翻譯表（translations 區塊）
  ├─ 翻譯表不存在 → 自動建立 xxx_translations
  ├─ 翻譯欄位不同 → ALTER 翻譯表
  └─ 外鍵、唯一索引自動維護
```

### 安全機制

| 機制 | 說明 |
|------|------|
| 不自動刪欄位 | 多餘欄位只警告，需加 `--drop-columns` 才會刪除 |
| 不自動刪表 | 資料庫有但 schema 沒定義的表，完全不處理 |
| dry-run 優先 | 建議永遠先 `--dry-run` 確認再執行 |

---

## 資料轉換腳本（Transitions）

### 何時需要

大多數情況下，新增/修改欄位不需要 transition — `db:sync` 自動處理結構。

只有需要**搬移資料**的時候才寫 transition：

| 情境 | 需要 Transition |
|------|:-:|
| 新增一個空欄位 | 否 |
| 修改欄位長度 | 否 |
| 加索引 | 否 |
| 舊欄位的值要搬到新欄位 | **是** |
| 一個欄位拆成兩個 | **是** |
| 根據 A 表重新計算 B 表的值 | **是** |

### 格式

```php
// database/schema/transitions/20260301_split_customer_name.php

return [
    'version'     => 20260301,
    'description' => '將 full_name 拆分為 first_name 和 last_name',

    'up' => function () {
        DB::table('sal_customers')
            ->whereNull('first_name')
            ->chunkById(500, function ($rows) {
                foreach ($rows as $row) {
                    $parts = explode(' ', $row->full_name, 2);
                    DB::table('sal_customers')
                        ->where('id', $row->id)
                        ->update([
                            'first_name' => $parts[0],
                            'last_name'  => $parts[1] ?? '',
                        ]);
                }
            });
    },
];
```

### 執行方式

```bash
# 預覽待執行的 transitions
php artisan db:transition --dry-run

# 執行
php artisan db:transition
```

### 版本追蹤

```sql
CREATE TABLE schema_transitions (
    version      INT UNSIGNED PRIMARY KEY,    -- 20260301
    description  VARCHAR(200),
    executed_at  DATETIME NOT NULL
);
```

系統比對 `schema/transitions/` 目錄中的檔案與 `schema_transitions` 表，只執行尚未跑過的腳本，依 version 數字順序執行。

### 檔案生命週期

Transition 檔案遵循與 schema 相同的理念：**專案只保留當前狀態，歷史交給 git。**

```
schema/transitions/
└── 20260301_split_customer_name.php   ← 目前待執行（或剛執行完）

schema_transitions 表：
version    | description                  | executed_at
20260201   | 搬移舊客戶編號               | 2026-02-01 10:00:00   ← 檔案已刪，記錄留存
20260301   | 拆分 full_name               | 2026-03-01 14:30:00   ← 剛執行完
```

**規則：**

1. **所有環境都執行完畢後，刪除 transition 檔案**（本地、開發區、正式區都跑過了）
2. `schema_transitions` 表的記錄**永久保留**，作為防重複執行的鎖與歷史日誌
3. 記錄對應的檔案不存在是正常的 — 系統只掃描 `schema/transitions/` 目錄中實際存在的檔案
4. 如需查閱已刪除的 transition 內容，**從 git 歷史取得**

```
schema/transitions/ 目錄 → 永遠只有「待執行」或「剛執行完尚未清理」的檔案
schema_transitions 表 → 記錄所有已執行過的版本，防止重複執行
git log               → 保存所有歷次 transition 的完整內容
```

---

## 開發工作流

### 日常開發：新增欄位

```
1. 編輯 database/schema/tables/sal_orders.php
   加一行：'note' => 'text|nullable',

2. 執行 php artisan db:sync
   → 自動 ALTER TABLE sal_orders ADD COLUMN note TEXT NULL

3. git commit
   → schema 檔只有一行差異，乾淨明確

完成。不需要建立 migration 檔案。
```

### 需要搬資料的情境

```
1. 編輯 schema，加新欄位、移除舊欄位定義

2. 建立 database/schema/transitions/20260301_split_customer_name.php
   撰寫資料搬移邏輯

3. 執行 php artisan db:sync        → 結構變更
   執行 php artisan db:transition  → 資料搬移

4. 確認沒問題後，舊欄位從 schema 移除
   執行 php artisan db:sync --drop-columns  → 清除舊欄位

5. git commit
```

### 部署流程

```bash
# 正式環境部署腳本
php artisan db:sync --dry-run        # 先預覽
php artisan db:sync                  # 執行結構同步
php artisan db:transition            # 執行資料轉換（有的話）
```

不論中間跳了幾個版本，`db:sync` 永遠比對的是「現在的資料庫」與「現在的 schema 定義」，不會有漏跑的問題。

---

## 與 Migration 的對比

| | Laravel Migration | 宣告式 Schema 同步 |
|--|------------------|-------------------|
| 理念 | 記錄每一步歷史 | 宣告目標狀態 |
| 檔案數量 | 隨時間累積 | 一張表一支，不累積 |
| 看現況 | 需讀完所有 migration | 直接看 schema 檔 |
| 跳版部署 | 需依序補跑所有中間版本 | 直接 diff 到目標狀態 |
| 資料轉換 | 寫在 migration 的 `up()` 裡 | 獨立的 transition 檔案 |
| 回滾 | `migrate:rollback`（有 `down()`） | 修改 schema 再 `db:sync`（正向操作） |
| 歷史記錄 | migration 檔案本身 | git diff |
| 團隊協作 | migration 檔名易衝突 | 每表一檔，衝突機率低 |
| 適用範圍 | 所有 Laravel 專案的慣例 | 本專案自訂機制 |

### 回滾策略

本機制不提供傳統的 `rollback` — 如果要撤銷變更：

1. `git revert` 或 `git checkout` 恢復 schema 檔
2. 再跑一次 `db:sync`
3. 系統自動產生反向 ALTER

歷史追蹤完全交給 git，不在資料庫層面維護 migration 歷史。

---

## Schema 檔案的產生與維護

### 雙向同步

Schema 檔案可以從兩個方向產生和更新：

```
                    ┌─────────────────┐
                    │  schema/tables/ │
                    │   （PHP 檔案）   │
                    └───┬─────────┬───┘
            db:sync │         ▲ db:export-schema
          （檔→庫） ▼         │ （庫→檔）
                    ┌─────────┴───┐
                    │   資料庫     │
                    └──┬────────┬──┘
                       │        │
              Ocadmin UI      開發者手動
              修改結構         用資料庫工具改
```

不管從哪邊修改，都能同步到另一邊。

### 方式一：`db:export-schema`（資料庫 → Schema 檔）

從現有資料庫反向匯出 schema 檔。適用於：

- **首次建立**：專案已有資料庫，一次性匯出所有表的 schema 定義
- **開發者手動改了 DB**：用資料庫工具加了欄位後，同步回 schema 檔

```bash
# 匯出所有業務表（排除 Laravel 框架表）
php artisan db:export-schema

# 匯出指定表
php artisan db:export-schema --table=sal_orders

# 預覽（不寫入檔案）
php artisan db:export-schema --dry-run
```

匯出範例：

```
$ php artisan db:export-schema --table=sal_orders

Exporting schema from database...

[sal_orders] → database/schema/tables/sal_orders.php
  columns: 8 (id, order_no, customer_id, total, note, status, created_at, updated_at)
  indexes: 1 (idx_status_ordered)
  foreign keys: 1 (customer_id → sal_customers.id)
  Written.

Done. 1 table exported.
```

### 方式二：Ocadmin 後台作業（UI → Schema 檔 → 資料庫）

在 Ocadmin 後台建立「資料表結構管理」作業，提供視覺化介面：

**功能規劃：**

```
System / 資料表結構管理
┌──────────────────────────────────────────────────────┐
│  資料表列表                                     [同步] │
├──────────────┬───────────┬───────────┬───────────────┤
│ 表名          │ 欄位數    │ 狀態      │ 操作          │
├──────────────┼───────────┼───────────┼───────────────┤
│ cfg_terms     │ 8         │ ✓ 同步    │ [編輯] [比對] │
│ sal_orders    │ 8         │ ⚠ 有差異  │ [編輯] [比對] │
│ hrm_employees │ 12        │ ✓ 同步    │ [編輯] [比對] │
└──────────────┴───────────┴───────────┴───────────────┘
```

**表結構編輯頁：**

```
編輯：sal_orders
┌─────────────┬──────────┬──────┬────────┬─────────────┬──────────┐
│ 欄位名       │ 類型      │ 長度 │ 允許NULL │ 預設值       │ 備註     │
├─────────────┼──────────┼──────┼────────┼─────────────┼──────────┤
│ id          │ bigint   │      │        │ auto_incr.  │          │
│ order_no    │ varchar  │ 30   │        │             │ 訂單編號  │
│ customer_id │ bigint   │      │        │             │ FK:客戶   │
│ total       │ decimal  │ 13,4 │        │ 0           │          │
│ note        │ text     │      │ ✓      │             │          │
│ status      │ tinyint  │      │        │ 0           │ 狀態     │
│ + 新增欄位                                                       │
├─────────────┴──────────┴──────┴────────┴─────────────┴──────────┤
│ ☐ 翻譯欄位                                                      │
│   name        │ varchar │ 200 │        │                         │
│   description │ text    │     │ ✓      │                         │
│   + 新增翻譯欄位                                                  │
├──────────────────────────────────────────────────────────────────┤
│                              [儲存] [比對預覽] [取消]              │
└──────────────────────────────────────────────────────────────────┘
```

**儲存流程：**

```
UI 編輯表結構
    │
    ▼
儲存 → 更新 database/schema/tables/sal_orders.php
    │
    ▼
自動執行 db:sync --dry-run 預覽差異
    │
    ▼
使用者確認 → 執行 db:sync 套用到資料庫
```

**比對預覽功能：**

點擊「比對」可以看到 schema 檔與實際資料庫的差異，等同 `db:sync --dry-run` 的視覺化版本。

### 方式三：開發者直接改檔案

開發者也可以直接編輯 `database/schema/tables/*.php`，再跑 `db:sync`。三種方式互不衝突，最終都以 schema 檔為中介：

| 操作方式 | 流程 |
|---------|------|
| Ocadmin UI | UI 編輯 → 寫入 schema 檔 → `db:sync` 套用到 DB |
| 改 schema 檔 | 編輯檔案 → `db:sync` 套用到 DB |
| 改資料庫 | 資料庫工具改 DB → `db:export-schema` 更新 schema 檔 |

---

## 實作檔案清單

### 核心 Service

三支 Service 由 Ocadmin UI 和 Artisan 指令共同使用，不直接依賴 Ocadmin Portal。

| 檔案 | 說明 |
|------|------|
| `app/Services/System/Database/SchemaParserService.php` | **Schema 語法解析器** — 解析 `'varchar:100\|nullable\|index'` 為結構化陣列，也可反向建構定義字串。負責讀寫 `database/schema/tables/*.php` |
| `app/Services/System/Database/SchemaExportService.php` | **DB→Schema 匯出服務** — 查詢 `INFORMATION_SCHEMA` 取得現有資料庫結構（欄位、索引、外鍵），轉換為 schema 定義格式並匯出檔案。自動偵測翻譯表（`xxx_translations`） |
| `app/Services/System/Database/SchemaDiffService.php` | **差異比對服務** — 比對 schema 定義檔與實際資料庫的差異，產生 ALTER/CREATE SQL 語句並可執行。提供 `getStatusOverview()` 供列表頁使用 |

### Artisan 指令

| 指令 | 檔案 | 說明 |
|------|------|------|
| `db:sync` | `app/Console/Commands/DbSyncCommand.php` | 比對 `schema/tables/` 與 DB，產生並執行 ALTER。支援 `--table`、`--dry-run`、`--drop-columns`、`--connection` |
| `db:export-schema` | `app/Console/Commands/DbExportSchemaCommand.php` | 從 DB 反向匯出 schema 檔到 `database/schema/tables/`。支援 `--table`、`--dry-run`、`--connection` |
| `db:transition` | `app/Console/Commands/DbTransitionCommand.php` | 掃描 `database/schema/transitions/` 目錄，比對 `schema_transitions` 表，執行未跑過的資料轉換腳本。首次執行自動建立 `schema_transitions` 表 |

### Ocadmin 後台作業

位於 Ocadmin Portal 的「系統管理」分類下，提供視覺化的資料表結構管理介面。

**Controller:**

| 檔案 | 說明 |
|------|------|
| `app/Portals/Ocadmin/Core/Controllers/System/SchemaController.php` | 資料表結構管理 Controller，注入三支 Service。包含 index/list/create/store/edit/update/diff/sync/export/exportAll 共 10 個方法 |

**Views（`app/Portals/Ocadmin/Core/Views/system/schema/`）：**

| 檔案 | 說明 |
|------|------|
| `index.blade.php` | 列表頁 — 篩選面板（表名、狀態）＋資料表列表＋差異比對 Modal＋匯出全部按鈕 |
| `list.blade.php` | 列表 partial — 顯示表名、備註、欄位數、翻譯欄位數、同步狀態（✓同步 / ⚠有差異 / 僅DB / 僅Schema）、操作按鈕（編輯、比對、匯出） |
| `form.blade.php` | 編輯頁 — Tab 切換（欄位定義 / 翻譯欄位 / 索引），每列可設定類型、長度、Unsigned、Nullable、預設值、PK、Auto Increment、索引、唯一、外鍵、備註。索引 Tab 管理複合 INDEX 和 UNIQUE。JavaScript 動態新增/刪除行 |

**路由（`app/Portals/Ocadmin/routes/ocadmin.php`，`system.schema.*`）：**

| 路由 | 方法 | 說明 |
|------|------|------|
| `GET  /system/schema` | `index` | 列表頁 |
| `GET  /system/schema/list` | `list` | AJAX 列表刷新 |
| `GET  /system/schema/create` | `create` | 新增頁面 |
| `POST /system/schema` | `store` | 儲存新 schema 檔 |
| `GET  /system/schema/{table}/edit` | `edit` | 編輯頁面 |
| `PUT  /system/schema/{table}` | `update` | 更新 schema 檔 |
| `GET  /system/schema/{table}/diff` | `diff` | AJAX 差異比對（JSON） |
| `POST /system/schema/{table}/sync` | `sync` | 執行同步到 DB |
| `POST /system/schema/{table}/export` | `export` | 從 DB 匯出單表 schema |
| `POST /system/schema/export-all` | `exportAll` | 匯出全部 |

**其他修改：**

| 檔案 | 修改 |
|------|------|
| `lang/zh_Hant/system/schema.php` | 語系檔（標題、狀態文字、欄位標籤、Tab、按鈕、提示訊息） |
| `app/Portals/Ocadmin/Core/ViewComposers/MenuComposer.php` | 系統管理選單新增「資料表結構」項目 |

### Schema 定義目錄

| 路徑 | 說明 |
|------|------|
| `database/schema/tables/` | Schema 定義檔目錄，一張表一支 `.php` 檔。初始為空，透過 `db:export-schema` 或 Ocadmin UI 產生 |
| `database/schema/transitions/` | 資料轉換腳本目錄，僅在需要搬移資料時建立 |

### Service 職責與 API

```php
// SchemaParserService — 解析 schema 定義檔語法
parseColumnDefinition('varchar:100|nullable')  → ['type'=>'varchar', 'length'=>'100', 'nullable'=>true, ...]
buildColumnDefinition(['type'=>'varchar', ...]) → 'varchar:100|nullable'
loadSchemaFile('sal_orders')                    → array|null
saveSchemaFile('sal_orders', $schema)           → void
getSchemaTableNames()                           → ['cfg_terms', 'sal_orders', ...]

// SchemaExportService — 從資料庫反向產生 schema 定義
getTableList()                     → [['name'=>'users', 'comment'=>'...'], ...]
getTableStructure('users')         → ['columns'=>[...], 'indexes'=>[...], 'foreign_keys'=>[...]]
exportToSchemaArray('users')       → ['columns'=>['id'=>'bigint|unsigned|auto_increment|primary', ...]]
exportToSchemaFile('users')        → void（寫入 database/schema/tables/users.php）
exportAll()                        → ['users', 'orders', ...]（已匯出表名）

// SchemaDiffService — 比對差異並產生 SQL
diff('sal_orders')          → ['status'=>'diff', 'changes'=>[...]]
generateSql('sal_orders')   → ['ALTER TABLE `sal_orders` ADD COLUMN ...', ...]
apply('sal_orders')         → ['executed'=>[SQL...], 'changes'=>[...]]
getStatusOverview()         → [['name'=>..., 'status'=>..., 'column_count'=>..., ...], ...]
```

---

---

## 問題反思

### 一、原始動機已消失

Schema 檔案最初的用途之一是供 `OrmHelper` 讀取欄位定義，避免每次查詢 DB。但歷經演進：

```
cache() 快取欄位資訊 → database/schema/tables/*.php 宣告式定義 → OrmHelper 改為直接查 DB INFORMATION_SCHEMA
```

現況：
- **OrmHelper** 使用 `Schema::getColumnListing()` 和 `INFORMATION_SCHEMA` 直接查 DB，**不讀 schema 檔**
- **SchemaParserService** 的消費者**只有 Schema 管理功能自己**（SchemaController、SchemaDiffService、3 支 artisan 指令），應用程式其他部分完全不碰它
- 讀取 `INFORMATION_SCHEMA` 是 metadata 查詢，不掃描資料列，不論表有 10 筆還是 1000 萬筆都 < 1ms

Schema 檔案成為一座**自給自足的孤島** — 自己寫、自己讀、自己 diff、自己 sync，但沒有其他模組依賴它。

### 二、改名偵測的先天限制

宣告式 diff 只看「結果」不看「過程」。當使用者把 `old_col` 改為 `new_col` 時，系統看到的是：

```
DB 有 old_col，Schema 沒有 → 多餘欄位
Schema 有 new_col，DB 沒有 → 新增欄位
```

即使加入「操作下拉選單」讓使用者標記改名意圖，仍有風險：

| 風險 | 說明 |
|------|------|
| 使用者忘記選「改名」 | 預設是「不變」，改了名稱卻沒切換 → 仍然 DROP + ADD，資料遺失 |
| CLI 指令無 UI | `php artisan db:sync` 走 schema 檔，但 `renames` 只在透過 UI 儲存時寫入 |
| renames 是暫態資訊 | 同步後即清除，若同步失敗再重試，renames 已不在 → 退化為 DROP + ADD |
| 多人協作衝突 | A 改名並儲存，B 同時編輯同一表但未重新載入，B 儲存時覆蓋 A 的 renames |

**根本原因**：改名是「歷史動作」，宣告式系統天生不記錄歷史。Migration 的 `$table->renameColumn('old', 'new')` 反而是明確指令。

### 三、無法安全回滾

系統不提供 `down()` 方法，回滾策略是 `git revert schema 檔 → 再跑 db:sync`，但以下情境無法回滾：

| 情境 | 問題 |
|------|------|
| 欄位被刪除（`--drop-columns`） | 資料已消失，git revert 只能再加回空欄位 |
| 類型縮減（`VARCHAR(200)` → `VARCHAR(50)`） | 超出長度的資料被截斷，還原類型也救不回內容 |
| Transition 資料轉換 | 沒有 `down()`，單向操作無法逆轉 |
| `INT` → `TINYINT` | 超出範圍的值被靜默截斷 |

實務上需依賴 DB 備份（mysqldump / snapshot）作為最後防線。

### 四、多環境同步的時序問題

宣告式的優點是「不管跳幾版都能 diff 到目標」，但搭配 transition 時會出問題：

```
v1: schema 有 full_name 欄位
v2: schema 拆成 first_name + last_name，transition 搬資料
v3: schema 移除 full_name
```

若某環境直接從 v1 跳到 v3：
- `db:sync` 看到 schema 沒有 `full_name` → 標記為多餘欄位
- `db:sync` 看到 schema 有 `first_name`、`last_name` → ADD COLUMN（空的）
- 若先跑了 `--drop-columns` 再跑 transition → `full_name` 已不在，transition 失敗

Migration 不存在此問題 — 每一步都是明確的時間序列，不會跳步。

### 五、外鍵與索引的 diff 不完整

| 缺口 | 說明 |
|------|------|
| 外鍵新增/移除 | 既有欄位加上或移除 `foreign:xxx.id`，不會產生 ADD/DROP FOREIGN KEY SQL |
| 索引改名 | 索引名從 `idx_old` 改為 `idx_new`，被視為「刪舊 + 加新」，但目前不刪索引 |
| 複合索引欄位順序 | `['a', 'b']` 改為 `['b', 'a']`，效能意義不同但 diff 可能不偵測 |
| 外鍵的 ON DELETE/ON UPDATE 策略 | 目前寫死 `ON DELETE CASCADE`，無法自訂也不比對 |

### 六、破壞性 MODIFY 無警告

`db:sync` 會直接執行 MODIFY COLUMN，但某些變更會造成資料損失：

```sql
-- 以下都會靜默執行，不會警告
ALTER TABLE orders MODIFY COLUMN note VARCHAR(50)      -- 原本是 TEXT，長內容被截斷
ALTER TABLE orders MODIFY COLUMN status TINYINT        -- 原本是 INT，超出 127 的值被截斷
ALTER TABLE orders MODIFY COLUMN price INT             -- 原本是 DECIMAL，小數部分遺失
```

`--dry-run` 只顯示 SQL，不分析資料影響。使用者需自行判斷哪些 MODIFY 有風險。

### 七、欄位順序 diff 的副作用

- 不同環境因建表/加欄歷史不同，導致順序不一致
- 每次 sync 都產生大量「純順序調整」的 ALTER，實際上不影響功能
- MySQL 的 `MODIFY COLUMN` 會導致表重建（大表耗時且鎖表）

### 八、翻譯表命名規則硬編碼

翻譯表名稱使用 `Str::singular($table) . '_translations'`，某些表名會出錯：

| 表名 | 預期 | 實際 |
|------|------|------|
| `news` | `news_translations` + `news_id` | `new_translations` + `new_id`（Str::singular 誤判）✗ |
| `sys_data_entries` | `sys_data_entry_translations` + `entry_id` | `entry_id` — 語意不夠明確 |

無法透過 schema 定義自訂翻譯表名稱或外鍵名稱。

### 九、Schema 檔與 DB 的權威性不明確

系統支援雙向同步（`db:sync` 從檔到庫、`db:export-schema` 從庫到檔），但「誰是 source of truth」沒有強制規範。Migration 模式下，檔案是唯一 source of truth，方向明確。

### 十、不支援的 DDL 範圍

| 不支援項目 | 說明 |
|-----------|------|
| VIEW | 無法定義或同步檢視表 |
| STORED PROCEDURE / FUNCTION | 無法管理預存程序 |
| TRIGGER | 無法管理觸發器 |
| PARTITION | 無法定義分區策略 |
| 表的 ENGINE / CHARSET 變更 | 建表時寫死 InnoDB + utf8mb4，既有表不比對 |
| GENERATED COLUMN | 無法定義計算欄位（`AS (price * qty)`） |

### 十一、與「UI 自訂欄位」需求的比較

類似宏力人事管理系統的「自訂欄位」功能（人事附加資料、考勤計算項目、職級薪資項目），真正需要的是**應用層的動態欄位管理**（EAV 模式或 JSON 欄位），而非 DDL 層級的 ALTER TABLE。branchAdminlte 的薪資模組已在 UI 動態增減薪資項目，走的正是應用層方案，不需要本機制。

### 結論

| 面向 | Migration 較優 | 宣告式 Schema 較優 |
|------|---------------|-------------------|
| 改名欄位 | `renameColumn()` 明確 | 需額外 UI 標記，仍有遺漏風險 |
| 回滾 | 有 `down()` 方法 | 依賴 git + DB 備份 |
| 跳版部署 | 需逐步補跑 | 直接 diff 到目標 |
| 文件爆炸 | 會累積 | 一表一檔 |
| 看現況 | 需讀多支檔案 | 直接看 schema 檔 |
| 資料安全 | 每步可控 | MODIFY 可能靜默截斷資料 |
| 協作衝突 | 檔名易衝突 | 每表一檔，衝突少但方向不明確 |

**此機制原始動機已消失、缺陷多於優勢，不建議繼續投入開發。**

---

**文件版本**: 3.0
**建立日期**: 2026-02-08
**更新日期**: 2026-03-22（合併問題反思、標記計劃不適用）
