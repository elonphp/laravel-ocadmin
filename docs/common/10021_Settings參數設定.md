# 參數設定 (Setting)

## 一、概述

系統參數設定模組，儲存全域組態、功能開關、預設值、IP 白名單等。

設計原則：

- 一個 `code` 對應一筆設定，全表唯一
- 設定值透過 `type` 欄位標註型別，Model 自動解析（int / bool / json / array …）
- 高頻設定可標 `is_autoload=true`，啟動時預載到 Laravel `Config`，後續 0 DB query
- 所有設定均可透過 `is_active` 軟下線（保留紀錄不刪除）
- 後台 `Ocadmin/system/setting` 提供完整 CRUD UI
- 多語顯示名稱（`name_translations`）依當前 locale 自動 fallback 到 `name`

> 多層擴充（品牌層 / 門市層覆寫）見 [`10022_Settings多層擴充架構.md`](./10022_Settings多層擴充架構.md)。

---

## 二、資料表 schema

### 2-1. `sys_settings`

| 欄位 | 型別 | 說明 |
|------|------|------|
| `id` | bigint PK | |
| `group` | varchar(100) nullable | 群組分類，如 `config` / `mail` / `portal`（後台 UI 用來分組顯示） |
| `code` | varchar(255) unique | 設定代碼，全表唯一；命名慣例 `{group}_{name}` snake_case |
| `name` | varchar(255) nullable | 顯示名稱（後台列表/編輯顯示） |
| `name_translations` | json nullable | 多語名稱 `{locale: name}`，啟用第 2 種 locale 後才填 |
| `value` | text nullable | 設定值字串；解析方式由 `type` 決定 |
| `type` | enum | 值類型，見 §2-2 |
| `is_autoload` | bool default false | true=啟動時預載到 Config，零 DB query；false=按需查詢 |
| `is_active` | bool default true | false=軟下線，`setting()` 視同不存在（見 §四） |
| `note` | varchar(255) nullable | 備註說明 |
| `created_by` | FK → users.id nullable | 建立者（審計用） |
| `updated_by` | FK → users.id nullable | 最近異動者（審計用） |
| `created_at` / `updated_at` | timestamp | |

### 2-2. `SettingType` enum

| 值 | 說明 | value 範例 | parsed_value 結果 |
|----|------|------|------|
| `text` | 純文字 | `Hello World` | `"Hello World"` |
| `line` | 多行文字，一行一項 | `a\nb\nc` | `["a","b","c"]` |
| `json` | JSON 格式 | `{"k":"v"}` | `["k"=>"v"]`（assoc array） |
| `serialized` | PHP 序列化 | `a:1:{...}` | unserialize 後的 mixed |
| `bool` | 布林值 | `1` / `0` / `true` / `false` | `true` / `false` |
| `int` | 整數 | `10` | `10` (int) |
| `float` | 小數 | `3.14` | `3.14` (float) |
| `array` | 逗號分隔 | `a,b,c` | `["a","b","c"]` |

實作位置：`app/Enums/System/SettingType.php`、解析邏輯 `Setting::getParsedValueAttribute()`。

### 2-3. Migration 範本

```php
// database/migrations/0001_01_04_000001_create_settings_table.php
Schema::create('sys_settings', function (Blueprint $table) {
    $table->id();
    $table->string('group', 100)->nullable()->comment('群組');
    $table->string('code')->unique()->comment('設定代碼');
    $table->string('name')->nullable()->comment('名稱');
    $table->json('name_translations')->nullable()->comment('名稱多語 {locale: name}');
    $table->text('value')->nullable();

    $table->enum('type', SettingType::values())
        ->default(SettingType::Text->value)
        ->comment('設定值類型');

    $table->boolean('is_autoload')->default(false)->comment('啟動時自動載入至 Config');
    $table->boolean('is_active')->default(true)->comment('false=軟下線，setting() 視同不存在');

    $table->string('note')->nullable()->comment('備註');
    $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
    $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
    $table->timestamps();
});
```

---

## 三、`is_autoload` + 兩層讀取機制

### 3-1. 設計

