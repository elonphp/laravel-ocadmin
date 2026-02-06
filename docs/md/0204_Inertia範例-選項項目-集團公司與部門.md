# 集團架構 — 公司與部門

## 背景

目前系統的 `organizations` 表定位為**外部往來對象**（經銷商、客戶、供應商），`hrm_employees` 的 `organization_id` 語意不正確——員工是內部人員，不應歸屬於外部對象。

此外，`department` 目前是純文字欄位，無法建立階層關係，也無法統一管理。

本文件規劃「集團架構」，將**內部公司**與**外部對象**明確分離，並支援**多集團 / 獨立公司**共用同一套系統。

---

## 實際營運情境

```
本系統（單一部署）
│
├── 天行集團（母公司）             ← 集團，底下子公司財報合併
│   ├── 星河科技                       ← 子公司
│   └── 雲端數位                       ← 子公司
│
└── 晨光創意                         ← 獨立運作，共用 IT 資源
```

**設計目標**：
- 集團母公司 + 子公司 → `parent_id` 自引用，一張 `companies` 表搞定
- 獨立公司 → `parent_id = null` 且無 children，自然就是獨立公司
- 資料隔離 → 靠 `company_user` 中間表控制每個帳號能存取哪些公司

---

## 架構總覽

```
內部（集團 / 公司）                         外部（往來對象）
──────────────                            ─────────
companies ← 集團母公司 / 子公司 / 獨立公司    organizations ← 經銷商/客戶/供應商
  ├── children (self-ref parent_id)           （維持現狀，不異動）
  └── departments ← 公司下的部門
        └── employees ← 部門下的員工

↕ 兩者獨立，不互相關聯
```

### 對照表

| | companies（內部） | organizations（外部） |
|---|---|---|
| 代表 | 集團內法人公司 / 獨立公司 | 經銷商、客戶、供應商 |
| 階層 | 有（parent_id 自引用，母公司→子公司） | 無 |
| 身分 | 無需（都是自家公司） | OrganizationIdentity enum |
| 統編 | 有 | 有 |
| 部門 | 有（departments 表） | 無 |
| 員工 | 有（employees 歸屬） | 無 |
| 翻譯 | 有（HasTranslation） | 有（HasTranslation） |

### 公司類型判斷

| 情境 | 判斷方式 |
|------|---------|
| 集團母公司 | `parent_id = null` 且有 children |
| 子公司 | `parent_id != null` |
| 獨立公司 | `parent_id = null` 且無 children |
| 合併財報範圍 | 母公司 + 所有 children |

---

## 第一部分：User 與 Employee 的關係

在設計公司存取機制前，必須先釐清 `users` 和 `hrm_employees` 的關係。

### 1.1 兩者獨立

```
users（系統帳號）                   hrm_employees（員工資料）
├── id                              ├── id
├── username                        ├── user_id  → nullable FK to users
├── email                           ├── company_id
├── password                        ├── department_id
└── ...                             └── ...
```

### 1.2 四種情境

| 情境 | user | employee | 範例 |
|------|------|----------|------|
| 有帳號、是員工 | ✓ | ✓ (`user_id` 有值) | 一般職員 |
| 有帳號、非員工 | ✓ | ✗ | IT 管理員、外部顧問、老闆 |
| 無帳號、是員工 | ✗ | ✓ (`user_id = null`) | 工廠作業員、工讀生 |
| 無帳號、非員工 | — | — | 不在系統內 |

### 1.3 歸屬 vs 存取

兩個不同概念，分開處理：

```
歸屬（HR 關係）：employee.company_id = 2      → 這個人「在星河科技上班」
存取（系統權限）：company_user: user_id → [1,2,3]  → 這個帳號「能看到哪些公司的資料」
```

- **歸屬**掛在 `employees.company_id`（員工屬於哪間公司）
- **存取**掛在 `company_user`（帳號能操作哪些公司）
- 沒有帳號的員工不進 `company_user`（他不登入系統）
- 沒有員工記錄的帳號也能透過 `company_user` 存取公司資料

### 1.4 存取範例

```
company_user（系統存取權）
├── IT管理員 (user, 非員工)   → [天行集團, 星河科技, 雲端數位, 晨光創意]  ← 全部
├── 集團HR  (user + 員工)    → [天行集團, 星河科技, 雲端數位]         ← 集團內
├── 晨光員工 (user + 員工)    → [晨光創意]                           ← 只有自家
└── 工讀生   (員工, 無 user)  → 沒有帳號，不進 company_user
```

---

