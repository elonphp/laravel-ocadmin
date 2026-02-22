# Figma 設計轉程式碼工作流程

## 概述

本文件說明如何運用 AI，將 Figma 設計稿轉換為可用的程式碼。流程分為三個階段：

1. **Phase 1**：設計師在 Figma 準備與匯出素材
2. **Phase 2**：AI 讀取設計稿，產生程式碼
3. **Phase 3**：工程師加入業務邏輯，整合框架

技術棧的選擇（Blade vs Inertia）以及前後端分離的架構決策，請參考 [0900_Inertia統一架構](./0900_Inertia統一架構.md)。

---

## Phase 1：設計師準備素材

### Figma 檔案結構

```
Figma 專案
├── [設計頁面]          ← 正常設計工作區
│   ├── Home
│   ├── About
│   └── Contact
│
└── [For Export]        ← 匯出專用頁面
    ├── 00-style-guide
    ├── 01-components
    ├── 02-layout
    ├── 03-page-home
    ├── 04-page-about
    └── 05-page-contact
```

### 必要匯出項目

#### 00-style-guide（設計規範）

```
┌─────────────────────────────────────────────────────────┐
│ Style Guide                                             │
├─────────────────────────────────────────────────────────┤
│                                                         │
│ Colors                                                  │
│ ┌─────┐ ┌─────┐ ┌─────┐ ┌─────┐ ┌─────┐               │
│ │ Pri │ │ Sec │ │ Bg  │ │Text │ │Error│               │
│ │#2563│ │#6366│ │#f5f5│ │#1a1a│ │#ef44│               │
│ └─────┘ └─────┘ └─────┘ └─────┘ └─────┘               │
│                                                         │
│ Typography                                              │
│ H1: Montserrat Bold 48px                               │
│ H2: Montserrat Bold 36px                               │
│ Body: Noto Sans TC 16px                                │
│                                                         │
│ Spacing: xs:4  sm:8  md:16  lg:24  xl:32              │
│ Radius:  sm:4  md:8  lg:16                             │
│                                                         │
└─────────────────────────────────────────────────────────┘
```

#### 01-components（元件庫）

```
┌─────────────────────────────────────────────────────────┐
│ Components                                              │
├─────────────────────────────────────────────────────────┤
│                                                         │
│ Buttons                                                 │
│ ┌─────────┐ ┌─────────┐ ┌─────────┐  ← 樣式變體        │
│ │ Primary │ │Secondary│ │ Outline │                    │
│ └─────────┘ └─────────┘ └─────────┘                    │
│ ┌───┐ ┌─────────┐ ┌───────────────┐  ← 尺寸變體        │
│ │ S │ │    M    │ │       L       │                    │
│ └───┘ └─────────┘ └───────────────┘                    │
│                                                         │
│ Inputs                                                  │
│ ┌─────────────────────┐  Default                       │
│ │ placeholder...      │                                │
│ └─────────────────────┘                                │
│ ┌─────────────────────┐  Error                         │
│ │ ⚠ Error message     │                                │
│ └─────────────────────┘                                │
│                                                         │
│ Cards, Alerts, Badges...                               │
│                                                         │
└─────────────────────────────────────────────────────────┘
```

#### 02-layout（版面結構）

```
┌─────────────────────────────────────────────────────────┐
│ ┌─────────────────────────────────────────────────────┐│
│ │ Header                                    [Nav] [Btn]││
│ └─────────────────────────────────────────────────────┘│
│ ┌─────────────────────────────────────────────────────┐│
│ │                    Main Content                     ││
│ └─────────────────────────────────────────────────────┘│
│ ┌─────────────────────────────────────────────────────┐│
│ │ Footer                              [Links] [Social]││
│ └─────────────────────────────────────────────────────┘│
└─────────────────────────────────────────────────────────┘
```

### 匯出設定

```
1. 選取 "For Export" 內所有 Frame
2. Export 設定：
   - Format: PNG
   - Scale: 2x
3. 匯出全部
```

### 交付檔案

```
/design-exports/
├── 00-style-guide.png
├── 01-components.png
├── 02-layout.png
├── 03-page-home.png
├── 04-page-about.png
├── 05-page-contact.png
└── assets/
    ├── logo.svg
    ├── images/
    └── icons/
```

### 設計師注意事項

#### 命名規範

```
✓ 好的命名
  00-style-guide
  01-components
  button-primary-default
  button-primary-hover

✗ 避免的命名
  設計稿-最終版v2
  新版本
  未命名
```

#### 元件狀態完整

```
Button 需包含：
├── Variants: Primary / Secondary / Outline / Ghost
├── Sizes: Small / Medium / Large
└── States: Default / Hover / Active / Disabled

Input 需包含：
├── States: Empty / Filled / Focus / Error / Disabled
├── With/Without: Label, Helper text, Error message
└── Types: Text, Password, Textarea
```

---

## Phase 2 & 3：AI 轉換與工程師整合

