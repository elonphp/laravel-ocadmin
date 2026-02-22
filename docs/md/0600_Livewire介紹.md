# Livewire 介紹

> 建立日期：2026-02-21

## 概述

Livewire 是 Laravel 官方的全端框架，讓你**用 PHP 寫互動式 UI**，不需要寫 JavaScript。它的核心理念是：Blade 模板負責渲染 HTML，PHP class 負責處理狀態與邏輯，兩者之間的互動由 Livewire 自動處理（透過 AJAX）。

### 與其他方案的定位

| 方案 | 語言 | 互動方式 | 適用場景 |
|------|------|----------|----------|
| **Blade（傳統）** | PHP | 每次操作整頁重新載入 | 靜態頁面、簡單表單 |
| **Blade + Livewire** | PHP | 局部 AJAX 更新，不換頁 | 後台系統、CRUD、表單互動 |
| **Blade + Livewire + Alpine.js** | PHP + 少量 JS | 伺服器互動 + 純前端互動 | 上述 + 下拉選單、動畫等 |
| **Inertia + React** | PHP + TypeScript | SPA 體驗，前後端分離 | 高互動前台、需要 React 生態 |

### Livewire 的優勢

- **只需要 PHP** — 不需要 npm、不需要 build、不需要 Node.js
- **跟 Laravel 無縫整合** — Eloquent、Validation、Policy、Session 直接用
- **漸進式採用** — 傳統 Blade 頁面中，只在需要互動的地方加入 Livewire
- **完全掌控 HTML** — 不像 Filament 有框架限制，你寫什麼就是什麼

---

## 核心概念：Livewire 元件

一個 Livewire 元件由兩個檔案組成：

```
app/Livewire/Counter.php          ← PHP class（邏輯 + 狀態）
resources/views/livewire/counter.blade.php  ← Blade 模板（畫面）
```

### PHP Class

```php
<?php

namespace App\Livewire;

use Livewire\Component;

class Counter extends Component
{
    public int $count = 0;       // 公開屬性 = 狀態，自動同步到前端

    public function increment()   // 公開方法 = 動作，前端可觸發
    {
        $this->count++;
    }

    public function render()
    {
        return view('livewire.counter');
    }
}
```

### Blade 模板

```blade
<div>
    <h1>{{ $count }}</h1>
    <button wire:click="increment">+1</button>
</div>
```

### 發生了什麼事？

```
使用者點擊 "+1" 按鈕
        │
        ▼
Livewire 攔截 click 事件
        │
        ▼
發送 AJAX 到 Laravel 後端
        │
        ▼
執行 Counter::increment()
$count 從 0 變成 1
        │
        ▼
重新渲染 Blade 模板
        │
        ▼
Livewire 只更新畫面中有變動的部分（diff）
        │
        ▼
使用者看到數字從 0 變成 1（不換頁）
```

**關鍵：每次互動都是一次 AJAX 來回，但使用者感覺像是即時的。**

---

## 架構全貌

### 一個完整的 CRUD 頁面涉及哪些檔案？

以「商品管理」為例：

```
routes/web.php                                    ← 路由
app/Livewire/Product/ProductList.php              ← 列表頁元件
app/Livewire/Product/ProductForm.php              ← 表單元件（新增/編輯共用）
resources/views/livewire/product/product-list.blade.php   ← 列表模板
resources/views/livewire/product/product-form.blade.php   ← 表單模板
resources/views/layouts/app.blade.php             ← 共用版面
app/Models/Product.php                            ← Model（不變）
```

### 請求流程

```
瀏覽器 GET /admin/products
        │
        ▼
routes/web.php
    Route::get('/admin/products', ProductList::class)
        │
        ▼
Livewire 建立 ProductList 元件實例
    呼叫 render()，回傳完整 HTML
        │
        ▼
使用者看到商品列表
        │
        ▼
使用者在搜尋框輸入「手機」
    wire:model.live.debounce.300ms="search"
        │
        ▼
Livewire 發送 AJAX（不換頁）
    攜帶：元件 ID + 新的 $search 值
        │
        ▼
後端更新 $search 屬性
    重新執行 render()
    查詢 Product::where('name', 'like', '%手機%')
        │
        ▼
回傳新的 HTML diff
    只更新表格內容，搜尋框保持焦點
```

---

## 路由

### 整頁元件（Full-page Component）

