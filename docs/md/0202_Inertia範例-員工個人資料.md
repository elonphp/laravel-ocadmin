# Inertia 範例 — 員工管理 + ESS 個人資料

## 概述

本文件描述一個完整的端對端範例：

1. **Ocadmin 端**：在人資管理模組中建立員工 CRUD，可指派對應的系統使用者（透過 email AJAX 即時查找）
2. **ESS 端**：員工登入後，可在個人資料頁修改自己的員工資料

兩端共用同一個 `employees` 資料表，差別在於 Ocadmin 可管理所有員工，ESS 僅能編輯自己。

---

## 第一部分：資料層（共用）

### 1.1 Migration

**檔案**：`database/migrations/0001_01_01_000020_create_hrm_employees_table.php`

```php
Schema::create('hrm_employees', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
    $table->foreignId('organization_id')->nullable()->constrained()->nullOnDelete();
    $table->string('employee_no', 20)->nullable()->unique();
    $table->string('first_name', 50);
    $table->string('last_name', 50)->nullable();
    $table->string('email', 100)->nullable();
    $table->string('phone', 30)->nullable();
    $table->date('hire_date')->nullable();
    $table->date('birth_date')->nullable();
    $table->string('gender', 10)->nullable();          // male / female / other
    $table->string('job_title', 100)->nullable();
    $table->string('department', 100)->nullable();
    $table->text('address')->nullable();
    $table->text('note')->nullable();
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});
```

**設計說明**：

| 欄位 | 說明 |
|------|------|
| `user_id` | 關聯系統使用者帳號，nullable（員工不一定有系統帳號） |
| `organization_id` | 所屬組織 |
| `employee_no` | 員工編號，unique |
| `first_name` / `last_name` | 員工姓名（獨立於 User，員工不一定有帳號） |
| `email` | 員工 email（可與 User email 不同） |
| `is_active` | 在職狀態 |

### 1.2 Model

**檔案**：`app/Models/Hrm/Employee.php`

```php
namespace App\Models\Hrm;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Employee extends Model
{
    protected $table = 'hrm_employees';

    protected $fillable = [
        'user_id',
        'organization_id',
        'employee_no',
        'first_name',
        'last_name',
        'email',
        'phone',
        'hire_date',
        'birth_date',
        'gender',
        'job_title',
        'department',
        'address',
        'note',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'hire_date'  => 'date',
            'birth_date' => 'date',
            'is_active'  => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }
}
```

**不使用 HasTranslation**：員工個人資料為事實資料（姓名、生日等），不因語系而異，因此不需多語翻譯機制。

### 1.3 User Model 補充關聯

在 `app/Models/User.php` 新增：

```php
use App\Models\Hrm\Employee;

public function employee(): HasOne
{
    return $this->hasOne(Employee::class);
}
```

---

## 第二部分：Ocadmin 員工管理

### 2.1 目錄結構

```
app/Portals/Ocadmin/Modules/Hrm/
└── Employee/
    ├── EmployeeController.php
    └── Views/
        ├── index.blade.php
        ├── list.blade.php
        └── form.blade.php
```

模組路徑為 `Modules/Hrm/Employee/`，view namespace 自動註冊為 `ocadmin.hrm.employee`。

### 2.2 路由

在 `app/Portals/Ocadmin/routes/ocadmin.php` 新增：

```php
use App\Portals\Ocadmin\Modules\Hrm\Employee\EmployeeController;

// 人資管理
Route::prefix('hrm')->name('hrm.')->group(function () {
    // 員工管理
    Route::prefix('employee')->name('employee.')->group(function () {
        Route::get('/', [EmployeeController::class, 'index'])->name('index');
        Route::get('/list', [EmployeeController::class, 'list'])->name('list');
        Route::get('/create', [EmployeeController::class, 'create'])->name('create');
        Route::post('/', [EmployeeController::class, 'store'])->name('store');
        Route::get('/{employee}/edit', [EmployeeController::class, 'edit'])->name('edit');
        Route::put('/{employee}', [EmployeeController::class, 'update'])->name('update');
        Route::delete('/{employee}', [EmployeeController::class, 'destroy'])->name('destroy');
        Route::post('/batch-delete', [EmployeeController::class, 'batchDelete'])->name('batch-delete');
    });
});

// User AJAX 查找（供表單使用）
Route::get('/api/users/search', [EmployeeController::class, 'searchUsers'])->name('api.users.search');
```

