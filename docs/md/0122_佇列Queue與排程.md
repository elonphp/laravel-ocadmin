# Laravel 佇列（Queue）與排程（Schedule）

---

## 一、排程（Schedule）：Kernel.php + schedule:run

### 運作原理

Laravel 的排程機制不需要為每個任務單獨設定 cron。只需在作業系統設定**一條每分鐘執行的 cron**，Laravel 會自己依 `Kernel.php` 的設定判斷哪些任務到期需要執行。

**作業系統 cron（Plesk 計劃的任務）：**

```bash
* * * * * php /path/to/artisan schedule:run >> /dev/null 2>&1
```

**Kernel.php 範例：**

```php
protected function schedule(Schedule $schedule): void
{
    // 每5分鐘執行一次
    $schedule->job(new \App\Jobs\Sale\UpdateOrderByDatesJob)->everyFiveMinutes();

    // 每天凌晨執行
    $schedule->command('report:update-monthly')->dailyAt('01:00');
}
```

### 重點

- `schedule:run` 每分鐘被 cron 觸發，但不代表每分鐘都執行任務
- Laravel 依 `everyFiveMinutes()`、`dailyAt()` 等條件自動判斷是否到期
- `QUEUE_CONNECTION=sync` 時，`$schedule->job()` 的 Job 會**同步立即執行**，不進 jobs table

---

## 二、佇列（Queue）：標準做法 — Supervisor 常駐

Queue Worker 必須**持續運行**才能處理 jobs table 裡的任務。標準做法是安裝 Supervisor，以常駐程式管理 Worker，而非排程。

### 安裝 Supervisor

```bash
apt install supervisor
```

### 設定檔 `/etc/supervisor/conf.d/laravel-worker.conf`

```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/vhosts/your-domain.com/httpdocs/laravel/artisan queue:work --sleep=3 --tries=3 --timeout=300
autostart=true
autorestart=true
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/vhosts/your-domain.com/httpdocs/laravel/storage/logs/worker.log
```

### 啟動

```bash
supervisorctl reread
supervisorctl update
supervisorctl start laravel-worker:*
```

### 重點

- Worker 常駐，有新 job 進來立即處理
- `autorestart=true` 確保 Worker 異常結束後自動重啟
- 需要 root 或 sudo 權限安裝與管理

---

## 三、佇列替代做法 — 無 Supervisor 時用 flock + cron

若沒有安裝 Supervisor 的權限（如某些虛擬主機環境），可以用 cron 定期啟動 Worker，搭配 `flock` 防止重複執行。

### Plesk 計劃的任務設定

```bash
flock -n /tmp/laravel-queue-work.lock \
  php84 /var/www/vhosts/pos.example.com/httpdocs/laravel/artisan queue:work --stop-when-empty --timeout=300
```

### 說明

| 參數 | 作用 |
|---|---|
| `flock -n` | 嘗試取得檔案鎖，若已被鎖定則**立即放棄**，不啟動新 Worker |
| `--stop-when-empty` | jobs table 清空後 Worker 自動結束 |
| `--timeout=300` | 單一 Job 執行上限 300 秒，超時強制終止 |

### 防重複執行原理

```
第1分鐘：cron 啟動 → 取得鎖 → Worker 開始執行（任務耗時超過1分鐘）
第2分鐘：cron 再次啟動 → 嘗試取鎖 → 鎖被佔用 → 立即放棄退出，不重複執行；第1分鐘的任務持續進行
任務完成：Worker 結束 → 鎖自動釋放
第N分鐘：cron 啟動 → 成功取鎖 → 正常執行
異常情況：假設存在 --timeout 參數(非必要)，當 Job 執行超過 --timeout 秒 → Worker 強制終止該 Job → 寫入 failed_jobs → 鎖釋放
```

### `--timeout` 的作用

`--timeout=300` 是 Worker 的保護機制：若單一 Job 執行超過 300 秒，Worker 會強制終止該 Job，標記為失敗並寫入 `failed_jobs`，然後繼續處理下一個 Job。

這可防止某個 Job 卡死（例如 SQL 鎖死、外部 API 無回應）導致 Worker 永久掛住、後續任務全部堆積。

