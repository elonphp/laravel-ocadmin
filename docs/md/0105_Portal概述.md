# Portal 概述

> 建立日期：2026-02

---

## 一、什麼是 Portal

Portal 是系統的**應用入口**，每個 Portal 面向不同的使用者群體，擁有獨立的介面、角色與權限範圍。

### 1.1 概念

```
┌─────────────┐  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐
│   Ocadmin   │  │     HRM     │  │     WWW     │  │     POS     │
│   Portal    │  │   Portal    │  │   Portal    │  │   Portal    │
│             │  │             │  │             │  │             │
│  公司內部    │  │  員工/主管   │  │  大眾/客戶   │  │  門市人員    │
│  後台管理    │  │  人力資源    │  │  官網前台    │  │  銷售系統    │
└─────────────┘  └─────────────┘  └─────────────┘  └─────────────┘
  role: admin.*    role: hrm.*      role: www.*      role: pos.*
```

每個 Portal：
- 有自己的**角色**（以 `role_prefix` 為前綴，如 `admin.*`、`hrm.*`）
- 有自己的**權限**（`{role_prefix}.{module}.{resource}.{action}`）
- 可以有獨立的**技術棧**（Blade、Inertia/React、純 API）
- 可以有獨立的**URL 前綴**與**認證方式**

### 1.2 Portal 列表

| Portal | url_prefix | role_prefix | 面向對象 | 說明 |
|--------|------------|-------------|----------|------|
| Ocadmin | `admin` | `admin` | 公司內部職員 | 通用後台管理，跨專案複用 |
| HRM | `hrm` | `hrm` | 員工 / 主管 | 人力資源管理（ESS + MSS） |
| WWW | `www` | `www` | 大眾 / 客戶 / 經銷商 | 官網或系統前台 |
| POS | 依專案定義 | 依專案定義 | 門市人員 | POS 銷售系統 |

> Portal 可依專案需求增減。以上為常見配置，並非全部都要實作。

---

## 二、角色與權限的 Portal 前綴

角色與權限皆帶有 `role_prefix`，確保不同入口之間的隔離性：

| 項目 | 格式 | 範例 |
|------|------|------|
| 角色 | `{role_prefix}.{role_name}` | `hrm.hr_manager`, `admin.operator` |
| 權限 | `{role_prefix}.{module}.{resource}.{action}` | `hrm.mss.employee.access`, `admin.config.setting.modify` |
| 全域角色（例外） | `{role_name}` | `super_admin`（跨 Portal） |

### 2.1 Portal 存取判斷

透過 `role_prefix` 判斷使用者可進入哪些 Portal：

```php
// User Model
// 參數傳入的是 role_prefix（來自 config/portals.php），不是 portal array key
public function hasPortalRole(string $rolePrefix): bool
{
    if ($this->hasRole('super_admin')) {
        return true;
    }

    return $this->roles->contains(
        fn ($role) => str_starts_with($role->name, $rolePrefix . '.')
    );
}

// 使用：傳入 role_prefix
$user->hasPortalRole('admin');  // 是否可進入 role_prefix=admin 的 Portal（如 Ocadmin）
$user->hasPortalRole('hrm');    // 是否可進入 HRM Portal
```

### 2.2 Middleware

```php
// 路由級 Portal 守門，參數為 role_prefix
Route::middleware(['auth', 'requirePortalRole:admin'])->group(/* Ocadmin 路由 */);
Route::middleware(['auth', 'requirePortalRole:hrm'])->group(/* HRM 路由 */);
```

### 2.3 Portal 目錄與版本演進

`config/portals.php` 每個 portal 用 `dir` 欄位對應 `app/Portals/` 下的實作目錄，**一個 key 對應一個目錄，一份設定**：

```php
'ocadmin' => [
    'url_prefix'  => 'admin',
    'role_prefix' => 'admin',
    'dir'         => 'Ocadmin',
],
```

大版本改版時，新增一個獨立的 portal 條目，而不是在同一條目裡堆疊多個目錄：

```php
// 新舊並存過渡期：各自宣告，各自獨立
'ocadmin' => [
    'url_prefix'  => 'admin',
    'role_prefix' => 'admin',
    'dir'         => 'Ocadmin',      // 舊版，路由逐步移走
],
'ocadmin-v2' => [
    'url_prefix'  => 'admin',        // 相同 URL 前綴（路由檔決定哪段走哪個）
    'role_prefix' => 'admin',        // 相同角色前綴（共用角色，明確宣告）
    'dir'         => 'OcadminV2',    // 各自獨立的實作目錄
],
```

