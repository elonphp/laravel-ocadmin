# 資料庫 Transition 變更機制

> **使用對象：開發人員** — 用於開發階段的結構變更，或交案後的客製調整。
>
> 發案方資訊人員透過後台 UI 操作結構變更，請見 [00005_資料庫Schema同步](00005_資料庫Schema同步.md)。

## 設計理念

### Migration 的痛點

| 痛點 | 說明 |
|------|------|
| 檔案持續累積 | 專案越久歷史步驟檔越多，但只有最新狀態有意義 |
| 順序相依 | 一旦有人手動改了 DB，後續 migration 可能失敗 |
| 難以一眼看出現況 | 要讀完所有 migration 才知道表結構 |

### 本系統的做法

**一個變更一個檔案放在 `database/transitions/`，每筆帶 `version`；執行過的 version 記錄在 `schema_transitions` 表，跑 `db:transition` 時跳過已執行的，達到重跑安全與多人協作友善。**

- **重跑安全**：以 `version` 為唯一識別，DB 表的 `status='success'` 才視為完成，重跑只執行新檔
- **失敗可重試**：`status='failed'` 的版本，下次跑會自動重試（成功後覆寫該行）
- **多人不衝突**：每筆獨立檔案，git merge 幾乎不會撞
- **隨時可刪**：已執行檔案不必立刻刪；下次提交、下下次提交、整理時再刪都無妨（重要的是 `schema_transitions` 表已記錄了 version）
- **歷史追蹤**：DB 表記錄誰在何時跑過哪版、結果如何；檔案歷史與動機交給 git

### 與 Laravel migrate 的差異

`schema_transitions` 跟 Laravel 的 `migrations` 表雖然都在 DB 記錄已執行檔案，但檢查邏輯相反：

- **Laravel migrate**：以 DB 紀錄為主、檔案為輔——所有歷史 migration 檔都必須存在，否則 `migrate:status` 對不上、rollback 也要依賴檔案還在。專案越久檔案越多，且不能刪。
- **db:transition**：以「**檔案還在 + DB 沒有 `success` 紀錄**」這個交集為主——掃資料夾現存的檔，跳過 `status='success'` 的 `version`，剩下的（含 `failed` 與從未跑過的）才跑。
  - 不必從遠古逐一比對：不存在的檔案不執行，歷史變更不重要，只執行當前需要變更的檔案。
  - 已成功的檔案隨時可刪——「成功執行過」這個事實留在 DB 表裡就夠了，舊檔對日後執行沒有任何約束力。

---

## 架構

```
database/
├── migrations/                    ← Laravel 框架表 + 業務表初始建表
│
├── transitions/                   ← 後續結構與資料變更（每變更一檔）
│   ├── example.php               ← 範本檔，DbTransitionCommand 會略過
│   ├── 20260509_001_xxx.php
│   ├── 20260510_001_xxx.php
│   └── 20260510_002_xxx.php
│
└── schema/
    └── tables/                    ← 表結構定義檔（參考用，非必要）
```

`schema_transitions` 表由 `DbTransitionCommand` 在首次執行時**自動建立**，不需手動建：

```sql
CREATE TABLE `schema_transitions` (
    `version`       VARCHAR(100) NOT NULL PRIMARY KEY,
    `description`   VARCHAR(200) NULL,
    `status`        VARCHAR(20)  NOT NULL DEFAULT 'success',   -- 'success' | 'failed'
    `error_message` TEXT         NULL,                          -- 失敗時的例外訊息
    `executed_at`   DATETIME     NOT NULL                       -- 最後一次嘗試的時間
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
```

**一行對應一個 version**（PK），最新嘗試的結果會覆寫前一筆。`status` 是「跳過 / 重試」判斷的依據：

- `success` → **永不重跑**
- `failed` → 下次跑 `db:transition` 會**再次嘗試**，成功後 row 會被覆寫為 `success`

舊版本（無 `status` / `error_message` 欄）的表，`DbTransitionCommand` 首次跑時會自動 `ALTER ADD COLUMN`，既有行 default `success`。

| 類別 | 管理方式 |
|------|---------|
| Laravel 框架表 | `migrations/` |
| 業務表初始結構 | `migrations/` |
| 後續結構與資料變更 | `transitions/`（一變更一檔） |
| 已執行版本追蹤 | `schema_transitions` 表（DB） |