以下分別說明兩種技術棧的流程。

---

## 方案 A：Blade + Livewire + Tailwind

適用於：純後台系統、簡單官網。

### 流程概覽

```
Phase 2              Phase 3
AI                   工程師
──                   ───────

讀取設計稿           檢視產出
    │                   │
    ▼                   ▼
分析 Style Guide     微調樣式
    │                   │
    ▼                   ▼
產生 tailwind.config  加入 @foreach
    │                   │
    ▼                   ▼
產生 Blade 元件       加入 @can
    │                   │
    ▼                   ▼
產生頁面             整合 Livewire
```

### AI Prompt 範本

```
我要將 Figma 設計轉成 Laravel Blade + Tailwind 專案。
設計稿在 /design-exports/ 資料夾。

請依照以下順序處理：

## Step 1：分析設計規範
讀取 00-style-guide.png，產生：
- tailwind.config.js（colors, fontFamily, spacing, borderRadius）

## Step 2：建立元件庫
讀取 01-components.png，產生 Blade 元件：
- resources/views/components/button.blade.php
- resources/views/components/input.blade.php
- resources/views/components/card.blade.php
（依圖片內容決定）

每個元件：
- 使用 @props 支援變體
- 使用 tailwind.config.js 的設計變數
- 附上使用範例註解

## Step 3：建立版面
讀取 02-layout.png，產生：
- resources/views/layouts/app.blade.php
- resources/views/partials/header.blade.php
- resources/views/partials/footer.blade.php

## Step 4：建立頁面
讀取其他 page-*.png，使用上述元件組合頁面。

## 要求
- 風格一致，使用設計變數
- RWD 響應式（mobile-first）
- 語意化 HTML
```

### 預期產出

```
resources/
├── css/
│   └── app.css
├── views/
│   ├── layouts/
│   │   └── app.blade.php
│   ├── components/
│   │   ├── button.blade.php
│   │   ├── input.blade.php
│   │   └── card.blade.php
│   ├── partials/
│   │   ├── header.blade.php
│   │   └── footer.blade.php
│   └── pages/
│       ├── home.blade.php
│       └── contact.blade.php
│
tailwind.config.js
```

### 工程師整合

#### 加入 Blade 邏輯

```blade
{{-- AI 產出（靜態）--}}
<div class="product-card">
    <h2>產品名稱</h2>
    <p>產品描述</p>
    <button>加入購物車</button>
</div>

{{-- 工程師修改（動態）--}}
@foreach($products as $product)
    <x-card>
        <x-slot:title>{{ $product->name }}</x-slot:title>
        <p>{{ $product->description }}</p>

        @can('action.shop.cart.add')
            <x-button wire:click="addToCart({{ $product->id }})">
                加入購物車
            </x-button>
        @endcan
    </x-card>
@endforeach
```

#### 加入 Livewire 互動

```php
// app/Livewire/ContactForm.php
class ContactForm extends Component
{
    public $name = '';
    public $email = '';
    public $message = '';

    public function submit()
    {
        $this->validate([
            'name' => 'required',
            'email' => 'required|email',
            'message' => 'required|min:10',
        ]);

        Contact::create([...]);
        session()->flash('success', '訊息已送出');
    }

    public function render()
    {
        return view('livewire.contact-form');
    }
}
```

### Blade 元件範例

```blade
{{-- resources/views/components/button.blade.php --}}
{{--
  <x-button>Default</x-button>
  <x-button variant="outline" size="lg">Large</x-button>
--}}

@props([
    'variant' => 'primary',
    'size' => 'md',
    'href' => null,
])

@php
$classes = 'inline-flex items-center justify-center font-medium rounded-xl transition-all';

$classes .= match($variant) {
    'primary' => ' bg-primary-600 text-white hover:bg-primary-700',
    'secondary' => ' bg-secondary-500 text-white hover:bg-secondary-600',
    'outline' => ' border-2 border-primary-600 text-primary-600 hover:bg-primary-50',
    default => ' bg-primary-600 text-white hover:bg-primary-700',
};

$classes .= match($size) {
    'sm' => ' px-3 py-1.5 text-sm',
    'md' => ' px-5 py-2.5 text-base',
    'lg' => ' px-8 py-3 text-lg',
    default => ' px-5 py-2.5 text-base',
};
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif
```

---

## 方案 B：Inertia + React + Tailwind

適用於：前台 + 後台整合專案、需要良好 UX 的應用。

架構說明與程式碼範例請參考 [0900_Inertia統一架構](./0900_Inertia統一架構.md)。

### 流程概覽

```
Phase 2              Phase 3
AI                   工程師
──                   ───────

讀取設計稿           檢視產出
    │                   │
    ▼                   ▼
分析 Style Guide     微調樣式
    │                   │
    ▼                   ▼
產生 tailwind.config  加入 useState
    │                   │
    ▼                   ▼
產生 React 元件       加入權限判斷
    │                   │
    ▼                   ▼
產生頁面             整合 Inertia
```