## 第二部分：companies 表

### 2.1 Migration

**檔案**：`database/migrations/xxxx_create_companies_table.php`

```php
// companies — 集團 / 公司（自引用階層）
Schema::create('companies', function (Blueprint $table) {
    $table->id();
    $table->foreignId('parent_id')->nullable()
          ->constrained('companies')->nullOnDelete();    // 上級公司（母公司）
    $table->string('code', 20)->nullable()->unique();    // 公司代碼（如 TX, XH, YD）
    $table->string('business_no', 20)->nullable();        // 統一編號
    $table->string('phone', 30)->nullable();
    $table->string('address')->nullable();
    $table->boolean('is_active')->default(true);
    $table->integer('sort_order')->default(0);
    $table->timestamps();
});

// company_translations — 多語名稱
Schema::create('company_translations', function (Blueprint $table) {
    $table->id();
    $table->foreignId('company_id')->constrained()->cascadeOnDelete();
    $table->string('locale', 10);
    $table->string('name', 200);                          // 公司名稱
    $table->string('short_name', 100)->nullable();        // 簡稱
    $table->unique(['company_id', 'locale']);
});
```

**設計說明**：

- `parent_id`：上級公司（母公司），nullable（頂層無上級），nullOnDelete
- `code`：內部代碼，方便識別（非統編），如 `TX`、`XH`、`CG`
- 結構參考現有 `organizations` + `organization_translations`，保持一致
- 使用 `HasTranslation` trait，與 Organization 相同模式

### 2.2 Model

**檔案**：`app/Models/Company.php`

```php
namespace App\Models;

use App\Traits\HasTranslation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use HasTranslation;

    protected $fillable = [
        'parent_id', 'code', 'business_no', 'phone', 'address',
        'is_active', 'sort_order',
    ];

    protected $translatedAttributes = ['name', 'short_name'];
    protected $translationModel = CompanyTranslation::class;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    // ── 自引用階層 ──

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Company::class, 'parent_id');
    }

    // ── 下屬資料 ──

    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    public function employees(): HasMany
    {
        return $this->hasMany(\App\Models\Hrm\Employee::class);
    }

    // ── 使用者存取 ──

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
}
```

### 2.3 公司階層範例

```
天行集團 (id=1, parent_id=null)          ← 集團母公司
├── 星河科技   (id=2, parent_id=1)            ← 子公司
└── 雲端數位   (id=3, parent_id=1)            ← 子公司

晨光創意     (id=4, parent_id=null)          ← 獨立公司（無 children）
```

---

## 第三部分：departments 表

### 3.1 Migration

**檔案**：`database/migrations/xxxx_create_departments_table.php`

```php
Schema::create('departments', function (Blueprint $table) {
    $table->id();
    $table->foreignId('company_id')->constrained()->cascadeOnDelete();
    $table->foreignId('parent_id')->nullable()
          ->constrained('departments')->nullOnDelete();   // 上級部門
    $table->string('name', 100);                           // 部門名稱
    $table->string('code', 20)->nullable();                // 部門代碼
    $table->boolean('is_active')->default(true);
    $table->integer('sort_order')->default(0);
    $table->timestamps();
});
```

**設計說明**：

| 欄位 | 說明 |
|------|------|
| `company_id` | 歸屬哪間公司，cascadeOnDelete（公司刪除時部門一起刪） |
| `parent_id` | 上級部門，nullable（頂層部門無上級），nullOnDelete |
| `name` | 暫不做多語翻譯，部門名稱通常不需要（如需要可日後加 translations 表） |
| `code` | 內部代碼，如 `RD`、`HR`、`FIN` |

### 3.2 Model

**檔案**：`app/Models/Department.php`

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    protected $fillable = [
        'company_id', 'parent_id', 'name', 'code',
        'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Department::class, 'parent_id');
    }

    public function employees(): HasMany
    {
        return $this->hasMany(\App\Models\Hrm\Employee::class);
    }
}
```

### 3.3 樹狀結構範例

```
星河科技 (company_id=2)
├── 管理部     (id=1, parent_id=null)
├── 研發部     (id=2, parent_id=null)
│   ├── 前端組 (id=3, parent_id=2)
│   └── 後端組 (id=4, parent_id=2)
└── 業務部     (id=5, parent_id=null)

雲端數位 (company_id=3)
├── 營運部     (id=6, parent_id=null)
└── 客服部     (id=7, parent_id=null)

