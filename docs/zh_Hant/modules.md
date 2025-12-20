# 模組開發指南

本文件說明如何開發 Ocadmin 模組。

---

## 模組類型

| 類型 | 位置 | 說明 |
|------|------|------|
| 標準模組 | `vendor/.../src/Modules/` | 套件內建，不可修改 |
| 客製模組 | `app/Ocadmin/Modules/` | 專案自訂，可完全控制 |

---

## 快速建立模組

```bash
# 初始化 Ocadmin 目錄（首次）
php artisan ocadmin:init

# 建立新模組
php artisan ocadmin:module Inventory
```

---

## 模組結構

```
app/Ocadmin/Modules/Inventory/
├── Controllers/
│   └── InventoryController.php
├── Models/
│   └── Product.php
├── Services/
│   └── InventoryService.php
├── Repositories/
│   └── ProductRepository.php
├── Views/
│   ├── index.blade.php
│   ├── list.blade.php
│   └── form.blade.php
├── Routes/
│   └── routes.php
├── Config/
│   └── menu.php
├── database/
│   └── migrations/
│       └── 2025_01_01_000001_create_products_table.php
└── module.json
```

---

## module.json

模組設定檔：

```json
{
    "name": "Inventory",
    "description": "庫存管理模組",
    "version": "1.0.0",
    "priority": 50,
    "enabled": true,
    "providers": [],
    "aliases": {}
}
```

### 欄位說明

| 欄位 | 類型 | 說明 |
|------|------|------|
| `name` | string | 模組名稱（PascalCase） |
| `description` | string | 模組描述 |
| `version` | string | 版本號 |
| `priority` | int | 載入順序（數字越小越先載入） |
| `enabled` | bool | 是否啟用 |
| `providers` | array | 額外的 ServiceProvider |
| `aliases` | object | Facade 別名 |

---

## Controller

### 基礎 Controller

```php
<?php

namespace App\Ocadmin\Modules\Inventory\Controllers;

use Elonphp\LaravelOcadminModules\Core\Controllers\Controller;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        return $this->view('index', [
            'title' => '庫存管理',
        ]);
    }

    public function list(Request $request)
    {
        $products = Product::query()
            ->when($request->keyword, fn($q, $keyword) =>
                $q->where('name', 'like', "%{$keyword}%")
            )
            ->orderByDesc('id')
            ->paginate(10);

        return $this->view('list', compact('products'));
    }

    public function form(Request $request)
    {
        $product = $request->id
            ? Product::findOrFail($request->id)
            : new Product();

        return $this->view('form', compact('product'));
    }

    public function save(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|unique:products,sku,' . $request->id,
            'price' => 'required|numeric|min:0',
        ]);

        $product = $request->id
            ? Product::findOrFail($request->id)
            : new Product();

        $product->fill($validated)->save();

        return redirect()
            ->route('lang.ocadmin.inventory.index', ocadmin_locale())
            ->with('success', '儲存成功');
    }
}
```

### 基礎 Controller 提供的方法

```php
// 渲染視圖（自動加上模組前綴）
$this->view('index', $data);
// 等同於 view('ocadmin::modules.inventory.index', $data)

// 取得模組名稱
$this->moduleName();  // 'Inventory'

// 取得模組路徑
$this->modulePath();  // '/path/to/app/Ocadmin/Modules/Inventory'
```

---

## Routes

### 路由定義

```php
<?php
// app/Ocadmin/Modules/Inventory/Routes/routes.php

use Illuminate\Support\Facades\Route;
use App\Ocadmin\Modules\Inventory\Controllers\InventoryController;

Route::prefix('inventory')->name('inventory.')->group(function () {

    // 列表頁
    Route::get('/', [InventoryController::class, 'index'])
        ->name('index');

    // 列表內容（AJAX）
    Route::get('/list', [InventoryController::class, 'list'])
        ->name('list');

    // 表單頁（新增/編輯）
    Route::get('/form', [InventoryController::class, 'form'])
        ->name('form');

    // 儲存
    Route::post('/save', [InventoryController::class, 'save'])
        ->name('save');

    // 刪除
    Route::delete('/{id}', [InventoryController::class, 'destroy'])
        ->name('destroy');
});
```

### 完整路由

模組路由會自動加上語系和 ocadmin 前綴：

```
GET  /{locale}/ocadmin/inventory           → inventory.index
GET  /{locale}/ocadmin/inventory/list      → inventory.list
GET  /{locale}/ocadmin/inventory/form      → inventory.form
POST /{locale}/ocadmin/inventory/save      → inventory.save
DEL  /{locale}/ocadmin/inventory/{id}      → inventory.destroy
```

