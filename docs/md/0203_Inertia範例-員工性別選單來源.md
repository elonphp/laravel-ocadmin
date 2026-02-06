# Inertia 範例 — 選單資料來源（以性別 Gender 為例）

## 概述

本文件以「性別」下拉選單為例，說明 **Enum 定義 → 多語翻譯 → Model Cast → Controller 傳值 → 前端渲染** 的完整流程。

這套模式適用於所有固定選項的下拉選單（如：狀態、類型、身分等）。掌握 Gender 的做法後，新增其他 Enum 選單只需照同樣步驟即可。

---

## 資料流總覽

```
                              後端 (PHP / Laravel)
┌──────────────────────────────────────────────────────────────────┐
│                                                                  │
│  ① Enum 定義            ② 語系檔翻譯             ③ Model Cast    │
│  Gender::Male='male'    enums.gender.male='男'    gender→Gender  │
│       │                        │                        │        │
│       └──── label() ───────────┘                        │        │
│                │                                        │        │
│       ④ Controller 組裝                                 │        │
│       Gender::options(placeholder)                      │        │
│                │                                        │        │
│       ⑤ Inertia::render('...', [                        │        │
│            'genderOptions' => [...],  ← options() 結果  │        │
│            'employee' => $employee,   ← gender 已 cast  │        │
│          ])                                             │        │
│                │                                        │        │
└────────────────┼────────────────────────────────────────┘        │
                 │  JSON 序列化                                    │
                 ▼                                                 │
┌──────────────────────────────────────────────────────────────────┐
│                                                                  │
│  ⑥ React 元件接收 props                                          │
│  { genderOptions: [{value:'', label:'請選擇性別'},               │
│                     {value:'male', label:'男'}, ...] }           │
│                                                                  │
│  ⑦ Headless UI Listbox 渲染下拉選單                              │
│  {genderOptions.map(opt => <ListboxOption ...>{opt.label})}      │
│                                                                  │
│                              前端 (React / TypeScript)           │
└──────────────────────────────────────────────────────────────────┘
```

> **核心原則**：選單的「值」和「標籤」全部由後端產生，前端只負責渲染。切換語系時，後端 `__()` 自動查對應語系檔，前端不需任何改動。

---

## 第一步：建立 PHP Enum

**檔案**：`app/Enums/Common/Gender.php`

```php
namespace App\Enums\Common;

enum Gender: string
{
    case Male = 'male';       // ← 資料庫儲存值
    case Female = 'female';
    case Other = 'other';

    /**
     * 取得翻譯後的標籤
     */
    public function label(): string
    {
        return __('enums.gender.' . $this->value);
        //       ↑ 查語系檔 lang/{locale}/enums.php 的 gender.male 鍵
    }

    /**
     * 取得所有值（供 migration / 驗證使用）
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
        // → ['male', 'female', 'other']
    }

    /**
     * 組裝前端下拉選單資料
     * @param string|null $placeholder 第一項空白選項的文字，如 "請選擇性別"
     */
    public static function options(?string $placeholder = null): array
    {
        $options = [];

        if ($placeholder !== null) {
            $options[] = ['value' => '', 'label' => $placeholder];
        }

        foreach (self::cases() as $case) {
            $options[] = ['value' => $case->value, 'label' => $case->label()];
        }

        return $options;
    }
}
```

### Enum 目錄慣例

```
app/Enums/
├── Common/                       ← 跨領域共用（Gender 可用於 Employee、User 等）
│   └── Gender.php
├── OrganizationIdentity.php      ← 根層級也可（舊有，未來可搬入 Common）
└── System/
    └── SettingType.php            ← 系統管理專屬
```

- **`Common/`**：不歸屬特定領域、多處共用的 Enum
- **`System/`**、**`Hrm/`** 等：歸屬特定領域模組

---

## 第二步：建立語系檔翻譯

**檔案**：`lang/zh_Hant/enums.php`

```php
return [
    'gender' => [
        'male'   => '男',
        'female' => '女',
        'other'  => '其他',
    ],

    'gender_placeholder' => '請選擇性別',
];
```

新增英文語系時，建立 `lang/en/enums.php`：

```php
return [
    'gender' => [
        'male'   => 'Male',
        'female' => 'Female',
        'other'  => 'Other',
    ],

    'gender_placeholder' => 'Select Gender',
];
```

`Gender::label()` 內部呼叫 `__('enums.gender.male')`，Laravel 會依照當前 `app()->getLocale()` 自動查找對應語系檔。