路由檔決定哪個 portal key 實際對應哪段 URL，config 只做宣告。

### 2.4 四個概念完全解耦

`config/portals.php` 的每個 portal 共有四個獨立欄位，各管一件事：

```php
'ocadmin' => [                      // key：內部穩定識別碼
    'url_prefix'  => 'admin',       // 路由前綴：/{locale}/admin/...
    'role_prefix' => 'admin',       // 角色前綴：admin.*
    'dir'         => 'Ocadmin',     // 實作目錄：app/Portals/Ocadmin/
],
```

| 欄位 | 用途 | 必填 | 可獨立變更 |
|------|------|------|-----------|
| array key | 內部識別碼，config 索引 | ✓ | ✓ |
| `url_prefix` | 路由前綴，決定 path-based URL | 選填 | ✓ |
| `role_prefix` | 角色/權限命名前綴 | ✓ | ✓ |
| `dir` | 實作目錄名稱，一對一 | ✓ | ✓ |

`url_prefix` 僅適用於 **path-based portal**（如 `/{locale}/admin`）。Domain-based portal（如官網 `www.example.com`）不透過 URL 路徑區分，省略此欄位，路由由程式另行處理。

**設計原則：一個 key，一份設定，一個目錄。**

若多個 portal 需要相同的 `role_prefix` 或 `url_prefix`，各自明確宣告相同的值，而不是共用同一個 config 條目。明確勝於隱含，避免不同目錄被迫耦合在同一份設定。

**典型情境：**

```php
// 情境 1：目錄名與業務語意不同
// 目錄叫 Ocadmin，但使用者與角色皆用 admin
'ocadmin' => [
    'url_prefix'  => 'admin',
    'role_prefix' => 'admin',
    'dir'         => 'Ocadmin',
],

// 情境 2：客戶要求 URL 改為 /backend，但角色不動
'ocadmin' => [
    'url_prefix'  => 'backend',   // URL 換掉
    'role_prefix' => 'admin',     // 角色不換，既有授權不受影響
    'dir'         => 'Ocadmin',
],

// 情境 3：交付案中以新後台取代舊後台，過渡期並存
// 新後台接管 /admin，舊後台移至 /ocadmin 以便對比或保留存取
'ocadmin' => [
    'url_prefix'  => 'ocadmin',   // 舊後台移至 /ocadmin，讓出 /admin
    'role_prefix' => 'admin',     // 角色不變
    'dir'         => 'Ocadmin',
],
'adminlte' => [
    'url_prefix'  => 'admin',     // 新後台接管 /admin
    'role_prefix' => 'admin',     // 共用相同角色，明確宣告
    'dir'         => 'Adminlte',
],
```

---

## 三、URL 結構設計原則

### 3.1 基本結構

```
/{locale}/{url_prefix}/{module}/{resource}
```

- `{locale}`：語系前綴，如 `zh-hant`、`en`。即使單語系專案也保留此段，以便未來擴充及跨專案共用框架。
- `{url_prefix}`：Portal 識別，來自 `config/portals.php` 的 `url_prefix`。
- 其後為各 portal 自行定義的路由結構。

### 3.2 不建議將業務識別碼放入 URL 前綴

常見誤解：以為應該在 URL 中放入品牌代碼（如 `/{locale}/huabing/pos`）。

**不建議的原因：**

- 品牌是登入後的 session 上下文，不是應用入口的識別符
- 若品牌更名或調整，URL 需跟著改
- 管理後台（admin）本來就跨品牌，無法對應單一品牌 URL

**正確做法：** URL 只到 portal 層級，品牌由 session/auth 決定。

### 3.3 無語系前綴的重導

使用者通常不會直接輸入含 locale 的 URL，需設定重導（以 `url_prefix` 為準）：

```
/admin → /{default_locale}/admin
/hrm   → /{default_locale}/hrm
```

### 3.4 各 Portal 登入策略

**各 Portal 各自有登入頁（建議）：**

```
/{locale}/admin/login
/{locale}/hrm/login
/{locale}/pos-catering/login   ← 依專案實際 url_prefix
```

優點：
- 各 portal 完全自治，不依賴跨 portal 重導邏輯
- 設備（如門市平板）可直接書籤到正確入口
- 錯誤明確：帳號無此 portal 權限 → 403，而非靜默重導

**Auth Guard：** 多個 portal 可共用同一個 guard（如 `web`），差異在於登入後的 role_prefix 驗證，而非 guard 本身。