路由名稱範例：`lang.ocadmin.hrm.employee.index`

### 2.3 EmployeeController

參考 [0106_Ocadmin程式規範.md](0106_Ocadmin程式規範.md)

**檔案**：`app/Portals/Ocadmin/Modules/Hrm/Employee/EmployeeController.php`

```php
namespace App\Portals\Ocadmin\Modules\Hrm\Employee;

use App\Portals\Ocadmin\Core\Controllers\OcadminController;
use App\Models\Hrm\Employee;
use App\Models\User;
use App\Models\Organization;

class EmployeeController extends OcadminController
{
    protected function setLangFiles(): array
    {
        return ['common', 'ocadmin/hrm/employee'];
    }

    protected function setBreadcrumbs(): void
    {
        $this->breadcrumbs = [
            (object) ['text' => $this->lang->text_home, 'href' => route('lang.ocadmin.dashboard')],
            (object) ['text' => $this->lang->text_hrm,  'href' => ''],
            (object) ['text' => $this->lang->heading_title, 'href' => route('lang.ocadmin.hrm.employee.index')],
        ];
    }
}
```

**關鍵方法**：

#### getList — 列表查詢

```php
protected function getList(Request $request): string
{
    $query = Employee::with(['user', 'organization.translation']);

    // 關鍵字搜尋
    if ($search = $request->input('search')) {
        $query->where(function ($q) use ($search) {
            $q->where('employee_no', 'like', "%{$search}%")
              ->orWhere('first_name', 'like', "%{$search}%")
              ->orWhere('last_name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%");
        });
    }

    // OrmHelper 處理 filter_* / equal_* / 排序 / 分頁
    OrmHelper::prepare($query, $request);
    // ...
}
```

#### create / edit — 表單資料

```php
public function create(): View
{
    $data['organizations'] = Organization::with('translation')->get();
    $data['employee'] = new Employee();
    // user 由前端 AJAX 查找，不需預載全部
    return view('ocadmin.hrm.employee::form', $data + $this->viewData('create'));
}
```

#### store / update — 儲存

```php
public function store(Request $request): JsonResponse
{
    $validated = $request->validate([
        'employee_no'     => 'nullable|string|max:20|unique:employees',
        'first_name'      => 'required|string|max:50',
        'last_name'       => 'nullable|string|max:50',
        'email'           => 'nullable|email|max:100',
        'phone'           => 'nullable|string|max:30',
        'user_id'         => 'nullable|exists:users,id',
        'organization_id' => 'nullable|exists:organizations,id',
        'hire_date'       => 'nullable|date',
        'birth_date'      => 'nullable|date',
        'gender'          => 'nullable|in:male,female,other',
        'job_title'       => 'nullable|string|max:100',
        'department'      => 'nullable|string|max:100',
        'address'         => 'nullable|string',
        'note'            => 'nullable|string',
        'is_active'       => 'boolean',
    ]);

    $employee = Employee::create($validated);

    return response()->json([
        'success'  => true,
        'message'  => $this->lang->text_success_add,
        'redirect' => route('lang.ocadmin.hrm.employee.index'),
    ]);
}
```

#### searchUsers — AJAX 使用者查找

供表單中的「關聯使用者」欄位使用，輸入 email 即時搜尋：

```php
public function searchUsers(Request $request): JsonResponse
{
    $keyword = $request->input('q', '');

    $users = User::where('email', 'like', "%{$keyword}%")
        ->orWhere('username', 'like', "%{$keyword}%")
        ->orWhere('name', 'like', "%{$keyword}%")
        ->select('id', 'name', 'email', 'username')
        ->limit(10)
        ->get();

    return response()->json($users);
}
```

### 2.4 Views

參考 [0106_Ocadmin程式規範.md](0106_Ocadmin程式規範.md)

#### form.blade.php — 使用者 AJAX 查找欄位

Employee 沒有多語 Tab，表單為單一分頁。使用者欄位以 AJAX 即時查找：