---

## 檔名與 version 慣例

**檔名：`database/transitions/YYYYMMDD_NNN_短描述.php`**

- `YYYYMMDD`：當日日期
- `NNN`：當日流水（`001` 起算，每人每天各自累積）
- 短描述：snake_case，看得出在改什麼即可

例：

```
20260509_001_fix_clock_records_unique_constraint.php
20260510_001_fix_daily_attendance_status_default.php
20260510_002_create_impersonation_sessions.php
20260511_001_drop_clock_records_unused_audit_columns.php
```

**version：對應 `schema_transitions.version`（VARCHAR(100) PK）**

- 慣例：`YYYYMMDDNNN`（去掉檔名底線、純數字字串，與檔名對齊但更精簡）
- 必須全專案唯一
- 多人同日加 transition 用 `001` / `002` / `003` 區隔

例：`'version' => '20260511001'`

### ⚠ 版本號撞號：指令會直接中止

`version` 是 PK 且是「是否已執行」的唯一判準，**兩支腳本共用同一個 version 會讓排序在後者被判成「已執行」而整支靜默跳過**（第一支寫入紀錄後，第二支永遠等不到執行機會，且沒有任何錯誤訊息）。

`DbTransitionCommand` 在執行 / 預覽前一律先掃全部檔案的 version，偵測到重複即**印出撞號清單並中止（exit 1）**，不會跑任何一支：

```
偵測到重複的 transition 版本號，已中止（請修正後再執行）：
  v20260727001
    - 20260727_001_acl_rename_bom.php
    - 20260727_001_terms_user_scoping.php
```

**修正方式：把其中一支改成未使用的版本號**（新增前先看資料夾最近幾支用到哪些號）。版本號只需唯一、**不需連續**，所以不必順延其他檔、也不必動 `schema_transitions` 表——改號後該支變回 pending，靠 `up()` 的 idempotent 守門在各環境重跑自癒（原則 1）。

---

## 檔案格式

每個 transition 檔 return 一個 array：

```php
<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return [
    'version'     => '20260511001',
    'description' => 'hrm/clock_records: drop unused audit columns',
    'transaction' => false,   // DDL 必填 false；DML 可省略（預設 true）
    'up'          => function () {
        // ALTER / UPDATE / 等等
    },
];
```

| Key | 必填 | 說明 |
|-----|------|------|
| `version` | ✅ | 唯一識別字串，對應 `schema_transitions.version` |
| `description` | 建議 | 一句話描述，會寫入 DB 與 console log |
| `transaction` | DDL 必填 `false` | 預設 `true`；DDL 必須關閉（見下方原則 2） |
| `up` | ✅ | callable，實際執行的變更邏輯 |

---

## 撰寫原則

### 1. 必須 idempotent — 重跑不會 fail

線上常因手動執行過部分 SQL 導致與 dev 分歧。`up` closure 內必須先檢查狀態，已套用就 skip：

| 檢查 | 工具 |
|------|------|
| 欄位 | `Schema::hasColumn($table, $col)` |
| 索引 | `Schema::hasIndex($table, $indexName)` |
| 表 | `Schema::hasTable($table)` |
| 外鍵 | `information_schema.TABLE_CONSTRAINTS` 查詢 |
| 資料 | `where(...)` 守門，DML 自身 idempotent |

範例：

```php
if (Schema::hasColumn('ctl_products', 'color')) {
    return; // 欄位已存在 → 視為通過
}
DB::statement("ALTER TABLE `ctl_products` ADD COLUMN `color` VARCHAR(50) NULL");
```

### 2. DDL 必須設 `'transaction' => false`

MySQL 對 DDL（`ALTER TABLE` / `CREATE TABLE` / `DROP INDEX` …）會觸發**隱式 commit**，把外層 transaction 吃掉。若仍包 transaction，DbTransitionCommand 結尾的 `commit()` 會拋 `no active transaction`。

```php
'transaction' => false,
'up' => function () {
    DB::statement("ALTER TABLE ...");
},
```

### 3. 純 DML 保持 `'transaction' => true`（預設）

`UPDATE` / `INSERT` / `DELETE` 不會隱式 commit，多條 DML 失敗時可整批 rollback。預設值即 `true`，省略不寫即可。