```
┌─ 啟動時 ────────────────────────────────────────┐
│ SettingServiceProvider::boot()                  │
│   → 撈 is_autoload=true AND is_active=true 的列  │
│   → Config::set("settings.{code}", parsed_value) │
└──────────────────────────────────────────────────┘
                     │
┌─ 每個 request ────────────────────────────────────┐
│ setting('config_admin_per_page')                  │
│   ↓                                                │
│   1. 先查 Config — autoload 設定 0 query           │
│   ↓ Config miss                                    │
│   2. 查 DB（active 的）+ per-request static cache  │
│   ↓                                                │
│   回傳 parsed_value（或 default）                  │
└────────────────────────────────────────────────────┘
```

兩層意義：

| 層級 | 涵蓋範圍 | 性能 |
|---|---|---|
| Config（全域） | `is_autoload=true` 的設定 | 0 DB query / request |
| Per-request static cache | 非 autoload 的設定，第一次 request 內查到後 | 同 request 後續 0 query |

效果：高頻設定（如 IP 白名單，middleware 要查）標 autoload 完全避開 DB；低頻設定（如某個功能旗標）每 request 最多 1 次 query。

### 3-2. SettingServiceProvider

```php
<?php

namespace App\Providers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class SettingServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // 避免 migrate 前資料表/欄位不存在時報錯
        if (!Schema::hasTable('sys_settings') || !Schema::hasColumn('sys_settings', 'is_autoload')) {
            return;
        }

        $settings = \App\Models\System\Setting::active()
            ->where('is_autoload', true)
            ->get();

        foreach ($settings as $setting) {
            Config::set("settings.{$setting->code}", $setting->parsed_value);
        }
    }
}
```

註冊：`config/app.php` `providers` 陣列。

### 3-3. `setting()` helper

```php
// app/Helpers/helpers.php
if (! function_exists('setting')) {
    function setting(string $code, mixed $default = null): mixed
    {
        // 1. 先查 Config（autoload 已預載）
        $configKey = "settings.{$code}";
        if (config()->has($configKey)) {
            return config($configKey) ?? $default;
        }

        // 2. Config miss → 查 DB（per-request static cache）
        static $cache = [];
        if (array_key_exists($code, $cache)) {
            return $cache[$code] ?? $default;
        }

        $row = \App\Models\System\Setting::active()->where('code', $code)->first();
        $cache[$code] = $row?->parsed_value;

        return $cache[$code] ?? $default;
    }
}
```

---

## 四、`is_active` 機制（軟下線）

### 4-1. 設計

```
is_active = true   → 設定生效，setting() 正常回傳
is_active = false  → 軟下線，setting() 視同不存在 → 回 default / null
```

不直接 DELETE 的理由：

- 設定 row 留著，配合 `updated_at` 可推斷某 key 是不是真的沒人用（很久沒人改 + 標 inactive 後若無人抱怨 → 確認可刪）
- 比 DELETE 安全：萬一某 lib 還在偷讀，DELETE 後直接 missing；inactive 走 default fallback 不爆
- 比「程式碼移除 setting() 呼叫」直接：先標 inactive 觀察 → 全 codebase 真的沒人讀 → 才 DELETE

### 4-2. Model scope

```php
// app/Models/System/Setting.php
public function scopeActive(Builder $query): Builder
{
    return $query->where('is_active', true);
}
```

`SettingServiceProvider` 與 `setting()` helper 都已套用 `active()` → inactive key 連 Config 都不會載入、helper 直接視同 missing。

### 4-3. 後台 UI 行為

- 列表預設只顯示 active；提供 toggle「顯示 inactive」灰底列
- 新增表單預設 `is_active=true`
- 編輯表單可切換 inactive / active，無需 DELETE
- 批次刪除按鈕保留（給「真的確定不用」的清理）

---

## 五、`name` 多語顯示機制

### 5-1. 讀取：自動 fallback

`Setting` model 提供 accessor：

```php
public function getTranslatedNameAttribute(): ?string
{
    $locale = app()->getLocale();
    return $this->name_translations[$locale] ?? $this->name;
}
```

呼叫：`$setting->translated_name`。

設計：**讀取邏輯不需判斷支援的 locale 數量**。即使啟用 N 種 locale，某 row 沒填 `name_translations`，仍 fallback 到 `name`。

### 5-2. 寫入：admin UI 依 locale 數變形

差別處理在後台輸入介面。Helper：

```php
// app/Helpers/helpers.php
function is_multilingual(): bool
{
    return count(config('localization.supported_locales', [])) > 1;
}

function supported_locales(): array
{
    return config('localization.supported_locales', []);
}
```