Livewire 元件可以直接當作路由的目標，不需要 Controller：

```php
// routes/web.php
use App\Livewire\Product\ProductList;
use App\Livewire\Product\ProductForm;

Route::middleware(['auth'])->prefix('admin')->group(function () {
    Route::get('/products', ProductList::class)->name('admin.products.index');
    Route::get('/products/create', ProductForm::class)->name('admin.products.create');
    Route::get('/products/{product}/edit', ProductForm::class)->name('admin.products.edit');
});
```

> **注意**：路由直接指向 Livewire class，不需要 Controller。Livewire 元件本身就扮演了 Controller + View 的角色。

### 巢狀元件（Nested Component）

也可以在 Blade 模板中嵌入 Livewire 元件，適合局部互動：

```blade
{{-- resources/views/pages/dashboard.blade.php --}}
<x-layouts.app>
    <h1>儀表板</h1>

    {{-- 這個區塊是 Livewire 元件，有獨立的互動能力 --}}
    <livewire:recent-orders />

    {{-- 另一個獨立的 Livewire 元件 --}}
    <livewire:sales-chart />
</x-layouts.app>
```

### 路由參數

Livewire 元件可以透過 `mount()` 接收路由參數：

```php
// Route: /admin/products/{product}/edit

class ProductForm extends Component
{
    public ?Product $product = null;
    public string $name = '';
    public int $price = 0;

    // mount() 等同於 Controller 的建構邏輯，只在首次載入時執行一次
    public function mount(?Product $product = null)
    {
        if ($product) {
            $this->product = $product;
            $this->name = $product->name;
            $this->price = $product->price;
        }
    }
}
```

---

## 屬性與資料綁定

### 公開屬性 = 狀態

```php
class ProductForm extends Component
{
    // 這些屬性會自動同步到前端
    public string $name = '';
    public int $price = 0;
    public string $description = '';
    public int $category_id = 0;
}
```

### wire:model — 雙向綁定

```blade
{{-- 輸入框的值與 PHP 的 $name 同步 --}}
<input type="text" wire:model="name">

{{-- .live：每次輸入都即時同步（預設是 blur 時才同步） --}}
<input type="text" wire:model.live="search">

{{-- .live.debounce.300ms：停止輸入 300ms 後才同步 --}}
<input type="text" wire:model.live.debounce.300ms="search">
```

### 修飾符比較

| 寫法 | 同步時機 | 適用場景 |
|------|----------|----------|
| `wire:model="name"` | 失去焦點（blur）時 | 一般表單欄位 |
| `wire:model.live="search"` | 每次按鍵 | 即時搜尋 |
| `wire:model.live.debounce.300ms="search"` | 停止輸入 300ms 後 | 搜尋（避免過多請求） |
| `wire:model.blur="name"` | 明確失去焦點時 | 同 預設行為 |
| `wire:model.change="category_id"` | 值改變時 | 下拉選單 |

---

## 動作（Actions）

### wire:click — 點擊觸發

```blade
<button wire:click="save">儲存</button>
<button wire:click="delete({{ $product->id }})">刪除</button>
<button wire:click="setSort('name')">依名稱排序</button>
```

```php
class ProductList extends Component
{
    public string $sortBy = 'id';

    public function setSort(string $column)
    {
        $this->sortBy = $column;
    }

    public function delete(int $id)
    {
        Product::findOrFail($id)->delete();
    }
}
```

### wire:submit — 表單送出

```blade
<form wire:submit="save">
    <input type="text" wire:model="name">
    <input type="number" wire:model="price">
    <button type="submit">儲存</button>
</form>
```

```php
public function save()
{
    $this->validate([
        'name' => 'required|max:255',
        'price' => 'required|integer|min:0',
    ]);

    Product::create([
        'name' => $this->name,
        'price' => $this->price,
    ]);

    return redirect()->route('admin.products.index');
}
```

### wire:confirm — 確認對話框

```blade
<button
    wire:click="delete({{ $product->id }})"
    wire:confirm="確定要刪除此商品嗎？"
>
    刪除
</button>
```

---

## 驗證（Validation）

Livewire 使用 Laravel 原生的驗證規則，完全一樣：

```php
class ProductForm extends Component
{
    public string $name = '';
    public int $price = 0;
    public string $description = '';

    public function save()
    {
        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|integer|min:0',
            'description' => 'nullable|string|max:1000',
        ]);

        Product::create($validated);

        session()->flash('success', '商品已建立');
        return redirect()->route('admin.products.index');
    }
}
```