```html
{{-- 關聯使用者 --}}
<div class="row mb-3" id="input-user">
    <label class="col-sm-2 col-form-label">{{ $lang->column_user }}</label>
    <div class="col-sm-10">
        <div class="input-group">
            <input type="text"
                   id="input-user-search"
                   class="form-control"
                   placeholder="{{ $lang->placeholder_user_search }}"
                   value="{{ $employee->user?->email ?? '' }}"
                   autocomplete="off">
            <input type="hidden" name="user_id" id="input-user-id"
                   value="{{ old('user_id', $employee->user_id) }}">
            <button type="button" class="btn btn-outline-secondary" id="btn-clear-user">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div id="user-search-results" class="list-group position-absolute w-100"
             style="z-index:1000; display:none;"></div>
        <div id="error-user_id" class="invalid-feedback"></div>
    </div>
</div>
```

**AJAX 查找 JavaScript**：

```javascript
let searchTimer;
$('#input-user-search').on('input', function () {
    clearTimeout(searchTimer);
    const q = $(this).val();
    if (q.length < 2) {
        $('#user-search-results').hide();
        return;
    }
    searchTimer = setTimeout(() => {
        $.get('{{ route("lang.ocadmin.api.users.search") }}', { q }, function (users) {
            const $results = $('#user-search-results').empty();
            users.forEach(user => {
                $results.append(
                    `<a href="#" class="list-group-item list-group-item-action user-result"
                        data-id="${user.id}" data-email="${user.email}">
                        ${user.name} &lt;${user.email}&gt;
                    </a>`
                );
            });
            $results.toggle(users.length > 0);
        });
    }, 300);
});

$(document).on('click', '.user-result', function (e) {
    e.preventDefault();
    $('#input-user-id').val($(this).data('id'));
    $('#input-user-search').val($(this).data('email'));
    $('#user-search-results').hide();
});

$('#btn-clear-user').on('click', function () {
    $('#input-user-id').val('');
    $('#input-user-search').val('');
});
```

#### list.blade.php — 列表欄位

| checkbox | 員工編號 | 姓名 | Email | 組織 | 職稱 | 到職日 | 狀態 | 操作 |

可排序欄位：employee_no、first_name、email、hire_date

### 2.5 側邊欄

在 `MenuComposer.php` 新增「人資管理」群組（介於組織管理與系統管理之間）：

```php
$menus[] = [
    'id'       => 'menu-hrm',
    'icon'     => 'fa-solid fa-users',
    'name'     => '人資管理',
    'href'     => '',
    'children' => [
        [
            'id'       => 'menu-hrm-employee',
            'icon'     => 'fa-solid fa-id-card',
            'name'     => '員工管理',
            'href'     => route('lang.ocadmin.hrm.employee.index'),
            'children' => [],
        ],
    ],
];
```

### 2.6 語言檔

**檔案**：`lang/zh_Hant/ocadmin/hrm/employee.php`

```php
return [
    'heading_title'        => '員工管理',
    'text_hrm'             => '人資管理',
    'text_list'            => '員工列表',
    'text_add'             => '新增員工',
    'text_edit'            => '編輯員工',
    'text_success_add'     => '員工新增成功！',
    'text_success_edit'    => '員工更新成功！',

    'column_employee_no'   => '員工編號',
    'column_first_name'    => '名',
    'column_last_name'     => '姓',
    'column_email'         => 'Email',
    'column_phone'         => '電話',
    'column_user'          => '系統帳號',
    'column_organization'  => '所屬組織',
    'column_job_title'     => '職稱',
    'column_department'    => '部門',
    'column_hire_date'     => '到職日',
    'column_birth_date'    => '生日',
    'column_gender'        => '性別',
    'column_address'       => '地址',
    'column_note'          => '備註',
    'column_is_active'     => '狀態',

    'placeholder_search'        => '搜尋編號、姓名或 Email',
    'placeholder_user_search'   => '輸入 email 或姓名搜尋使用者...',

    'text_gender_male'     => '男',
    'text_gender_female'   => '女',
    'text_gender_other'    => '其他',
    'text_active'          => '在職',
    'text_inactive'        => '離職',

    'error_select_delete'  => '請選擇要刪除的項目',
];
```

---

## 第三部分：ESS Portal — 個人資料

### 3.1 技術棧