### 路由名稱

```php
route('lang.ocadmin.inventory.index', ['locale' => 'zh-TW'])

// 使用 helper
ocadmin_route('inventory.index')
```

---

## Views

### 視圖結構

```
app/Ocadmin/Modules/Inventory/Views/
├── index.blade.php      # 主頁面（含搜尋、列表容器）
├── list.blade.php       # 列表內容（AJAX 載入）
└── form.blade.php       # 表單頁
```

### 主頁面範例

```php
{{-- index.blade.php --}}
@extends('ocadmin::layouts.app')

@section('title', $title)

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">{{ $title }}</h5>
        <a href="{{ ocadmin_route('inventory.form') }}" class="btn btn-primary btn-sm">
            <i class="fa fa-plus"></i> 新增
        </a>
    </div>
    <div class="card-body">
        {{-- 搜尋表單 --}}
        <form id="searchForm" class="row g-3 mb-3">
            <div class="col-md-4">
                <input type="text" name="keyword" class="form-control"
                       placeholder="搜尋..." value="{{ request('keyword') }}">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-outline-primary">
                    <i class="fa fa-search"></i> 搜尋
                </button>
            </div>
        </form>

        {{-- 列表容器 --}}
        <div id="listContainer">
            @include('ocadmin::modules.inventory.list')
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('searchForm').addEventListener('submit', function(e) {
    e.preventDefault();
    loadList();
});

function loadList(page = 1) {
    const form = document.getElementById('searchForm');
    const params = new URLSearchParams(new FormData(form));
    params.set('page', page);

    fetch(`{{ ocadmin_route('inventory.list') }}?${params}`)
        .then(res => res.text())
        .then(html => {
            document.getElementById('listContainer').innerHTML = html;
        });
}
</script>
@endpush
```

### 列表範例

```php
{{-- list.blade.php --}}
<table class="table table-hover">
    <thead>
        <tr>
            <th>ID</th>
            <th>名稱</th>
            <th>SKU</th>
            <th>價格</th>
            <th>操作</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($products as $product)
        <tr>
            <td>{{ $product->id }}</td>
            <td>{{ $product->name }}</td>
            <td>{{ $product->sku }}</td>
            <td>{{ number_format($product->price) }}</td>
            <td>
                <a href="{{ ocadmin_route('inventory.form', ['id' => $product->id]) }}"
                   class="btn btn-sm btn-outline-primary">
                    <i class="fa fa-edit"></i>
                </a>
                <button type="button" class="btn btn-sm btn-outline-danger"
                        onclick="deleteItem({{ $product->id }})">
                    <i class="fa fa-trash"></i>
                </button>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="5" class="text-center text-muted">
                {{ __('ocadmin::common.no_data') }}
            </td>
        </tr>
        @endforelse
    </tbody>
</table>

{{-- 分頁 --}}
@if ($products->hasPages())
<div class="d-flex justify-content-center">
    {{ $products->links() }}
</div>
@endif
```

### 表單範例

```php
{{-- form.blade.php --}}
@extends('ocadmin::layouts.app')

@section('title', $product->exists ? '編輯商品' : '新增商品')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">@yield('title')</h5>
    </div>
    <div class="card-body">
        <form action="{{ ocadmin_route('inventory.save') }}" method="POST">
            @csrf
            @if ($product->exists)
                <input type="hidden" name="id" value="{{ $product->id }}">
            @endif

            <div class="mb-3">
                <label class="form-label">名稱 <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                       value="{{ old('name', $product->name) }}">
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">SKU <span class="text-danger">*</span></label>
                <input type="text" name="sku" class="form-control @error('sku') is-invalid @enderror"
                       value="{{ old('sku', $product->sku) }}">
                @error('sku')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">價格 <span class="text-danger">*</span></label>
                <input type="number" name="price" class="form-control @error('price') is-invalid @enderror"
                       value="{{ old('price', $product->price) }}" step="0.01" min="0">
                @error('price')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-save"></i> {{ __('ocadmin::common.save') }}
                </button>
                <a href="{{ ocadmin_route('inventory.index') }}" class="btn btn-outline-secondary">
                    {{ __('ocadmin::common.cancel') }}
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
```

---

## Menu

### 選單設定