```blade
<form wire:submit="save">
    <div>
        <label>商品名稱</label>
        <input type="text" wire:model="name">
        @error('name')
            <span class="text-red-500 text-sm">{{ $message }}</span>
        @enderror
    </div>

    <div>
        <label>價格</label>
        <input type="number" wire:model="price">
        @error('price')
            <span class="text-red-500 text-sm">{{ $message }}</span>
        @enderror
    </div>

    <button type="submit">儲存</button>
</form>
```

### 即時驗證

```php
// 使用者輸入完（blur）立即驗證該欄位
public function updated(string $property)
{
    $this->validateOnly($property, [
        'name' => 'required|string|max:255',
        'price' => 'required|integer|min:0',
    ]);
}
```

---

## 生命週期

```php
class ProductForm extends Component
{
    public string $name = '';
    public int $category_id = 0;

    // 1. mount()：元件首次建立時執行（類似 Controller 的建構）
    //    只執行一次
    public function mount(?Product $product = null)
    {
        if ($product) {
            $this->name = $product->name;
        }
    }

    // 2. hydrate()：每次 AJAX 請求時，元件從快照還原後執行
    //    很少需要用到
    public function hydrate()
    {
        // ...
    }

    // 3. updated{Property}()：特定屬性更新後執行
    //    常用於連動邏輯
    public function updatedCategoryId(int $value)
    {
        // 使用者選了分類後，自動載入該分類的子選項
        $this->subcategories = Category::find($value)->children;
    }

    // 4. render()：每次都執行，回傳 Blade 視圖
    public function render()
    {
        return view('livewire.product.product-form', [
            'categories' => Category::all(),
        ]);
    }
}
```

### 生命週期順序

```
首次載入：
    mount() → render() → 回傳完整 HTML

後續互動（每次 AJAX）：
    hydrate() → 執行動作/更新屬性 → updated() → render() → 回傳 HTML diff
```

---

## 完整 CRUD 範例：商品管理

### 路由

```php
// routes/web.php
use App\Livewire\Product\ProductList;
use App\Livewire\Product\ProductForm;

Route::middleware(['auth'])->prefix('admin')->group(function () {
    Route::get('/products', ProductList::class)->name('admin.products.index');
    Route::get('/products/create', ProductForm::class)->name('admin.products.create');
    Route::get('/products/{product}/edit', ProductForm::class)->name('admin.products.edit');
});
```

### 列表頁

#### `app/Livewire/Product/ProductList.php`

```php
<?php

namespace App\Livewire\Product;

use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;

class ProductList extends Component
{
    use WithPagination;

    public string $search = '';
    public string $sortBy = 'id';
    public string $sortDir = 'desc';

    // 搜尋條件改變時，回到第一頁
    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function sort(string $column)
    {
        if ($this->sortBy === $column) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDir = 'asc';
        }
    }

    public function delete(int $id)
    {
        Product::findOrFail($id)->delete();
    }

    public function render()
    {
        $products = Product::query()
            ->when($this->search, fn ($q) =>
                $q->where('name', 'like', "%{$this->search}%")
            )
            ->orderBy($this->sortBy, $this->sortDir)
            ->paginate(15);

        return view('livewire.product.product-list', [
            'products' => $products,
        ]);
    }
}
```

#### `resources/views/livewire/product/product-list.blade.php`

```blade
<div>
    {{-- 搜尋框 --}}
    <div class="mb-4 flex items-center justify-between">
        <input
            type="text"
            wire:model.live.debounce.300ms="search"
            placeholder="搜尋商品..."
            class="input input-bordered w-64"
        >
        <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
            新增商品
        </a>
    </div>

    {{-- 表格 --}}
    <table class="table">
        <thead>
            <tr>
                <th wire:click="sort('id')" class="cursor-pointer">
                    ID
                    @if($sortBy === 'id')
                        {{ $sortDir === 'asc' ? '▲' : '▼' }}
                    @endif
                </th>
                <th wire:click="sort('name')" class="cursor-pointer">
                    名稱
                    @if($sortBy === 'name')
                        {{ $sortDir === 'asc' ? '▲' : '▼' }}
                    @endif
                </th>
                <th>價格</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody>
            @forelse($products as $product)
                <tr>
                    <td>{{ $product->id }}</td>
                    <td>{{ $product->name }}</td>
                    <td>${{ number_format($product->price) }}</td>
                    <td>
                        <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-sm">
                            編輯
                        </a>
                        <button
                            wire:click="delete({{ $product->id }})"
                            wire:confirm="確定要刪除「{{ $product->name }}」嗎？"
                            class="btn btn-sm btn-error"
                        >
                            刪除
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center">找不到商品</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- 分頁 --}}
    <div class="mt-4">
        {{ $products->links() }}
    </div>
</div>
```