晨光創意 (company_id=4)
├── 行銷部     (id=8, parent_id=null)
└── 業務部     (id=9, parent_id=null)
```

---

## 第四部分：employees 表調整

### 4.1 Migration（修改）

```php
// 移除舊欄位，新增 company_id / department_id
Schema::table('hrm_employees', function (Blueprint $table) {
    // 新增
    $table->foreignId('company_id')->nullable()
          ->after('user_id')
          ->constrained()->nullOnDelete();
    $table->foreignId('department_id')->nullable()
          ->after('company_id')
          ->constrained()->nullOnDelete();

    // 移除
    $table->dropForeign(['organization_id']);
    $table->dropColumn('organization_id');
    $table->dropColumn('department');
});
```

### 4.2 欄位變更對照

```diff
 hrm_employees
   user_id           → 維持（系統帳號，nullable）
-  organization_id   → 移除（外部對象，不適用）
+  company_id        → 新增（歸屬哪間公司）
+  department_id     → 新增（歸屬哪個部門）
   employee_no       → 維持
   first_name        → 維持
   last_name         → 維持
   email             → 維持
   phone             → 維持
   hire_date         → 維持
   birth_date        → 維持
   gender            → 維持（Gender enum）
   job_title         → 維持
-  department        → 移除（改用 department_id FK）
   address           → 維持
   note              → 維持
   is_active         → 維持
```

### 4.3 Model 調整

```php
// app/Models/Hrm/Employee.php

use App\Models\Company;
use App\Models\Department;

// fillable 調整
- 'organization_id',
- 'department',
+ 'company_id',
+ 'department_id',

// 關聯調整
- public function organization(): BelongsTo
+ public function company(): BelongsTo
  {
-     return $this->belongsTo(Organization::class);
+     return $this->belongsTo(Company::class);
  }

+ public function department(): BelongsTo
+ {
+     return $this->belongsTo(Department::class);
+ }
```

---

## 第五部分：company_user 中間表

### 5.1 Migration

```php
// company_user — 使用者可存取哪些公司
Schema::create('company_user', function (Blueprint $table) {
    $table->foreignId('company_id')->constrained()->cascadeOnDelete();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->primary(['company_id', 'user_id']);
});
```

### 5.2 User Model 調整

```php
// app/Models/User.php

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

public function companies(): BelongsToMany
{
    return $this->belongsToMany(Company::class);
}
```

### 5.3 用途說明

`company_user` 控制的是**系統存取權**，不是 HR 歸屬：

| 概念 | 儲存位置 | 意義 |
|------|---------|------|
| 員工歸屬 | `employees.company_id` | 這個人在哪間公司上班 |
| 帳號存取 | `company_user` | 這個帳號能看到哪些公司的資料 |

一個 user 可以存取多間公司（如集團 HR），一個 company 也可以有多個 user 存取。

---

## 第六部分：「當前公司」機制

使用者登入後，需要知道目前操作的是哪間公司。

### 6.1 概念

```
User 登入
  ↓
查 company_user，取得可存取的公司列表
  ↓
自動選擇第一間（或上次選擇的）作為「當前公司」
  ↓
存入 session
  ↓
所有查詢自動加 where company_id = 當前公司
```

### 6.2 Session 管理

```php
// 存入 session
session(['current_company_id' => $companyId]);

// 取得當前公司（可封裝為 helper）
function currentCompanyId(): ?int
{
    return session('current_company_id');
}
```

### 6.3 公司切換 UI

Ocadmin / ESS 的 header 區域提供公司切換下拉選單：

```
┌──────────────────────────────────────────┐
│  [星河科技 ▾]              使用者名稱  登出  │
│──────────────────────────────────────────│
│  側邊欄 ...              主內容區 ...      │
```

下拉選單內容來自 `company_user`，只顯示該使用者有權存取的公司。選擇不同公司後，重新載入頁面，所有資料切換為該公司的資料。

---

## 第七部分：受影響的現有程式

### 7.1 需修改的檔案

| 檔案 | 變更 |
|------|------|
| `Employee` model | `organization()` → `company()`, 新增 `department()` |
| `User` model | 新增 `companies()` 多對多關聯 |
| `EmployeeController`（Ocadmin） | 表單下拉改為 companies + departments（二級連動） |
| `form.blade.php`（Ocadmin） | 關聯資料 Tab：organization select → company select + department select |
| `list.blade.php`（Ocadmin） | 組織欄位 → 公司 + 部門 |
| `ProfileController`（ESS） | employee->only() 加入 company/department 關聯資料 |
| `Edit.tsx`（ESS） | 唯讀區塊顯示公司名稱與部門名稱 |
| `EmployeeSeeder` | 改為建立 companies + departments 後，隨機指派 |
| `HandleEssInertiaRequests` | share 當前公司資訊 |
| `MenuComposer`（Ocadmin） | 側邊欄調整（見下方） |
| 語言檔 | 新增 company / department 相關翻譯 |

### 7.2 不受影響

| 檔案 | 原因 |
|------|------|
| `organizations` 表 / Model | 完全不動，繼續作為外部往來對象 |
| `OrganizationController`（Ocadmin） | 不動，側邊欄改名為「外部公司」 |
| `OrganizationIdentity` enum | 不動 |
| `Gender` enum | 不動 |
| ESS 登入/登出 | 不動 |

### 7.3 Ocadmin 側邊欄調整

```
後台側邊欄
├── 集團管理                    ← 新增群組
│   ├── 集團公司                ← companies CRUD（新建）
│   └── 部門管理                ← departments CRUD（新建）
├── 人資管理
│   └── 員工管理                ← 修改（company + department 取代 organization）
├── 組織管理
│   └── 外部公司                ← organizations（原「組織管理」，改名）
└── 系統管理
    └── ...
