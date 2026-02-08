# HRM Portal - 人力資源管理系統

> 基於 Laravel + Inertia.js + React + TypeScript + Tailwind CSS 的現代化 HRM 子系統

---

## 📚 目錄

- [專案概述](#專案概述)
- [技術棧](#技術棧)
- [快速開始（前端工程師）](#快速開始前端工程師)
- [目錄結構](#目錄結構)
- [開發指南](#開發指南)
- [API 文件](#api-文件)
- [與 AI 協作指南](#與-ai-協作指南)
- [文檔索引](#文檔索引)
- [常見問題](#常見問題)

---

## 專案概述

### 什麼是 HRM Portal？

HRM Portal 是 LaravelOcadmin 系統的**獨立子系統**，專注於人力資源管理，包含：

- ✅ **行事曆管理** - 工作日、假日、補班日設定
- 🚧 **排班管理** - 員工排班、班表產生
- 🚧 **打卡管理** - 打卡記錄、匯入匯出
- 🚧 **出勤統計** - 每日出勤、每月統計
- 🚧 **請假管理** - 請假申請、審核
- 🚧 **薪資計算** - 工時統計、薪資計算

### 設計理念

1. **前後端完全分離** - 前端工程師只需處理 `resources/` 目錄
2. **模組化架構** - 每個功能模組獨立（Controller + Service）
3. **型別安全** - 使用 TypeScript 確保程式碼品質
4. **現代化 UI** - 可以使用 Tailwind CSS 開發模版或整合付費模板

---

## 技術棧

### 後端
- **Laravel 12** - PHP 框架
- **Inertia.js** - 前後端橋接（無需 API）
- **MySQL** - 資料庫

### 前端
- **React 18** - UI 框架
- **TypeScript** - 型別安全
- **Tailwind CSS** - CSS 框架
- **Vite** - 打包工具
- **ShadcN UI** / **其他付費模板** - UI 組件庫

### 開發工具
- **Postman** - API 測試
- **Laravel Tinker** - 後端邏輯測試

---

## 快速開始（前端工程師）

### 環境要求

- Node.js >= 18
- npm 或 yarn
- PHP >= 8.4（後端已配置好）

### 安裝依賴

```bash
# 安裝前端依賴
npm install

# 或使用 yarn
yarn install
```

### 啟動開發伺服器

```bash
# 啟動 Vite 開發伺服器
npm run dev

# 後端伺服器（由後端工程師啟動）
php artisan serve
```

### 存取 HRM Portal

```
http://localhost:8000/hrm
```

---

## 目錄結構

```
App\Portals\Hrm\
│
├── 📁 Core/                          # Portal 核心（不需要修改）
│   ├── Controllers/
│   │   └── HrmController.php         # 基礎控制器
│   ├── Providers/
│   │   └── HrmServiceProvider.php    # 服務提供者
│   └── Views/
│       └── app.blade.php             # Inertia 根模板（唯一的 Blade）
│
├── 📁 Modules/                       # 後端業務模組（後端負責）
│   └── Calendar/
│       ├── CalendarDayController.php # 控制器（返回 Inertia Response）
│       └── CalendarDayService.php       # 業務邏輯
│
├── 📁 resources/                     # 🎯 前端工程師主要工作區域
│   ├── js/
│   │   ├── 📁 Pages/                 # Inertia Pages（頁面組件）
│   │   │   ├── Dashboard/
│   │   │   │   └── Index.tsx
│   │   │   └── Calendar/             # 行事曆頁面
│   │   │       ├── Index.tsx         # 列表頁
│   │   │       ├── Create.tsx        # 新增頁
│   │   │       ├── Edit.tsx          # 編輯頁
│   │   │       └── Show.tsx          # 詳情頁
│   │   │
│   │   ├── 📁 Components/            # 共用 UI 組件
│   │   │   ├── Layout/
│   │   │   │   ├── AppLayout.tsx     # 主版型
│   │   │   │   ├── Sidebar.tsx       # 側邊欄
│   │   │   │   └── Header.tsx        # 頂部導航
│   │   │   ├── Forms/
│   │   │   │   ├── Input.tsx
│   │   │   │   ├── Select.tsx
│   │   │   │   └── DatePicker.tsx
│   │   │   ├── Tables/
│   │   │   │   ├── DataTable.tsx
│   │   │   │   └── Pagination.tsx
│   │   │   └── Cards/
│   │   │       └── StatCard.tsx
│   │   │
│   │   ├── 📁 Layouts/               # Inertia 佈局組件
│   │   │   └── HrmLayout.tsx
│   │   │
│   │   ├── 📁 types/                 # TypeScript 型別定義
│   │   │   ├── index.d.ts
│   │   │   ├── models.d.ts           # 資料模型型別
│   │   │   └── inertia.d.ts          # Inertia 型別擴充
│   │   │
│   │   ├── 📁 lib/                   # 工具函數
│   │   │   ├── utils.ts
│   │   │   └── dateUtils.ts
│   │   │
│   │   └── app.tsx                   # Inertia 入口
│   │
│   └── css/
│       └── app.css                   # Tailwind CSS 入口
│
├── 📁 routes/                        # 路由定義（後端負責）
│   └── web.php
│
├── 📁 docs/                          # 📖 文檔（重要！）
│   ├── 1000_差勤系統概述.md
│   ├── 1001_行事曆作業.md
│   ├── 1002_原始打卡表.md
│   ├── 1003_每日出勤統計.md
│   └── 1004_每月出勤統計.md
│
├── POSTMAN_TESTS.md                  # API 測試範例
└── README.md                         # 本文件
```

---

## 開發指南

### 1. 如何建立新頁面

#### 步驟 1：查看後端定義的資料結構

後端 Controller 已經定義好 Inertia Response：

```php
// Modules/Calendar/CalendarDayController.php
public function index()
{
    return Inertia::render('Calendar/Index', [
        'calendars' => $calendars,          // 📊 資料
        'filters' => $filters,              // 🔍 篩選條件
        'breadcrumbs' => $this->breadcrumbs, // 🍞 麵包屑
    ]);
}
```

#### 步驟 2：建立對應的 React 組件

```tsx
// resources/js/Pages/Calendar/Index.tsx
import { Head } from '@inertiajs/react';
import HrmLayout from '@/Layouts/HrmLayout';

interface CalendarDay {
    id: number;
    date: string;
    day_type: string;
    is_workday: boolean;
    name: string | null;
}

interface Props {
    calendars: {
        data: CalendarDay[];
        current_page: number;
        total: number;
    };
    filters: {
        year?: number;
        month?: number;
    };
    breadcrumbs: Array<{
        text: string;
        href: string;
    }>;
}

export default function Index({ calendars, filters, breadcrumbs }: Props) {
    return (
        <HrmLayout>
            <Head title="行事曆管理" />

            <div className="p-6">
                <h1 className="text-2xl font-bold mb-4">行事曆管理</h1>

                {/* 使用付費模板的 Table 組件 */}
                <DataTable data={calendars.data} />
            </div>
        </HrmLayout>
    );
}
```

### 2. 如何使用 Inertia

#### 頁面跳轉

```tsx
import { Link, router } from '@inertiajs/react';

// 使用 Link 組件
<Link href="/hrm/calendar/create">新增</Link>

// 或使用 router
router.visit('/hrm/calendar/create');
```

#### 表單提交

```tsx
import { useForm } from '@inertiajs/react';

export default function Create() {
    const { data, setData, post, processing, errors } = useForm({
        date: '',
        day_type: 'workday',
        is_workday: true,
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/hrm/calendar');
    };

    return (
        <form onSubmit={handleSubmit}>
            <input
                type="date"
                value={data.date}
                onChange={e => setData('date', e.target.value)}
            />
            {errors.date && <span>{errors.date}</span>}

            <button type="submit" disabled={processing}>
                儲存
            </button>
        </form>
    );
}
```

#### 資料重新載入

```tsx
import { router } from '@inertiajs/react';

// 重新載入當前頁面
router.reload();

// 只重新載入特定資料
router.reload({ only: ['calendars'] });
```

### 3. TypeScript 型別定義

在 `resources/js/types/models.d.ts` 定義資料模型：

```typescript
// resources/js/types/models.d.ts
export interface CalendarDay {
    id: number;
    date: string;
    day_type: 'workday' | 'weekend' | 'holiday' | 'company_holiday' | 'makeup_workday' | 'typhoon_day';
    is_workday: boolean;
    name: string | null;
    description: string | null;
    color: string | null;
    created_at: string;
    updated_at: string;
}

export interface PaginatedData<T> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}
```

### 4. 共用組件開發

建立可重用的 UI 組件：

```tsx
// resources/js/Components/Forms/Input.tsx
interface InputProps extends React.InputHTMLAttributes<HTMLInputElement> {
    label?: string;
    error?: string;
}

export default function Input({ label, error, ...props }: InputProps) {
    return (
        <div className="mb-4">
            {label && (
                <label className="block text-sm font-medium mb-1">
                    {label}
                </label>
            )}
            <input
                {...props}
                className="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-blue-500"
            />
            {error && (
                <span className="text-sm text-red-600">{error}</span>
            )}
        </div>
    );
}
```

---

## API 文件

### 後端已完成的 API

詳見 `POSTMAN_TESTS.md`，包含：

- ✅ 行事曆 CRUD
- ✅ 批次建立工作日
- ✅ 匯入國定假日
- ✅ 設定補班日
- ✅ 查詢月曆資料

### 測試 API

```bash
# 使用 Postman 匯入
# 檔案位置：POSTMAN_TESTS.md

# 或使用 curl
curl -X GET http://localhost:8000/hrm/calendar \
  -H "Accept: application/json"
```

---

## 與 AI 協作指南

### 🤖 給 AI 的專案背景說明

**重要！將以下內容提供給 AI（如 ChatGPT、Claude）以獲得更好的協助：**

```
我正在開發 HRM Portal 的前端介面，這是一個基於 Inertia.js + React + TypeScript + Tailwind 的專案。

專案背景：
- 這是 Laravel 專案的獨立子系統（App\Portals\Hrm）
- 使用 Inertia.js 連接前後端（不需要寫 API）
- 後端已完成，會返回 Inertia Response 和完整的資料結構
- 我只需要負責 resources/ 目錄的前端程式碼

目前進度：
- ✅ 後端架構完成（Controller + Service）
- ✅ 路由定義完成
- ✅ 資料模型定義完成
- ⏭ 需要實作前端 UI（使用付費模板）

目錄結構：
- resources/js/Pages/ - 頁面組件（對應 Inertia 路由）
- resources/js/Components/ - 共用 UI 組件
- resources/js/Layouts/ - 佈局組件
- resources/js/types/ - TypeScript 型別定義

技術約束：
- 必須使用 Inertia.js 的 useForm、Link、router
- 必須使用 TypeScript
- 必須使用 Tailwind CSS
- UI 組件使用 [你的付費模板名稱]

當前任務：
[描述你要實作的功能]

請幫我：
[具體要求]
```

### 常見 AI 提示範例

#### 建立列表頁

```
請幫我建立 resources/js/Pages/Calendar/Index.tsx

需求：
1. 接收 Inertia props：calendars（分頁資料）、filters（篩選條件）
2. 使用 DataTable 組件顯示資料
3. 包含篩選功能（年份、月份、日期類型）
4. 包含分頁功能
5. 使用 Tailwind CSS 樣式
```

#### 建立表單頁

```
請幫我建立 resources/js/Pages/Calendar/Create.tsx

需求：
1. 使用 Inertia 的 useForm 處理表單
2. 欄位：date（日期）、day_type（日期類型）、is_workday（是否工作日）、name（名稱）
3. 包含表單驗證和錯誤顯示
4. 送出後返回列表頁
5. 使用我們的 Input、Select 共用組件
```

#### 建立共用組件

```
請幫我建立 resources/js/Components/Tables/DataTable.tsx

需求：
1. 接收泛型資料陣列
2. 支援排序功能
3. 支援分頁（使用 Inertia 的分頁）
4. 使用 Tailwind CSS 樣式
5. 響應式設計（手機版顯示卡片模式）
```

---

## 文檔索引

### 系統設計文檔（在 docs/ 目錄）

| 文檔 | 說明 | 狀態 |
|------|------|------|
| [1000_差勤系統概述.md](docs/1000_差勤系統概述.md) | 系統整體架構、資料流程、功能模組 | ✅ 完成 |
| [1001_行事曆作業.md](docs/1001_行事曆作業.md) | 行事曆功能詳細說明、業務邏輯 | ✅ 完成 |
| [1002_原始打卡表.md](docs/1002_原始打卡表.md) | 打卡記錄功能說明 | 📋 規劃中 |
| [1003_每日出勤統計.md](docs/1003_每日出勤統計.md) | 每日出勤功能說明 | 📋 規劃中 |
| [1004_每月出勤統計.md](docs/1004_每月出勤統計.md) | 每月統計功能說明 | 📋 規劃中 |

### API 文檔

| 文檔 | 說明 |
|------|------|
| [POSTMAN_TESTS.md](POSTMAN_TESTS.md) | 完整的 API 測試範例、Tinker 測試、前端整合指南 |

---

## 常見問題

### Q1: Inertia 和傳統 API 有什麼不同？

**傳統方式（REST API）**：
```tsx
// 需要手動 fetch API
const response = await fetch('/api/calendar');
const data = await response.json();
```

**Inertia 方式**：
```tsx
// 資料自動透過 props 傳遞，不需要 fetch
export default function Index({ calendars }) {
    // calendars 已經是解析好的資料
}
```

### Q2: 如何知道後端傳了哪些資料？

1. 查看 Controller 的 `Inertia::render()` 第二個參數
2. 查看 `POSTMAN_TESTS.md` 的 Response 範例
3. 使用瀏覽器開發者工具查看 Network 請求

### Q3: TypeScript 型別從哪裡來？

1. 後端定義資料結構（Model）
2. 前端在 `resources/js/types/models.d.ts` 定義對應的 TypeScript interface
3. 可以使用工具自動產生（如 Laravel IDE Helper）

### Q4: 如何整合付費模板？

1. 將付費模板的組件複製到 `resources/js/Components/`
2. 調整 import 路徑
3. 確保 Tailwind 配置包含組件的樣式
4. 在頁面中引入並使用

### Q5: 為什麼只有一個 Blade 檔案？

Inertia.js 的特點：
- 只需要一個根模板（`app.blade.php`）
- 所有頁面都是 React 組件
- 不需要為每個頁面建立 Blade 檔案

---

## 開發流程建議

### 1. 第一次開發

1. ✅ 閱讀本 README
2. ✅ 查看 `docs/` 了解業務邏輯
3. ✅ 查看 `POSTMAN_TESTS.md` 了解 API
4. ⏭ 建立基礎佈局（HrmLayout）
5. ⏭ 建立共用組件（Input、Select、DataTable）
6. ⏭ 實作第一個頁面（Calendar/Index）
7. ⏭ 逐步完成其他頁面

### 2. 日常開發

1. 查看後端 Controller 確認資料結構
2. 建立 TypeScript 型別定義
3. 建立 React 組件
4. 測試功能
5. 提交程式碼

### 3. 與 AI 協作

1. 將本 README 提供給 AI
2. 說明你要實作的功能
3. 提供具體需求（資料結構、UI 需求）
4. AI 產生程式碼後，測試並調整

---

## 聯絡方式

- **後端問題**：詢問後端工程師
- **業務邏輯**：查看 `docs/` 文檔
- **API 問題**：查看 `POSTMAN_TESTS.md`
- **前端問題**：參考本文件或與 AI 協作

---

## 授權

本專案為內部專案，未經授權不得外傳。

---

**最後更新**: 2026-02-09
**版本**: 1.0.0
**維護者**: HRM 開發團隊