### 表單頁（新增/編輯共用）

#### `app/Livewire/Product/ProductForm.php`

```php
<?php

namespace App\Livewire\Product;

use App\Models\Product;
use Livewire\Component;

class ProductForm extends Component
{
    public ?Product $product = null;

    public string $name = '';
    public int $price = 0;
    public string $description = '';
    public int $category_id = 0;

    public function mount(?Product $product = null)
    {
        if ($product) {
            $this->product = $product;
            $this->name = $product->name;
            $this->price = $product->price;
            $this->description = $product->description ?? '';
            $this->category_id = $product->category_id;
        }
    }

    public function save()
    {
        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|integer|min:0',
            'description' => 'nullable|string|max:1000',
            'category_id' => 'required|exists:categories,id',
        ]);

        if ($this->product) {
            $this->product->update($validated);
            session()->flash('success', '商品已更新');
        } else {
            Product::create($validated);
            session()->flash('success', '商品已建立');
        }

        return redirect()->route('admin.products.index');
    }

    public function render()
    {
        return view('livewire.product.product-form', [
            'categories' => \App\Models\Category::all(),
            'isEdit' => $this->product !== null,
        ]);
    }
}
```

#### `resources/views/livewire/product/product-form.blade.php`

```blade
<div>
    <h2 class="text-2xl font-bold mb-4">
        {{ $isEdit ? '編輯商品' : '新增商品' }}
    </h2>

    <form wire:submit="save" class="space-y-4 max-w-lg">
        {{-- 商品名稱 --}}
        <div class="form-control">
            <label class="label">商品名稱</label>
            <input type="text" wire:model="name" class="input input-bordered">
            @error('name')
                <span class="text-error text-sm mt-1">{{ $message }}</span>
            @enderror
        </div>

        {{-- 價格 --}}
        <div class="form-control">
            <label class="label">價格</label>
            <input type="number" wire:model="price" class="input input-bordered">
            @error('price')
                <span class="text-error text-sm mt-1">{{ $message }}</span>
            @enderror
        </div>

        {{-- 分類 --}}
        <div class="form-control">
            <label class="label">分類</label>
            <select wire:model="category_id" class="select select-bordered">
                <option value="0">請選擇分類</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
            @error('category_id')
                <span class="text-error text-sm mt-1">{{ $message }}</span>
            @enderror
        </div>

        {{-- 描述 --}}
        <div class="form-control">
            <label class="label">描述</label>
            <textarea wire:model="description" class="textarea textarea-bordered" rows="4"></textarea>
            @error('description')
                <span class="text-error text-sm mt-1">{{ $message }}</span>
            @enderror
        </div>

        {{-- 按鈕 --}}
        <div class="flex gap-2">
            <button type="submit" class="btn btn-primary">
                {{ $isEdit ? '更新' : '建立' }}
            </button>
            <a href="{{ route('admin.products.index') }}" class="btn">取消</a>
        </div>
    </form>
</div>
```

---

## Layout（版面配置）

Livewire 整頁元件需要一個 Layout 來包裹：

### 方式一：使用 Blade Component Layout（推薦）

#### `resources/views/components/layouts/app.blade.php`

```blade
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }}</title>
    {{-- Tailwind CDN（開發用）或編譯後的 CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/daisyui@5/daisyui.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
    <div class="drawer lg:drawer-open">
        {{-- 側邊欄 --}}
        <input id="drawer" type="checkbox" class="drawer-toggle">
        <div class="drawer-side">
            <label for="drawer" class="drawer-overlay"></label>
            <ul class="menu bg-base-200 w-64 min-h-full p-4">
                <li><a href="{{ route('admin.products.index') }}">商品管理</a></li>
                <li><a href="#">訂單管理</a></li>
                <li><a href="#">會員管理</a></li>
            </ul>
        </div>

        {{-- 主要內容 --}}
        <div class="drawer-content p-6">
            @if(session('success'))
                <div class="alert alert-success mb-4">{{ session('success') }}</div>
            @endif

            {{ $slot }}
        </div>
    </div>
</body>
</html>
```

