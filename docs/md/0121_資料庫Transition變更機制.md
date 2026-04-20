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

**單一檔案 `database/schema/transitions.php`，明確寫入變更 SQL 或程式碼，部署時執行，執行後清空。**

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

有變更時：

```php
use Illuminate\Support\Facades\DB;

return [
    'description' => 'products: dog→cat, 新增 color 欄位',
    'up' => function () {
        DB::statement('ALTER TABLE `ctl_products` RENAME COLUMN `dog` TO `cat`');
        DB::statement("ALTER TABLE `ctl_products` ADD COLUMN `color` VARCHAR(50) NULL AFTER `name`");
    },
];
```

無變更時：

```php
return [
    'description' => '',
    'up'          => null,
];
```

---

## 指令

```bash
php artisan db:transition --dry-run    # 預覽（不執行）
php artisan db:transition              # 執行
```

邏輯：`up` 不是 callable → 無事可做。是 callable → 包在 transaction 裡執行。

---

## 工作流程

```
1. 編輯 database/schema/transitions.php，寫入變更
2. 本地執行 php artisan db:transition 確認
3. git commit & push
4. 正式區 git pull → php artisan db:transition
5. 執行後清空 description 與 up，commit
```

---

## 注意事項

以下風險由開發人員自行判斷與承擔：

| 風險 | 說明 |
|------|------|
| 重複執行 | 無防重複機制，同一內容跑兩次可能報錯或造成資料問題 |
| 忘記清空 | 執行後未清空檔案，下次部署會再跑一次 |
| 跳版部署 | 檔案只有當前變更，中間版本的變更需從 git 歷史補回 |
| 破壞性操作 | DROP COLUMN、類型縮減等操作不可逆，需自行確認 |

---

## 實作檔案

| 檔案 | 說明 |
|------|------|
| `database/schema/transitions.php` | 變更定義檔 |
| `app/Console/Commands/DbTransitionCommand.php` | `db:transition` 指令 |

---

**文件版本**: 1.0
**建立日期**: 2026-03-24
