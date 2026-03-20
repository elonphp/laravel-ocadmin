# 認證機制 — Sanctum

## 目錄

1. [方案選型](#1-方案選型)
2. [Session 認證（Web 模式）](#2-session-認證web-模式)
3. [Sanctum Bearer Token（API 模式）](#3-sanctum-bearer-tokenapi-模式)
4. [Access Token（personal_access_tokens）](#4-access-tokenpersonal_access_tokens)
5. [認證方式總覽](#5-認證方式總覽)
6. [與四層安全架構的關係](#6-與四層安全架構的關係)
7. [相關檔案索引](#7-相關檔案索引)

---

## 1. 方案選型

### 方案比較

| 方案 | 運作方式 | 適用場景 | 本專案適用性 |
|------|---------|---------|-------------|
| **Sanctum Session** | cookie-based session | Web 後台、同域 SPA | **採用** — admin portal 主要認證方式 |
| **Sanctum Token** | `Authorization: Bearer {token}` | SPA 前端互動式操作（使用者已登入） | **採用** — API portal 主要認證方式 |
| **Sanctum Personal Access Token** | `X-USER-ACCESS-TOKEN` | S2S、排程、webhook、自動化腳本 | **採用** — 免互動式登入場景 |
| **Passport (OAuth2)** | OAuth2 完整流程（authorization code、client credentials 等） | 本身要當 OAuth Provider 給第三方串接 | **不採用** — 過度設計 |

### 結論

- **Web 模式**（admin portal）：使用 **Sanctum Session** — 傳統 cookie-based session 認證
- **API 模式**（api portal）：使用 **Sanctum Bearer Token** — 互動式登入後的操作
- **免互動場景**（排程/S2S/webhook）：使用 **Access Token**（`personal_access_tokens`）— 預先建立的長期 token
- 不需要 Passport，因為系統不扮演 OAuth Provider 角色

---

## 2. Session 認證（Web 模式）

### 登入流程

```
使用者提交 email + password
  → LoginController::login()
    → 驗證輸入（email required, password required）
    → Auth::attempt(['email' => $email, 'password' => $password], $remember)
      → 成功：
        → session regenerate（防 session fixation）
        → 更新 users.last_login_at
        → redirect /admin/dashboard
      → 失敗：
        → back()->withErrors('帳號或密碼錯誤')
```

### 登出流程

```
LoginController::logout()
  → Auth::logout()
  → $request->session()->invalidate()
  → $request->session()->regenerateToken()（重新產生 CSRF token）
  → redirect /admin/login
```

### Remember Me

- `Auth::attempt()` 第二個參數 `$remember` 為 `true` 時啟用
- Laravel 自動在 `users.remember_token` 寫入隨機值
- 瀏覽器關閉後重新開啟，Laravel 透過 remember cookie 自動恢復 session
- Remember cookie 為 HttpOnly、加密、綁定 remember_token

### Session 設定

| 設定項 | 值 | 說明 |
|--------|-----|------|
| `SESSION_DRIVER` | `database` | Session 存入資料庫，可跨 server 共享 |
| `SESSION_LIFETIME` | `120`（分鐘） | 閒置 2 小時後過期 |
| `SESSION_ENCRYPT` | `false` | payload 不額外加密（已有 cookie 加密） |
| `SESSION_SAME_SITE` | `lax` | 防止 CSRF，允許同站導航 |
| `SESSION_HTTP_ONLY` | `true` | JavaScript 無法存取 session cookie |
| `SESSION_SECURE_ONLY` | `false`（開發）/ `true`（正式） | 正式環境強制 HTTPS |

### sessions 資料表結構

```
sessions
├── id              VARCHAR        主鍵（session ID）
├── user_id         BIGINT         關聯使用者（nullable，未登入時為 null）
├── ip_address      VARCHAR(45)    用戶端 IP
├── user_agent      TEXT           瀏覽器 User-Agent
├── payload         LONGTEXT       session 資料（序列化）
└── last_activity   INT            最後活動時間戳（indexed）
```

### 路由保護

Admin portal 路由群組使用 `auth` middleware：

```php
// 已登入 session 才能存取
Route::middleware(['auth', 'requirePortalRole:admin'])->group(function () {
    // admin 路由...
});
```

未登入時 `auth` middleware 自動重導至 `/admin/login`。

---

## 3. Sanctum Bearer Token（API 模式）

### 運作方式

使用者透過帳號密碼登入 API 端點，取得 Sanctum token 後，後續請求以 `Authorization: Bearer {token}` 攜帶。

```
API 請求
  → Header: Authorization: Bearer {token}
  → checkSanctum middleware
    → Sanctum guard 驗證 token
      → 有效：取得 User，繼續處理
      → 無效：401 JSON
```

### 旁路邏輯

若 `checkPortal` middleware 已透過其他方式完成使用者驗證，`checkSanctum` 自動跳過：

```
checkPortal 已驗證（auth_method = access_token 或 dev_impersonation）
  → Auth::check() === true
  → checkSanctum 直接放行，不再驗證 Bearer Token
```

此設計避免 access_token 或 dev_impersonation 使用者被重複驗證。

### 適用場景

- SPA 前端互動式操作（使用者已透過帳號密碼登入取得 token）
- 前端 AJAX 請求攜帶 Bearer Token 存取 API

### 與 Session 認證的差異

| | Session 認證 | Bearer Token |
|---|---|---|
| **傳遞方式** | Cookie（自動） | Header（手動攜帶） |
| **適用模式** | Web | API |
| **跨域** | 受 SameSite 限制 | 無限制 |
| **CSRF 保護** | 需要 | 不需要（token 本身即憑證） |
| **典型場景** | Admin 後台 | SPA 前端、行動 App |

---

## 4. Access Token（personal_access_tokens）

### 資料表結構

```
personal_access_tokens
├── id              BIGINT         主鍵
├── tokenable_type  VARCHAR        多態關聯類型（App\Models\User）
├── tokenable_id    BIGINT         綁定的使用者 ID
├── name            VARCHAR        用途識別（如 "my_api_token"）
├── token           VARCHAR(64)    SHA-256 雜湊後的 token（unique）
├── abilities       JSON           存取範圍（如 ["portal:api"]）（nullable）
├── expires_at      TIMESTAMP      到期日（nullable，indexed）
├── last_used_at    TIMESTAMP      最後使用時間（nullable）
├── created_at      TIMESTAMP
└── updated_at      TIMESTAMP
```

### 建立範例

```php
$user = User::where('username', 'my_service_account')->first();
$token = $user->createToken('my_api_token', ['portal:api']);

// $token->plainTextToken 即為 access_token，只在建立時可見
// 格式：{id}|{plainTextToken}
// Header 使用：X-USER-ACCESS-TOKEN: {id}|{plainTextToken}
```

### 撤銷

```php
// 撤銷特定 token
$user->tokens()->where('name', 'my_api_token')->delete();

// 撤銷所有 token
$user->tokens()->delete();
```

### 到期管理

- `expires_at` 欄位控制 token 有效期限
- 到期 token 在驗證時自動拒絕
- **Key rotation**：建立新 token → 設定舊 token 的 `expires_at` 為近期 → 舊 token 自然到期失效

### abilities 存取範圍

- 格式：`portal:{portal}`（如 `portal:api`、`portal:admin`）
- 驗證時檢查 token 的 abilities 是否包含目標 Portal
- 不同於權限層的角色/權限檢查，abilities 限定的是 **token 可存取哪些 Portal**

### 使用場景

| 場景 | 說明 |
|------|------|
| 服務對服務（S2S） | 後端服務間 API 呼叫 |
| 排程任務 | Cron job 以特定使用者身分呼叫 API |
| Webhook | 外部服務 callback 時驗證身分 |
| 自動化腳本 | CI/CD、部署腳本等 |

### Header 傳遞

```
X-USER-ACCESS-TOKEN: {id}|{plainTextToken}
```

Access Token 由 `CheckPortalAuthorization` middleware 的身份層處理，驗證通過後 `Auth::setUser()` 並設定 `auth_method=access_token`，下游 `checkSanctum` 自動跳過。

---

## 5. 認證方式總覽

| | Session | Bearer Token | Access Token | Dev Impersonation |
|---|---|---|---|---|
| **傳遞方式** | Cookie（自動） | `Authorization: Bearer {token}` | `X-USER-ACCESS-TOKEN` | `X-DEV-KEY` + `X-DEV-USER-ID` / `X-DEV-USER-EMAIL` |
| **適用模式** | Web | API | API | API |
| **適用環境** | 所有 | 所有 | 所有 | 僅非 production |
| **需互動式登入** | 是（帳密） | 是（先帳密取 token） | 否 | 否 |
| **使用者綁定** | Session 綁定 | Token 綁定 | Token 綁定 | Header 指定 |
| **生命週期** | SESSION_LIFETIME（120 分鐘） | 依 Sanctum 設定 | expires_at 自訂 | 單次請求 |
| **安全等級** | 高（HttpOnly + SameSite + CSRF） | 高（token 不可偽造） | 中高（長期有效，需管理到期） | 低（開發用，環境隔離） |
| **auth_method** | — | — | `access_token` | `dev_impersonation` |
| **典型場景** | Admin 後台操作 | SPA 前端 API 呼叫 | S2S / 排程 / webhook | Postman 開發測試 |
| **2FA 適用** | 是（登入時觸發） | 是（登入取 token 時已通過 2FA） | 否（token 本身即憑證） | 否（開發便利機制） |

---

## 6. 與四層安全架構的關係

### Sanctum 在四層架構中的定位

```
請求 → [第一層] 網路層 → [第二層] 閘道層 → [第三層] 身份層 → [第四層] 權限層 → Controller
                                              ▲
                                              │
                                     Sanctum 認證在此層
                                              │
                               ┌──────────────┼──────────────┐
                               │              │              │
                          Session 認證    Bearer Token    Access Token
                          （Web 模式）    （API 模式）    （API 模式）
```

Sanctum 是**第三層身份層**的核心技術，負責回答「你是哪位使用者？」。

### 各認證方式在架構中的位置

| 認證方式 | 處理的 Middleware | 層級 | 說明 |
|----------|------------------|------|------|
| Session | `auth`（Laravel 內建） | 身份層 | Web 模式，Admin portal 使用 |
| Bearer Token | `checkSanctum`（CheckSanctum） | 身份層 | API 模式，互動式登入後的操作 |
| Access Token | `checkPortal`（CheckPortalAuthorization） | 身份層 | API 模式，免互動式登入場景 |
| Dev Impersonation | `checkPortal`（CheckPortalAuthorization） | 身份層 | API 模式，開發測試快速切換使用者 |

### Web 模式（Admin Portal）路由堆疊

```
auth → requirePortalRole:admin → logRequest → Controller
```

- `auth`：Session 認證（未登入重導至 /admin/login）
- `requirePortalRole:admin`：權限層，檢查角色前綴
- `logRequest`：請求日誌

### API 模式路由堆疊

```
checkPortal:{portal} → checkSanctum → requirePortalRole:{prefix} → Controller
```

- `checkPortal`：網路層 + 閘道層 + 身份層（X-API-KEY、Access Token、Dev Impersonation）
- `checkSanctum`：身份層（Bearer Token，若已由 checkPortal 驗證則自動跳過）
- `requirePortalRole`：權限層

### 交叉引用

四層安全架構的完整說明（含網路層 IP 限制、閘道層 X-API-KEY、Key 職責定義、各 Portal 對照、開發環境便利機制）請參考 [0118_Portal 與 Api 安全機制](0118_Portal與Api安全機制.md)。

2FA（兩步驟驗證）在登入流程中的整合方式請參考 [0119_2FA 機制](0119_2FA機制.md)。

裝置管理（登入裝置列表、遠端登出、可信任裝置）請參考 [0120_UserDevice 裝置管理](0120_UserDevice裝置管理.md)。

---

## 7. 相關檔案索引

| 檔案 | 說明 |
|------|------|
| `app/Portals/Ocadmin/Core/Controllers/LoginController.php` | 登入/登出（Session 認證） |
| `app/Http/Middleware/CheckSanctum.php` | Sanctum Bearer Token 驗證（含 access_token / dev_impersonation 旁路） |
| `app/Http/Middleware/CheckPortalAuthorization.php` | 統一閘道驗證（網路層 + 閘道層 + 身份層，含 Access Token / Dev Impersonation） |
| `app/Http/Middleware/RequirePortalRole.php` | Portal 角色前綴檢查（權限層） |
| `config/auth.php` | Laravel 認證設定（guards、providers、passwords） |
| `config/vars.php` | Portal Key 設定（api_key、dev_key、mode、redirect_url） |
| `database/migrations/0001_01_01_000000_create_users_table.php` | users 表 + sessions 表 migration |
| `database/migrations/2026_03_16_081201_create_personal_access_tokens_table.php` | personal_access_tokens 表 migration |
