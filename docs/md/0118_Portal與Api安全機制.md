# Portal 與 API 安全機制

## 目錄

1. [架構總覽](#1-架構總覽)
2. [Key 職責定義](#2-key-職責定義)
3. [第一層：網路層驗證（Network Authentication）](#3-第一層網路層驗證network-authentication)
4. [第二層：身份層驗證（User Authentication）](#4-第二層身份層驗證user-authentication)
5. [第三層：權限控制（Authorization）](#5-第三層權限控制authorization)
6. [各 Portal 安全機制對照](#6-各-portal-安全機制對照)
7. [開發環境便利機制](#7-開發環境便利機制)
8. [未來演進：api_keys 資料表化](#8-未來演進api_keys-資料表化)

---

## 1. 架構總覽

系統採用**三層式安全架構**：

```
請求 → [第一層] 網路層驗證 → [第二層] 身份層驗證 → [第三層] 權限控制 → Controller
       ┌─────────────────────────────────────┐   ┌──────────────────────┐
       │      CheckPortalAuthorization       │   │  requirePortalRole   │
       │  Step 1          Step 2             │   │  Controller 內部     │
       │  IP白名單/       X-ACCESS-TOKEN/    │   │  (角色/權限)         │
       │  X-API-KEY       X-DEV-USER-ID(*)/  │   └──────────────────────┘
       │                  X-DEV-USER-EMAIL(*)/│
       │                  session            │
       └─────────────────────────────────────┘
                          ↓ access_token / dev_impersonation 通過時
                          checkOAuth 自動跳過

(*) X-DEV-USER-ID / X-DEV-USER-EMAIL 僅限非 production 環境，需搭配有效 X-API-KEY
    X-ACCESS-TOKEN、X-DEV-USER-ID、X-DEV-USER-EMAIL 三者互斥，同時存在多個回傳 400 錯誤
```

### 安全相關 Middleware 清單

| Middleware 別名 | 類別 | 層級 | 用途 |
|---|---|---|---|
| `checkPortal` | `CheckPortalAuthorization` | 閘道（網路層 + 身份層） | 統一閘道驗證（參數化） |
| `checkOAuth` | `CheckOAuth` | 使用者驗證 | 純 OAuth（不降級） |
| `checkOAuthToken` | `CheckOAuthToken` | 使用者驗證 | OAuth（可攜式，供其他系統複製） |
| `requirePortalRole` | `RequirePortalRole` | 權限控制 | 角色前綴檢查 |

---

## 2. Key 職責定義

### `api_key` — 網路層通行（略過 IP 檢查）

- **用途**：允許不在 IP 白名單內的合法應用存取 API
- **場景**：已部署的前端應用、Postman 外部測試
- **傳遞方式**：`X-API-KEY` Header
- **特性**：
  - 靜態 key，定義於 `.env`
  - 純網路層使用，不涉及使用者身分
  - 通過後仍需經過身份層驗證才能操作
- **儲存**：`config/vars.php` → `portal_keys.{portal}.api_key`

### `access_token` — 使用者身分驗證

- **用途**：驗證使用者身分，免 OAuth 登入流程
- **場景**：服務對服務（S2S）呼叫、排程任務、webhook、自動化腳本、Postman 測試
- **傳遞方式**：`X-ACCESS-TOKEN` Header
- **特性**：
  - 以 Sanctum `personal_access_tokens` 儲存，綁定特定 User
  - 通過身份層驗證的同時完成使用者身分識別，**免 OAuth**
  - 操作記錄可追溯至具體使用者
  - 支援到期日（`expires_at`）、存取範圍（`abilities`）、使用追蹤（`last_used_at`）
- **儲存**：`personal_access_tokens` 表

### 對比

| | `api_key` | `access_token` |
|---|---|---|
| **目的** | 略過 IP 檢查 | 驗證使用者身分 |
| **使用者身分** | 無（需後續 OAuth） | 有（token 綁定 User） |
| **是否免 OAuth** | 否 | 是 |
| **儲存位置** | `.env` 靜態設定 | `personal_access_tokens` 表 |
| **可追溯** | 僅知道哪個 portal | 知道具體使用者 |
| **到期機制** | 無（改 key 需重啟） | `expires_at` |
| **Key rotation** | 改 `.env` + 重啟 | DB 新增 token，舊的設到期 |
| **適用場景** | 前端應用、外部測試 | 服務對服務、免登入測試 |

### 驗證流程

```
請求帶 X-API-KEY + Bearer Token（人類操作，正式環境）
  → Step 1 網路層：X-API-KEY 通過
  → Step 2 身份層：checkOAuth 驗證 Bearer Token
  → Step 3 權限層：requirePortalRole 檢查角色
  → Controller

請求帶 X-API-KEY + X-ACCESS-TOKEN（服務對服務 / 免登入測試）
  → Step 1 網路層：X-API-KEY 通過
  → Step 2 身份層：X-ACCESS-TOKEN 通過（查 personal_access_tokens → Auth::setUser）
  → checkOAuth 偵測 auth_method=access_token，自動跳過
  → Step 3 權限層：requirePortalRole 檢查角色
  → Controller

請求帶 X-API-KEY + X-DEV-USER-ID（開發測試快切，僅非 production）
  → Step 1 網路層：X-API-KEY 通過
  → Step 2 身份層：X-DEV-USER-ID 通過（以 users.id 查找 → Auth::setUser）
  → checkOAuth 偵測 auth_method=dev_impersonation，自動跳過
  → Controller

請求帶 X-API-KEY + X-DEV-USER-EMAIL（開發測試快切，僅非 production）
  → Step 1 網路層：X-API-KEY 通過
  → Step 2 身份層：X-DEV-USER-EMAIL 通過（以 users.email 查找 → Auth::setUser）
  → checkOAuth 偵測 auth_method=dev_impersonation，自動跳過
  → Controller

IP 白名單內 + X-ACCESS-TOKEN
  → Step 1 網路層：IP 白名單通過
  → Step 2 身份層：X-ACCESS-TOKEN 通過
  → Controller

請求同時帶 X-ACCESS-TOKEN / X-DEV-USER-ID / X-DEV-USER-EMAIL 其中多個
  → 直接回傳 400 錯誤（三者互斥，不允許同時使用）
```

### access_token 與 OAuth 的關係

`access_token` **不需要串接 OAuth 認證中心**。兩者的認證對象不同：

| | OAuth（認證中心） | access_token |
|---|---|---|
| **認證對象** | 人（員工） | 系統/服務、免登入測試 |
| **認證中心記錄** | 需要（登入時間、裝置） | 不需要 |
| **網路依賴** | 依賴認證中心可用 | 純本地驗證，無外部依賴 |
| **操作記錄** | 本地 + 認證中心雙邊記錄 | 僅本地記錄 |

```
人類操作 → OAuth → 認證中心記錄登入 → 本地記錄操作
系統操作 → access_token → 本地記錄操作（認證中心不介入）
```

---

## 3. 第一層：網路層驗證（Network Authentication）

### 用途

驗證**請求來源是否為合法的網路環境**。未通過則直接拒絕，不進入身份驗證。

### 驗證條件

```
Step 1 — 網路層（必須通過其一，否則直接拒絕）：
  1. IP 白名單（私有 IP 或設定檔允許的 IP）
  2. X-API-KEY Header 正確
```

### Portal 設定

定義於 `config/vars.php`：

```php
'portal_keys' => [
    'admin' => [
        'api_key'      => env('ADMIN_API_KEY', ''),
        'mode'         => 'web',
        'redirect_url' => '/login',
    ],
    'api' => [
        'api_key' => env('API_API_KEY', ''),
        'mode'    => 'api',
    ],
],
```

### 模式差異

| | `web` 模式 | `api` 模式 |
|---|---|---|
| session 已登入放行 | 是 | 否 |
| 失敗行為 | redirect（`redirect_url`） | 401 JSON |
| 適用 | 後台管理 | API 服務 |

---

## 4. 第二層：身份層驗證（User Authentication）

### 驗證條件

```
Step 2 — 身份層（必須通過其一）：

  前提：X-ACCESS-TOKEN、X-DEV-USER-ID、X-DEV-USER-EMAIL 三者互斥，同時存在多個回傳 400 錯誤

  1. X-ACCESS-TOKEN Header 正確
     → SHA-256 比對 + 到期日檢查 + 存取範圍檢查（abilities 含 portal:{portal}）
     → Auth::setUser() + auth_method=access_token
  2. X-DEV-USER-ID Header（僅非 production + 需搭配有效 X-API-KEY）
     → 以 users.id 查找使用者，直接登入
     → Auth::setUser() + auth_method=dev_impersonation
  3. X-DEV-USER-EMAIL Header（僅非 production + 需搭配有效 X-API-KEY）
     → 以 users.email 查找使用者，直接登入
     → Auth::setUser() + auth_method=dev_impersonation
  4. Web 模式：已登入 session
```

> **注意**：Access Token 驗證時的 `abilities` 檢查屬於 token 的**存取範圍**（此 token 可存取哪些 Portal），
> 與第三層的**權限控制**（使用者能做什麼操作）是不同層次的概念。

### access_token 與 personal_access_tokens

```
personal_access_tokens
├── id
├── tokenable_type  → App\Models\User\User
├── tokenable_id    → 綁定的使用者 ID
├── name            → 用途識別（如 "my_api_token"）
├── token           → SHA-256 雜湊後的 access_token
├── abilities       → 存取範圍（如 ["portal:api"]，限定可存取的 Portal）
├── expires_at      → 到期日（支援 key rotation）
├── last_used_at    → 最後使用時間（自動追蹤）
└── device_id       → 裝置識別（選用）
```

### 建立 access_token 範例

```php
$user = User::where('username', 'my_service_account')->first();
$token = $user->createToken('my_api_token', ['portal:api']);

// $token->plainTextToken 即為 access_token，只在建立時可見
// Header 使用：X-ACCESS-TOKEN: {plainTextToken}
```

### OAuth（認證中心）

- Middleware：`checkOAuth`
- 流程：Bearer Token → 認證中心 API 驗證 → 取得使用者 code → 查找本地 User
- 快取：`oauth:token:{md5(token)}`，TTL 1 小時

> **access_token 旁路**：若 `checkPortal` 已透過 Access Token 完成使用者驗證（`auth_method=access_token` 且 `Auth::check()` 為 true），`checkOAuth` 會自動跳過 OAuth 驗證，直接放行。

### Middleware 驗證流程

```php
// CheckPortalAuthorization — 網路層 + 身份層

// Step 1：網路層（IP 白名單 或 X-API-KEY，擇一通過）
if (!$this->checkNetwork($request, $config)) {
    return $this->deny($mode, $config);  // 網路層未通過，直接拒絕
}

// Step 2：身份層（checkIdentity 內部流程）
// 2-0. 互斥檢查：X-ACCESS-TOKEN / X-DEV-USER-ID / X-DEV-USER-EMAIL 同時存在多個 → 400 錯誤
// 2-1. X-ACCESS-TOKEN   → authenticateByAccessToken()
// 2-2. X-DEV-USER-ID    → authenticateByDevUser()（非 production + 有效 X-API-KEY）
// 2-3. X-DEV-USER-EMAIL → authenticateByDevUser()（非 production + 有效 X-API-KEY）
// 2-4. Web session
return $this->checkIdentity($request, $next, $portal, $mode, $config);

// checkOAuth — access_token / dev_impersonation 旁路
$authMethod = $request->attributes->get('auth_method');
if (in_array($authMethod, ['access_token', 'dev_impersonation']) && Auth::check()) {
    return $next($request);  // 已由 checkPortal 驗證，跳過 OAuth
}
```

---

## 5. 第三層：權限控制（Authorization）

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

## 6. 各 Portal 安全機制對照

### API Portal（`/api/*`）

```
所有路由
  └─ checkPortal:{portal}
      ├─ Step 1 網路層：IP 白名單 / X-API-KEY
      ├─ Step 2 身份層：X-ACCESS-TOKEN / X-DEV-USER-ID(*) / X-DEV-USER-EMAIL(*) / session
      │
      ├─ 公開路由（如 login）：僅需通過 Step 1
      └─ 受保護路由
          └─ checkOAuth（access_token / dev_impersonation 已驗證時自動跳過）
              └─ requirePortalRole:{prefix}
```

### Admin Portal（`/admin/*`）

```
所有路由
  └─ checkPortal:admin
      ├─ Step 1 網路層：IP 白名單 / X-API-KEY
      └─ Step 2 身份層：X-ACCESS-TOKEN / X-DEV-USER-ID(*) / X-DEV-USER-EMAIL(*) / session（web 模式）
```

> (*) X-DEV-USER-ID / X-DEV-USER-EMAIL 僅限非 production 環境

---

## 7. 開發環境便利機制

### Dev Impersonation（X-DEV-USER-ID / X-DEV-USER-EMAIL）

整合於 `CheckPortalAuthorization` Middleware，**非獨立 Middleware**。

- **用途**：開發/測試環境快速切換使用者身分，免建 Access Token
- **傳遞方式**（二擇一）：
  - `X-DEV-USER-ID` Header — 以 `users.id` 查找
  - `X-DEV-USER-EMAIL` Header — 以 `users.email` 查找（比 ID 更好記）
- **前提條件**：
  1. 非 `production` 環境（`APP_ENV != production`）
  2. 必須搭配有效的 `X-API-KEY`
  3. 不可與 `X-ACCESS-TOKEN` 同時使用（三者互斥，同時存在多個回傳 400）
- **驗證通過後**：`Auth::setUser()` + `auth_method=dev_impersonation`
- **User 不存在時**：回傳 400 + 明確錯誤訊息（含查詢值，方便 debug）

### 與 X-ACCESS-TOKEN 的定位差異

| | `X-ACCESS-TOKEN` | `X-DEV-USER-ID` / `X-DEV-USER-EMAIL` |
|---|---|---|
| **適用環境** | 所有環境 | 僅非 production |
| **前提** | 無（獨立驗證） | 需搭配有效 X-API-KEY |
| **切換使用者** | 每人需各自建 token | 改一個數字或 email 即可 |
| **安全性** | token 綁定單一 user，有 scope 限制 | 可冒充任何人，僅靠環境限制 |
| **auth_method** | `access_token` | `dev_impersonation` |
| **場景** | 正式 S2S、排程、webhook | Postman 開發、多角色測試 |

### Postman 使用範例

```
# 方式一：以 User ID 指定
X-API-KEY: {your_api_key}
X-DEV-USER-ID: 101

# 方式二：以 Email 指定（更好記）
X-API-KEY: {your_api_key}
X-DEV-USER-EMAIL: admin@example.com

# 切換角色只需改一個值
X-API-KEY: {your_api_key}
X-DEV-USER-EMAIL: manager@example.com
```

> **安全邊界**：`production` 環境下無論是否帶 `X-DEV-USER-ID` 或 `X-DEV-USER-EMAIL`，一律忽略，不會進入 dev impersonation 流程。

---

## 8. 未來演進：api_keys 資料表化

### 背景

目前 `api_key` 以靜態方式存於 `.env`，透過 `config/vars.php` 讀取。此方案在現階段（少量 key、內部使用為主）已足夠。未來若外部串接增加，可考慮將 api_key 改為資料表管理。

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

### 資料表方案的優勢

1. **Key hash 儲存** — `.env` 明碼存放有外洩風險，hash 比對更安全
2. **多把 Key 分用途** — 不同 partner / 工具各自獨立，可個別撤銷
3. **即時停用** — 不需改 `.env` + 重啟即可停用外洩的 key

### 現階段不實作的理由

1. **使用場景單純** — api_key 主要用於 Postman 開發便利 + 內部服務串接，實際 key 數量 2-3 把，`.env` 管理足夠
2. **效能考量** — 每次請求多一次 DB 查詢，對 POS 高頻 API 非免費開銷；加 cache 又引入 cache invalidation 複雜度
3. **開發成本** — 需建 migration、model、controller、views、lang files 一整套 CRUD，產出的管理價值（對目前規模）有限
### 建議觸發時機

當以下任一情況發生時，再啟動 `api_keys` 資料表化：

- 有**外部 partner** 需要獨立 api_key（需個別撤銷/過期）
- api_key 數量超過 5 把，`.env` 管理開始混亂
- 有**安全稽核**要求 key 使用記錄

### 建議實作方向

不需拆分現有 Middleware。只需將 `CheckPortalAuthorization::checkNetwork()` 的 api_key 比對邏輯，從讀 config 改為查 DB（加 cache），其餘流程不變。預計欄位：

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

> **注意**：`api_keys` 僅負責網路層（略過 IP 檢查），不涉及使用者身分。使用者身分驗證統一由 `personal_access_tokens`（Access Token）負責，維持兩者職責分離。

---

## 相關檔案索引

| 檔案 | 說明 |
|---|---|
| `config/vars.php` | Portal Key 設定 |
| `app/Http/Kernel.php` | Middleware 註冊 |
| `app/Http/Middleware/CheckPortalAuthorization.php` | 統一閘道驗證（網路層 + 身份層） |
| `app/Http/Middleware/CheckOAuth.php` | 純 OAuth 驗證 |
| `app/Http/Middleware/CheckOAuthToken.php` | 可攜式 OAuth 驗證 |
| `app/Http/Middleware/RequirePortalRole.php` | Portal 角色前綴檢查 |
| `app/Http/Middleware/BypassSanctumMiddleware.php` | 開發環境略過驗證 |
| `app/Helpers/Classes/IpHelper.php` | IP 白名單工具 |