---

## 第三步：Model 設定 Enum Cast

**檔案**：`app/Models/Hrm/Employee.php`

```php
use App\Enums\Common\Gender;

class Employee extends Model
{
    protected function casts(): array
    {
        return [
            'gender' => Gender::class,  // ← string ↔ Gender enum 自動轉換
            // ...
        ];
    }
}
```

**效果**：

| 操作 | 行為 |
|------|------|
| `$employee->gender` | 回傳 `Gender::Male` enum 實例（不是字串 `'male'`） |
| `$employee->gender->value` | 回傳 `'male'`（原始字串值） |
| `$employee->gender->label()` | 回傳 `'男'`（翻譯後的標籤） |
| `$employee->gender = 'female'` | 自動轉為 `Gender::Female` |
| `$employee->gender = Gender::Female` | 直接接受 enum |
| 寫入資料庫 | 自動轉為字串 `'female'` 儲存 |

> **資料庫欄位維持 `string` 型態**，不需改為 DB enum。PHP enum cast 提供型別安全，同時保持 migration 的彈性（新增選項不需資料庫遷移）。

---

## 第四步：Controller 傳值給前端

### ESS Portal（Inertia + React）

**檔案**：`app/Portals/ESS/Modules/Hrm/Employee/ProfileController.php`

```php
use App\Enums\Common\Gender;

public function edit(Request $request): Response
{
    $employee = Employee::where('user_id', $request->user()->id)->firstOrFail();

    return Inertia::render('Hrm/Employee/Edit', [
        'employee' => $employee->only([
            'id', 'employee_no', 'first_name', 'last_name',
            'email', 'phone', 'birth_date', 'gender',
            'job_title', 'department', 'address',
        ]),
        'genderOptions' => Gender::options(__('enums.gender_placeholder')),
        //                                  ↑ '請選擇性別'
    ]);
}
```

`Gender::options(...)` 在這一刻執行，產出的是**當前語系下已翻譯好的陣列**：

```php
[
    ['value' => '',       'label' => '請選擇性別'],   // placeholder
    ['value' => 'male',   'label' => '男'],
    ['value' => 'female', 'label' => '女'],
    ['value' => 'other',  'label' => '其他'],
]
```

Inertia 會將這個 PHP 陣列 **序列化為 JSON**，嵌入 HTML 頁面的 `<div id="app" data-page="...">` 屬性中。

### 驗證也用 Enum

```php
use Illuminate\Validation\Rule;

$request->validate([
    'gender' => ['nullable', Rule::enum(Gender::class)],
    //           ↑ 只接受 'male', 'female', 'other'，不用手寫 in:male,female,other
]);
```

### Ocadmin Portal（Blade + jQuery）

**檔案**：`app/Portals/Ocadmin/Modules/Hrm/Employee/EmployeeController.php`

Ocadmin 傳 `Gender::cases()`（enum 實例陣列），由 Blade 迴圈渲染：

```php
$data['genderOptions'] = Gender::cases();

return view('ocadmin.hrm.employee::form', $data);
```

**Blade 模板**：

```blade
<select name="gender" class="form-select">
    <option value="">{{ __('enums.gender_placeholder') }}</option>
    @foreach($genderOptions as $gender)
    <option value="{{ $gender->value }}"
            @selected(old('gender', $employee->gender?->value) === $gender->value)>
        {{ $gender->label() }}
    </option>
    @endforeach
</select>
```

> **兩端差異**：ESS 傳 `options()`（純陣列，JSON 友好），Ocadmin 傳 `cases()`（enum 實例，Blade 可直接呼叫方法）。

---

## 第五步：React 元件接收並渲染

**檔案**：`app/Portals/ESS/resources/js/Pages/Hrm/Employee/Edit.tsx`

### 型別定義

```tsx
interface SelectOption {
    value: string;
    label: string;
}

interface Props {
    employee: Employee;
    genderOptions: SelectOption[];   // ← 後端傳來的選項陣列
}
```

### 接收 props

```tsx
export default function Edit({ employee, genderOptions }: Props) {
```

Inertia 自動將 JSON 反序列化為 JavaScript 物件，直接作為 React 元件的 props 傳入。

### 渲染下拉選單

使用 Headless UI 的 `Listbox` 搭配 DaisyUI 樣式：