### AI Prompt 範本

```
我要將 Figma 設計轉成 Laravel + Inertia + React + Tailwind 專案。
設計稿在 /design-exports/ 資料夾。

請依照以下順序處理：

## Step 1：分析設計規範
讀取 00-style-guide.png，產生：
- tailwind.config.js（colors, fontFamily, spacing, borderRadius）

## Step 2：建立元件庫
讀取 01-components.png，產生 React 元件：
- resources/js/Components/ui/Button.tsx
- resources/js/Components/ui/Input.tsx
- resources/js/Components/ui/Card.tsx
（依圖片內容決定）

每個元件：
- 使用 TypeScript
- 使用 forwardRef 支援 ref
- 使用 cn() 合併 className（參考 shadcn/ui）
- 支援變體（variant, size）props
- 附上使用範例註解

## Step 3：建立版面
讀取 02-layout.png，產生：
- resources/js/Layouts/AppLayout.tsx
- resources/js/Components/shared/Header.tsx
- resources/js/Components/shared/Footer.tsx

## Step 4：建立頁面
讀取其他 page-*.png，產生 Inertia 頁面：
- resources/js/Pages/Home.tsx
- resources/js/Pages/About.tsx
- 使用上述元件組合

## 要求
- TypeScript 嚴格模式
- 風格一致，使用設計變數
- RWD 響應式（mobile-first）
- 語意化 HTML
```

### 預期產出

```
resources/js/
├── Components/
│   ├── ui/                     ← 基礎 UI 元件
│   │   ├── Button.tsx
│   │   ├── Input.tsx
│   │   ├── Card.tsx
│   │   └── index.ts
│   │
│   └── shared/                 ← 業務共用元件
│       ├── Header.tsx
│       ├── Footer.tsx
│       └── ProductCard.tsx
│
├── Layouts/
│   └── AppLayout.tsx
│
├── Pages/
│   ├── Home.tsx
│   ├── About.tsx
│   └── Contact.tsx
│
├── lib/
│   └── utils.ts                ← cn() 等工具函式
│
└── app.tsx

tailwind.config.js
```

---

## tailwind.config.js（通用）

兩種方案共用相同的 Tailwind 設定：

```js
import defaultTheme from 'tailwindcss/defaultTheme'

export default {
  content: [
    "./resources/**/*.blade.php",   // Blade
    "./resources/**/*.tsx",          // React
    "./resources/**/*.ts",
  ],
  theme: {
    extend: {
      colors: {
        primary: {
          50: '#eff6ff',
          100: '#dbeafe',
          500: '#3b82f6',
          600: '#2563eb',
          700: '#1d4ed8',
        },
        secondary: {
          500: '#6366f1',
          600: '#4f46e5',
        },
        success: '#10b981',
        warning: '#f59e0b',
        error: '#ef4444',
      },
      fontFamily: {
        sans: ['Noto Sans TC', ...defaultTheme.fontFamily.sans],
        display: ['Montserrat', 'sans-serif'],
      },
      borderRadius: {
        'xl': '1rem',
        '2xl': '1.5rem',
      },
    },
  },
}
```

---

## 常見問題

### Q: AI 轉換不精確？

分段處理，針對問題部分重新描述：

```
Header 的間距不對。
Logo 和 Nav 之間應該是 48px，
Nav items 之間是 24px。
請重新產生 Header 元件。
```

### Q: 沒有手機版設計？

請 AI 自動產生：

```
設計稿只有桌面版，請產生 mobile-first 響應式版本：
- Mobile (<768px): 單欄、漢堡選單
- Desktop (>=768px): 依設計稿
```

### Q: 如何確保風格一致？

**先產生 tailwind.config.js，再產生元件和頁面。**

所有元件使用設計變數：

```tsx
// 使用設計變數
<button className="bg-primary-600">  ✓

// 避免寫死
<button className="bg-blue-600">     ✗
```

---

## 總結

```
設計師 → Figma 匯出 → AI 轉程式碼 → 工程師整合邏輯
```

| 傳統 | 本流程 |
|------|--------|
| 設計師 → 前端 → 後端 | 設計師 → AI → 工程師 |
| 需要前端工程師切版 | AI 輔助切版 |
| 設計還原度依賴個人功力 | AI 可反覆調整至精確 |

---

## 相關文件

- [0900_Inertia統一架構](./0900_Inertia統一架構.md) - 架構選擇、前後端分離決策、Inertia 完整說明
- [0500_權限機制](./0500_權限機制.md) - 權限命名規範
- [0502_Blade架構的權限實作](./0502_Blade架構的權限實作.md) - Blade + Livewire 權限實作
- [0800_選單機制](./0800_選單機制.md) - 選單建置方案

---

**文件版本**: 4.0
**建立日期**: 2026-02-04
**最後更新**: 2026-02-05