| 項目 | 技術 |
|------|------|
| 前端框架 | React 19 + TypeScript |
| SPA 橋接 | Inertia.js 2 |
| CSS 框架 | Tailwind CSS 4 |
| UI 元件庫 | DaisyUI 5（Tailwind 預製元件） |
| 無障礙元件 | Headless UI 2（對話框、下拉選單等互動元件） |
| 建構工具 | Vite 7（搭配 `@tailwindcss/vite` 插件） |

**DaisyUI** 提供現成的 class-based 元件（`btn`, `input`, `card`, `navbar`, `drawer` 等），減少手寫 Tailwind 的冗長 class。

**Headless UI** 提供無樣式但具完整鍵盤操作與無障礙的互動元件（Dialog、Listbox、Switch 等），搭配 Tailwind 自訂外觀。

### 3.2 目錄結構（方案 C：後端 Modules 化 + 前端 Pages 鏡射）

所有 ESS 檔案自包含於 `app/Portals/ESS/`。

**架構原則**：

- **後端**：`Core/` 放共用基礎設施（基礎 Controller、Middleware、ServiceProvider），`Modules/` 按功能模組分目錄放各自 Controller，與 Ocadmin 的 Modules 結構對齊。
- **前端**：`Pages/` 目錄結構「鏡射」後端 Modules 路徑，Inertia 自動對應（如 `Inertia::render('Hrm/Employee/Edit')` → `Pages/Hrm/Employee/Edit.tsx`），無需額外抽象層。
- **共用元件**：`Components/` 放跨模組共用元件（Layout、Form、UI）。頁面專屬小元件可放在 `Pages/Xxx/components/`（小寫，不會被 glob 當 Page）。

```
app/Portals/ESS/
├── Core/                                      # 共用核心
│   ├── Controllers/
│   │   └── EssController.php                  # 抽象基礎 Controller
│   ├── Providers/
│   │   └── EssServiceProvider.php
│   └── Middleware/
│       └── HandleEssInertiaRequests.php
│
├── Modules/                                   # 後端模組（與 Ocadmin 風格對齊）
│   ├── Auth/
│   │   └── LoginController.php                # 登入/登出
│   ├── Dashboard/
│   │   └── DashboardController.php
│   └── Hrm/
│       └── Employee/
│           └── ProfileController.php          # 個人資料
│
├── resources/                                 # 前端（集中）
│   ├── js/
│   │   ├── ess.tsx                            # Inertia 進入點
│   │   ├── Pages/                             # 鏡射 Modules 結構
│   │   │   ├── Auth/
│   │   │   │   └── Login.tsx                  # 登入頁
│   │   │   ├── Dashboard.tsx                  # 儀表板
│   │   │   └── Hrm/
│   │   │       └── Employee/
│   │   │           └── Edit.tsx               # 個人資料編輯
│   │   ├── Components/                        # 跨模組共用元件
│   │   │   └── Layout/
│   │   │       └── AuthenticatedLayout.tsx    # 已登入佈局（sidebar + header）
│   │   └── types/
│   │       └── index.d.ts
│   ├── css/
│   │   └── ess.css                            # Tailwind v4 directives + DaisyUI
│   └── views/
│       └── ess.blade.php                      # Inertia root template
│
└── routes/
    └── ess.php
```

> **Inertia 頁面解析**：`ess.tsx` 使用 `import.meta.glob('./Pages/**/*.tsx')` 掃描所有頁面。Controller 的 `Inertia::render('Hrm/Employee/Edit')` 會自動對應到 `./Pages/Hrm/Employee/Edit.tsx`，不需修改任何 Vite 設定。

### 3.3 安裝前端套件

```bash
npm install daisyui @headlessui/react
```

`@tailwindcss/forms`、`@inertiajs/react`、`react`、`tailwindcss` 已在 `package.json` 中。

### 3.4 CSS 進入點

**檔案**：`app/Portals/ESS/resources/css/ess.css`

```css
@import "tailwindcss";
@plugin "daisyui";
@plugin "@tailwindcss/forms";
```

### 3.5 Vite 設定

在 `vite.config.js` 加入 `@tailwindcss/vite` 插件、ESS 進入點與路徑別名：