```tsx
const selectedGender = genderOptions.find(g => g.value === data.gender)
    ?? genderOptions[0];

<Listbox value={data.gender} onChange={val => setData('gender', val)}>
    <ListboxButton className="select select-bordered w-full text-left">
        {selectedGender.label}         {/* 顯示已選項的中文標籤 */}
    </ListboxButton>
    <ListboxOptions>
        {genderOptions.map(option => (
            <ListboxOption key={option.value} value={option.value}>
                {option.label}          {/* '請選擇性別' / '男' / '女' / '其他' */}
            </ListboxOption>
        ))}
    </ListboxOptions>
</Listbox>
```

### 表單送出

```tsx
const { data, setData, put } = useForm({
    gender: employee.gender ?? '',    // ← 初始值 'male'（後端 enum 序列化為字串）
});

put(route('lang.ess.profile.update'));
// → PUT /zh-hant/ess/profile  body: { gender: 'male', ... }
```

Inertia 送出時，`data.gender` 是字串 `'male'`。後端收到後，`Rule::enum(Gender::class)` 驗證通過，`$employee->update()` 寫入資料庫。

---

## 逐步追蹤：使用者選擇「女」的完整過程

```
① 使用者進入 /zh-hant/ess/profile
   → ProfileController::edit() 被呼叫

② Gender::options(__('enums.gender_placeholder'))
   → __('enums.gender_placeholder') 查 lang/zh_Hant/enums.php → '請選擇性別'
   → Gender::Male->label() 查 lang/zh_Hant/enums.php → '男'
   → Gender::Female->label() → '女'
   → Gender::Other->label() → '其他'
   → 回傳 [{value:'', label:'請選擇性別'}, {value:'male', label:'男'}, ...]

③ Inertia::render('Hrm/Employee/Edit', ['genderOptions' => [...]])
   → Inertia 序列化為 JSON，嵌入 HTML

④ React 載入，Edit 元件收到 props.genderOptions
   → Listbox 渲染 4 個選項：請選擇性別 / 男 / 女 / 其他

⑤ 使用者點選「女」
   → Listbox onChange 觸發 setData('gender', 'female')
   → data.gender = 'female'
   → ListboxButton 顯示 '女'

⑥ 使用者按「儲存」
   → put(route('lang.ess.profile.update'))
   → PUT /zh-hant/ess/profile { gender: 'female', ... }

⑦ ProfileController::update() 收到 request
   → Rule::enum(Gender::class) 驗證 'female' ✓
   → $employee->update(['gender' => 'female'])
   → Laravel cast 自動將 'female' 轉為 Gender::Female 再存入 DB

⑧ 資料庫 hrm_employees.gender = 'female' ← 儲存字串
```

---

## 新增 Enum 選單的步驟清單

若要新增另一個 Enum（例如：婚姻狀態 `MaritalStatus`），依照以下步驟：

### 後端

1. **建立 Enum**：`app/Enums/Common/MaritalStatus.php`
   - 定義 cases、`label()`、`values()`、`options()` 方法
2. **新增語系**：`lang/zh_Hant/enums.php` 加入翻譯 key
3. **Model Cast**：在 Model 的 `casts()` 加入 `'marital_status' => MaritalStatus::class`
4. **Controller**：
   - ESS：`Inertia::render(...)` 傳 `'maritalStatusOptions' => MaritalStatus::options(...)`
   - Ocadmin：`$data['maritalStatusOptions'] = MaritalStatus::cases()`
5. **驗證**：`Rule::enum(MaritalStatus::class)`

### 前端

6. **React 元件**：props 接收 `maritalStatusOptions: SelectOption[]`，用 `Listbox` 或 `<select>` 渲染

**前端不需要知道**：有哪些選項、各選項的翻譯文字、驗證規則。這些全由後端 Enum + 語系檔決定。

---

## 相關檔案索引

| 用途 | 檔案路徑 |
|------|---------|
| Enum 定義 | `app/Enums/Common/Gender.php` |
| 語系翻譯 | `lang/zh_Hant/enums.php` |
| Model Cast | `app/Models/Hrm/Employee.php` |
| ESS Controller | `app/Portals/ESS/Modules/Hrm/Employee/ProfileController.php` |
| ESS React 頁面 | `app/Portals/ESS/resources/js/Pages/Hrm/Employee/Edit.tsx` |
| Ocadmin Controller | `app/Portals/Ocadmin/Modules/Hrm/Employee/EmployeeController.php` |
| Ocadmin Blade 模板 | `app/Portals/Ocadmin/Modules/Hrm/Employee/Views/form.blade.php` |