---

## 指令

```bash
php artisan db:transition --dry-run                      # 預覽，不執行
php artisan db:transition                                # 執行所有 pending
php artisan db:transition --connection=other             # 指定連線
php artisan db:transition -v                             # verbose：列出已跳過的版本
```

執行邏輯：

1. 自動建（或升級欄位的）`schema_transitions` 表
2. 掃 `database/transitions/*.php`（排除 `example.php`）
3. 檢查版本號是否撞號，有的話印清單並中止（見上方「版本號撞號」）
4. 逐檔 require、檢查 `version`；對照 DB `status='success'` 的版本，過濾出 pending（含從未跑過 + 上次 `failed` 的）
5. 依 `version` 排序、逐筆執行：
   - `transaction:true`（DML）→ 包 transaction，失敗 rollback
   - `transaction:false`（DDL）→ 不包 transaction
6. 每筆執行**不論成功失敗**，都 `updateOrInsert` 一行到 `schema_transitions`：
   - 成功 → `status='success'`，`error_message=null`
   - 失敗 → `status='failed'`，`error_message=例外訊息`
   - 紀錄寫入時機在 `up()` 的 transaction 之外，否則 rollBack 會把失敗紀錄一起回滾
7. 任一筆失敗 → 印錯誤、中斷（後續 pending 不會跑）；下次重跑會從失敗那筆繼續

---

## 後台頁面執行（系統管理 → 遷移功能）

指令之外，後台另備一頁把 `db:transition` 包成 UI，**免 SSH 也能套用遷移**：正式區走 Git 自動部署時 code 會自己到位，剩下的最後一步（跑遷移）就在這頁按下去。

**URL**：`/{locale}/admin/system/transition`（選單：系統管理 → 遷移功能）

| 動作 | 對應指令 | 權限 |
|---|---|---|
| 清單 | 掃 `database/transitions/` + join `schema_transitions` | `{prefix}.system.transition.access` |
| 預覽 | `db:transition --dry-run` | `{prefix}.system.transition.access` |
| 執行 | `db:transition` | `{prefix}.system.transition.run` |

- **狀態欄**：`success`（已執行）/ `failed`（上次失敗、下次會重試）/ `pending`（尚未執行），與指令判準同一套。
- **輸出區**：完整回傳 `Artisan::output()`，成功與失敗訊息一字不漏，等同看 console。
- **同步執行、不走佇列**：日常小 transition 秒殺即可；`run()` 內 `set_time_limit(0)` 解除 PHP 執行上限，但 **nginx / php-fpm 各自的 timeout 仍在**——長時間的 ETL / backfill 請照舊走 SSH 或離峰執行。
- **只能跑已部署到伺服器上的檔案**，本頁不做 `git pull`。

**權限設計（super_admin-only 的作法）**：`system.transition.{menu,access,run}` 三支權限由 `AclPermissionSeeder` 建立，但**刻意不指派給任何角色**——依 `AppServiceProvider` 的 `Gate::before`，`super_admin` 無條件放行即見選單且可執行，一般管理員角色沒有該權限 → 選單看不到、route 也擋。要開放給特定維運角色時，指派 `access`（可看、可 dry-run）而不給 `run`，即為「唯讀查狀態」。

---

## 失敗紀錄與重試

當 `up()` 拋出例外：

1. 該筆 transition 的 transaction 會 rollBack（DML 的話資料完全沒變；DDL 因 MySQL 隱式 commit 可能已落下部分）
2. `schema_transitions` 該 `version` 行寫入 `status='failed'` + `error_message`
3. 整個指令中斷退出（exit code 1），後續 pending 不會跑
4. 開發者修好 `up()`（或補手動的修復 SQL）後再跑 `db:transition`：
   - 該 version 仍是 pending（因 `status` 不是 `success`），會再嘗試
   - 成功 → row 覆寫為 `status='success'`、`error_message` 清空、`executed_at` 更新
   - 仍失敗 → row 更新 `error_message`、`executed_at`

**為什麼採「version 為 PK、每次嘗試覆寫」而非每次 attempt 一行 log？** 因為實務上需要的判斷只有「這版到底成不成功」，多次重試的歷史對日後執行沒有實質影響；保留最後一次嘗試結果，搭配 git commit 歷史，已足夠審計。要更細的嘗試 log 可走 Laravel log channel 或專屬監控系統，不在本機制範圍。