```js
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/js/app.tsx',
                'app/Portals/ESS/resources/js/ess.tsx',
            ],
            refresh: true,
        }),
        react(),
        tailwindcss(),
    ],
    resolve: {
        alias: {
            '@ess': path.resolve(__dirname, 'app/Portals/ESS/resources/js'),
        },
    },
});
```

> **Tailwind CSS 4**：不再需要 `tailwind.config.js` 和 `postcss.config.js`，改由 `@tailwindcss/vite` 插件處理。CSS 檔案使用 `@import "tailwindcss"` + `@plugin` 語法。

### 3.6 路由

**檔案**：`app/Portals/ESS/routes/ess.php`

```php
use App\Portals\ESS\Modules\Auth\LoginController;
use App\Portals\ESS\Modules\Dashboard\DashboardController;
use App\Portals\ESS\Modules\Hrm\Employee\ProfileController;
use App\Portals\ESS\Core\Middleware\HandleEssInertiaRequests;

Route::group([
    'prefix'     => '{locale}/ess',
    'as'         => 'lang.ess.',
    'middleware'  => ['setLocale', HandleEssInertiaRequests::class],
], function () {

    // 未登入
    Route::middleware('guest')->group(function () {
        Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
    });

    // 已登入
    Route::middleware('auth')->group(function () {
        Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // 個人資料
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    });
});
```

### 3.7 Controllers

#### EssController（基礎類別）

```php
namespace App\Portals\ESS\Core\Controllers;

use Illuminate\Routing\Controller;

abstract class EssController extends Controller
{
    // ESS 共用邏輯（如有需要可擴充）
}
```

#### LoginController

```php
namespace App\Portals\ESS\Modules\Auth;

use App\Portals\ESS\Core\Controllers\EssController;
use Inertia\Inertia;

class LoginController extends EssController
{
    public function showLoginForm()
    {
        return Inertia::render('Auth/Login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(
                route('lang.ess.dashboard', ['locale' => app()->getLocale()])
            );
        }

        return back()->withErrors([
            'email' => __('auth.failed'),
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('lang.ess.login', ['locale' => app()->getLocale()]);
    }
}
```

#### ProfileController

登入者只能編輯自己的員工資料：

```php
namespace App\Portals\ESS\Modules\Hrm\Employee;

use App\Models\Hrm\Employee;
use App\Portals\ESS\Core\Controllers\EssController;
use Inertia\Inertia;

class ProfileController extends EssController
{
    public function edit(Request $request)
    {
        $employee = Employee::where('user_id', $request->user()->id)->firstOrFail();

        return Inertia::render('Hrm/Employee/Edit', [
            'employee' => $employee->only([
                'id', 'employee_no', 'first_name', 'last_name',
                'email', 'phone', 'birth_date', 'gender',
                'job_title', 'department', 'address',
            ]),
        ]);
    }

    public function update(Request $request)
    {
        $employee = Employee::where('user_id', $request->user()->id)->firstOrFail();

        $validated = $request->validate([
            'phone'      => 'nullable|string|max:30',
            'birth_date' => 'nullable|date',
            'gender'     => 'nullable|in:male,female,other',
            'address'    => 'nullable|string',
        ]);

        // ESS 僅允許修改部分欄位（姓名、編號、職稱等由管理員維護）
        $employee->update($validated);

        return back()->with('success', '個人資料更新成功！');
    }
}
```

**重點**：ESS `update` 的驗證規則僅包含允許員工自行修改的欄位（電話、生日、性別、地址）。姓名、員工編號、職稱等由 Ocadmin 管理員維護。

### 3.8 HandleEssInertiaRequests

```php
namespace App\Portals\ESS\Core\Middleware;

use Inertia\Middleware;

class HandleEssInertiaRequests extends Middleware
{
    protected $rootView = 'ess::ess';

    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            'locale'  => app()->getLocale(),
            'locales' => config('localization.locale_names'),
            'flash'   => [
                'success' => fn () => $request->session()->get('success'),
                'error'   => fn () => $request->session()->get('error'),
            ],
            // 側邊欄選單
            'menu' => [
                ['name' => '儀表板',   'href' => route('lang.ess.dashboard'),    'icon' => 'home'],
                ['name' => '個人資料', 'href' => route('lang.ess.profile.edit'), 'icon' => 'user'],
            ],
        ];
    }
}
```

### 3.9 React Pages