> 注意：CLI 環境下 PHP 本身沒有執行時間限制（`max_execution_time = 0`），`--timeout` 才是實際生效的超時控制。

### 缺點

- 最多延遲 1 分鐘才開始處理（等下一次 cron 觸發）
- 若任務長時間卡住超過 `--timeout` 設定，Job 會被強制終止並記入 `failed_jobs`（需人工排查後重試）
- 若 Worker 本身異常卡住（非 Job 問題），鎖不會自動釋放，需手動刪除 `/tmp/laravel-queue-work.lock`

---

## 四、使用情境範例 — 人工觸發的耗時計算

### 情境：訂單用料計算（BOM 展開）

使用者在後台點擊「計算某日所有訂單用料」，系統需對未來各日期的訂單逐筆展開 BOM（物料清單），逐階計算所需原料數量。這是 ERP 系統常見的用料需求計算場景。

**問題點：**
- 訂單數量多時，BOM 逐階展開的計算可能耗時超過 PHP-FPM 預設的 30 秒限制
- 這類計算**不是固定時間觸發**，而是使用者依需求手動執行

**為何不用 Kernel.php + schedule:run：**
- 排程適合「每隔固定時間自動執行」的任務
- 用料計算是「使用者按需觸發」，不應每幾分鐘就自動重跑

**正確做法：放入 Queue**

```
使用者點擊「開始計算」
  └─ Controller dispatch(new BomCalculationJob($date))
       └─ 立即回應：「已加入計算佇列，請稍候」
            └─ Queue Worker 在背景執行計算
                 └─ 完成後更新狀態（資料表 / cache）
                      └─ 前端輪詢或重新整理後顯示結果
```

**使用者體驗：**
- 點擊後不需等待，頁面立即回應
- 可在佇列管理 UI 查看執行狀態（待處理 / 執行中 / 完成 / 失敗）

---

## 五、任務狀態追蹤與產出檔案管理

### 為何不用 `jobs` 表追蹤結果

Laravel 內建的 `jobs` 表僅存放**待執行**的 Job，執行完畢後自動刪除，無法保存執行結果、下載連結或歷史記錄。

### 建議：自訂 `async_tasks` 表

```sql
CREATE TABLE async_tasks (
    id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    type          VARCHAR(64)  NOT NULL,          -- 任務類型，e.g. bom_calculation
    status        ENUM('pending','processing','completed','failed') NOT NULL DEFAULT 'pending',
    payload       JSON         NULL,              -- 輸入參數（如日期、篩選條件）
    result        JSON         NULL,              -- 執行結果摘要
    file_path     VARCHAR(512) NULL,              -- 產生的檔案路徑（單檔）
    download_url  VARCHAR(512) NULL,              -- 簽名下載連結
    created_by    INT UNSIGNED NULL,              -- 操作者
    created_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    completed_at  TIMESTAMP    NULL,
    updated_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

**流程：**
1. 使用者點擊觸發 → Controller 建立 `async_tasks`（status=pending）→ dispatch Job → 回傳 `task_id`
2. Job 執行時更新 status=processing
3. 完成時寫入 result、file_path、download_url，status=completed
4. 失敗時寫入錯誤訊息，status=failed
5. 前端每 2 秒輪詢狀態，completed 後顯示下載按鈕

---

### 產出多份檔案的做法 — 自動壓縮為單一 zip

若一個任務需要產出多份檔案（例如：摘要 Excel + 明細 Excel），**不建議**用 JSON `files[]` 陣列或子表，原因是一覽表 UI 的下載按鈕數量不固定，版面難以標準化。

**推薦策略：多檔自動壓縮，`file_path` 永遠是單一路徑，UI 永遠單一下載按鈕。**

| 情況 | 做法 |
|---|---|
| 單一檔案 | 直接存，`file_path` 指向原檔（.xlsx） |
| 多份檔案 | Job 完成後壓縮為 zip，`file_path` 指向 zip，個別檔案刪除 |

```php
if (count($files) > 1) {
    $zipPath = "exports/{$type}/{$timestamp}.zip";
    $zip = new \ZipArchive();
    $zip->open(storage_path("app/{$zipPath}"), \ZipArchive::CREATE);
    foreach ($files as $file) {
        $zip->addFile(storage_path("app/{$file['path']}"), $file['name']);
    }
    $zip->close();
    foreach ($files as $file) {
        Storage::disk('local')->delete($file['path']);
    }
    $finalPath = $zipPath;
} else {
    $finalPath = $files[0]['path'];
}
```

`async_tasks` 的 `result` 欄位只需存摘要資訊（筆數、日期範圍等），無需 `files[]` 陣列。

**其他方案的適用情境（不推薦用於多檔下載）：**

- **`result` JSON `files[]` 陣列**：UI 按鈕數量不固定，版面難以標準化，不推薦。
- **`async_task_files` 子表**：僅在需要對單一檔案做 SQL 查詢或逐檔追蹤產出狀態時才有意義。
- **`parent_id` 子任務**：適合「一個大任務拆成多個子任務分別執行」（如 10 萬筆拆成 10 個 Job），不適合同一 Job 產出多個檔案。

---

### 非同步 Excel 的檔案存放

**建議路徑**：`storage/app/exports/{功能}/{日期}_{時間}_{uid}.xlsx`

```
storage/app/exports/
├── bom-calculation/
│   └── 2026-03-27_143022_uid42.xlsx
└── refresh-options/
    └── 2026-03-27_091500_uid7.xlsx