```php
// 各 portal 路由各自守門，guard 相同，role_prefix 不同
// requirePortalRole 參數為 config/portals.php 的 role_prefix
Route::middleware(['auth', 'requirePortalRole:admin'])->group(/* Ocadmin */);
Route::middleware(['auth', 'requirePortalRole:hrm'])->group(/* HRM */);
```

---

## 四、Ocadmin Portal

### 4.1 定位

**通用後台管理系統** — 固定制式的基底後台，可跨專案直接複用。

### 4.2 特性

| 項目 | 說明 |
|------|------|
| url_prefix | `admin` |
| role_prefix | `admin` |
| 存取角色 | `super_admin`（或 `admin.*` 角色） |
| 技術 | Blade 模板，固定版面 |
| URL | `/{locale}/admin` |

### 4.3 功能範圍

| 功能 | 權限 |
|------|------|
| 系統設定 | `admin.config.setting.*` |
| 詞彙管理 | `admin.config.taxonomy.*` |
| 使用者管理 | `admin.system_access.user.*` |
| 角色管理 | `admin.system_access.role.*` |

---

## 五、HRM Portal

### 5.1 定位

**人力資源管理系統** — 整合 ESS（員工自助）與 MSS（HR 管理），視覺設計隨專案客製。

### 5.2 特性

| 項目 | 說明 |
|------|------|
| url_prefix | `hrm` |
| role_prefix | `hrm` |
| 存取角色 | `hrm.*` 角色 |
| 技術 | Inertia + React（或其他前端框架） |
| URL | `/{locale}/hrm` |

### 5.3 模組分類

| 模組 | 前綴 | 目標使用者 | 資料範圍 |
|------|------|------------|----------|
| ESS | `hrm.ess.*` | 所有員工 | 僅自己 |
| MSS | `hrm.mss.*` | HR 人員 | 全公司 |
| Team | `hrm.team.*` | 部門主管 | 所屬部門 |

### 5.4 角色

| 角色 | 權限模組 |
|------|----------|
| `hrm.hr_manager` | `hrm.mss.*` + `hrm.team.*` + `hrm.ess.*` |
| `hrm.hr_operator` | `hrm.mss.*`（部分） + `hrm.ess.*` |
| `hrm.dept_manager` | `hrm.team.*` + `hrm.ess.*` |
| `hrm.employee` | `hrm.ess.*` |

---

## 六、其他 Portal（依專案擴充）

### 6.1 Web Portal（官網前台）

| 項目 | 說明 |
|------|------|
| url_prefix | 不適用（domain-based，省略） |
| role_prefix | `web`（或依專案定義） |
| 面向 | 大眾、客戶、經銷商 |
| 技術 | 前後端分離（API）或獨立前端 |
| URL | `www.example.com`（獨立網域，非 path prefix） |
| 範例權限 | `web.shop.order.access`, `web.account.profile.modify` |

### 6.2 POS Portal

POS 系統因各專案業務模型差異較大，`url_prefix` 與 `role_prefix` 由各專案自行定義。

常見模式：
- 單一 POS 類型：`pos`（`/{locale}/pos`）
- 多種 POS 類型各自獨立：`pos-catering`、`pos-retail` 等（各有獨立 url_prefix 與 role_prefix）

| 項目 | 說明 |
|------|------|
| url_prefix | 依專案定義，通常以 `pos` 為前綴 |
| role_prefix | 依專案定義，通常以 `pos` 為前綴 |
| 面向 | 門市人員 |
| 範例權限 | `pos.sale.order.modify`（或 `pos-catering.sale.order.modify`） |

---

## 七、Portal 總覽

| | Ocadmin | HRM | Web | POS |
|---|---------|-----|-----|-----|
| **url_prefix** | `admin` | `hrm` | —（domain-based） | 依專案 |
| **role_prefix** | `admin` | `hrm` | `web` | 依專案 |
| **面向** | 內部職員 | 員工/主管 | 大眾/客戶 | 門市人員 |
| **技術** | Blade | Inertia/React | API / 前端分離 | 依專案 |
| **角色** | `super_admin`, `admin.*` | `hrm.*` | `web.*` | 依專案 |
| **權限檢查** | 角色 or `can()` | `can()` + Wildcard | `can()` | `can()` |

---

## 相關文件

- [0104_權限機制.md](0104_權限機制.md) — 角色/權限命名規範、Spatie 設定、權限檢查方式

## 參考資料

- [Spatie Laravel Permission](https://spatie.be/docs/laravel-permission)
- [Laravel Authorization](https://laravel.com/docs/authorization)
