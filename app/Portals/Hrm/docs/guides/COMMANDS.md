# HRM Portal - Artisan Commands

HRM Portal 的 Console 命令列工具

---

## 📋 可用命令

### 1. 產生行事曆記錄

批次產生行事曆記錄到 `hrm_calendar_days` 表。

#### 基本用法

```bash
# 產生未來 30 天的行事曆（預設）
php artisan hrm:generate-calendar

# 產生未來 90 天
php artisan hrm:generate-calendar --days=90

# 產生 2026 年 3 月整個月
php artisan hrm:generate-calendar --yearmonth=202603
```

#### 進階選項

```bash
# 從指定日期開始產生 60 天
php artisan hrm:generate-calendar --from=2026-03-01 --days=60

# 產生 2026 年 12 月整個月，自訂週末
php artisan hrm:generate-calendar --yearmonth=202612 --weekends=5,6

# 從今天開始產生一整年
php artisan hrm:generate-calendar --days=365

# 組合使用（yearmonth 優先）
php artisan hrm:generate-calendar --yearmonth=202603 --weekends=0,6
```

#### 參數說明

| 選項 | 說明 | 預設值 | 範例 |
|------|------|--------|------|
| `--days` | 產生未來幾天（1-365） | `30` | `--days=90` |
| `--yearmonth` | 產生指定月份（YYYYMM） | - | `--yearmonth=202603` |
| `--from` | 開始日期（YYYY-MM-DD） | 今天 | `--from=2026-03-01` |
| `--weekends` | 週末日期（逗號分隔） | `0,6` | `--weekends=5,6` |

#### 參數優先順序

1. **`--yearmonth`** - 最高優先，指定整個月份
2. **`--days`** - 次之，可搭配 `--from`
3. **預設** - 產生未來 30 天

#### 週末代碼

- `0` = 週日（Sunday）
- `1` = 週一（Monday）
- `2` = 週二（Tuesday）
- `3` = 週三（Wednesday）
- `4` = 週四（Thursday）
- `5` = 週五（Friday）
- `6` = 週六（Saturday）

#### 輸出範例

##### 範例 1：產生指定月份

```bash
$ php artisan hrm:generate-calendar --yearmonth=202603

🚀 開始產生行事曆記錄...
📅 開始日期: 2026-03-01
📅 結束日期: 2026-03-31
🏖️  週末設定: 0, 6

✅ 成功！共建立 31 筆行事曆記錄

📊 統計資訊：
+--------+------+
| 項目   | 數量 |
+--------+------+
| 總天數 | 31   |
| 工作日 | 23   |
| 週末   | 8    |
+--------+------+
```

##### 範例 2：產生指定天數

```bash
$ php artisan hrm:generate-calendar --days=90 --from=2026-01-01

🚀 開始產生行事曆記錄...
📅 開始日期: 2026-01-01
📅 結束日期: 2026-03-31
🏖️  週末設定: 0, 6

✅ 成功！共建立 90 筆行事曆記錄

📊 統計資訊：
+--------+------+
| 項目   | 數量 |
+--------+------+
| 總天數 | 90   |
| 工作日 | 64   |
| 週末   | 26   |
+--------+------+
```

---

## 🔧 技術細節

### Command 位置

```
app\Portals\Hrm\Console\Commands\GenerateCalendarDaysCommand.php
```

### 註冊方式

在 `HrmServiceProvider` 中註冊：

```php
public function register(): void
{
    if ($this->app->runningInConsole()) {
        $this->commands([
            \App\Portals\Hrm\Console\Commands\GenerateCalendarDaysCommand::class,
        ]);
    }
}
```

### 使用的 Service

- `CalendarDayService::batchCreateWorkdays()` - 批次建立工作日

### 特性

- ✅ 自動跳過已存在的記錄（不會重複建立）
- ✅ 使用 Transaction 確保資料一致性
- ✅ 依據週末設定自動判斷工作日/週末
- ✅ 完整的參數驗證和錯誤處理
- ✅ 提供詳細的執行結果和統計資訊

---

## 📅 排程設定

### 每週自動產生未來一個月

在 `routes/console.php` 中設定：

```php
use Illuminate\Support\Facades\Schedule;

// 每週一凌晨 2 點執行
Schedule::command('hrm:generate-calendar --days=30')
    ->weeklyOn(1, '02:00')
    ->description('自動產生未來 30 天的行事曆');
```

### 每月自動產生下三個月

```php
// 每月 1 號凌晨 2 點執行
Schedule::command('hrm:generate-calendar --days=90')
    ->monthlyOn(1, '02:00')
    ->description('自動產生未來 90 天的行事曆');
```

