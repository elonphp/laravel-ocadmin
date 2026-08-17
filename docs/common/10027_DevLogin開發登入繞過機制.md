# DevLogin 開發登入繞過機制

> 建立日期：2026-05-24

## 目錄

- [一、概述](#一概述)
- [二、定位](#二定位)
- [三、設計](#三設計)
- [四、4 道安全閘](#四4-道安全閘)
- [五、實作擺放](#五實作擺放)
- [六、設定 .env / config](#六設定-env--config)
- [七、用法](#七用法)
- [八、安全護欄與風險](#八安全護欄與風險)
- [九、衍生專案差異](#九衍生專案差異)
- [十、不涵蓋](#十不涵蓋)

---

## 一、概述

### 1.1 功能目標

提供 dev 環境（`APP_ENV=local`）短路登入通道：

```
POST /dev/login (form: email + token) → Auth::login + JSON 回應
```

無需密碼、無需 CSRF token、無需走完整 OAuth flow。

### 1.2 動機情境

CLI / 自動化 / AI Agent 場景無法或不便走正常登入路徑：

| 情境 | 痛點 |
|---|---|
| `php artisan tinker` 想模擬登入態 | 沒有 web request、無 session |
| curl / Postman / Insomnia 測 controller | 要處理 CSRF token + cookie jar + 密碼 |
| 自動化測試（端對端非單元） | `actingAs()` 跳過 session 不夠真實；跑真登入太重 |
| AI Coding Agent（如 Claude Code）驗證頁面行為 | Agent 無法操作瀏覽器互動式登入 |
| 多角色快速切換驗 ACL | 反覆登出 / 切帳號摩擦大 |

`Auth::login()` 走 Laravel 原生路徑，登入後行為跟正常登入完全一致 — 避免「測試環境跟正式環境行為不同」的陷阱。

### 1.3 與權限機制的關係

DevLogin 純粹是「**登入入口**」的繞過，**登入後一切權限檢查照常**。target user 是 `super_admin` 就有 super_admin 權限；是受限角色就只看得到受限選單。本機制不繞過 ACL。

---

## 二、定位

DevLogin 是「**用密碼/OAuth 之外的方式完成登入**」。登入完成後，系統把 target user 視為**真正的當前登入者** — `Auth::user()` / `auth()->id()` / `request()->user()` 全部就是該 user，所有 controller / middleware / Gate 看到的都是該 user。**沒有「模擬」層、沒有「以他身分代理操作」的概念**。

跟正規登入唯一的差別在「**怎麼進來**」：

| 登入方式 | 入口 | 後續行為 |
|---|---|---|
| 正規登入（密碼 / OAuth） | 帳密表單 / OAuth provider redirect | `Auth::login()` |
| **DevLogin** | `POST /dev/login` + token + email | `Auth::login()`（**完全相同**） |

兩種入口在 session 裡留下完全等價的登入態。後續 controller 看到的 request、Auth::user()、Gate::check 結果都不可區分。**這是設計刻意為之** — 確保 dev 環境跟 prod 環境的執行路徑完全一致，避免「dev 過了 prod 掛了」的問題。

> ⚠️ **不要把 DevLogin 跟 [10025 帳號模擬機制](10025_帳號模擬機制.md) 混為一談**。Impersonate 是「super_admin 以另一 user 身分『看』」的後台 UI 工具，有「代理層」、有唯讀守衛、有雙向鎖、可在 production 啟用、責任歸屬上是 super_admin 本人。DevLogin 沒有這些 — 就是登入而已，登入後就是該 user。兩者主題完全不同，互不引用。

---

## 三、設計

### 3.1 端點

```
POST /dev/login
Body (form):
  email=<target user email>
  token=<DEV_LOGIN_TOKEN value>

Response:
  200 OK { "success": true, "user": { id, email, ... } }   通過
  404                                                       任一閘不過
  403  "Invalid token"                                      閘 1-3 過、token 不對
  422  "email is required"                                  缺 email
  404  "User not found: ..."                                user 不存在
```

### 3.2 設計取捨

| 議題 | 抉擇 | 理由 |
|---|---|---|
| 端點 HTTP method | `POST` | token 進 body 不寫 access log；query string 容易被 referer / proxy log 外洩 |
| 失敗回應 | 一律 `404` | 不洩漏「endpoint 是否啟用」。攻擊者掃 `/dev/login` 跟掃任何不存在路徑體驗相同 |
| 環境鎖 | `app()->environment('local')` | staging / production 即使誤填 `.env` 也是 404 |
| token 比對 | `hash_equals()` | 防 timing attack（雖然 length 固定下意義不大，慣例上要） |
| 登入後 session | `Auth::login($user, remember: true)` + `session()->regenerate()` | 走 Laravel 原生，避免 session fixation |
| 零角色 user | warning 不擋 | dev 場景有時就是要驗零角色行為 |

---

## 四、4 道安全閘

任一閘不過 → `abort(404)`，不洩漏 endpoint 存在。

| # | 閘 | 來源 | 失敗碼 |
|---|---|---|---|
| 1 | `APP_ENV=local` | `app()->environment()` | 404 |
| 2 | `DEV_LOGIN_TOKEN` 非空 | `config('auth.dev_login.token')`（即 .env `DEV_LOGIN_TOKEN`） | 404 |
| 3 | 來源 IP ∈ allowlist | `config('auth.dev_login.allowed_ips')` | 404 |
| 4 | POST token `hash_equals` 比對通過 | request body | 403 |

閘 4 用 403 而非 404 是刻意：前 3 閘確認「endpoint 是 active 的」是內網 + 已啟用 dev 模式，token 不對就回 403 給內網開發者看（自己機器 typo token 也比較好除錯）。

**IP allowlist 預設**（loopback + RFC1918 私有網段 + IPv6 ULA，sync projBaz / projBar 設定）：

```php
'127.0.0.0/8'      // IPv4 loopback
'10.0.0.0/8'       // RFC1918 A
'172.16.0.0/12'    // RFC1918 B
'192.168.0.0/16'   // RFC1918 C
'::1'              // IPv6 loopback
'fc00::/7'         // IPv6 ULA
```

不在 allowlist 表示請求來自公網（即使 `APP_ENV=local` 誤上線也擋）。

---

## 五、實作擺放

| 檔案 | 角色 |
|---|---|
| `app/Http/Controllers/Auth/DevLoginController.php` | controller，4 道閘 + Auth::login |
| `config/auth.php` | 設定段 `dev_login.token` / `dev_login.allowed_ips` |
| `routes/web.php` | 條件式註冊 `POST /dev/login`（only if env=local && token 設了） |
| `bootstrap/app.php` | CSRF middleware 排除 `dev/login` |
| `.env.example` | `DEV_LOGIN_TOKEN=` 模板（值為空，表示「預設關閉」） |

放 `routes/web.php` 而非 portal 路由的理由：DevLogin 不屬任何 portal，是全系統 dev tool。路由註冊本身用 `if (app()->environment('local') && config('auth.dev_login.token'))` 包起來，正式環境路由根本不存在。

---

## 六、設定 .env / config

### 6.1 .env

```bash
# DevLogin 短路登入 token（僅 APP_ENV=local 有效）。
# 留空 = 機制關閉（路由不註冊）。
# 產生：openssl rand -hex 32  或  php -r "echo bin2hex(random_bytes(32));"
DEV_LOGIN_TOKEN=
```

### 6.2 config/auth.php 新增段

```php
'dev_login' => [
    // .env 沒設 → 等同 endpoint 關閉
    'token' => env('DEV_LOGIN_TOKEN'),

    // 允許 IP（loopback + RFC1918 私有網段 + IPv6 ULA）
    'allowed_ips' => [
        '127.0.0.0/8',
        '10.0.0.0/8',
        '172.16.0.0/12',
        '192.168.0.0/16',
        '::1',
        'fc00::/7',
    ],
],
```

### 6.3 generate token 一條龍

```bash
# 生成 + 寫入 .env
TOKEN=$(openssl rand -hex 32)
echo "DEV_LOGIN_TOKEN=$TOKEN" >> .env
echo "Token: $TOKEN"
```

---

## 七、用法

### 7.1 curl

```bash
TOKEN='your-dev-token'

# 1. 登入（cookies 寫進 jar）
curl -c /tmp/devcookies.txt \
  -d "email=admin@example.com&token=$TOKEN" \
  http://127.0.0.1:8000/dev/login

# 2. 帶 cookie 訪問頁面
curl -b /tmp/devcookies.txt -L \
  "http://127.0.0.1:8000/zh-hant/admin/catalog/options?filter_name=車" \
  -o /tmp/page.html

# 切換角色
rm -f /tmp/devcookies.txt
curl -c /tmp/devcookies.txt \
  -d "email=editor@example.com&token=$TOKEN" \
  http://127.0.0.1:8000/dev/login
```

### 7.2 PHP 內 (tinker / artisan command)

無需走本 endpoint，直接：

```php
use Illuminate\Support\Facades\Auth;
use App\Models\User;

Auth::login(User::where('email', 'admin@example.com')->first());
```

DevLogin 是給 **HTTP 層** 短路用的。process 內已經能直接 `Auth::login()` 不需要這個機制。

### 7.3 AI Coding Agent 場景

Claude Code / Cursor 等 AI Agent 修完 controller 想驗證頁面是否正確時，可呼叫 DevLogin 拿 session 後 curl 頁面比對 HTML。範例（讓 Agent 自己跑）：

```bash
# Agent 先確認 DEV_LOGIN_TOKEN 已設
grep DEV_LOGIN_TOKEN .env

# 取 cookie + 訪問待驗頁面
TOKEN=$(grep '^DEV_LOGIN_TOKEN=' .env | cut -d= -f2)
curl -s -c /tmp/c.txt -d "email=admin@example.com&token=$TOKEN" \
  http://127.0.0.1:8000/dev/login
curl -s -b /tmp/c.txt "http://127.0.0.1:8000/zh-hant/admin/foo" | grep "預期字串"
```

---

## 八、安全護欄與風險

### 8.1 護欄總表

| 護欄 | 作用 |
|---|---|
| `APP_ENV=local` 環境鎖 | staging / production 即使誤填 `.env` 也是 404 |
| `.env` 不可 commit | `.gitignore` 預設規則；token 等同 RCE 級權限 |
| IP allowlist | 公網請求即使知道 token 也是 404 |
| `hash_equals` 比對 | 防 timing attack |
| `Log::warning` 留紀錄 | 每次使用都記 user_id / email / ip / roles 進 log |
| 4 道閘獨立判斷 | 任一不過即 404，不洩漏哪一閘擋掉 |

### 8.2 風險與緩解

| 風險 | 緩解 |
|---|---|
| token 外洩等同 super_admin 登入權 | `.env` 不 commit；輪換靠改 `.env`（hash_equals 不通即失效）；遠端 dev 機器嚴控 SSH |
| 公網來源 IP 偽造（反向代理 / ngrok） | `$request->ip()` 取的是 trusted proxy 處理後的 IP；用 ngrok 時請暫時拿掉 DEV_LOGIN_TOKEN |
| 多人共用 dev DB | 同事 email 即足以登入該人身份。log 留紀錄但**不可逆**；多人共用環境請各人各自設 token，或乾脆關掉 |
| CSRF 排除被忽略 | bootstrap/app.php 集中設定；CSRF middleware 升級時要 review except 仍有效 |
| log 雜訊 | 每次 dev login 都 warning level；long session 會在 log 堆積，看 log 時 filter `dev login` |

### 8.3 token 輪換

```bash
# 生成新 token 並覆寫 .env 既有那行
NEW=$(openssl rand -hex 32)
sed -i "s/^DEV_LOGIN_TOKEN=.*/DEV_LOGIN_TOKEN=$NEW/" .env
# Windows PowerShell:
#   $NEW = -join ((1..64) | %{ '{0:x}' -f (Get-Random -Max 16) })
#   (Get-Content .env) -replace '^DEV_LOGIN_TOKEN=.*', "DEV_LOGIN_TOKEN=$NEW" | Set-Content .env
```

舊 token 立即失效（hash_equals 不通）；無需重啟 server（每 request 都重讀 `.env` 在 dev 環境）。

---

## 九、衍生專案差異

本機制在 ocadmin 範本層落地後，衍生專案 fork 即繼承。各專案可有以下分歧：

| 議題 | ocadmin 範本 | projBar | projBaz |
|---|---|---|---|
| 設定段位置 | `config/auth.php` `dev_login` | `config/accounts.php` `dev_login`（跟 OAuth bypass 同主題） | `config/accounts.php`（同 projBar） |
| OAuth callback 補子系統角色 | 不適用（無 OAuth） | 不適用（accounts 不傳子系統 role） | 補 `hrm.employee` |
| 預設測試帳號 | seeder 內 super_admin / editor / viewer | 沿用本系統 UserSeeder | HR 角色（hr_manager / hr_operator / employee / super_admin） |
| 路由所在 | `routes/web.php`（全域） | `routes/web.php`（全域） | `routes/web.php`（全域） |

衍生專案若已有 `config/accounts.php` 或同類 auth-related config，可選擇放在那裡讓「OAuth 短路 + 一般 OAuth」同主題就近；本範本層因無 OAuth 統一放 `config/auth.php`。

---

## 十、不涵蓋

- **取代正規 OAuth / 密碼登入** — 本機制純粹 CLI / 測試輔助
- **staging / production 啟用** — 環境檢查強制關閉
- **切「本機 user / pass 表」** — ocadmin 仍維護密碼，DevLogin 是繞過而非取代
- **artisan command `dev:login`** — 未來可包成 `php artisan dev:login admin@example.com` 自動 curl 印 cookie；本期不做
- **IP allowlist `.env` override** — 遠端 dev 環境（跳板機）若需，未來再加；本期靠 RFC1918 + loopback 涵蓋