```

---

## 第八部分：新的關聯圖

```
                          ┌─────────────┐
                          │   users     │
                          └──┬──────┬───┘
                             │      │
                    hasOne   │      │ belongsToMany
                             ▼      ▼
┌──────────┐      ┌──────────────┐    ┌──────────────┐
│companies │◄─────│hrm_employees │    │ company_user │
└──┬───┬───┘      └──────┬───────┘    └──────────────┘
   │   │                 │                (pivot)
   │   │ hasMany         │ belongsTo
   │   │                 ▼
   │   │          ┌──────────────┐
   │   └─────────►│ departments  │
   │   hasMany    └──┬───────────┘
   │                 │ children (self-ref)
   │                 ▼
   │               子部門...
   │
   │ children (self-ref)
   ▼
 子公司...


                  ┌────────────────┐
                  │ organizations  │  ← 外部往來對象（獨立，不關聯 employees）
                  └────────────────┘
```

---

## 實作順序

### Phase 1 — 資料層

1. 建立 `companies` + `company_translations` migration（含 `parent_id`）
2. 建立 `departments` migration
3. 建立 `Company` model（HasTranslation + parent/children 自引用）
4. 建立 `CompanyTranslation` model
5. 建立 `Department` model（self-referencing parent/children）
6. 建立 `company_user` 中間表 migration
7. 修改 `hrm_employees` migration（加 company_id / department_id，移除 organization_id / department）
8. 修改 `Employee` model
9. 修改 `User` model（加 companies 多對多關聯）
10. 建立 `CompanySeeder` + `DepartmentSeeder`，修改 `EmployeeSeeder`

### Phase 2 — Ocadmin 公司管理 CRUD

11. 建立 `Modules/Company/CompanyController`
12. 建立 Views（index / list / form，含母公司下拉選單）
13. 新增路由、側邊欄（集團管理 → 集團公司）
14. 語言檔

### Phase 3 — Ocadmin 部門管理 CRUD

15. 建立 `Modules/Department/DepartmentController`（或 `Hrm/Department/`）
16. 建立 Views（含樹狀顯示或巢狀列表，公司篩選下拉）
17. 新增路由、側邊欄（集團管理 → 部門管理）
18. 語言檔

### Phase 4 — 修改員工管理

19. 修改 `EmployeeController`（表單傳 companies / departments）
20. 修改 `form.blade.php`（company + department 二級連動下拉）
21. 修改 `list.blade.php`（顯示公司名稱 + 部門名稱）
22. 修改 ESS `ProfileController` + `Edit.tsx`（唯讀區顯示公司與部門）

### Phase 5 — 當前公司機制

23. 建立 `SetCurrentCompany` middleware 或 helper
24. 公司切換 UI（header 下拉選單，來源為 company_user）
25. 查詢加入 company scope

### Phase 6 — 側邊欄與語言調整

26. Ocadmin 側邊欄：新增「集團管理」群組，organizations 改名「外部公司」
27. 語言檔統一調整

### Phase 7 — 驗證

28. `php artisan migrate:fresh --seed`
29. Ocadmin：建立集團母公司 → 建立子公司 → 建立部門 → 建立員工
30. 公司切換：切換後資料正確過濾
31. 非員工帳號（IT管理員）能透過 company_user 存取多間公司
32. ESS：登入後看到正確的公司與部門名稱