### 每月自動產生下個月

```php
// 每月 25 號產生下個月的行事曆
Schedule::call(function () {
    $nextMonth = now()->addMonth()->format('Ym');
    Artisan::call("hrm:generate-calendar --yearmonth={$nextMonth}");
})->monthlyOn(25, '02:00')
  ->description('自動產生下個月的行事曆');
```

---

## 🧪 測試

### 手動測試

```bash
# 測試產生 1 天
php artisan hrm:generate-calendar --days=1

# 測試產生 1 週
php artisan hrm:generate-calendar --days=7

# 測試產生指定月份
php artisan hrm:generate-calendar --yearmonth=202603

# 檢查資料庫
php artisan tinker
>>> \App\Models\Hrm\CalendarDay::count()
>>> \App\Models\Hrm\CalendarDay::where('is_workday', true)->count()
>>> \App\Models\Hrm\CalendarDay::whereYear('date', 2026)->whereMonth('date', 3)->count()
```

### 清除測試資料

```bash
php artisan tinker
>>> \App\Models\Hrm\CalendarDay::truncate()
>>> # 或刪除指定月份
>>> \App\Models\Hrm\CalendarDay::whereYear('date', 2026)->whereMonth('date', 3)->delete()
```

---

## ❓ 常見問題

### Q1: 如果某天已經存在記錄會怎樣？

**A**: 會自動跳過，不會重複建立或覆蓋現有記錄。

### Q2: --days 的上限是多少？

**A**: 上限為 365 天（一年）。如果需要產生更長時間，可以多次執行或使用 `--yearmonth` 產生特定月份。

### Q3: --yearmonth 和 --days 可以同時使用嗎？

**A**: 可以，但 `--yearmonth` 優先。如果指定 `--yearmonth`，則會忽略 `--days` 參數。

### Q4: 如何批次匯入國定假日？

**A**: 使用 API：

```bash
curl -X POST http://localhost:8000/hrm/calendar/import-holidays \
  -H "Content-Type: application/json" \
  -d '{
    "holidays": [
      {"date": "2026-01-01", "name": "元旦"},
      {"date": "2026-02-17", "name": "春節"}
    ]
  }'
```

或使用 Tinker：

```php
$service = app(\App\Portals\Hrm\Modules\Calendar\CalendarDayService::class);
$service->importHolidays([
    ['date' => '2026-01-01', 'name' => '元旦'],
    ['date' => '2026-02-17', 'name' => '春節'],
]);
```

### Q5: 週末設定改變了怎麼辦？

**A**: 重新執行 Command 不會覆蓋已存在的記錄。建議：

**方案 1：手動更新資料庫**
```sql
UPDATE hrm_calendar_days
SET day_type = 'weekend', is_workday = 0
WHERE DAYOFWEEK(date) IN (6, 7);  -- 週五、週六
```

**方案 2：刪除未來記錄後重新產生**
```sql
DELETE FROM hrm_calendar_days WHERE date >= '2026-03-01';
```

然後：
```bash
php artisan hrm:generate-calendar --from=2026-03-01 --days=365 --weekends=5,6
```

### Q6: 預設產生多少天？

**A**: 如果不指定任何參數，預設產生未來 30 天。

### Q7: 可以產生過去的日期嗎？

**A**: 可以。使用 `--from` 指定過去的日期即可：
```bash
php artisan hrm:generate-calendar --from=2025-01-01 --days=365
```

---

## 💡 使用建議

### 初次設定

```bash
# 1. 產生今年剩餘的時間
php artisan hrm:generate-calendar --yearmonth=202602
php artisan hrm:generate-calendar --yearmonth=202603
# ... 至 202612

# 2. 產生明年整年
php artisan hrm:generate-calendar --from=2027-01-01 --days=365
```

### 日常維護

設定排程每週自動產生未來一個月，確保永遠有足夠的行事曆資料。

### 大量產生

```bash
# 產生未來一年
php artisan hrm:generate-calendar --days=365

# 或逐月產生（更精確控制）
for month in {1..12}; do
    php artisan hrm:generate-calendar --yearmonth=2026$(printf "%02d" $month)
done
```

---

## 🚀 未來擴充

可能新增的 Commands：

- `hrm:import-holidays` - 從 CSV/JSON 批次匯入假日
- `hrm:sync-google-calendar` - 同步 Google Calendar
- `hrm:generate-schedule` - 自動產生排班
- `hrm:calculate-attendance` - 批次計算出勤
- `hrm:export-calendar` - 匯出行事曆為 CSV/iCal

---

**最後更新**: 2026-02-09
**版本**: 2.0.0
