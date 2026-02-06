# Portal 概述

> 建立日期：2026-02

---

## 一、架構決策

### 1.1 方案選擇

**Ocadmin（super_admin 專用）+ App Portal（ESS + MSS 合一）**

```
┌──────────────────────────┐     ┌──────────────────────────────────┐
│    Ocadmin Portal        │     │         App Portal               │
│    (通用基底後台)          │     │    (專案應用，Inertia/React)      │
│                          │     │                                  │
│  ● super_admin 專用       │     │  ● ess.hr_* 角色 → MSS 管理功能  │
│  ● Blade 模板，固定制式    │     │  ● ess.* 角色 → ESS 員工功能     │
│  ● 系統設定、角色、詞彙    │     │  ● 同一介面，靠權限控制可見性     │
│  ● 跨專案直接複用          │     │  ● 視覺設計隨專案客製            │
│                          │     │                                  │
│  URL: /{locale}/admin    │     │  URL: 由各專案自行定義            │
└──────────────────────────┘     └──────────────────────────────────┘
```

### 1.2 為什麼不分 ESS 和 MSS 兩個 Portal

| 考量 | 說明 |
|------|------|
| 主管也是員工 | 部門主管需要打卡、請假（ESS）也需要審核下屬（MSS），不應切換 Portal |
| MSS 是 ESS 的延伸 | MSS 功能 = ESS 功能 + 管理功能，差別僅在「看到的選單多寡」 |
| 開發維護成本 | 一個 Inertia/React 應用，比兩個獨立應用更好維護 |
| 使用者體驗 | 同一介面依角色顯示不同功能，不需記住多個入口 |

### 1.3 為什麼 Ocadmin 不做 MSS

| 考量 | 說明 |
|------|------|
| 通用基底 | Ocadmin 是固定制式的通用後台，可跨專案複用 |
| 視覺客製 | 每個專案的業務介面可能有不同設計需求，Ocadmin 的固定版面不適合 |
| 職責分離 | Ocadmin 管「系統組態」，App Portal 管「業務操作」 |
| 技術棧差異 | Ocadmin 用 Blade，App Portal 用 Inertia/React，各自適合的場景不同 |

---

## 二、Ocadmin Portal

### 2.1 定位

**系統組態管理後台** — 通用基底，跨專案複用。

### 2.2 特性

| 項目 | 說明 |
|------|------|
| 存取角色 | 僅 `super_admin` |
| 技術 | Blade 模板，固定版面（sidebar + 內容區） |
| URL | `/{locale}/admin` |
| 存取控制 | 整個 Portal 只檢查 `super_admin` 角色 |

### 2.3 功能範圍

| 功能 | 說明 |
|------|------|
| 儀表板 | 系統概覽 |
| 系統設定 | 參數管理 (`config.setting`) |
| 詞彙管理 | Taxonomy + Term (`config.taxonomy`, `config.term`) |
| 使用者管理 | 帳號建立、角色指派 (`system_access.user`) |
| 角色管理 | 角色定義、權限配置 (`system_access.role`) |

### 2.4 存取控制

Ocadmin 不使用細粒度權限，整個 Portal 只做一件事：

```php
// Ocadmin 路由中
Route::middleware('auth')->group(function () {
    // 所有 Ocadmin 路由都檢查 super_admin
    if (!auth()->user()->hasRole('super_admin')) {
        abort(403);
    }
    // ...
});
```

或透過 Middleware：

```php
// app/Http/Middleware/EnsureSuperAdmin.php

class EnsureSuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()?->hasRole('super_admin')) {
            abort(403);
        }

        return $next($request);
    }
}
```

```php
// Ocadmin 路由
Route::middleware(['auth', 'super_admin'])->group(function () {
    // ...
});
```

### 2.5 預設帳號

| 欄位 | 值 |
|------|------|
| Email | `admin@example.com` |
| Username | `admin` |
| Password | `123456` |
| 角色 | `super_admin` |

---

## 三、App Portal

### 3.1 定位

**專案業務應用** — 整合 ESS（員工自助服務）與 MSS（主管自助服務），以 Inertia/React 建構，視覺設計隨專案客製。

### 3.2 特性

| 項目 | 說明 |
|------|------|
| 存取角色 | 所有 `ess.*` 角色 |
| 技術 | Inertia + React（或另建獨立 Portal） |
| URL | 由各專案自行定義 |
| 存取控制 | 純權限檢查 `can('permission.name')`，搭配 Spatie Wildcard |

### 3.3 角色與功能對應

```
App Portal（所有角色皆為 ess.*）
│
├── HR 管理角色（MSS 功能）
│   ├── ess.hr_manager       → 所有 HR 管理功能
│   ├── ess.hr_operator      → 日常 HR 操作
│   └── ess.payroll_manager  → 薪資管理
│
└── 員工自助角色（ESS 功能）
    ├── ess.dept_manager     → ESS + 審核下屬、檢視部門
    └── ess.employee         → 個人資料、打卡、請假、薪資單
```

同一個使用者可能同時擁有多個 `ess.*` 角色，在同一介面看到所有被授權的功能。

### 3.4 存取控制

App Portal 完全依靠**個別權限**控制，不做角色前綴判斷。

資料範圍由權限名稱隱含：

| 權限前綴 | 資料範圍 | 說明 |
|----------|---------|------|
| `employee.*`、`leave.*` 等 | 全公司 | HR 管理權限 |
| `ess_team.*` | 同部門 | 部門主管權限 |
| `ess_profile.*`、`ess_leave.*` 等 | 僅自己 | 員工自助權限 |

