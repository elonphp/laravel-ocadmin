# 資料庫 Transition 變更機制

> **使用對象：開發人員** — 用於開發階段的結構變更，或交案後的客製調整。
>
> 發案方資訊人員透過後台 UI 操作結構變更，請見 [0111_資料庫Schema同步](0111_資料庫Schema同步.md)。

## 設計理念

### Migration 的痛點

| 痛點 | 說明 |
|------|------|
| 檔案持續累積 | 專案越久歷史步驟檔越多，但只有最新狀態有意義 |
| 順序相依 | 一旦有人手動改了 DB，後續 migration 可能失敗 |
| 難以一眼看出現況 | 要讀完所有 migration 才知道表結構 |

### 本系統的做法

**單一檔案 `database/schema/transitions.php`，以陣列累積多筆變更，部署時整包執行，執行後清空陣列。**

多筆累積的好處：多位開發各加一筆，git merge 幾乎不會衝突；每筆獨立 description，log 清楚；整包一個 transaction，失敗自動回滾。

歷史記錄交給 git，資料庫不留追蹤資訊。

---

## 架構

```
database/
├── migrations/                    ← Laravel 框架表 + 業務表初始建表
│
└── schema/
    ├── tables/                    ← 表結構定義檔（參考用，非必要）
    └── transitions.php            ← 結構與資料變更（唯一檔案）
```

| 類別 | 管理方式 |
|------|---------|
| Laravel 框架表 | `migrations/` |
| 業務表初始結構 | `migrations/` |
| 後續結構與資料變更 | `schema/transitions.php` |

---

## transitions.php 格式

每筆變更為一個陣列項目，可累積多筆：

```php
use Illuminate\Support\Facades\DB;

return [
    [
        'description' => 'products: dog→cat, 新增 color 欄位',
        'up' => function () {
            DB::statement('ALTER TABLE `ctl_products` RENAME COLUMN `dog` TO `cat`');
            DB::statement("ALTER TABLE `ctl_products` ADD COLUMN `color` VARCHAR(50) NULL AFTER `name`");
        },
    ],
    [
        'description' => 'orders: 新增 needs_review 欄位',
        'up' => function () {
            DB::statement("ALTER TABLE `sal_orders` ADD COLUMN `needs_review` TINYINT(1) NOT NULL DEFAULT 0");
        },
    ],
];
```

無變更時：

```php
return [];
```

---

## 指令

```bash
php artisan db:transition --dry-run    # 預覽（列出所有待執行項目，不執行）
php artisan db:transition              # 依序執行，整包一個 transaction
```

邏輯：
- 陣列為空 → 無事可做
- 有項目：過濾出 `up` 為 callable 的，依序執行，整包包在單一 transaction，任一筆失敗整批 rollback

---

## 工作流程

```
1. 編輯 database/schema/transitions.php，新增一筆陣列項目
2. 本地執行 php artisan db:transition 確認
3. git commit & push
4. 正式區 git pull → php artisan db:transition
5. 執行後清空陣列（保留 return []），commit
```

多人協作時，各自在陣列末尾 append 一筆即可，幾乎不會 merge 衝突。

---

## 注意事項

以下風險由開發人員自行判斷與承擔：

| 風險 | 說明 |
|------|------|
| 重複執行 | 無防重複機制，同一陣列跑兩次可能報錯或造成資料問題 |
| 忘記清空 | 執行後未清空陣列，下次部署會再跑一次 |
| 跳版部署 | 檔案只有當前變更，中間版本的變更需從 git 歷史補回 |
| 破壞性操作 | DROP COLUMN、類型縮減等操作不可逆，需自行確認 |
| 部分失敗回滾 | 整批在單一 transaction，任一筆失敗則全部 rollback；但部分 DDL（如 ALTER TABLE）在 MySQL 無法 rollback |

---

## 實作檔案

| 檔案 | 說明 |
|------|------|
| `database/schema/transitions.php` | 變更定義檔 |
| `app/Console/Commands/DbTransitionCommand.php` | `db:transition` 指令 |

---

**文件版本**: 1.1
**建立日期**: 2026-03-24
**更新日期**: 2026-04-21（改為多筆陣列格式）