> **路徑慣例**：`Pages/` 下的目錄結構與後端 `Modules/` 一一對應。Controller 呼叫 `Inertia::render('Hrm/Employee/Edit')` → 前端對應到 `Pages/Hrm/Employee/Edit.tsx`。

#### AuthenticatedLayout.tsx — 已登入佈局

使用 DaisyUI 的 `drawer` 元件做側邊欄：

```tsx
import { PropsWithChildren } from 'react';
import { Link, usePage } from '@inertiajs/react';

interface MenuItem {
    name: string;
    href: string;
    icon: string;
}

export default function AuthenticatedLayout({ children }: PropsWithChildren) {
    const { auth, menu } = usePage().props as any;

    return (
        <div className="drawer lg:drawer-open">
            <input id="ess-drawer" type="checkbox" className="drawer-toggle" />

            {/* 主內容區 */}
            <div className="drawer-content flex flex-col">
                {/* Header */}
                <div className="navbar bg-base-100 shadow-sm lg:hidden">
                    <label htmlFor="ess-drawer" className="btn btn-ghost drawer-button">
                        <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" fill="none"
                             viewBox="0 0 24 24" stroke="currentColor">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2}
                                  d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </label>
                    <span className="text-lg font-bold">ESS</span>
                </div>

                {/* Page Content */}
                <main className="flex-1 p-6">
                    {children}
                </main>
            </div>

            {/* 側邊欄 */}
            <div className="drawer-side">
                <label htmlFor="ess-drawer" className="drawer-overlay" />
                <aside className="bg-base-200 w-64 min-h-full p-4">
                    <div className="text-xl font-bold mb-6 px-2">ESS Portal</div>
                    <ul className="menu">
                        {menu.map((item: MenuItem) => (
                            <li key={item.href}>
                                <Link href={item.href}>{item.name}</Link>
                            </li>
                        ))}
                    </ul>
                    <div className="mt-auto pt-4 border-t">
                        <div className="px-2 text-sm text-gray-500">{auth.user.name}</div>
                        <Link href={route('lang.ess.logout')} method="post" as="button"
                              className="btn btn-ghost btn-sm mt-2 w-full">
                            登出
                        </Link>
                    </div>
                </aside>
            </div>
        </div>
    );
}
```

#### Hrm/Employee/Edit.tsx — 個人資料編輯頁

位於 `Pages/Hrm/Employee/Edit.tsx`，鏡射後端 `Modules/Hrm/Employee/ProfileController`。

使用 DaisyUI 表單元件 + Headless UI 的 Listbox（性別下拉）：

