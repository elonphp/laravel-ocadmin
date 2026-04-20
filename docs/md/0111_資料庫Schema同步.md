# 資料庫結構管理（即時 UI 變更）

> **⚠ 使用對象：發案方資訊人員（super_admin）**
>
> 後台提供類似 Navicat / phpMyAdmin / Laravel Voyager 的 UI，讓略懂資料庫的資訊人員直接在瀏覽器上變更當前系統的資料表結構。按下儲存即執行 `ALTER TABLE`，資料表即時變動。
>
> 典型情境：某些模組設計成可自動化（例如薪資表新增扁平式薪資項目欄位），IT 人員在 UI 上加一個欄位後，不必改程式，其它關聯功能模組自動拾取新欄位。
>
> **左側選單不顯示此功能，僅 super_admin 可存取。**
>
> 開發人員於版本升級的統一部署流程，請使用 [0121_資料庫Transition變更機制](0121_資料庫Transition變更機制.md)。

---

## ⚠ 重要警告

**使用本功能後，本專案不得再執行 `php artisan migrate`。**

原因：本功能直接修改資料庫結構，migration 歷史與實際資料表已不對齊。繼續執行 migrate 會：

- 因為欄位已存在而報錯
- 或（更糟）被 Laravel 誤判為「新變更」而重複執行，造成資料異常

使用此功能即代表接受：**後續結構維護一律走本 UI 或 [0121 Transition](0121_資料庫Transition變更機制.md)**，migration 系統視為凍結狀態。

---

## 核心設計

### 定位

這是**後台 UI 版的 Navicat**，不是宣告式工具：

- **直接操作 DB** — 不經過 schema 檔、不產生 migration 檔，不比對差異
- **所見即所得** — UI 當下看到什麼，儲存後 DB 就是什麼
- **即時生效** — 按下儲存 → 執行 `ALTER TABLE` → 完成

### 改名如何正確識別

表單上**明示原名稱**欄位，讓系統明確知道使用者是從哪一列改過來的：

```
編輯：sal_orders
┌────────────┬────────────┬──────────┬──────┬───────┬──────────┐
│ 原名        │ 欄位名      │ 類型      │ 長度 │ NULL  │ 備註      │
├────────────┼────────────┼──────────┼──────┼───────┼──────────┤
│ id         │ id         │ bigint   │      │       │          │
│ order_no   │ order_no   │ varchar  │  30  │       │ 訂單編號  │
│ colabc     │ coldef     │ varchar  │  50  │   ✓   │ (改名了)  │  ← 系統偵測到改名
│ (新增)      │ new_field  │ text     │      │   ✓   │          │  ← 原名空＝新增
│ (已刪除)    │ obsolete   │          │      │       │          │  ← 表單移除＝刪除
└────────────┴────────────┴──────────┴──────┴───────┴──────────┘
```

「原名」欄位在 UI 上**直接顯示**（非 hidden），使用者編輯時一眼看到「我是從哪裡改過來的」，減少誤改。

### 儲存時的 SQL 產生邏輯

| 表單狀態 | 產生的 SQL |
|---|---|
| `原名=colabc, 欄位名=colabc`（皆未改） | 無 |
| `原名=colabc, 欄位名=coldef`（改名） | `ALTER TABLE ... RENAME COLUMN colabc TO coldef` |
| `原名=colabc, 欄位名=colabc, 類型/屬性改了` | `ALTER TABLE ... MODIFY COLUMN colabc ...` |
| `原名=colabc, 欄位名=coldef, 類型/屬性也改了` | `RENAME COLUMN` + `MODIFY COLUMN`（分兩條，或一條複合 ALTER） |
| `原名=空, 欄位名=newcol` | `ALTER TABLE ... ADD COLUMN newcol ...` |
| DB 有但表單中被刪掉 | `ALTER TABLE ... DROP COLUMN ...` |

---

## 架構

```
資料來源：      INFORMATION_SCHEMA（直接查 DB）
                     ↓
UI 編輯表單：   每列帶「原名」+「欄位名」+ 類型/屬性
                     ↓
儲存：          比對原名 vs 新名，產生對應 ALTER SQL
                     ↓
執行：          DB::transaction() 內執行所有 SQL
                     ↓
完成：          重新載入頁面，顯示最新 DB 狀態
```

---

## 使用流程

1. 以 `super_admin` 登入後台
2. 直接存取 `/system/schemas` 路由（左側選單不顯示）
3. 列表顯示當前 DB 的所有業務表（排除 Laravel 框架表）
4. 選擇要編輯的表 → 進入編輯頁
5. **編輯欄位**：
   - 修改既有列 → 改名 / 改類型 / 改屬性
   - 按「新增欄位」→ 新增一列（原名自動為空）
   - 按某列的刪除鈕 → 該列標記為移除
6. 按「預覽 SQL」→ 彈出 Modal 顯示即將執行的 ALTER 語句
7. 確認後按「儲存」→ 執行 → 頁面重新載入並顯示最新結構

---

## UI 規劃

### 列表頁

```
System / 資料表結構
┌──────────────────────────────────────────────────────┐
│  資料表列表                                           │
├──────────────┬───────────┬──────────────┬────────────┤
│ 表名          │ 欄位數    │ 備註          │ 操作       │
├──────────────┼───────────┼──────────────┼────────────┤
│ sal_orders    │ 8         │ 銷售訂單      │ [編輯]     │
│ hrm_employees │ 12        │ 員工          │ [編輯]     │
│ cfg_terms     │ 6         │ 詞彙          │ [編輯]     │
└──────────────┴───────────┴──────────────┴────────────┘
```