Blade 範本：

```blade
@if (is_multilingual())
    {{-- 多語：tab 切換每個 locale 一個輸入框 --}}
    <ul class="nav nav-tabs">
        @foreach (supported_locales() as $locale)
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#name-{{ $locale }}">
                    {{ config('localization.locale_names.'.$locale) }}
                </a>
            </li>
        @endforeach
    </ul>
    <div class="tab-content">
        @foreach (supported_locales() as $locale)
            <div id="name-{{ $locale }}" class="tab-pane">
                <input type="text"
                       name="name_translations[{{ $locale }}]"
                       value="{{ old('name_translations.'.$locale, $setting->name_translations[$locale] ?? '') }}">
            </div>
        @endforeach
    </div>
@else
    {{-- 單語：只顯示 name 欄 --}}
    <input type="text" name="name" value="{{ old('name', $setting->name) }}">
@endif
```

### 5-3. 啟用第 2 種 locale 時

- 舊資料的 `name_translations` 仍是 NULL → 顯示時 fallback 到 `name`（OK，無需 data migration）
- admin 編輯 UI 自動長出多語 tab，需要時逐筆補翻譯
- 若想批次補翻譯（例：跑 LLM 自動譯），寫 one-off command 掃 `WHERE name_translations IS NULL` 補
- ORDER BY 翻譯名需用 `JSON_EXTRACT(name_translations, '$.{locale}')` 或 generated column；以 `name` 排序作為 fallback

---

## 六、Model 完整實作

```php
<?php

namespace App\Models\System;

use App\Enums\System\SettingType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $table = 'sys_settings';

    protected $fillable = [
        'group', 'code', 'name', 'name_translations',
        'value', 'type', 'is_autoload', 'is_active', 'note',
        'created_by', 'updated_by',
    ];

    protected $casts = [
        'type'              => SettingType::class,
        'name_translations' => 'array',
        'is_autoload'       => 'boolean',
        'is_active'         => 'boolean',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function getTranslatedNameAttribute(): ?string
    {
        $locale = app()->getLocale();
        return $this->name_translations[$locale] ?? $this->name;
    }

    public function getParsedValueAttribute(): mixed
    {
        return match ($this->type) {
            SettingType::Bool       => filter_var($this->value, FILTER_VALIDATE_BOOLEAN),
            SettingType::Int        => (int) $this->value,
            SettingType::Float      => (float) $this->value,
            SettingType::Json       => json_decode($this->value, true),
            SettingType::Serialized => unserialize($this->value),
            SettingType::Array      => array_map('trim', explode(',', $this->value ?? '')),
            SettingType::Line       => array_filter(array_map('trim', explode("\n", $this->value ?? ''))),
            default                 => $this->value,
        };
    }
}
```

---

## 七、檔案位置

```
app/Models/System/Setting.php
app/Enums/System/SettingType.php
app/Providers/SettingServiceProvider.php
app/Helpers/helpers.php                         # setting() / is_multilingual() / supported_locales()
app/Portals/Ocadmin/Core/Controllers/System/SettingController.php
app/Portals/Ocadmin/resources/views/system/setting/
├── index.blade.php
├── list.blade.php
└── form.blade.php
database/migrations/0001_01_04_000001_create_settings_table.php
database/seeders/SettingSeeder.php
```

---

## 八、路由

前綴：`/{locale}/admin/system/setting/`，權限：`admin.system.setting.{access|modify|delete}`

| 方法 | 路徑 | 名稱 |
|------|------|------|
| GET | /system/setting | system.setting.index |
| GET | /system/setting/create | system.setting.create |
| POST | /system/setting | system.setting.store |
| GET | /system/setting/{setting}/edit | system.setting.edit |
| PUT | /system/setting/{setting} | system.setting.update |
| DELETE | /system/setting/{setting} | system.setting.destroy |
| POST | /system/setting/batch-delete | system.setting.batch-delete |
| POST | /system/setting/parse-serialize | system.setting.parse-serialize |
| POST | /system/setting/to-serialize | system.setting.to-serialize |

---

## 九、使用範例

### 9-1. 讀取（首選 helper）