```php
// Policy 範例 — 直接用 can() 判斷，不需 hasAdminAccess()
public function view(User $user, Employee $employee): bool
{
    // 有管理權限 → 可看全部
    if ($user->can('employee.employee.read')) {
        return true;
    }

    // 查看自己
    if ($employee->user_id === $user->id) {
        return true;
    }

    // 部門主管查看下屬
    if ($user->can('ess_team.employee.list')) {
        return $employee->department_id === $user->employee?->department_id;
    }

    return false;
}
```

### 3.5 功能範圍

#### MSS 功能（HR 管理角色）

| 功能模組 | 說明 |
|----------|------|
| 員工管理 | 新增、編輯、檢視員工資料 |
| 部門管理 | 部門設定 |
| 職稱管理 | 職稱設定 |
| 出勤管理 | 全公司出勤紀錄檢視、修改 |
| 請假管理 | 全公司請假審核 |
| 假別管理 | 假別設定 |
| 國定假日 | 國定假日設定 |
| 薪資管理 | 薪資計算、薪資單產生 |
| 班別管理 | 班別設定 |

#### ESS 功能（員工自助角色）

| 功能模組 | 說明 |
|----------|------|
| 個人資料 | 檢視/修改自己的資料 |
| 打卡 | 上下班打卡 |
| 請假申請 | 申請請假 |
| 個人出勤 | 檢視自己的出勤紀錄 |
| 個人薪資單 | 檢視自己的薪資單 |
| 部門管理（主管） | 檢視部門成員、審核下屬請假 |

---

## 四、Portal 判斷

### 4.1 User Model 方法

```php
// app/Models/User.php

/**
 * 是否有 App Portal 存取權（任何 ess.* 角色）
 */
public function hasAppAccess(): bool
{
    if ($this->hasRole('super_admin')) {
        return true;
    }

    return $this->roles->contains(
        fn ($role) => str_starts_with($role->name, 'ess.')
    );
}
```

> **注意**：App Portal 不再使用 `hasAdminAccess()` 或 `hasEssAccess()` 做分層判斷。
> 功能存取完全由個別權限 `can('permission.name')` 控制。
> `hasAppAccess()` 僅用於判斷「使用者是否可以進入 App Portal」。

### 4.2 前端選單控制

App Portal 的側邊欄依權限動態顯示：

```tsx
// 範例：選單項目根據權限顯示
const menuItems = [
    // ESS — 所有人都看得到
    { label: '個人資料', permission: 'ess_profile.profile.read' },
    { label: '打卡', permission: 'ess_attendance.attendance.create' },
    { label: '請假申請', permission: 'ess_leave.leave.create' },
    { label: '薪資單', permission: 'ess_payroll.payslip.list' },

    // MSS — 部門主管
    { label: '部門成員', permission: 'ess_team.employee.list' },
    { label: '請假審核', permission: 'ess_leave.leave.approve' },

    // MSS — HR 管理
    { label: '員工管理', permission: 'employee.employee.list' },
    { label: '出勤管理', permission: 'attendance.attendance.list' },
    { label: '薪資管理', permission: 'payroll.payroll.list' },
];

// 過濾出有權限的項目
const visibleMenu = menuItems.filter(item => can(item.permission));
```

---

## 五、資料權限場景

### 5.1 組織結構與可見範圍

```
公司
├── HR 部門
│   ├── HR 主管 (ess.hr_manager + ess.employee)  → 管理所有員工
│   └── HR 管理員 (ess.hr_operator + ess.employee) → 管理所有員工
├── 業務部
│   ├── 部門主管 (ess.dept_manager + ess.employee)  → 看自己 + 部門內
│   ├── 員工 A (ess.employee)                        → 只看自己
│   └── 員工 B (ess.employee)                        → 只看自己
└── 研發部
    ├── 部門主管 (ess.dept_manager + ess.employee)  → 看自己 + 部門內
    └── 員工 C (ess.employee)                        → 只看自己
```

### 5.2 可見範圍規則

| 角色 | 出勤/請假/薪資可見範圍 |
|------|----------------------|
| `super_admin` | 全部（但通常不在 App Portal 操作） |
| `ess.hr_manager` | 全公司所有員工 |
| `ess.hr_operator` | 全公司所有員工（依權限範圍） |
| `ess.dept_manager` | 自己 + 同部門員工 |
| `ess.employee` | 僅自己 |

---

## 六、Portal 總覽

| | Ocadmin | App Portal |
|---|---------|------------|
| **定位** | 系統組態管理 | 業務應用（ESS + MSS） |
| **角色** | `super_admin` only | `ess.*` |
| **技術** | Blade | Inertia/React |
| **版面** | 固定制式 | 隨專案客製 |
| **權限檢查** | 僅角色（`super_admin`） | 個別權限 `can()` + Wildcard |
| **跨專案** | 直接複用 | 各專案獨立開發 |
| **URL** | `/{locale}/admin` | 專案自訂 |

---

## 相關文件

- [0104_權限機制.md](0104_權限機制.md) — 角色/權限命名規範、Spatie 設定、權限檢查方式

## 參考資料

- [Spatie Laravel Permission](https://spatie.be/docs/laravel-permission)
- [Laravel Authorization](https://laravel.com/docs/authorization)
