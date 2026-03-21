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
    admin.*          hrm.*           www.*            pos.*
```

每個 Portal：
- 有自己的**角色**（以 Portal 名稱為前綴，如 `admin.*`、`hrm.*`）
- 有自己的**權限**（`{portal}.{module}.{resource}.{action}`）
- 可以有獨立的**技術棧**（Blade、Inertia/React、純 API）
- 可以有獨立的**URL 前綴**與**認證方式**

### 1.2 Portal 列表

| Portal | 前綴 | 面向對象 | 說明 |
|--------|------|----------|------|
| Ocadmin | `admin` | 公司內部職員 | 通用後台管理，跨專案複用 |
| HRM | `hrm` | 員工 / 主管 | 人力資源管理（ESS + MSS） |
| WWW | `www` | 大眾 / 客戶 / 經銷商 | 官網或系統前台 |
| POS | `pos` | 門市人員 | POS 銷售系統 |

> Portal 可依專案需求增減。以上為常見配置，並非全部都要實作。

---

## 二、角色與權限的 Portal 前綴

角色與權限皆帶有 Portal 前綴，確保不同入口之間的隔離性：

| 項目 | 格式 | 範例 |
|------|------|------|
| 角色 | `{portal}.{role_name}` | `hrm.hr_manager`, `admin.operator` |
| 權限 | `{portal}.{module}.{resource}.{action}` | `hrm.mss.employee.access`, `admin.config.setting.modify` |
| 全域角色（例外） | `{role_name}` | `super_admin`（跨 Portal） |

### 2.1 Portal 存取判斷

透過角色前綴判斷使用者可進入哪些 Portal：

```php
// User Model
public function hasPortalRole(string $portal): bool
{
    if ($this->hasRole('super_admin')) {
        return true;
    }

    return $this->roles->contains(
        fn ($role) => str_starts_with($role->name, $portal . '.')
    );
}

// 使用
$user->hasPortalRole('admin');  // 是否可進入 Ocadmin
$user->hasPortalRole('hrm');    // 是否可進入 HRM Portal
```

### 2.2 Middleware

```php
// 路由級 Portal 守門
Route::middleware(['auth', 'requirePortalRole:admin'])->group(/* Ocadmin 路由 */);
Route::middleware(['auth', 'requirePortalRole:hrm'])->group(/* HRM 路由 */);
```

### 2.3 Portal Alias 與版本演進

`config/portals.php` 將 Portal 目錄名稱對應到 portal 識別碼，同一個 portal 可以有多個目錄別名：

```php
'admin' => [
    'aliases' => ['Admin', 'Ocadmin'],
],
```

這讓 Portal 可以方便地進行大版本改版。例如原本使用 `app/Portals/Admin`，要做較大改版但又想暫時維持原有程式運作，可以：

1. 建立新目錄 `app/Portals/AdminV2`
2. 在 `config/portals.php` 的 aliases 加入 `'AdminV2'`
3. 主要索引仍然是 `admin`，角色前綴也仍然是 `admin.*`

新舊兩套 Portal 程式共用同一組角色與權限，切換時只需調整路由指向即可，不影響使用者的角色授權。

---

## 三、Ocadmin Portal

### 3.1 定位

**通用後台管理系統** — 固定制式的基底後台，可跨專案直接複用。

### 3.2 特性

| 項目 | 說明 |
|------|------|
| 前綴 | `admin` |
| 存取角色 | `super_admin`（或 `admin.*` 角色） |
| 技術 | Blade 模板，固定版面 |
| URL | `/{locale}/admin` |

### 3.3 功能範圍

| 功能 | 權限 |
|------|------|
| 系統設定 | `admin.config.setting.*` |
| 詞彙管理 | `admin.config.taxonomy.*` |
| 使用者管理 | `admin.system_access.user.*` |
| 角色管理 | `admin.system_access.role.*` |

---

## 四、HRM Portal

### 4.1 定位

**人力資源管理系統** — 整合 ESS（員工自助）與 MSS（HR 管理），視覺設計隨專案客製。

### 4.2 特性

| 項目 | 說明 |
|------|------|
| 前綴 | `hrm` |
| 存取角色 | `hrm.*` 角色 |
| 技術 | Inertia + React（或其他前端框架） |
| URL | 由各專案自行定義 |

### 4.3 模組分類

| 模組 | 前綴 | 目標使用者 | 資料範圍 |
|------|------|------------|----------|
| ESS | `hrm.ess.*` | 所有員工 | 僅自己 |
| MSS | `hrm.mss.*` | HR 人員 | 全公司 |
| Team | `hrm.team.*` | 部門主管 | 所屬部門 |

### 4.4 角色

| 角色 | 權限模組 |
|------|----------|
| `hrm.hr_manager` | `hrm.mss.*` + `hrm.team.*` + `hrm.ess.*` |
| `hrm.hr_operator` | `hrm.mss.*`（部分） + `hrm.ess.*` |
| `hrm.dept_manager` | `hrm.team.*` + `hrm.ess.*` |
| `hrm.employee` | `hrm.ess.*` |

---

## 五、其他 Portal（依專案擴充）

### 5.1 WWW Portal

| 項目 | 說明 |
|------|------|
| 前綴 | `www` |
| 面向 | 大眾、客戶、經銷商 |
| 技術 | 前後端分離（API） |
| 範例權限 | `www.shop.order.access`, `www.account.profile.modify` |

### 5.2 POS Portal

| 項目 | 說明 |
|------|------|
| 前綴 | `pos` |
| 面向 | 門市人員 |
| 技術 | 前後端分離（API） |
| 範例權限 | `pos.sale.order.modify`, `pos.sale.order.export` |

---

## 六、Portal 總覽

| | Ocadmin | HRM | WWW | POS |
|---|---------|-----|-----|-----|
| **前綴** | `admin` | `hrm` | `www` | `pos` |
| **面向** | 內部職員 | 員工/主管 | 大眾/客戶 | 門市人員 |
| **技術** | Blade | Inertia/React | API | API |
| **角色** | `super_admin`, `admin.*` | `hrm.*` | `www.*` | `pos.*` |
| **權限檢查** | 角色 or `can()` | `can()` + Wildcard | `can()` | `can()` |

---

## 相關文件

- [0104_權限機制.md](0104_權限機制.md) — 角色/權限命名規範、Spatie 設定、權限檢查方式

## 參考資料

- [Spatie Laravel Permission](https://spatie.be/docs/laravel-permission)
- [Laravel Authorization](https://laravel.com/docs/authorization)
