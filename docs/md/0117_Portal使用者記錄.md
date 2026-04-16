# Portal 使用者追蹤

> 建立日期：2026-02-26
> 修訂日期：2026-03-21

> ⚠️ **已棄用（2026-04-17）**：本機制（`acl_portal_users` 表、`PortalUser` Model、`syncFromRoles()`）已移除。
> 保留本文件作為歷史紀錄。移除理由、替代方案與後續建議見文末 [附錄：移除說明](#附錄移除說明)。

> 💡 **建議替代方案：[Spatie activitylog](https://github.com/spatie/laravel-activitylog)**
> 若需角色授撤歷史、敏感欄位變動、登入紀錄等稽核需求，改用 activitylog 是更通用且維護成本低的做法：
> - 自動掛 Eloquent events，無同步義務
> - 完整歷史（誰、何時、從什麼變成什麼），不是只有「最後一次」
> - 同一套機制可審計 role、setting、價格、權限等任何敏感變動

---

## 一、問題背景

### 1.1 現況缺失

LaravelOcadmin 使用 Spatie Laravel Permission 管理角色，角色資料分散在 `acl_model_has_roles` 中，**無法直觀回答以下問題**：

| 問題 | 現況 |
|------|------|
| 目前後台（admin portal）有哪些使用者？ | 需 join 角色表再比對前綴，查詢複雜 |
| 某使用者屬於哪些 portal？ | 需逐一比對所有角色前綴 |
| 這個管理帳號多久沒登入了？ | 無法查詢 |
| 使用者列表想按 portal 篩選？ | 無現成欄位可用 |

### 1.2 解決方案

引入 `acl_portal_users` 資料表，每個 `user_id + portal` 組合保留唯一一筆記錄（UNIQUE 約束），記錄該使用者是否為該 portal 的成員。角色移除時設定 `revoked_at`，重新授予時清空 `revoked_at`，不追蹤歷史變動過程。

**實際應用**：後台使用者列表頁（`/admin/system/users`）預設以 `portal = admin` 篩選，一眼看出目前後台有哪些使用者，也可切換查看其他 portal 的使用者。

---

## 二、Portal 識別與 Alias 機制

### 2.1 Portal 與目錄的關係

系統以 Portal 作為應用入口，每個 Portal 對應一組角色前綴。但實際的 Portal 目錄名稱可能因版本演進或技術棧不同而改變，多個目錄可能共用同一個 portal 識別碼：

```
app\Portals\Ocadmin    ─┐
app\Portals\OcadminV2  ─┤── portal = 'admin' ── admin.* 角色
app\Portals\Adminlte   ─┘

app\Portals\HRM        ─── portal = 'hrm'   ── hrm.* 角色
```

Portal 目錄名稱是實作層面的選擇，portal 識別碼才是邏輯上的身份。識別碼與角色前綴相同（`admin` → `admin.*`），對應關係天然存在，無需額外設定。

### 2.2 config/portals.php

透過設定檔定義 Portal 目錄名稱到 portal 識別碼的對應：

```php
// config/portals.php
return [
    'admin' => [
        'aliases' => ['Ocadmin', 'OcadminV2', 'Adminlte'],
    ],
    'hrm' => [
        'aliases' => ['HRM'],
    ],
    'www' => [
        'aliases' => ['WWW'],
    ],
    'pos' => [
        'aliases' => ['POS'],
    ],
];
```

**用途**：當系統需從 Portal 目錄名稱（如從 `App\Portals\Ocadmin` 命名空間取得的 `Ocadmin`）解析出 portal 識別碼時，透過此設定反查為 `'admin'`。

**注意**：此設定僅處理 Portal 目錄對應，不涉及角色定義。

### 2.3 解析方法

全域 helper function，定義於 `app/helpers.php`：

```php
function resolvePortal(string $directory): ?string
{
    foreach (config('portals') as $portal => $config) {
        if (in_array($directory, $config['aliases'] ?? [])) {
            return $portal;
        }
    }
    return null;
}

// 使用範例
resolvePortal('Ocadmin');    // 'admin'
resolvePortal('OcadminV2');  // 'admin'
resolvePortal('HRM');        // 'hrm'
```

---

## 三、資料表結構

### 3.1 acl_portal_users

```sql
CREATE TABLE acl_portal_users (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         BIGINT UNSIGNED,               -- users.id
    portal          VARCHAR(20) NOT NULL,           -- portal 識別碼（admin, hrm, www, pos）
    enrolled_at     TIMESTAMP NULL,                 -- 最近一次授予角色的時間
    revoked_at      TIMESTAMP NULL,                 -- 最近一次移除角色的時間（NULL 表示目前有效）
    last_login_at   TIMESTAMP NULL,                 -- 最後登入時間（該 portal）
    created_at      TIMESTAMP NULL,
    updated_at      TIMESTAMP NULL,

    UNIQUE (user_id, portal),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

**設計要點**：

- 每個 `user_id + portal` 組合唯一一筆記錄（UNIQUE 約束），不追蹤歷史變動過程
- 授予角色 → `enrolled_at = now(), revoked_at = NULL`（updateOrCreate）
- 移除角色 → `revoked_at = now()`
- 同一使用者可同時有 `admin` 和 `hrm` 兩筆記錄
- `super_admin` 不記錄在此表（見 3.3）

**資料範例**：

```
id | user_id | portal | enrolled_at | revoked_at  | 說明
1  | 1       | admin  | 2026-06     | NULL        | 目前有 admin 角色
2  | 1       | hrm    | 2026-06     | NULL        | 目前也有 hrm 角色
3  | 2       | admin  | 2026-02     | 2026-05     | 曾有 admin 角色，已撤銷
```

### 3.2 時間欄位分類

| 分類 | 欄位 | 觸發時機 | 用途 |
|------|------|---------|------|
| 角色生命週期 | `enrolled_at` | UserController 授予角色時 | 記錄加入時間 |
| 角色生命週期 | `revoked_at` | UserController 移除角色時 | 記錄移除時間 |
| 登入事件 | `last_login_at` | 登入成功時（僅更新對應 portal） | 帳號安全審計 |

### 3.3 super_admin 的處理

`super_admin` 是全域角色，不屬於任何特定 portal。當使用者被授予 `super_admin` 等無前綴角色時，`acl_portal_users` 會寫入一筆 `portal = 'global'` 的記錄。

- **portal 值**：`global`（定義於 `config/portals.php`，代表不屬於特定 portal 的全域角色）
- **存取權限**：`RequirePortalRole` middleware 已獨立判斷 `super_admin`，可進入任何 portal
- **列表篩選**：後台使用者列表篩選 `global` 可查看持有全域角色的使用者，篩選 `*` 可查看全部

---

## 四、Portal 角色檢查

### 4.1 RequirePortalRole Middleware

參數化的 middleware，可複用於不同 Portal：

```php
class RequirePortalRole
{
    public function handle(Request $request, Closure $next, string $portal): Response
    {
        $user = $request->user();

        if (!$user || !$user->hasPortalRole($portal)) {
            if ($request->expectsJson()) {
                abort(401, '未授權');
            }
            return redirect()->guest('/admin/login');
        }

        return $next($request);
    }
}
```

### 4.2 User Model 方法

```php
public function hasPortalRole(string $portal): bool
{
    if ($this->hasRole('super_admin')) {
        return true;
    }

    return $this->roles->contains(
        fn ($role) => str_starts_with($role->name, $portal . '.')
    );
}

// hasBackendRole() 保留為便捷別名
public function hasBackendRole(): bool
{
    return $this->hasPortalRole('admin');
}
```

### 4.3 Middleware 掛載

```php
// bootstrap/app.php
$middleware->alias([
    'requirePortalRole' => \App\Http\Middleware\RequirePortalRole::class,
]);

// Ocadmin 路由（portal = admin）
Route::middleware(['auth', 'requirePortalRole:admin'])->group(...);

// 未來 HRM Portal
Route::middleware(['auth', 'requirePortalRole:hrm'])->group(...);
```

---

## 五、角色連動規則

當 UserController 儲存使用者角色時，呼叫 `PortalUser::syncFromRoles($user)` 同步更新。

### 5.1 連動動作（Upsert 模式）

對指定 portal 執行：

| 情境 | 動作 |
|------|------|
| 有 `{portal}.*` 角色 | `updateOrCreate`：設 `enrolled_at = now(), revoked_at = null` |
| 無 `{portal}.*` 角色，有未撤銷記錄 | 設 `revoked_at = now()` |
| 無角色，無記錄或已撤銷 | 不動 |

### 5.2 程式碼

```php
class PortalUser extends Model
{
    protected $table = 'acl_portal_users';

    public static function syncFromRoles(User $user, string $prefix = 'admin'): void
    {
        $hasPrefixRoles = $user->roles->contains(
            fn ($role) => str_starts_with($role->name, $prefix . '.')
        );

        static::syncPortal($user, $prefix, $hasPrefixRoles);
    }

    protected static function syncPortal(User $user, string $portal, bool $hasRoles): void
    {
        if ($hasRoles) {
            // 有角色 → 啟用（新增或重新啟用）
            static::updateOrCreate(
                ['user_id' => $user->id, 'portal' => $portal],
                ['enrolled_at' => now(), 'revoked_at' => null],
            );
        } else {
            // 無角色 → 撤銷（若有未撤銷記錄才更新）
            static::where('user_id', $user->id)
                ->where('portal', $portal)
                ->whereNull('revoked_at')
                ->update(['revoked_at' => now()]);
        }
    }
}
```

### 5.3 登入事件更新

登入時僅更新對應 portal 的 `last_login_at`（單筆模式，不需 `whereNull('revoked_at')`）：

```php
// LoginController（Ocadmin portal = admin）
PortalUser::where('user_id', Auth::id())
    ->where('portal', 'admin')
    ->update(['last_login_at' => now()]);
```

---

## 六、追蹤範圍

### 6.1 初期追蹤

| portal | 角色 | 說明 |
|--------|------|------|
| `admin` | `admin.*` | 後台管理權限 |

其他 portal（`hrm`、`www`、`pos`）待實際建立時再啟用追蹤。

### 6.2 常用查詢

```sql
-- 目前有效的所有後台管理者（使用者列表頁預設篩選）
SELECT * FROM acl_portal_users WHERE portal = 'admin' AND revoked_at IS NULL;

-- 某使用者屬於哪些 portal
SELECT * FROM acl_portal_users WHERE user_id = ? AND revoked_at IS NULL;

-- 曾經有過後台權限但目前已撤銷的使用者
SELECT * FROM acl_portal_users WHERE portal = 'admin' AND revoked_at IS NOT NULL;

-- 超過 90 天未登入的 active 後台管理者
SELECT * FROM acl_portal_users
WHERE portal = 'admin' AND revoked_at IS NULL AND last_login_at < NOW() - INTERVAL 90 DAY;
```

---

## 七、權限檢查流程

```
1. 使用者登入
   └── 更新該使用者對應 portal 的 last_login_at

2. 進入 Portal
   └── RequirePortalRole:admin middleware
       ├── super_admin → 放行
       ├── 有任一 admin.* 角色 → 放行
       └── 其他 → redirect 或 401

3. 操作功能
   └── Spatie Permission 個別權限檢查 can('...')
```

---

## 八、實作項目

| # | 項目 | 狀態 |
|---|------|------|
| 1 | Migration：建立 `acl_portal_users` 資料表（UNIQUE 約束，取代 `acl_system_users`） | done |
| 2 | Model：`App\Models\Acl\PortalUser`（upsert 模式 `syncFromRoles()` / `syncPortal()`） | done |
| 3 | Config：`config/portals.php` Portal alias 設定 | done |
| 4 | `RequirePortalRole` middleware | done |
| 5 | `User::hasPortalRole($portal)` 方法，`hasBackendRole()` 保留為別名 | done |
| 6 | `bootstrap/app.php` 註冊 `requirePortalRole` alias | done |
| 7 | Ocadmin 路由掛 `requirePortalRole:admin` | done |
| 8 | UserController：角色儲存時連動 `acl_portal_users` + 列表頁 portal 篩選 | done |
| 9 | 登入事件：更新 `last_login_at`（限定 portal） | done |
| 10 | 移除舊 `acl_system_users` 資料表與 `SystemUser` Model | done |

---

## 相關文件

- [0104_權限機制.md](0104_權限機制.md) — 角色/權限命名規範、Spatie 設定
- [0105_Portal概述.md](0105_Portal概述.md) — Portal 架構與存取控制

## 參考資料

- [Spatie Laravel Permission](https://spatie.be/docs/laravel-permission)

---

## 附錄：移除說明

本機制於 2026-04-17（commit `9f9d5d6`）移除。

### 為什麼移除

重新檢視後發現 `acl_portal_users` 將兩類資訊混在同一張表：

| 資訊 | 來源 | 備註 |
|------|------|------|
| `user_id + portal` 成員關係 | 可由 roles 推導 | 冗餘快取 |
| `enrolled_at` | 可由 `acl_model_has_roles.created_at` 推導 | 冗餘快取 |
| `revoked_at` | Spatie 是硬刪除，此為新資訊 | 但只保留「最後一次」，是 lossy 的 |
| `last_login_at` | 新資訊 | 真正需要獨立儲存 |

主要問題：

1. **同步負擔**：每次角色變動必須呼叫 `syncFromRoles()`，任何繞過路徑（直接 DB、console、package 升級）都會讓快取 drift
2. **稽核能力薄弱**：`revoked_at` 只保留最後一次撤銷時間，無法回答「撤了哪個具體角色、由誰、為什麼、總共幾次」
3. **語意模糊**：`enrolled_at` 名義是「加入」，實際會被任何 `admin.*` 角色變動覆寫
4. **效能論點不成立**：後台管理員規模下，`roles.name LIKE 'admin.%'` 的 JOIN 完全可接受，不需要 denormalization

### 後續機制如何替代

| 原功能 | 替代方式 |
|--------|----------|
| 列表按 portal 篩選 | `whereHas('roles', fn($q) => $q->where('name', 'like', $portal.'.%'))` |
| 顯示 user 屬於哪些 portal | `User::derivedPortals()` 從 roles 名稱推導 |
| 最後登入時間 | `users.last_login_at` 欄位 + `UpdateLastLoginAt` listener（掛 `Illuminate\Auth\Events\Login`） |
| 角色授撤歷史稽核 | **尚未實作**，建議導入 [Spatie activitylog](https://github.com/spatie/laravel-activitylog) |

### 相關變更

- 新增：`app/Listeners/UpdateLastLoginAt.php`、`users.last_login_at` 欄位
- 移除：`app/Models/Acl/PortalUser.php`、`acl_portal_users` migration 與 schema、`LoginController` 的 last_login 更新邏輯、`UserController` 的 `syncFromRoles()` 呼叫
- 參考：[0128_全域帳號.md](0128_全域帳號.md)