Livewire 整頁元件會自動使用此 layout（在 `config/livewire.php` 設定）。也可以在元件中指定：

```php
class ProductList extends Component
{
    public function render()
    {
        return view('livewire.product.product-list')
            ->layout('components.layouts.app');   // 指定 layout
    }
}
```

---

## 與 Alpine.js 搭配

Livewire 內建 Alpine.js。純前端互動（不需要後端參與的）用 Alpine 處理，避免不必要的 AJAX：

```blade
{{-- 下拉選單：純前端互動，用 Alpine --}}
<div x-data="{ open: false }" class="relative">
    <button @click="open = !open" class="btn">選單</button>
    <ul x-show="open" @click.away="open = false" class="menu bg-base-100 shadow mt-2 absolute">
        <li><a href="#">選項 A</a></li>
        <li><a href="#">選項 B</a></li>
    </ul>
</div>

{{-- 刪除確認 Modal：Alpine 控制顯示，Livewire 處理刪除 --}}
<div x-data="{ showModal: false, productId: null }">
    <button @click="showModal = true; productId = {{ $product->id }}">
        刪除
    </button>

    <div x-show="showModal" class="modal modal-open">
        <div class="modal-box">
            <p>確定要刪除嗎？</p>
            <div class="modal-action">
                <button @click="showModal = false" class="btn">取消</button>
                <button
                    @click="$wire.delete(productId); showModal = false"
                    class="btn btn-error"
                >
                    確定刪除
                </button>
            </div>
        </div>
    </div>
</div>
```

### 分工原則

| 互動類型 | 用誰 | 範例 |
|----------|------|------|
| 需要存取資料庫 | **Livewire**（wire:click） | 儲存、刪除、搜尋、篩選 |
| 純畫面切換 | **Alpine.js**（x-show, @click） | 開關 Modal、Tab 切換、下拉選單 |
| 兩者混合 | **Alpine 控制 UI + `$wire` 呼叫 Livewire** | 確認對話框後執行刪除 |

---

## 與傳統 Laravel MVC 的對比

### 傳統 MVC

```
Route → Controller → View（Blade）
                  ↑
              每次操作都是完整的 HTTP 請求/回應
```

```php
// routes/web.php
Route::get('/products', [ProductController::class, 'index']);
Route::post('/products', [ProductController::class, 'store']);

// ProductController.php
public function index()
{
    $products = Product::paginate(15);
    return view('products.index', compact('products'));
}

public function store(Request $request)
{
    $request->validate([...]);
    Product::create($request->all());
    return redirect()->route('products.index');
}
```

### Livewire

```
Route → Livewire Component（PHP class + Blade 模板）
                  ↑
              首次載入是完整 HTTP，之後都是 AJAX 局部更新
```

```php
// routes/web.php
Route::get('/products', ProductList::class);

// ProductList.php — 同時處理顯示、搜尋、排序、刪除
class ProductList extends Component
{
    public string $search = '';

    public function delete(int $id) { ... }

    public function render()
    {
        return view('livewire.product.product-list', [
            'products' => Product::where('name', 'like', "%{$this->search}%")->paginate(15),
        ]);
    }
}
```

**差別：** 傳統 MVC 每個操作都是一個 route + 一個 Controller method。Livewire 把相關操作集中在同一個元件裡，畫面不重新載入。

---

## 自訂元件目錄（Portal 架構）

預設 Livewire 元件放在 `app/Livewire/`，但可以自訂路徑，適合多 Portal 架構。

### 方式一：改 config（全域替換）

```php
// config/livewire.php
'class_namespace' => 'App\\Portals\\Admin\\Livewire',
'view_path' => resource_path('views/livewire'),
```

### 方式二：在 ServiceProvider 註冊額外命名空間（推薦）

保留預設的 `app/Livewire`，同時加入自訂路徑，多個 Portal 可以共存：

