# 宣告式 Schema 同步

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
├── schema/                    ← 業務表的宣告式結構定義
│   └── tables/
│       ├── cfg_terms.php
│       ├── cfg_taxonomies.php
│       ├── hrm_employees.php
│       ├── hrm_departments.php
│       ├── sal_orders.php
│       └── sal_order_items.php
│
└── transitions/               ← 資料轉換腳本（僅在需要時建立）
    └── 20260301_split_customer_name.php
```

### 分工原則

| 類別 | 管理方式 | 說明 |
|------|---------|------|
| Laravel 框架表 | `migrations/` | `sessions`、`cache`、`jobs` 等，照 Laravel 原生方式 |
| 業務表結構 | `schema/tables/` | 一張表一支檔案，宣告現在的結構 |
| 資料轉換 | `transitions/` | 欄位搬移、值轉換、重新計算等，偶爾才需要 |

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
// database/transitions/20260301_split_customer_name.php

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

系統比對 `transitions/` 目錄中的檔案與 `schema_transitions` 表，只執行尚未跑過的腳本，依 version 數字順序執行。

### 檔案生命週期

Transition 檔案遵循與 schema 相同的理念：**專案只保留當前狀態，歷史交給 git。**

```
transitions/
└── 20260301_split_customer_name.php   ← 目前待執行（或剛執行完）

schema_transitions 表：
version    | description                  | executed_at
20260201   | 搬移舊客戶編號               | 2026-02-01 10:00:00   ← 檔案已刪，記錄留存
20260301   | 拆分 full_name               | 2026-03-01 14:30:00   ← 剛執行完
```

**規則：**

1. **所有環境都執行完畢後，刪除 transition 檔案**（本地、開發區、正式區都跑過了）
2. `schema_transitions` 表的記錄**永久保留**，作為防重複執行的鎖與歷史日誌
3. 記錄對應的檔案不存在是正常的 — 系統只掃描 `transitions/` 目錄中實際存在的檔案
4. 如需查閱已刪除的 transition 內容，**從 git 歷史取得**

```
transitions/ 目錄     → 永遠只有「待執行」或「剛執行完尚未清理」的檔案
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

2. 建立 database/transitions/20260301_split_customer_name.php
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
| `db:transition` | `app/Console/Commands/DbTransitionCommand.php` | 掃描 `database/transitions/` 目錄，比對 `schema_transitions` 表，執行未跑過的資料轉換腳本。首次執行自動建立 `schema_transitions` 表 |

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
| `database/transitions/` | 資料轉換腳本目錄，僅在需要搬移資料時建立 |

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

**文件版本**: 2.1
**建立日期**: 2026-02-08
**更新日期**: 2026-02-08（新增 `primary` 修飾符、索引 Tab 說明）