```php
// 整數
$perPage = setting('config_admin_per_page', 10);

// 陣列（type=array，逗號分隔）
$ips = setting('admin_allowed_ips', []);

// JSON（type=json，回 assoc array）
$slotCapacities = setting('pos_catering_slot_capacities', []);

// 布林
if (setting('config_maintenance', false)) {
    abort(503, 'Site under maintenance');
}
```

### 9-2. 直接 query model

```php
// 跨多筆條件查詢
$mailSettings = Setting::active()
    ->where('group', 'config')
    ->where('code', 'like', 'config_mail_%')
    ->get()
    ->keyBy('code');

$smtp_host = $mailSettings['config_mail_smtp_hostname']?->parsed_value;
```

### 9-3. 寫入（後台 Controller，自動觸發 Config 失效）

由 `SettingController::update()` 走 model save → 自動更新 `updated_by`。Config 預載僅在 boot 時生效，**同 request 內改設定不會立即反映在 Config**（但下次 request boot 時會更新）。若需要同 request 內反映，可在 update 後手動 `Config::set("settings.{$code}", ...)`。

---

## 十、預設種子資料（範例）

```php
// database/seeders/SettingSeeder.php
$items = [
    // Portal IP 白名單（is_autoload=true，由 SettingServiceProvider 預載）
    ['group' => 'portal', 'code' => 'admin_allowed_ips', 'value' => '127.0.0.1,::1', 'type' => SettingType::Array, 'is_autoload' => true, 'note' => 'Admin Portal IP 白名單（逗號分隔 IP/CIDR）'],
    ['group' => 'portal', 'code' => 'api_allowed_ips',   'value' => '',               'type' => SettingType::Array, 'is_autoload' => true, 'note' => 'API Portal IP 白名單'],

    // 一般設定
    ['group' => 'config', 'code' => 'config_admin_per_page', 'value' => '10',   'type' => SettingType::Int,  'note' => '後台列表每頁筆數'],
    ['group' => 'config', 'code' => 'config_login_attempts', 'value' => '5',    'type' => SettingType::Int,  'note' => '登入錯誤次數'],

    // 圖片尺寸、郵件、上傳…（略，見實際 SettingSeeder）
];

foreach ($items as $item) {
    Setting::updateOrCreate(['code' => $item['code']], $item);
}
```

---

## 附錄 A：OpenCart 4 原型參考

> 完整 schema 與預設資料：[`docs/reference/opencart4x.sql`](../reference/opencart4x.sql)（OpenCart 4 全新安裝 dump）。
> `grep "oc_setting" docs/reference/opencart4x.sql` 可查實際表定義與所有預設 INSERT。

OpenCart 4 的 `oc_setting` 表結構：

| 欄位 | 類型 | 說明 |
|------|------|------|
| setting_id | int PK AI | 主鍵 |
| store_id | int default 0 | 商店 ID（多商店覆寫，0=全域） |
| code | varchar(128) | 群組代碼，如 `config` |
| key | varchar(128) | 設定鍵，如 `config_admin_limit` |
| value | text | 設定值 |
| serialized | tinyint(1) default 0 | 是否為 JSON 序列化值 |

本系統與 OpenCart 的差異：

| 項目 | OpenCart | 本系統 |
|---|---|---|
| 多語值 | 整筆 row 用 JSON 自帶 `language_id` 結構 | 用 `name_translations` 欄位，僅針對顯示名稱多語；value 本身單一 |
| 序列化標記 | `serialized` (0/1) | `type` enum 8 種，明確表達型別 |
| 多商店 | 直接內建 `store_id` | `sys_settings` 不加；多層覆寫見 [10022](./10022_Settings多層擴充架構.md) |
| 軟下線 | 無 | 加 `is_active`，安全下線 |
| 預載 | OpenCart 預設整個 `code='config'` 全載 | 改採 `is_autoload` 選擇性預載 |

---

## 附錄 B：歷史決議：locale 欄位

舊版 `settings` 曾有 `locale` 欄位，`unique(locale, code)`，代表同一 `code` 可依語系不同值。後評估發現：

- 絕大多數設定與語言無關（per_page、SMTP、開關值）
- `locale=''` 是「通用」還是「忘了填」語意混淆
- 違反 OpenCart 原型設計
- HRM 內部系統，設定值多為系統行為組態，不是面向使用者的多語內容

**結論**：移除 `locale` 欄位，`code` 全表唯一。極少數需多語的設定（如顯示名稱）改用 `name_translations` 欄位處理（見 §五）。
