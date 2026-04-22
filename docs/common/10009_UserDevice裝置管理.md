# UserDevice 裝置管理

## 目錄

1. [概述](#1-概述)
2. [登入時記錄裝置](#2-登入時記錄裝置)
3. [裝置列表與管理](#3-裝置列表與管理)
4. [遠端登出](#4-遠端登出)
5. [可信任裝置（2FA 整合）](#5-可信任裝置2fa-整合)
6. [資料表設計](#6-資料表設計)
7. [新裝置登入通知](#7-新裝置登入通知)
8. [系統管理端](#8-系統管理端)
9. [開發測試注意事項](#9-開發測試注意事項)
10. [相關檔案索引](#10-相關檔案索引)

---

## 1. 概述

UserDevice 為**獨立可選模組**，記錄使用者的登入裝置資訊，提供裝置列表、遠端登出、可信任裝置等功能。

### 與 2FA 的關係

- **UserDevice 不依賴 2FA**，可單獨安裝使用（裝置列表、遠端登出獨立有價值）
- **2FA 依賴 UserDevice**（必須） — 2FA 的觸發時機（新裝置、信任到期、閒置過久）皆需裝置記錄來判斷
- 2FA 啟用時，UserDevice 額外提供「可信任裝置」功能（免重複 TOTP）
- 2FA 未啟用時，`trusted_until` 欄位存在但不被使用

### 功能分層

| 入口 | 對象 | 說明 |
|------|------|------|
| **Account**（個人帳戶） | 使用者管自己的裝置 | 查看我的裝置、遠端登出、管理信任 |
| **System**（系統管理） | 管理員管所有人的裝置 | 檢視任意使用者的裝置、強制登出 |

---

## 2. 登入時記錄裝置

### 記錄流程

```
登入成功（帳密通過 + 2FA 通過（若啟用））
  → 解析 User-Agent → 取得裝置名稱、瀏覽器、作業系統
  → 記錄用戶端 IP
  → 產生 device_fingerprint
  → 查找 user_devices 是否有同一 fingerprint 的記錄
    → 有 → 更新 last_active_at、ip_address
    → 無 → 新增記錄（觸發新裝置通知，若啟用）
  → 標記當前裝置 is_current = true
  → 其他裝置 is_current = false
```

### User-Agent 解析

使用 `jenssegers/agent` 套件：

```php
use Jenssegers\Agent\Agent;

$agent = new Agent();
$agent->setUserAgent($request->userAgent());

$deviceName = sprintf('%s on %s',
    $agent->browser() ?: 'Unknown Browser',
    $agent->platform() ?: 'Unknown OS'
);
// 範例：Chrome on Windows、Safari on macOS、Firefox on Ubuntu
```

### Device Fingerprint

裝置指紋用於識別「同一台裝置」，以 session ID 為基礎產生 hash：

```php
$fingerprint = hash('sha256', $request->session()->getId() . $request->userAgent());
```

> **注意**：此方式非精確指紋（使用者清除 cookie 後視為新裝置），但足以涵蓋大多數場景。
> 若需更精確的識別，可引入 browser fingerprinting 方案（如 FingerprintJS），但會增加前端複雜度。

---

## 3. 裝置列表與管理

### 個人裝置頁面

使用者在「個人帳戶 > 我的裝置」頁面可查看：

| 顯示欄位 | 來源 | 說明 |
|----------|------|------|
| 裝置名稱 | `device_name` | 如 "Chrome on Windows" |
| IP 位址 | `ip_address` | 登入時的用戶端 IP |
| 地理位置 | `location` | 依 IP 反查（可選） |
| 最後活動 | `last_active_at` | 最後一次使用時間 |
| 當前裝置 | `is_current` | 醒目標記，不可登出自己 |
| 信任狀態 | `trusted_until` | 若已信任，顯示到期時間（僅 2FA 啟用時） |

### 操作

| 操作 | 說明 |
|------|------|
| 遠端登出 | 使特定裝置的 session 失效 |
| 登出所有其他裝置 | 一鍵登出當前裝置以外的所有裝置 |
| 撤銷信任 | 將特定裝置的 `trusted_until` 設為 null |

---

## 4. 遠端登出

### 單一裝置登出

```php
// 刪除 user_devices 記錄
$device = UserDevice::where('id', $deviceId)
    ->where('user_id', auth()->id())
    ->firstOrFail();

// 使對應 session 失效（若可追溯 session ID）
// 刪除裝置記錄
$device->delete();
```

### 登出所有其他裝置

```php
// Laravel 內建：使其他 session 失效（需使用者確認密碼）
Auth::logoutOtherDevices($password);

// 清除所有非當前的 user_devices 記錄
UserDevice::where('user_id', auth()->id())
    ->where('is_current', false)
    ->delete();
```

---

## 5. 可信任裝置（2FA 整合）

> 此功能僅在 2FA 模組啟用時有意義。2FA 未啟用時，`trusted_until` 欄位不會被使用。

### 運作方式

2FA 驗證通過後，使用者可勾選「信任此裝置 N 天」。信任期間內，該裝置登入免再輸入 TOTP 驗證碼。

### 信任流程

```
2FA 驗證通過
  → 使用者勾選「信任此裝置」
  → user_devices.trusted_until = now + N 天（預設 30 天）

下次登入（帳密通過後）
  → 查找 user_devices（以 device_fingerprint + user_id 比對）
  → trusted_until > now？
    → 是 → 跳過 2FA，直接登入
    → 否 → 顯示 TOTP 輸入頁
```

### 信任管理

| 操作 | 行為 |
|------|------|
| 使用者手動撤銷 | 在裝置管理頁將特定裝置的 `trusted_until` 設為 null |
| 密碼變更 | 自動清除所有裝置的 `trusted_until`（全部需重新 2FA） |
| 管理員停用帳號 | session 失效，信任一併失效 |
| 信任到期 | 下次登入自動觸發 2FA |

### 設定

```env
# 可信任裝置天數（0 = 不允許信任裝置）
TWO_FACTOR_TRUST_DAYS=30
```

```php
// config/vars.php
'two_factor' => [
    // ...
    'trust_days' => env('TWO_FACTOR_TRUST_DAYS', 30),
],
```

---

## 6. 資料表設計

### user_devices 表

```
user_devices
├── id                  BIGINT          主鍵
├── user_id             BIGINT(FK)      關聯使用者（cascadeOnDelete）
├── device_name         VARCHAR         裝置名稱（解析 User-Agent，如 "Chrome on Windows"）
├── device_fingerprint  VARCHAR         裝置指紋（session-based hash）
├── ip_address          VARCHAR(45)     用戶端 IP
├── location            VARCHAR         地理位置（nullable，可選，依 IP 反查）
├── last_active_at      TIMESTAMP       最後活動時間
├── is_current          BOOLEAN         是否為當前裝置（default: false）
├── trusted_until       TIMESTAMP       可信任裝置到期時間（nullable，僅 2FA 啟用時使用）
├── created_at          TIMESTAMP
└── updated_at          TIMESTAMP
```

### Migration

```php
Schema::create('user_devices', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('device_name');
    $table->string('device_fingerprint');
    $table->string('ip_address', 45);
    $table->string('location')->nullable();
    $table->timestamp('last_active_at');
    $table->boolean('is_current')->default(false);
    $table->timestamp('trusted_until')->nullable();
    $table->timestamps();

    $table->index(['user_id', 'device_fingerprint']);
});
```

### 欄位說明

| 欄位 | 說明 |
|------|------|
| `device_name` | 解析 User-Agent 得到的人類可讀名稱 |
| `device_fingerprint` | 用於識別「同一台裝置」，以 session ID + User-Agent 的 SHA-256 hash |
| `location` | 可選欄位，依 IP 反查地理位置（如 "Taipei, TW"），需串接 GeoIP 服務 |
| `is_current` | 每次登入時更新，同一使用者僅有一筆 `is_current = true` |
| `trusted_until` | 僅 2FA 啟用時有意義，為 null 表示不信任 |

---

## 7. 新裝置登入通知

### 功能概述（可選）

偵測到新裝置登入時，寄 email 通知使用者。

### 判斷邏輯

```
登入成功
  → 以 device_fingerprint + user_id 查找 user_devices
    → 無記錄 → 新裝置 → 發送通知
    → 有記錄 → 既有裝置 → 不通知
```

### 通知內容

- 登入時間
- 裝置名稱（如 "Chrome on Windows"）
- IP 位址
- 地理位置（若有）
- 「如果這不是你，請立即變更密碼」的提醒連結

### 設定

```env
# 新裝置登入通知（預設關閉）
USER_DEVICE_LOGIN_NOTIFICATION=false
```

---

## 8. 系統管理端

### 功能概述

管理員可在「系統管理 > 裝置管理」檢視與管理所有使用者的登入裝置。

### 功能清單

| 功能 | 說明 |
|------|------|
| **使用者裝置列表** | 選擇使用者後，顯示其所有登入裝置 |
| **強制登出** | 管理員可強制登出特定使用者的特定裝置 |
| **強制登出全部** | 管理員可強制登出特定使用者的所有裝置 |
| **清除信任** | 管理員可清除特定使用者的所有可信任裝置設定 |

### 權限控制

- 系統管理端的裝置管理功能需 `requirePortalRole:admin` 權限
- 操作記錄應寫入系統 log（誰在何時強制登出了誰的裝置）

### 使用場景

| 場景 | 操作 |
|------|------|
| 員工離職 | 強制登出所有裝置 + 停用帳號 |
| 帳號疑似被盜 | 強制登出所有裝置 + 清除信任 + 通知使用者變更密碼 |
| 使用者手機遺失 | 管理員協助清除信任裝置（搭配 2FA 重置） |

---

## 9. 開發測試注意事項

### 模組獨立性

UserDevice 模組不依賴 2FA，移除 2FA 模組後仍可正常運作：
- 裝置列表、遠端登出等功能不受影響
- `trusted_until` 欄位存在但不被使用

### Seeder

```php
// 為測試使用者建立模擬裝置記錄
UserDevice::create([
    'user_id' => 1,
    'device_name' => 'Chrome on Windows',
    'device_fingerprint' => hash('sha256', 'test-session-1'),
    'ip_address' => '127.0.0.1',
    'last_active_at' => now(),
    'is_current' => true,
]);

UserDevice::create([
    'user_id' => 1,
    'device_name' => 'Safari on macOS',
    'device_fingerprint' => hash('sha256', 'test-session-2'),
    'ip_address' => '192.168.1.100',
    'last_active_at' => now()->subDays(3),
    'is_current' => false,
    'trusted_until' => now()->addDays(27),
]);
```

---

## 10. 相關檔案索引

### 預計新增

| 檔案 | 說明 |
|------|------|
| `app/Models/UserDevice.php` | UserDevice Model |
| `app/Portals/Ocadmin/Modules/Account/UserDevice/UserDeviceController.php` | 個人裝置管理（我的裝置） |
| `app/Portals/Ocadmin/Modules/System/UserDevice/UserDeviceAdminController.php` | 系統管理（所有人的裝置） |
| `database/migrations/xxxx_create_user_devices_table.php` | user_devices 表 |

### 預計修改

| 檔案 | 修改內容 |
|------|---------|
| `app/Portals/Ocadmin/Core/Controllers/LoginController.php` | 登入成功後記錄裝置 |
| `routes/ocadmin.php`（或對應路由檔） | 新增裝置管理路由 |
| `composer.json` | 新增 `jenssegers/agent` 依賴 |

### 模組結構

```
Modules/Account/UserDevice/              ← 使用者管自己的裝置
└── UserDeviceController.php             我的裝置列表、遠端登出、撤銷信任

Modules/System/UserDevice/               ← 管理員管所有人的裝置
└── UserDeviceAdminController.php        檢視任意使用者裝置、強制登出
```

### 交叉引用

- 2FA 機制（可信任裝置的觸發邏輯）：[10008_2FA 機制](10008_2FA機制.md)
- 四層安全架構：[10006_Portal 與 Api 安全機制](10006_Portal與Api安全機制.md)
- Sanctum 認證方式：[10004_認證機制 Sanctum](10004_認證機制Sanctum.md)