```php
// app/Providers/AppServiceProvider.php
use Livewire\Livewire;

public function boot(): void
{
    // 前台
    Livewire::componentNamespace(
        'App\\Portals\\Front\\Livewire',
        'front'
    );

    // 後台
    Livewire::componentNamespace(
        'App\\Portals\\Admin\\Livewire',
        'admin'
    );
}
```

使用時加上前綴：

```blade
{{-- 前台元件：對應 App\Portals\Front\Livewire\ProductList --}}
<livewire:front.product-list />

{{-- 後台元件：對應 App\Portals\Admin\Livewire\Dashboard --}}
<livewire:admin.dashboard />
```

路由也一樣可以直接指向自訂路徑的元件：

```php
Route::get('/products', \App\Portals\Front\Livewire\ProductList::class);
```

### Portal 目錄結構範例

```
app/Portals/
├── Front/                          # 前台（電商）
│   ├── Livewire/
│   │   ├── ProductList.php
│   │   ├── ProductDetail.php
│   │   └── Cart.php
│   ├── Views/
│   │   └── livewire/
│   │       ├── product-list.blade.php
│   │       ├── product-detail.blade.php
│   │       └── cart.blade.php
│   └── routes/
│       └── front.php
│
└── Admin/                          # 後台（管理）
    ├── Livewire/
    │   ├── Product/
    │   │   ├── ProductList.php
    │   │   └── ProductForm.php
    │   └── Order/
    │       └── OrderList.php
    ├── Views/
    │   └── livewire/
    │       ├── product/
    │       └── order/
    └── routes/
        └── admin.php
```

> **重點**：Livewire 不綁死目錄結構，只要 PHP class 繼承 `Livewire\Component`，放哪裡都行，註冊好命名空間就能用。

### 視圖路徑也要對應註冊

如果 Blade 模板不放在預設的 `resources/views/livewire/`，需要在 ServiceProvider 註冊視圖命名空間：

```php
// app/Portals/Front/Providers/FrontServiceProvider.php
public function boot(): void
{
    // 註冊視圖命名空間
    $this->loadViewsFrom(
        app_path('Portals/Front/Views'),
        'front'
    );

    // 註冊 Livewire 元件命名空間
    Livewire::componentNamespace(
        'App\\Portals\\Front\\Livewire',
        'front'
    );
}
```

元件的 `render()` 使用命名空間視圖：

```php
// App\Portals\Front\Livewire\ProductList
public function render()
{
    return view('front::livewire.product-list', [
        'products' => $products,
    ]);
}
```

---

## 常用指令

```bash
# 建立 Livewire 元件（同時產生 PHP class + Blade 模板）
./php.bat artisan make:livewire Product/ProductList

# 建立只有 PHP class 的行內元件（不產生 Blade 模板）
./php.bat artisan make:livewire Counter --inline
```

---

## 常見問題

### Q: Livewire 每次互動都要打 AJAX，會不會很慢？

不會。一次 AJAX 通常在 50-200ms 內完成。搭配 `wire:model.live.debounce.300ms` 控制頻率，使用者幾乎感覺不到延遲。後台系統的互動頻率不高，這個延遲完全可接受。

### Q: 什麼時候不該用 Livewire？

- **高頻拖拉排序**（如 Trello 看板）— 用 Alpine.js + SortableJS
- **即時繪圖/動畫** — 用 JavaScript 圖表庫
- **離線功能** — 需要 Service Worker，非 Livewire 範疇

這些場景可以在 Livewire 頁面中局部使用 JavaScript，不需要整個換掉。

### Q: CSS 框架可以自由搭配嗎？

可以。Livewire 只管 PHP 互動（`wire:click`、`wire:model`），完全不管 CSS。Blade 模板裡寫什麼 class 就是什麼，Tailwind、DaisyUI、Bootstrap 都可以用，也可以混用。本文件的範例全部使用 **Tailwind + DaisyUI**。

### Q: Livewire 跟 Filament 的關係？

Filament 是建立在 Livewire 之上的框架。用 Livewire 等於是自己掌控一切，Filament 則是用別人定好的規則。

```
Livewire = 自己蓋房子，完全自由
Filament = 住別人蓋好的房子，方便但改裝受限
```

---

## 相關文件

- [0501_Figma設計轉程式碼工作流程](0501_Figma設計轉程式碼工作流程.md) — 方案 A（Blade + Livewire）的 AI 切版流程

---

**文件版本**: 1.1
**建立日期**: 2026-02-21
**最後更新**: 2026-02-22