```tsx
import { useForm, Head } from '@inertiajs/react';
import { Listbox, ListboxButton, ListboxOption, ListboxOptions } from '@headlessui/react';
import AuthenticatedLayout from '../../../Components/Layout/AuthenticatedLayout';

interface Employee {
    id: number;
    employee_no: string;
    first_name: string;
    last_name: string;
    email: string;
    phone: string | null;
    birth_date: string | null;
    gender: string | null;
    job_title: string | null;
    department: string | null;
    address: string | null;
}

const genderOptions = [
    { value: '',       label: '請選擇' },
    { value: 'male',   label: '男' },
    { value: 'female', label: '女' },
    { value: 'other',  label: '其他' },
];

export default function Edit({ employee }: { employee: Employee }) {
    const { data, setData, put, processing, errors } = useForm({
        phone:      employee.phone ?? '',
        birth_date: employee.birth_date ?? '',
        gender:     employee.gender ?? '',
        address:    employee.address ?? '',
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        put(route('lang.ess.profile.update'));
    };

    const selectedGender = genderOptions.find(g => g.value === data.gender) ?? genderOptions[0];

    return (
        <AuthenticatedLayout>
            <Head title="個人資料" />

            <div className="max-w-2xl">
                <h1 className="text-2xl font-bold mb-6">個人資料</h1>

                {/* 唯讀區塊 */}
                <div className="card bg-base-100 shadow mb-6">
                    <div className="card-body">
                        <h2 className="card-title text-sm text-gray-500">基本資訊（由管理員維護）</h2>
                        <div className="grid grid-cols-2 gap-4 mt-2">
                            <div>
                                <span className="text-sm text-gray-500">員工編號</span>
                                <p className="font-medium">{employee.employee_no}</p>
                            </div>
                            <div>
                                <span className="text-sm text-gray-500">姓名</span>
                                <p className="font-medium">{employee.first_name} {employee.last_name}</p>
                            </div>
                            <div>
                                <span className="text-sm text-gray-500">Email</span>
                                <p className="font-medium">{employee.email}</p>
                            </div>
                            <div>
                                <span className="text-sm text-gray-500">職稱</span>
                                <p className="font-medium">{employee.job_title ?? '-'}</p>
                            </div>
                            <div>
                                <span className="text-sm text-gray-500">部門</span>
                                <p className="font-medium">{employee.department ?? '-'}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {/* 可編輯表單 */}
                <form onSubmit={submit}>
                    <div className="card bg-base-100 shadow">
                        <div className="card-body space-y-4">
                            <h2 className="card-title text-sm text-gray-500">可編輯資訊</h2>

                            {/* 電話 */}
                            <div className="form-control">
                                <label className="label">
                                    <span className="label-text">電話</span>
                                </label>
                                <input type="text"
                                       className={`input input-bordered ${errors.phone ? 'input-error' : ''}`}
                                       value={data.phone}
                                       onChange={e => setData('phone', e.target.value)} />
                                {errors.phone && <span className="text-error text-sm mt-1">{errors.phone}</span>}
                            </div>

                            {/* 生日 */}
                            <div className="form-control">
                                <label className="label">
                                    <span className="label-text">生日</span>
                                </label>
                                <input type="date"
                                       className={`input input-bordered ${errors.birth_date ? 'input-error' : ''}`}
                                       value={data.birth_date}
                                       onChange={e => setData('birth_date', e.target.value)} />
                                {errors.birth_date && <span className="text-error text-sm mt-1">{errors.birth_date}</span>}
                            </div>

                            {/* 性別 — Headless UI Listbox */}
                            <div className="form-control">
                                <label className="label">
                                    <span className="label-text">性別</span>
                                </label>
                                <Listbox value={data.gender} onChange={val => setData('gender', val)}>
                                    <div className="relative">
                                        <ListboxButton className="select select-bordered w-full text-left">
                                            {selectedGender.label}
                                        </ListboxButton>
                                        <ListboxOptions className="menu bg-base-100 shadow-lg rounded-box absolute z-10 mt-1 w-full">
                                            {genderOptions.map(option => (
                                                <ListboxOption key={option.value} value={option.value}
                                                    className={({ active }) =>
                                                        `cursor-pointer px-4 py-2 ${active ? 'bg-primary text-primary-content' : ''}`
                                                    }>
                                                    {option.label}
                                                </ListboxOption>
                                            ))}
                                        </ListboxOptions>
                                    </div>
                                </Listbox>
                            </div>

                            {/* 地址 */}
                            <div className="form-control">
                                <label className="label">
                                    <span className="label-text">地址</span>
                                </label>
                                <textarea className={`textarea textarea-bordered ${errors.address ? 'textarea-error' : ''}`}
                                          rows={3}
                                          value={data.address}
                                          onChange={e => setData('address', e.target.value)} />
                                {errors.address && <span className="text-error text-sm mt-1">{errors.address}</span>}
                            </div>

                            <div className="card-actions justify-end pt-4">
                                <button type="submit" className="btn btn-primary" disabled={processing}>
                                    {processing ? <span className="loading loading-spinner loading-sm" /> : null}
                                    儲存
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </AuthenticatedLayout>
    );
}
```

#### Auth/Login.tsx — 登入頁