**特別注意 DDL 失敗**：MySQL 對 DDL 隱式 commit，`up()` 內若連續多條 DDL，前幾條成功、第 N 條失敗，**前面幾條已落地無法回滾**。`status='failed'` 只代表「最後沒跑完」，DB 此時是「半套用」狀態。修復後重跑時，`up()` 內的 `hasColumn` / `hasIndex` 守門會自然 skip 已套用部分——這就是為什麼**原則 1（idempotent）對 DDL 特別重要**。

---

## 工作流程

### 新增一筆 transition

```
1. cp database/transitions/example.php database/transitions/20260512_001_短描述.php
2. 填入 version / description / transaction / up
3. php artisan db:transition --dry-run    # 確認 pending list 對
4. php artisan db:transition              # 本地實際執行
5. git add + commit + push
6. 正式區 git pull → php artisan db:transition
```

### 多人協作

各自加各自的檔案，檔名與 version 用「日期 + 流水」分流即可，幾乎不會 merge 衝突。

### 已執行檔案的清理

- 不必立刻刪：留在資料夾無害，新環境跑也會因 `schema_transitions` 已記錄而跳過
- 何時刪：下次提交、下下提交、整理 PR、或里程碑後統一清理皆可
- 刪除時不必動 `schema_transitions` 表，記錄留著作審計

---

## 注意事項

| 風險 | 說明 |
|------|------|
| 忘記填 `version` | DbTransitionCommand 會 warn 並跳過該檔，不影響其它 transition |
| 版本號撞號 | 兩支同 version 會讓後者被判「已執行」而靜默跳過；指令已內建偵測，撞號時直接中止並列出檔名，改其中一支的號即可（見「檔名與 version 慣例」） |
| 不 idempotent | 重跑會炸（尤其 DDL 部分失敗後重試會卡在已落地的 DDL 上），請務必加狀態守門 |
| 跳版部署 | 已成功版本在 DB 表，跨版本上線跑 `db:transition` 會把所有 pending 一次補完，不需介入 |
| DDL 包 transaction | 必出現 `no active transaction` 錯誤，設 `transaction:false` 即可 |
| 破壞性操作 | `DROP COLUMN` / 類型縮減等不可逆，建議先 backup |
| DDL 部分失敗 | MySQL 隱式 commit，前 N 條成功 + 第 N+1 條失敗時，前 N 條已落地。`status='failed'` 只代表「沒跑完」，需 idempotent `up()` 在下次重跑時 skip 已套用部分 |
| 手動補修後忘記更新 status | 若手動把失敗的部分修完，要手動 `UPDATE schema_transitions SET status='success' WHERE version=?`，否則下次重跑會再嘗試（idempotent 的 `up()` 跑兩次無害，但會混淆狀態） |

---

## 實作檔案

| 檔案 | 說明 |
|------|------|
| `database/transitions/` | 變更檔案資料夾（一變更一檔） |
| `database/transitions/example.php` | 新增範本，會被指令略過 |
| `app/Console/Commands/DbTransitionCommand.php` | `db:transition` 指令（含撞號偵測 `assertNoDuplicateVersions()`） |
| `schema_transitions` (DB 表) | 已執行版本記錄，由指令自動建立 |
| `app/Portals/Ocadmin/Core/Controllers/System/TransitionController.php` | 後台「遷移功能」頁（index / preview / run） |
| `app/Portals/Ocadmin/resources/views/ocadmin/system/transition/index.blade.php` | 該頁 view |
| `lang/{en,zh_Hant}/admin/system/transition.php` | 該頁語系檔 |

---

**文件版本**: 2.2
**建立日期**: 2026-03-24
**更新日期**:
- 2026-05-12 v2.0：改為「一變更一檔 + DB 表追蹤」機制，棄用單一檔案陣列版
- 2026-05-12 v2.1：`schema_transitions` 加 `status` / `error_message` 欄；失敗不再「靜默」，會留紀錄並允許下次重試
- 2026-07-27 v2.2：新增後台「系統管理 → 遷移功能」頁（免 SSH 執行）；指令加版本號撞號偵測，撞號直接中止
