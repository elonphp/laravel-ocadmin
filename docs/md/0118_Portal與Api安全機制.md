# Portal 與 API 安全機制

## 目錄

1. [架構總覽](#1-架構總覽)
2. [Key 職責定義](#2-key-職責定義)
3. [第一層：網路層（Per-Portal IP 限制）](#3-第一層網路層per-portal-ip-限制)
4. [第二層：閘道層（Application Gateway）](#4-第二層閘道層application-gateway)
5. [第三層：身份層驗證（User Authentication）](#5-第三層身份層驗證user-authentication)
6. [第四層：權限控制（Authorization）](#6-第四層權限控制authorization)
7. [各 Portal 安全機制對照](#7-各-portal-安全機制對照)
8. [開發環境便利機制](#8-開發環境便利機制)
9. [IP 限制設計背景](#9-ip-限制設計背景)
10. [未來演進：api_keys 資料表化](#10-未來演進api_keys-資料表化)

---

## 1. 架構總覽

系統採用**四層式安全架構**：

```
請求 → [第一層] 網路層 → [第二層] 閘道層 → [第三層] 身份層驗證 → [第四層] 權限控制 → Controller

┌──────────────────┐   ┌──────────────┐   ┌────────────────────────────────┐   ┌──────────────────────┐
│ Per-Portal IP     │   │ X-API-KEY     │   │   CheckPortalAuthorization     │   │  requirePortalRole   │
│ 限制（選用）      │   │ 你是我方 App？│   │   X-USER-ACCESS-TOKEN /             │   │  Controller 內部     │
│ 你的 IP 可通行？  │   └──────────────┘   │   X-DEV-KEY + X-DEV-USER-ID /  │   │  (角色/權限)         │
└──────────────────┘                       │   X-DEV-KEY + X-DEV-USER-EMAIL /│   └──────────────────────┘
                                           │   session / Bearer Token        │
                                           └────────────────────────────────┘
                                                        │
                                                        ├─ access_token / dev_impersonation 通過 → checkSanctum 自動跳過
                                                        └─ API 模式未帶身份 header → 放行至 checkSanctum 驗證 Bearer Token

(*) 第一層網路層為 per-portal 開關，預設關閉。僅限內部使用的 Portal 才應啟用。
    對大眾開放的 Portal（如官網 API）不可啟用，因為終端使用者 IP 不固定、無法預先列舉。
(*) X-DEV-KEY + X-DEV-USER-ID / X-DEV-USER-EMAIL 僅限非 production 環境
    X-USER-ACCESS-TOKEN、X-DEV-USER-ID、X-DEV-USER-EMAIL 三者互斥，同時存在多個回傳 400 錯誤
```

網路層 IP 限制僅作為內部限定 Portal 的額外保護，設計背景詳見[第 9 節](#9-ip-限制設計背景)。

### 四層職責

| 層級 | 問題 | 機制 |
|---|---|---|
| 第一層：網路層 | 你的 IP 可通行嗎？ | Per-Portal IP 白名單（選用，僅內部限定 Portal 開啟） |
| 第二層：閘道層 | 你是我方授權的 App 嗎？ | `X-API-KEY`（取代原 IP 白名單） |
| 第三層：身份層 | 你是哪位使用者？ | Sanctum Bearer Token / Access Token / Dev Impersonation / session |
| 第四層：權限層 | 你能做什麼？ | `requirePortalRole` / Controller 內部權限 |

### 安全相關 Middleware 清單

| Middleware 別名 | 類別 | 層級 | 用途 |
|---|---|---|---|
| `checkPortal` | `CheckPortalAuthorization` | 網路層 + 閘道層 + 身份層 | 統一閘道驗證（參數化） |
| `checkSanctum` | `CheckSanctum` | 身份層驗證 | Sanctum Bearer Token 驗證（含 access_token / dev_impersonation 旁路） |
| `requirePortalRole` | `RequirePortalRole` | 權限控制 | 角色前綴檢查 |

---

## 2. Key 職責定義

### `api_key` — 應用層閘道

- **用途**：驗證請求來自我方授權的 App（取代原 IP 白名單）
- **場景**：所有環境，API 模式的每個請求都必須攜帶
- **傳遞方式**：`X-API-KEY` Header
- **特性**：
  - 靜態 key，定義於 `.env`
  - 內嵌於前端（SPA），使用者可透過 DevTools 看到
  - 定期更換可阻斷離職員工 / 外部未授權存取
- **防護定位**：X-API-KEY 為輕量閘道，非安全邊界。真正的安全由身份層（第三層）和權限層（第四層）負責
- **儲存**：`config/vars.php` → `portal_keys.{portal}.api_key`

### `dev_key` — Dev Impersonation 專用

- **用途**：搭配 `X-DEV-USER-ID` / `X-DEV-USER-EMAIL`，驗證開發者身分
- **場景**：非 production 環境，Postman 開發測試時快速切換使用者
- **傳遞方式**：`X-DEV-KEY` Header
- **特性**：
  - 靜態 key，定義於 `.env`
  - 僅在非 production 環境有效
  - **不會出現在前端程式碼中**，只有開發者在 Postman 使用
  - 必須搭配 Dev Impersonation header（單獨使用不具效果）
- **儲存**：`config/vars.php` → `portal_keys.{portal}.dev_key`
- **拆分原因**：`api_key` 內嵌前端（使用者可見），若同一把 key 還能用於 Dev Impersonation，等於所有使用者都能在非 production 環境冒充任何人

### `access_token` — 使用者身分驗證

- **用途**：驗證使用者身分，免互動式登入流程
- **場景**：服務對服務（S2S）呼叫、排程任務、webhook、自動化腳本、Postman 測試
- **傳遞方式**：`X-USER-ACCESS-TOKEN` Header
- **特性**：
  - 以 Sanctum `personal_access_tokens` 儲存，綁定特定 User
  - 通過身份層驗證的同時完成使用者身分識別，**免互動式登入**
  - 操作記錄可追溯至具體使用者
  - 支援到期日（`expires_at`）、存取範圍（`abilities`）、使用追蹤（`last_used_at`）
- **儲存**：`personal_access_tokens` 表

### 對比

| | `api_key` | `dev_key` | `access_token` |
|---|---|---|---|
| **目的** | 應用層閘道 | Dev Impersonation | 驗證使用者身分 |
| **使用者身分** | 無（僅識別 App） | 無（需搭配 X-DEV-USER-*） | 有（token 綁定 User） |
| **是否免互動登入** | 否（僅為閘道） | 是（直接 setUser） | 是 |
| **曝露面** | 前端可見（DevTools） | 僅開發者 Postman | 僅持有者 |
| **儲存位置** | `.env` 靜態設定 | `.env` 靜態設定 | `personal_access_tokens` 表 |
| **適用環境** | 所有環境 | 僅非 production | 所有環境 |
| **Key rotation** | 改 `.env` + 重啟 + 前端更新 | 改 `.env` + 重啟 | DB 新增 token，舊的設到期 |
| **適用場景** | 所有 API 請求 | Postman 開發、多角色測試 | 服務對服務、免登入測試 |

### 驗證流程

```
請求帶 Bearer Token（人類互動登入後的操作）
  → 閘道層：X-API-KEY 驗證通過
  → 身份層：checkPortal 未帶身份 header，放行
  → checkSanctum 驗證 Sanctum Bearer Token
  → 權限層：requirePortalRole 檢查角色
  → Controller

請求帶 X-API-KEY + X-USER-ACCESS-TOKEN（正式環境排程 / S2S / webhook）
  → 閘道層：X-API-KEY 驗證通過
  → 身份層：X-USER-ACCESS-TOKEN 通過（查 personal_access_tokens → Auth::setUser）
  → checkSanctum 偵測 auth_method=access_token，自動跳過
  → 權限層：requirePortalRole 檢查角色
  → Controller

請求帶 X-API-KEY + X-DEV-KEY + X-DEV-USER-ID（開發測試快切，僅非 production）
  → 閘道層：X-API-KEY 驗證通過
  → 身份層：X-DEV-KEY + X-DEV-USER-ID 通過（以 users.id 查找 → Auth::setUser）
  → checkSanctum 偵測 auth_method=dev_impersonation，自動跳過
  → Controller

請求帶 X-API-KEY + X-DEV-KEY + X-DEV-USER-EMAIL（開發測試快切，僅非 production）
  → 閘道層：X-API-KEY 驗證通過
  → 身份層：X-DEV-KEY + X-DEV-USER-EMAIL 通過（以 users.email 查找 → Auth::setUser）
  → checkSanctum 偵測 auth_method=dev_impersonation，自動跳過
  → Controller

請求同時帶 X-USER-ACCESS-TOKEN / X-DEV-USER-ID / X-DEV-USER-EMAIL 其中多個
  → 直接回傳 400 錯誤（三者互斥，不允許同時使用）
```

---

## 3. 第一層：網路層（Per-Portal IP 限制）

### 適用場景

> **重要**：若 Portal 對大眾開放（如 www），**不可啟用** IP 限制，
> 因為終端使用者 IP 不固定、無法預先列舉。
>
> 僅當 Portal 嚴格限制特定 IP 使用時（如內部後台、VPN 環境），
> 才應啟用此層，作為額外的網路層保護。

### 設定方式

開關與白名單分開存放：

| 項目 | 儲存位置 | 命名 | 說明 |
|------|---------|------|------|
| 開關 | `.env` | `{PORTAL}_IP_RESTRICT` | 部署層級決策，重啟生效 |
| 白名單 | `settings` 資料表 | `{portal}_allowed_ips` | 營運資料，可動態調整免重啟 |

#### 開關（`.env`）

```env
# 不啟用（預設）— 對大眾開放的 Portal 應維持此設定
WWW_IP_RESTRICT=false

# 啟用（內部限定 Portal）
OCADMIN_IP_RESTRICT=true
POS_IP_RESTRICT=false
API_IP_RESTRICT=false
```

對應 `config/vars.php`：

```php
'admin' => [
    'ip_restrict' => env('OCADMIN_IP_RESTRICT', false),
    // ...
],
```

#### 白名單（`settings` 資料表）

| 欄位 | 值 |
|------|-----|
| `code` / `setting_key` | `{portal}_allowed_ips`（如 `admin_allowed_ips`） |
| `group` | `portal` |
| `value` / `setting_value` | 逗號分隔的 IP / CIDR（如 `10.0.0.0/8,192.168.0.0/16,127.0.0.1,::1`） |

各 Portal 對應的 `setting_key`：

| Portal | setting_key |
|--------|-------------|
| admin  | `admin_allowed_ips` |
| pos    | `pos_allowed_ips` |
| www    | `www_allowed_ips` |
| api    | `api_allowed_ips` |

支援格式：精確 IP（`127.0.0.1`）、CIDR（`10.0.0.0/8`），逗號分隔多筆。

### 行為邏輯

1. `.env` 開關 `ip_restrict=false`（預設）→ 不做 IP 檢查，所有 IP 放行
2. `.env` 開關 `ip_restrict=true` + `settings` 白名單為空或不存在 → 不限制（避免誤鎖）
3. `.env` 開關 `ip_restrict=true` + `settings` 白名單 `127.0.0.1,::1` → 僅允許 localhost
4. `.env` 開關 `ip_restrict=true` + `settings` 白名單 `10.0.0.0/8` → 僅允許 10.x.x.x 內網段

### 被拒絕時的回應

- **Web 模式**：302 redirect 至 `redirect_url`
- **API 模式**：HTTP 403 `{"error": "Access denied: IP not allowed."}`

### 相關檔案

- 開關：`config/vars.php` → `portal_keys.{portal}.ip_restrict`
- 白名單：`settings` 資料表 → `{portal}_allowed_ips`
- Middleware：`CheckPortalAuthorization` → `checkIpRestriction()` / `ipMatchesCidr()`

---

## 4. 第二層：閘道層（Application Gateway）

### X-API-KEY 驗證

API 模式的所有請求都必須攜帶有效的 `X-API-KEY`，否則直接回傳 401。
此層取代原本的 IP 白名單，做為「只有我方 App 才能打這組 API」的第一道關卡。

```
閘道層：

  API 模式：
    X-API-KEY 必須存在且與 config('vars.portal_keys.{portal}.api_key') 一致
    → 通過：進入身份層
    → 失敗：401 JSON

  Web 模式：
    本地/私有 IP → 放行（開發便利設計，非安全保證；正式環境的內網安全應由網路層負責）
    其他 → 檢查 X-API-KEY
```

### 防護效果

| 威脅 | 是否有效 | 說明 |
|---|---|---|
| 離職員工 | ✅ | api_key 一換，舊 key 立即失效 |
| 外部掃描 / 爬蟲 | ✅ | 不知道 key 無法存取 API |
| API 暴露面 | ✅ | 即使知道 URL，沒 key 也無法呼叫 |
| 在職員工窺探 api_key | ✅ | 看到也只能打 API（仍需登入），無法觸發 Dev Impersonation（需另一把 dev_key） |

### 安全邊界說明

> **X-API-KEY 為輕量閘道，非安全邊界。** api_key 內嵌於 SPA 前端，本質為 security by obscurity。真正的安全由身份層（第三層）和權限層（第四層）負責。
>
> **Web 模式本地/私有 IP 放行為開發便利設計，非安全保證。** `isLocalOrPrivateIp()` 為 true 時，Web 模式跳過 API-KEY 檢查，同網段任何人都可繞過閘道層。正式環境的內網安全應由網路層（第一層 Per-Portal IP 限制）負責。

### Key 管理

| | `api_key` | `dev_key` |
|---|---|---|
| **env 變數** | `API_API_KEY`、`OCADMIN_API_KEY` 等 | `API_DEV_KEY`、`OCADMIN_DEV_KEY` 等 |
| **更換方式** | 更新 `.env` → 重啟 → 前端同步更新 | 更新 `.env` → 重啟 → 通知開發者 |
| **更換時機** | 定期（建議每季）或有人員異動 | 開發團隊人員異動時 |
| **誰需要知道** | 前端部署流程（自動） | 僅開發者（Postman） |

---

## 5. 第三層：身份層驗證（User Authentication）

### 驗證條件

```
身份層：

  前提：X-USER-ACCESS-TOKEN、X-DEV-USER-ID、X-DEV-USER-EMAIL 三者互斥，同時存在多個回傳 400 錯誤

  1. X-USER-ACCESS-TOKEN Header 正確
     → SHA-256 比對 + 到期日檢查 + 存取範圍檢查（abilities 含 portal:{portal}）
     → Auth::setUser() + auth_method=access_token
  2. X-DEV-USER-ID Header（僅非 production + 需搭配有效 X-DEV-KEY）
     → 以 users.id 查找使用者，直接登入
     → Auth::setUser() + auth_method=dev_impersonation
  3. X-DEV-USER-EMAIL Header（僅非 production + 需搭配有效 X-DEV-KEY）
     → 以 users.email 查找使用者，直接登入
     → Auth::setUser() + auth_method=dev_impersonation
  4. Web 模式：已登入 session
  5. API 模式 + 未提供任何身份 header → 放行（由下游 checkSanctum 驗證 Bearer Token）
```

> **注意**：Access Token 驗證時的 `abilities` 檢查屬於 token 的**存取範圍**（此 token 可存取哪些 Portal），
> 與第三層的**權限控制**（使用者能做什麼操作）是不同層次的概念。

### Portal 設定

定義於 `config/vars.php`：

```php
'portal_keys' => [
    'admin' => [
        'api_key'      => env('OCADMIN_API_KEY', ''),
        'dev_key'      => env('OCADMIN_DEV_KEY', ''),
        'mode'         => 'web',
        'redirect_url' => '/admin/login',
        'ip_restrict'  => env('OCADMIN_IP_RESTRICT', false),
    ],
    'api' => [
        'api_key'     => env('API_API_KEY', ''),
        'dev_key'     => env('API_DEV_KEY', ''),
        'mode'        => 'api',
        'ip_restrict' => env('API_IP_RESTRICT', false),
    ],
],
```

### 模式差異

| | `web` 模式 | `api` 模式 |
|---|---|---|
| 閘道層 | 本地 IP 放行 or X-API-KEY | X-API-KEY 必要 |
| session 已登入放行 | 是 | 否 |
| 失敗行為 | redirect（`redirect_url`） | 401 JSON |
| 適用 | 後台管理 | API 服務 |

### access_token 與 personal_access_tokens

```
personal_access_tokens
├── id
├── tokenable_type  → App\Models\User
├── tokenable_id    → 綁定的使用者 ID
├── name            → 用途識別（如 "my_api_token"）
├── token           → SHA-256 雜湊後的 access_token
├── abilities       → 存取範圍（如 ["portal:api"]，限定可存取的 Portal）
├── expires_at      → 到期日（支援 key rotation）
└── last_used_at    → 最後使用時間（自動追蹤）
```

### 建立 access_token 範例

```php
$user = User::where('username', 'my_service_account')->first();
$token = $user->createToken('my_api_token', ['portal:api']);

// $token->plainTextToken 即為 access_token，只在建立時可見
// Header 使用：X-USER-ACCESS-TOKEN: {plainTextToken}
```

### Sanctum Bearer Token

- Middleware：`checkSanctum`
- 流程：`Authorization: Bearer {token}` → Sanctum guard 驗證 → 取得 User

> **access_token / dev_impersonation 旁路**：若 `checkPortal` 已透過 X-USER-ACCESS-TOKEN 或 Dev Impersonation 完成使用者驗證（`auth_method` 已設定且 `Auth::check()` 為 true），`checkSanctum` 會自動跳過，直接放行。

### Middleware 驗證流程

```php
// CheckPortalAuthorization — 網路層 + 閘道層 + 身份層

// 網路層（checkIpRestriction）
// ip_restrict=true 且 allowed_ips 有值時，檢查用戶端 IP 是否在白名單內
// ip_restrict=false（預設）→ 跳過 IP 檢查
$this->checkIpRestriction($request, $portal, $mode, $config);

// 閘道層（checkApiKey）
// API 模式：X-API-KEY 必須存在且正確，否則 401
// Web 模式：本地/私有 IP 放行，或檢查 X-API-KEY
$this->checkApiKey($request, $mode, $config);

// 身份層（checkIdentity 內部流程）
// 1. 互斥檢查：X-USER-ACCESS-TOKEN / X-DEV-USER-ID / X-DEV-USER-EMAIL 同時存在多個 → 400 錯誤
// 2. X-USER-ACCESS-TOKEN   → authenticateByAccessToken()
// 3. X-DEV-USER-ID    → authenticateByDevUser()（非 production + 有效 X-DEV-KEY）
// 4. X-DEV-USER-EMAIL → authenticateByDevUser()（非 production + 有效 X-DEV-KEY）
// 5. Web session
// 6. API 模式 + 未帶身份 header → 放行（由下游 checkSanctum 驗證 Bearer Token）
return $this->checkIdentity($request, $next, $portal, $mode, $config);

// CheckSanctum — access_token / dev_impersonation 旁路
$authMethod = $request->attributes->get('auth_method');
if (in_array($authMethod, ['access_token', 'dev_impersonation']) && Auth::check()) {
    return $next($request);  // 已由 checkPortal 驗證，跳過 Sanctum
}
```

> **設計原則**：身份層對 API 模式是「可選」的——當未提供 X-USER-ACCESS-TOKEN 等身份 header 時，
> 放行至下游 `checkSanctum` 以 Sanctum Bearer Token 完成使用者驗證。這確保互動式登入的使用者不會被閘道擋住。
> 若提供了身份 header 但驗證失敗（如無效的 X-USER-ACCESS-TOKEN），仍會回傳 401 拒絕。
> 但閘道層（X-API-KEY）是**必要的**，未攜帶或無效時直接 401，不會放行至身份層。

---

## 6. 第四層：權限控制（Authorization）

### requirePortalRole

```php
middleware('requirePortalRole:admin')
```

- `super_admin` 角色直接放行
- 檢查使用者是否擁有 `{prefix}.*` 角色（如 `admin.manager`、`admin.staff`）
- API 回傳 401，Web 重導登入頁

### Controller 層級權限

部分 Controller 在方法內手動檢查：

```php
if (!$this->permissionRepository->userHasPermission($user, 'resource.action')) {
    return 403;
}
```

---

## 7. 各 Portal 安全機制對照

### API Portal（`/api/*`）

```
公開路由（如 login）
  └─ 無 middleware 保護

受保護路由
  └─ checkPortal:{portal}
      └─ 閘道層：X-API-KEY 必須有效
          └─ 身份層（API 模式，以下擇一）：
              ├─ X-USER-ACCESS-TOKEN → Auth::setUser → checkSanctum 自動跳過
              ├─ X-DEV-KEY + X-DEV-USER-ID(*) / X-DEV-USER-EMAIL(*) → 同上
              └─ 未帶身份 header → 放行至 checkSanctum
                  └─ checkSanctum（驗證 Bearer Token）
                      └─ requirePortalRole:{prefix}
```

### Admin Portal（`/admin/*`）

```
所有路由
  └─ auth（Web session 登入）
      └─ requirePortalRole:admin
          └─ logRequest
```

> (*) X-DEV-KEY + X-DEV-USER-ID / X-DEV-USER-EMAIL 僅限非 production 環境

---

## 8. 開發環境便利機制

### Dev Impersonation（X-DEV-KEY + X-DEV-USER-ID / X-DEV-USER-EMAIL）

整合於 `CheckPortalAuthorization` Middleware，**非獨立 Middleware**。

- **用途**：開發/測試環境快速切換使用者身分，免建 Access Token
- **傳遞方式**（二擇一）：
  - `X-DEV-USER-ID` Header — 以 `users.id` 查找
  - `X-DEV-USER-EMAIL` Header — 以 `users.email` 查找（比 ID 更好記）
- **前提條件**：
  1. 非 `production` 環境（`APP_ENV != production`）
  2. 必須搭配有效的 `X-DEV-KEY`（獨立於 `X-API-KEY`，不會出現在前端程式碼中）
  3. 不可與 `X-USER-ACCESS-TOKEN` 同時使用（三者互斥，同時存在多個回傳 400）
- **驗證通過後**：`Auth::setUser()` + `auth_method=dev_impersonation`
- **User 不存在時**：回傳 400 + 明確錯誤訊息（含查詢值，方便 debug）

### 與 X-USER-ACCESS-TOKEN 的定位差異

| | `X-USER-ACCESS-TOKEN` | `X-DEV-KEY` + `X-DEV-USER-ID` / `X-DEV-USER-EMAIL` |
|---|---|---|
| **適用環境** | 所有環境 | 僅非 production |
| **前提** | 無（獨立驗證） | 需搭配有效 `X-DEV-KEY` |
| **切換使用者** | 每人需各自建 token | 改一個數字或 email 即可 |
| **安全性** | token 綁定單一 user，有 scope 限制 | 可冒充任何人，靠環境限制 + dev_key 隔離 |
| **auth_method** | `access_token` | `dev_impersonation` |
| **場景** | 正式 S2S、排程、webhook | Postman 開發、多角色測試 |

### 正式環境排程 / S2S 使用範例

```
# 排程 / webhook / 自動化腳本以特定使用者身份呼叫 API
# X-API-KEY 通過閘道層，X-USER-ACCESS-TOKEN 通過身份層並綁定使用者
X-API-KEY: {your_api_key}
X-USER-ACCESS-TOKEN: {id}|{plainTextToken}
```

> **適用場景**：Cron 排程、服務對服務呼叫、webhook callback、自動化腳本。
> Token 綁定特定 User，操作記錄可追溯，且支援到期日與存取範圍限制。

### Postman 使用範例

```
# 方式一：以 User ID 指定
X-API-KEY: {your_api_key}
X-DEV-KEY: {your_dev_key}
X-DEV-USER-ID: 101

# 方式二：以 Email 指定（更好記）
X-API-KEY: {your_api_key}
X-DEV-KEY: {your_dev_key}
X-DEV-USER-EMAIL: admin@example.com

# 切換角色只需改一個值
X-API-KEY: {your_api_key}
X-DEV-KEY: {your_dev_key}
X-DEV-USER-EMAIL: manager@example.com
```

> **注意**：Postman 需同時攜帶 `X-API-KEY`（閘道層）和 `X-DEV-KEY`（Dev Impersonation），兩者職責不同。

> **安全邊界**：`production` 環境下無論是否帶 `X-DEV-USER-ID` 或 `X-DEV-USER-EMAIL`，一律忽略，不會進入 dev impersonation 流程。`X-DEV-KEY` 在 production 環境亦無效。

---

## 9. IP 限制設計背景

Per-Portal IP 限制已實作（見[第 3 節](#3-第一層網路層per-portal-ip-限制)），**僅適用於內部限定 Portal**（使用者必定在公司內網或 VPN，IP 在可控範圍），對大眾開放的 Portal 不可啟用。

帳號安全由本系統的登入驗證機制負責（密碼 + Sanctum），未來可在登入流程加入 2FA（TOTP 等）。

### 曾評估但不採用的方案

| 方案 | 結論 | 原因 |
|---|---|---|
| 全面 IP 白名單 | **不採用** | SPA 架構下 API 看到的是使用者上網 IP（不固定），無法做白名單 |
| 帳號級 IP 限制（users.allowed_ips） | **不採用** | 需在各子系統各自實作和維護，不易統一管理 |

> **SPA 前後端分離注意事項**：SPA 從瀏覽器直接呼叫 API，Server 看到的是使用者的網路 IP，Application 層的 IP 限制可行。但前端靜態資源（HTML / JS / CSS）的存取限制需在 Web Server 層（如 Nginx `allow/deny`）處理。

---

## 10. 未來演進：api_keys 資料表化

### 背景

目前 `api_key` 以靜態方式存於 `.env`，透過 `config/vars.php` 讀取。`api_key` 為前後端分離（API 模式）的閘道層驗證，確認請求來自我方授權的 App。現階段 key 數量少（每個 Portal 一把），靜態管理已足夠。未來若有外部 partner 串接或需要獨立管理多把 key，可考慮改為資料表管理。

### 現況 vs 資料表方案

| | 現況（`.env` 靜態 key） | 資料表方案（`api_keys`） |
|---|---|---|
| **儲存** | `config/vars.php` → `env()` | DB 表，hash 存放 |
| **每次請求** | 記憶體比對，零 DB 查詢 | 多一次 DB query（可加 cache） |
| **Key 數量** | 每個 Portal 一把 | 可多把，分用途 |
| **過期/停用** | 改 `.env` + 重啟 | DB 欄位即時控制 |
| **審計追蹤** | 無 | `last_used_at`、`note` |
| **管理介面** | 無（改 .env） | 需建 CRUD |
| **Key rotation** | 改 `.env` + 重啟 | DB 新增 key，舊的設到期 |

### 現階段不實作的理由

1. **使用場景單純** — api_key 目前僅用於非 production 環境的開發便利機制，實際 key 數量 2-3 把，`.env` 管理足夠
2. **效能考量** — 每次請求多一次 DB 查詢；加 cache 又引入 cache invalidation 複雜度
3. **開發成本** — 需建 migration、model、controller、views 一整套 CRUD，產出的管理價值有限

### 建議觸發時機

當以下任一情況發生時，再啟動 `api_keys` 資料表化：

- 有**外部 partner** 需要獨立 api_key（需個別撤銷/過期）
- api_key 數量超過 5 把，`.env` 管理開始混亂
- 有**安全稽核**要求 key 使用記錄

### 建議實作方向

預計欄位：

```
api_keys
├── id
├── name            → 用途識別（如 "postman-dev"、"partner-xxx"）
├── key_hash        → SHA-256 雜湊
├── portal          → 綁定的 Portal（如 "api"、"admin"、"*"）
├── status          → active / disabled
├── note            → 備註
├── last_used_at    → 最後使用時間
├── expires_at      → 到期日
├── created_at
└── updated_at
```

---

## 相關檔案索引

| 檔案 | 說明 |
|---|---|
| `config/vars.php` | Portal Key 設定 |
| `bootstrap/app.php` | Middleware 註冊 |
| `app/Http/Middleware/CheckPortalAuthorization.php` | 統一閘道驗證（網路層 + 閘道層 + 身份層） |
| `app/Http/Middleware/CheckSanctum.php` | Sanctum Bearer Token 驗證（含旁路邏輯） |
| `app/Http/Middleware/RequirePortalRole.php` | Portal 角色前綴檢查 |
| `app/Providers/SettingServiceProvider.php` | 自動載入 `is_autoload=true` 的設定至 Config |