### 編輯頁

```
編輯：sal_orders
┌────────────┬────────────┬──────────┬──────┬──────┬──────┬─────────┬────────┐
│ 原名        │ 欄位名      │ 類型      │ 長度 │ NULL │ 預設 │ 備註     │  [刪]  │
├────────────┼────────────┼──────────┼──────┼──────┼──────┼─────────┼────────┤
│ id         │ id         │ bigint   │      │      │      │         │        │ ← PK/auto_increment 鎖定不可改
│ order_no   │ order_no   │ varchar  │  30  │      │      │ 訂單編號 │   ✕   │
│ total      │ total      │ decimal  │ 13,4 │      │ 0    │         │   ✕   │
│ colabc     │ coldef     │ varchar  │  50  │   ✓  │      │ 改名了  │   ✕   │
│ (新增)      │           │          │      │      │      │         │   ✕   │ ← 空白列讓使用者填新欄位
├────────────┴────────────┴──────────┴──────┴──────┴──────┴─────────┴────────┤
│  [新增欄位列] [預覽 SQL] [儲存]                                               │
└──────────────────────────────────────────────────────────────────────────────┘
```

### 儲存前的預覽 Modal

```
即將對 sal_orders 執行：

  ALTER TABLE `sal_orders`
    RENAME COLUMN `colabc` TO `coldef`,
    MODIFY COLUMN `coldef` VARCHAR(50) NULL COMMENT '改名了',
    ADD COLUMN `new_field` TEXT NULL;

    [取消]  [確認執行]
```

---

## 支援的欄位操作

| 操作 | 說明 |
|------|------|
| 新增欄位 | 原名留空，填新欄位名與類型 |
| 改名 | 修改「欄位名」格，原名保留 |
| 改類型/長度/NULL/預設/備註 | 修改對應格子 |
| 刪除欄位 | 按列末的刪除鈕 |
| 調整順序 | 拖曳列重排（產生 `MODIFY COLUMN ... AFTER ...`） |

### 支援的欄位類型

| 分類 | 類型 |
|------|------|
| 整數 | `tinyint` `smallint` `mediumint` `int` `bigint` |
| 浮點 | `decimal` `float` `double` |
| 字串 | `char` `varchar` `tinytext` `text` `mediumtext` `longtext` |
| 日期 | `date` `time` `datetime` `timestamp` `year` |
| 其它 | `json` `boolean` `enum` `binary` `blob` |

### 支援的屬性

- `unsigned`（整數專用）
- `nullable`
- `default:值`
- `comment:註解`
- 索引（單欄 index / unique）
- 外鍵（參考其它表欄位）

---

## 安全機制

| 機制 | 說明 |
|------|------|
| **預覽 SQL** | 儲存前一律顯示 Modal，確認再執行 |
| **Transaction** | 所有 ALTER 包在 `DB::transaction()`，任一失敗全部回滾 |
| **權限鎖** | 僅 `super_admin` 可存取，左側選單不顯示 |
| **PK/auto_increment 欄位保護** | 表單鎖定不可改名或改類型（避免破壞 FK） |
| **不處理 VIEW / SP / TRIGGER** | 功能範圍限於 `ALTER TABLE` |

---

## 實作規劃

### 需要的核心 Service

| Service | 職責 |
|---|---|
| `SchemaExportService`（保留原有） | 查 `INFORMATION_SCHEMA` 取得當前 DB 表結構，作為編輯頁的初始資料 |
| `SchemaAlterService`（**新建**） | 接收「原名 vs 新名」的欄位清單，逐欄產生 ALTER SQL 並執行 |

### 資料流

```
編輯頁載入
  ↓
Controller 呼叫 SchemaExportService::getTableStructure($table)
  ↓
把每欄塞進 form，原名 = 當前欄位名
  ↓
使用者編輯
  ↓
提交時：columns[] 陣列，每筆有 original_name + name + type + ...
  ↓
Controller 呼叫 SchemaAlterService::applyChanges($table, $columns)
  ↓
比對產生 SQL，DB::transaction 執行
  ↓
回傳 executed SQL 清單 + 重新載入頁面
```

### 要下架的舊內容

| 項目 | 處理 |
|---|---|
| `database/schema/tables/*.php` | 不再產生、不再讀取，可保留當作初始參考，或整個移除 |
| `SchemaParserService` | 如果只剩此模組用，可移除 |
| `SchemaDiffService` | 不再需要 diff 模式；若保留 `db:sync` CLI 則留著 |
| `db:sync` / `db:export-schema` CLI | 可下架或改由開發者自行查 DB |
| 現有 form.blade.php 的 Tab 切換（欄位 / 翻譯 / 索引） | 改成單一扁平表格 + 原名欄位 |
| 「差異比對」Modal | 改為「預覽 SQL」Modal |

> 具體程式碼重構留待實作階段確認。本文件只定義功能目標。

---

**文件版本**: 6.0
**建立日期**: 2026-02-08
**更新日期**: 2026-04-21（重新定位為「後台 UI 版 Navicat」：直接操作 DB，不經 schema 檔，UI 明示原名稱支援改名識別）