```php
<?php
// app/Ocadmin/Modules/Inventory/Config/menu.php

return [
    [
        'group' => 'inventory',
        'title' => '庫存管理',
        'icon' => 'fa-solid fa-boxes-stacked',
        'priority' => 100,
        'items' => [
            [
                'title' => '商品列表',
                'route' => 'lang.ocadmin.inventory.index',
                'icon' => 'fa-solid fa-box',
            ],
            [
                'title' => '庫存調整',
                'route' => 'lang.ocadmin.inventory.adjustment',
                'icon' => 'fa-solid fa-sliders',
            ],
        ],
    ],
];
```

### 選單結構

```php
[
    'group' => 'unique_key',      // 群組唯一鍵
    'title' => '顯示名稱',         // 支援翻譯 key
    'icon' => 'fa-solid fa-xxx',  // Font Awesome 圖示
    'priority' => 100,            // 排序（數字越小越前面）
    'permission' => 'view-inventory',  // 權限檢查（可選）
    'items' => [                  // 子項目
        [
            'title' => '子項目',
            'route' => 'lang.ocadmin.xxx',
            'icon' => 'fa-solid fa-xxx',
            'permission' => 'xxx',
        ],
    ],
]
```

---

## Model

### 模組 Model

```php
<?php

namespace App\Ocadmin\Modules\Inventory\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $table = 'pos_products';

    protected $fillable = [
        'name',
        'sku',
        'price',
        'stock',
        'category_id',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'stock' => 'integer',
    ];

    // 關聯
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
```

### 使用專案 Model

若要使用專案的 Model：

```php
use App\Models\Product;

// 或透過設定
$productClass = config('ocadmin.models.product', \App\Models\Product::class);
```

---

## Service

### 服務類別

```php
<?php

namespace App\Ocadmin\Modules\Inventory\Services;

use App\Ocadmin\Modules\Inventory\Models\Product;

class InventoryService
{
    public function adjustStock(Product $product, int $quantity, string $reason): void
    {
        $product->increment('stock', $quantity);

        // 記錄調整歷史
        $product->stockAdjustments()->create([
            'quantity' => $quantity,
            'reason' => $reason,
            'user_id' => auth()->id(),
        ]);
    }

    public function getLowStockProducts(int $threshold = 10)
    {
        return Product::where('stock', '<', $threshold)->get();
    }
}
```

---

## Migration

### 建立 Migration

```php
<?php
// app/Ocadmin/Modules/Inventory/database/migrations/2025_01_01_000001_create_products_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('sku')->unique();
            $table->decimal('price', 10, 2)->default(0);
            $table->integer('stock')->default(0);
            $table->foreignId('category_id')->nullable()->constrained('cfg_terms');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_products');
    }
};
```

### 執行 Migration

```bash
# 執行模組 migrations
php artisan migrate --path=app/Ocadmin/Modules/Inventory/database/migrations
```

---

## 權限控制

### 定義權限

```php
// app/Ocadmin/Modules/Inventory/Config/permissions.php

return [
    'inventory' => [
        'view-inventory' => '查看庫存',
        'create-inventory' => '新增商品',
        'edit-inventory' => '編輯商品',
        'delete-inventory' => '刪除商品',
        'adjust-stock' => '調整庫存',
    ],
];
```

### Controller 權限檢查

```php
public function __construct()
{
    $this->middleware('permission:view-inventory')->only(['index', 'list']);
    $this->middleware('permission:create-inventory')->only(['form', 'save']);
    $this->middleware('permission:delete-inventory')->only(['destroy']);
}
```

### Blade 權限檢查

```php
@can('create-inventory')
    <a href="{{ ocadmin_route('inventory.form') }}" class="btn btn-primary">
        新增
    </a>
@endcan
```

---

## 最佳實踐

### 1. 命名規範

| 項目 | 規範 | 範例 |
|------|------|------|
| 模組名稱 | PascalCase | `Inventory`, `SystemLog` |
| Controller | PascalCase + Controller | `InventoryController` |
| Model | PascalCase 單數 | `Product`, `Category` |
| 資料表 | snake_case 複數 + 前綴 | `pos_products` |
| 路由 | kebab-case | `inventory`, `system-logs` |
| 視圖 | snake_case | `index`, `form`, `list` |

### 2. 目錄規範

- Controllers 放 Controller
- Models 放 Eloquent Model
- Services 放業務邏輯
- Repositories 放資料存取邏輯
- Views 放 Blade 視圖
- Routes 放路由定義
- Config 放選單、權限等設定

### 3. 視圖規範

- `index.blade.php` - 列表主頁（含搜尋）
- `list.blade.php` - 列表內容（AJAX）
- `form.blade.php` - 新增/編輯表單
- `show.blade.php` - 詳情頁（唯讀）

---

*文件版本：v1.0*