```tsx
import { useForm, Head } from '@inertiajs/react';

export default function Login() {
    const { data, setData, post, processing, errors } = useForm({
        email: '',
        password: '',
        remember: false,
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('lang.ess.login.submit'));
    };

    return (
        <>
            <Head title="登入" />
            <div className="min-h-screen flex items-center justify-center bg-base-200">
                <div className="card w-full max-w-sm bg-base-100 shadow-xl">
                    <div className="card-body">
                        <h2 className="card-title justify-center text-2xl mb-4">ESS 登入</h2>
                        <form onSubmit={submit}>
                            <div className="form-control">
                                <label className="label"><span className="label-text">Email</span></label>
                                <input type="email" className="input input-bordered"
                                       value={data.email}
                                       onChange={e => setData('email', e.target.value)} />
                                {errors.email && <span className="text-error text-sm">{errors.email}</span>}
                            </div>
                            <div className="form-control mt-4">
                                <label className="label"><span className="label-text">密碼</span></label>
                                <input type="password" className="input input-bordered"
                                       value={data.password}
                                       onChange={e => setData('password', e.target.value)} />
                            </div>
                            <div className="form-control mt-2">
                                <label className="label cursor-pointer justify-start gap-2">
                                    <input type="checkbox" className="checkbox checkbox-sm"
                                           checked={data.remember}
                                           onChange={e => setData('remember', e.target.checked)} />
                                    <span className="label-text">記住我</span>
                                </label>
                            </div>
                            <div className="form-control mt-6">
                                <button type="submit" className="btn btn-primary" disabled={processing}>
                                    {processing ? <span className="loading loading-spinner loading-sm" /> : null}
                                    登入
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </>
    );
}
```

---

## 流程總覽

```
┌─────────────────────────────────────────────────────┐
│                    共用資料層                          │
│  users ──< employees >── organizations              │
│           (user_id)      (organization_id)          │
└─────────────┬───────────────────┬───────────────────┘
              │                   │
    ┌─────────▼─────────┐ ┌──────▼──────────┐
    │   Ocadmin Portal  │ │   ESS Portal    │
    │   Blade + jQuery  │ │  React+Inertia  │
    │                   │ │                 │
    │  人資管理          │ │  登入            │
    │  └─ 員工管理 CRUD │ │  儀表板          │
    │     - 全部員工    │ │  個人資料（自己） │
    │     - 指派 User   │ │   └─ 唯讀區塊   │
    │     - AJAX 查找   │ │   └─ 可編輯欄位  │
    └───────────────────┘ └─────────────────┘
```

---

## 實作順序

### Phase 1 — 資料層

1. 建立 `employees` migration
2. 建立 `Employee` model
3. 修改 `User` model 加入 `employee()` 關聯
4. 建立 `EmployeeSeeder`（從 OrganizationSeeder 的組織中隨機指派）

### Phase 2 — Ocadmin 員工管理

5. 建立 `EmployeeController`（`Modules/Hrm/Employee/`）
6. 建立語言檔 `lang/zh_Hant/ocadmin/hrm/employee.php`
7. 建立 Views：index、list、form（含 AJAX user search）
8. 新增路由（`ocadmin.php`）
9. 新增側邊欄（`MenuComposer`）
10. 驗證 Ocadmin CRUD 功能

### Phase 3 — ESS Portal 基礎建設

11. 安裝 `daisyui`、`@tailwindcss/vite`
12. 建立 `Core/Providers/EssServiceProvider`，註冊到 `bootstrap/providers.php`
13. 建立 `Core/Middleware/HandleEssInertiaRequests`
14. 建立 ESS root view（`resources/views/ess.blade.php`）
15. 建立 ESS Inertia 進入點（`resources/js/ess.tsx` + `resources/css/ess.css`）
16. 修改 `vite.config.js` 加入 `@tailwindcss/vite` 插件與 ESS 進入點
17. 建立 ESS 路由（`routes/ess.php`）

### Phase 4 — ESS 登入與個人資料

18. 建立 `Modules/Auth/LoginController`
19. 建立 `Pages/Auth/Login.tsx`
20. 建立 `Components/Layout/AuthenticatedLayout.tsx`（DaisyUI drawer 側邊欄）
21. 建立 `Modules/Dashboard/DashboardController` + `Pages/Dashboard.tsx`
22. 建立 `Modules/Hrm/Employee/ProfileController`
23. 建立 `Pages/Hrm/Employee/Edit.tsx`（唯讀區塊 + 可編輯表單 + Headless UI Listbox）
24. 端對端測試：登入 → 儀表板 → 修改個人資料 → 儲存

### Phase 5 — 驗證

25. `php artisan route:list --name=employee` 確認 Ocadmin 路由
26. `php artisan route:list --name=ess` 確認 ESS 路由
27. Ocadmin：新增員工 → 指派 User（AJAX 查找）→ 儲存
28. ESS：以該 User 登入 → 看到個人資料 → 修改電話/地址 → 儲存成功