```

**下載連結**：不直接暴露路徑，使用簽名路由：

```php
URL::temporarySignedRoute('exports.download', now()->addMinutes(30), ['file' => $filename]);
```

**下載時限建議規範：**

> 所有系統產出的檔案均有時限，使用者必須在時限內自行下載至本機。超過時限後檔案將被自動清除，系統不另行通知，亦不提供補產。
>
> 建議時限：**3 天**（足夠一般使用者下載，且不佔用過多磁碟空間）。

**定期清理**（在 `Kernel.php` 設定每日排程自動執行）：

```php
// app/Console/Kernel.php
$schedule->call(function () {
    $files = Storage::disk('local')->allFiles('exports');
    foreach ($files as $file) {
        if (now()->diffInDays(Carbon::createFromTimestamp(
            Storage::disk('local')->lastModified($file)
        )) >= 3) {
            Storage::disk('local')->delete($file);
        }
    }
})->daily();
```

清理同時建議將 `async_tasks` 中對應記錄的 `download_url` 標記為失效（例如清空或加註「已過期」），讓管理介面能正確顯示狀態，避免使用者點擊已失效的連結。

---

## 六、佇列管理 UI

當系統引入 Queue 機制後，建議在後台新增「佇列任務管理」頁面，讓使用者（或管理員）能夠：

| 功能 | 說明 |
|---|---|
| 查看待處理任務 | 列出 `jobs` table 中尚未執行的任務 |
| 查看執行中任務 | 顯示目前 Worker 正在處理的任務 |
| 查看失敗任務 | 列出 `failed_jobs` table，顯示錯誤訊息 |
| 重新執行失敗任務 | `php artisan queue:retry {id}` |
| 清除失敗任務 | `php artisan queue:flush` |

> Laravel 官方提供 **Laravel Horizon**（需 Redis）作為完整的佇列監控工具。若使用 database driver，可自行實作簡易管理頁面，查詢 `jobs` 與 `failed_jobs` 資料表即可。

---

## 六、環境設定：啟用 database Queue

**`.env` 設定：**

```env
QUEUE_CONNECTION=database
```

**建立所需資料表（若尚未存在）：**

```bash
php artisan queue:table
php artisan migrate
```

產生的資料表：

| 資料表 | 用途 |
|---|---|
| `jobs` | 儲存待處理與執行中的任務 |
| `failed_jobs` | 儲存執行失敗的任務與錯誤訊息 |

---

## 七、選擇建議

| 需求 | 建議方式 |
|---|---|
| 定時執行（每X分鐘、每天等） | Kernel.php + schedule:run |
| 人工觸發、需要背景執行 | Queue（Supervisor 或 flock + cron）|
| 有 root 權限 | Supervisor（標準、穩定）|
| 無 root 權限 / 虛擬主機 | flock + cron（簡易替代）|
| 執行時間短、不需背景 | `QUEUE_CONNECTION=sync`（同步執行，最簡單）|
