# 認證機制 — Sanctum + Fortify + 裝置管理 + 2FA

## 方案選型

本專案為單一站台，使用 Laravel + Inertia 前後端同一套架構，認證方案選用 **Sanctum (Session-based)**，不需要 OAuth。

### 方案比較

| 方案 | 適用場景 | 本專案適用性 |
|------|---------|-------------|
| **Sanctum (Session)** | 單站、前後端同一套、Inertia/SPA | 完全符合 |
| **Sanctum (Token)** | 需要給外部 mobile app 用 API | 目前不需要 |
| **Passport (OAuth2)** | 本身要當 OAuth Provider 給第三方串接 | 過度設計 |

Inertia 走的是傳統 web session，Sanctum 的 cookie-based session 認證就是最佳搭配。

---

## 2FA（兩步驟驗證）

### 推薦方案：Laravel Fortify

- 內建 TOTP 2FA 支援（Google Authenticator / Authy）
- `config/fortify.php` 啟用 `Features::twoFactorAuthentication()`
- 自動處理 QR Code 產生、recovery codes、驗證流程

### 可選擴充：WebAuthn

- 若需支援指紋 / 硬體金鑰，可加裝 `laragear/webauthn`

---

## 裝置管理

Laravel 沒有內建裝置管理，需自行實作。

### 資料表設計

```php
Schema::create('device_sessions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('device_name');        // 解析 User-Agent
    $table->string('device_fingerprint'); // browser fingerprint or session token
    $table->string('ip_address', 45);
    $table->string('location')->nullable();
    $table->timestamp('last_active_at');
    $table->boolean('is_current')->default(false);
    $table->timestamps();
});
```

### 功能規劃

1. **登入時記錄裝置** — 解析 User-Agent（用 `jenssegers/agent`）、記錄 IP
2. **列出所有活躍裝置** — 使用者可在設定頁看到所有登入中的裝置
3. **遠端登出** — 刪除特定裝置的 session（`Auth::logoutOtherDevices()`）
4. **新裝置通知** — 偵測到新裝置登入時寄 email 通知
5. **可信任裝置** — 2FA 驗證後可標記為信任，一段時間內免再驗證

---

## 技術組合總覽

```
Laravel Sanctum (session)   ← 認證核心
Laravel Fortify             ← 2FA + 密碼重設 + email 驗證
自建 DeviceSession Model    ← 裝置管理
jenssegers/agent            ← User-Agent 解析
```

不需要 Breeze / Jetstream 的 scaffold，因為本專案用 Inertia 自行撰寫前端，只需 Fortify 提供後端邏輯即可。
