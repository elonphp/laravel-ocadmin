# 內容多語

本文件說明資料庫內容翻譯機制，支援兩種模式可切換。

---

## 概述

內容多語用於翻譯資料庫中的動態內容，本套件支援兩種模式：

| 模式 | 說明 | 適用場景 |
|------|------|----------|
| **SUFFIX** | 主表 + `_translations` 後綴表 | 單純翻譯需求 |
| **EAV** | `*_metas` + `ztm_*_profiles` 快取表 | 需要擴展欄位 + 多語 |

---

## 設定

### 環境變數

```env
# .env
TRANSLATION_MODE=SUFFIX
# 或
TRANSLATION_MODE=EAV
```

### config/ocadmin.php

```php
'localization' => [
    // 內容多語模式
    'content' => [
        // SUFFIX: 主表 + _translations 後綴表
        // EAV: *_metas + ztm_*_profiles 快取表
        'mode' => env('TRANSLATION_MODE', 'SUFFIX'),

        // EAV 模式設定
        'eav' => [
            'meta_keys_table' => 'meta_keys',
            'profile_prefix' => 'ztm_',
        ],
    ],
],
```

---

## 模式比較

| 項目 | SUFFIX 模式 | EAV 模式 |
|------|-------------|----------|
| 資料表 | `products` + `product_translations` | `products` + `product_metas` + `ztm_product_profiles` |
| 欄位定義 | 固定於翻譯表 | 動態於 `meta_keys` |
| 排序/JOIN | 直接 JOIN 翻譯表 | JOIN 快取表 `ztm_*` |
| 擴展欄位 | 需 ALTER TABLE | 新增 meta_key 即可 |
| 複雜度 | 低 | 中 |
| 適用 | 固定欄位的多語 | 動態欄位 + 多語 |

---

# SUFFIX 模式

## 資料表結構

```
┌─────────────────┐      ┌─────────────────────────────┐
│    products     │      │    product_translations     │
├─────────────────┤      ├─────────────────────────────┤
│ id              │──┐   │ id                          │
│ sku             │  │   │ product_id (FK)             │
│ price           │  └──▶│ locale                      │
│ category_id     │      │ name                        │
│ created_at      │      │ description                 │
└─────────────────┘      └─────────────────────────────┘
```

### Migration

```php
Schema::create('products', function (Blueprint $table) {
    $table->id();
    $table->string('sku')->unique();
    $table->decimal('price', 10, 2);
    $table->timestamps();
});

Schema::create('product_translations', function (Blueprint $table) {
    $table->id();
    $table->foreignId('product_id')->constrained()->cascadeOnDelete();
    $table->string('locale', 10);
    $table->string('name');
    $table->text('description')->nullable();
    $table->timestamps();

    $table->unique(['product_id', 'locale']);
});
```

## Trait

```php
<?php

namespace Elonphp\LaravelOcadminModules\Traits;

trait HasTranslationsSuffix
{
    public function translations()
    {
        return $this->hasMany($this->getTranslationModel());
    }

    public function translation()
    {
        return $this->hasOne($this->getTranslationModel())
            ->where('locale', app()->getLocale());
    }

    protected function getTranslationModel(): string
    {
        return static::class . 'Translation';
    }

    public function getTranslation(string $attr, ?string $locale = null): ?string
    {
        $locale = $locale ?? app()->getLocale();
        return $this->translations->firstWhere('locale', $locale)?->$attr;
    }

    public function setTranslation(string $attr, $value, ?string $locale = null): void
    {
        $locale = $locale ?? app()->getLocale();
        $this->translations()->updateOrCreate(
            ['locale' => $locale],
            [$attr => $value]
        );
    }

    public function scopeWithTranslation($query, ?string $locale = null)
    {
        $locale = $locale ?? app()->getLocale();
        return $query->with(['translations' => fn($q) => $q->where('locale', $locale)]);
    }
}
```

---

# EAV 模式

## 資料表結構

```
┌───────────────┐
│   meta_keys   │  ← 統一欄位定義
├───────────────┤
│ id            │
│ name          │  ← 'name', 'description'
│ table_name    │  ← null=共用, 'products'=專屬
│ data_type     │  ← text, integer, decimal
│ is_translatable│ ← true/false
└───────────────┘
        │
        ▼
┌─────────────────────┐
│   product_metas     │  ← EAV 擴展欄位
├─────────────────────┤
│ product_id (FK)     │
│ key_id (FK)         │
│ locale              │  ← '' = 非多語
│ value               │
└─────────────────────┘
        │
        ▼ (同步)
┌─────────────────────┐
│ ztm_product_profiles│  ← 扁平化快取表
├─────────────────────┤
│ product_id (FK)     │
│ locale              │
│ name                │  ← 動態欄位
│ description         │
└─────────────────────┘
```

### Migration

```php
// meta_keys 表（全系統共用）
Schema::create('meta_keys', function (Blueprint $table) {
    $table->smallIncrements('id');
    $table->string('name', 50)->unique();
    $table->string('table_name', 30)->nullable();
    $table->string('data_type', 20)->default('text');
    $table->boolean('is_translatable')->default(false);
    $table->string('description', 100)->nullable();
    $table->timestamps();
});

// 主表
Schema::create('products', function (Blueprint $table) {
    $table->id();
    $table->string('sku')->unique();
    $table->decimal('price', 10, 2);
    $table->timestamps();
});

// EAV 表
Schema::create('product_metas', function (Blueprint $table) {
    $table->foreignId('product_id')->constrained()->cascadeOnDelete();
    $table->unsignedSmallInteger('key_id');
    $table->string('locale', 10)->default('');
    $table->text('value')->nullable();

    $table->primary(['product_id', 'key_id', 'locale']);
    $table->foreign('key_id')->references('id')->on('meta_keys')->cascadeOnDelete();
});

// 快取表
Schema::create('ztm_product_profiles', function (Blueprint $table) {
    $table->foreignId('product_id')->constrained()->cascadeOnDelete();
    $table->string('locale', 10)->default('');
    // 欄位動態產生
    $table->string('name')->nullable();
    $table->text('description')->nullable();

    $table->primary(['product_id', 'locale']);
    $table->index('name');
});
```

## Trait

```php
<?php

namespace Elonphp\LaravelOcadminModules\Traits;

trait HasTranslationsEav
{
    public function metas()
    {
        return $this->hasMany($this->getMetaModel());
    }

    public function profile()
    {
        return $this->hasOne($this->getProfileModel())
            ->where('locale', app()->getLocale());
    }

    public function profiles()
    {
        return $this->hasMany($this->getProfileModel());
    }

    protected function getMetaModel(): string
    {
        return static::class . 'Meta';
    }

    protected function getProfileModel(): string
    {
        return 'Ztm' . class_basename(static::class) . 'Profile';
    }

    public function getMeta(string $key, ?string $locale = null)
    {
        $locale = $locale ?? ($this->isTranslatable($key) ? app()->getLocale() : '');

        return $this->metas
            ->where('key.name', $key)
            ->where('locale', $locale)
            ->first()?->value;
    }

    public function setMeta(string $key, $value, ?string $locale = null): void
    {
        $keyModel = $this->resolveMetaKey($key);
        $locale = $locale ?? ($keyModel->is_translatable ? app()->getLocale() : '');

        $this->metas()->updateOrCreate(
            ['key_id' => $keyModel->id, 'locale' => $locale],
            ['value' => $value]
        );

        // 同步到快取表
        $this->syncProfile($locale);
    }

    protected function syncProfile(string $locale): void
    {
        // 從 metas 取得該語系的所有值
        $values = $this->metas()
            ->where('locale', $locale)
            ->orWhere('locale', '')
            ->with('key')
            ->get()
            ->pluck('value', 'key.name')
            ->toArray();

        $this->profiles()->updateOrCreate(
            ['locale' => $locale],
            $values
        );
    }

    public function scopeWithProfile($query, ?string $locale = null)
    {
        $locale = $locale ?? app()->getLocale();
        return $query->with(['profiles' => fn($q) => $q->where('locale', $locale)]);
    }
}
```

---

# 統一介面

## 架構設計（Strategy Pattern）

採用 Handler 類別處理各模式邏輯，Trait 僅作為 Facade：

```
┌─────────────────────────────────────────────────────────┐
│                   Model (use HasTranslations)           │
└─────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────┐
│                     HasTranslations                     │
│  - trans(), setTrans(), scopeWithTrans()               │
│  - 委派給 TranslationHandler                            │
└─────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────┐
│              TranslationHandlerInterface                │
├─────────────────────────────────────────────────────────┤
│ + get(string $key, ?string $locale): mixed             │
│ + set(string $key, $value, ?string $locale): void      │
│ + scopeWith($query, ?string $locale): Builder          │
│ + scopeSearch($query, $field, $keyword, $locale)       │
└─────────────────────────────────────────────────────────┘
              ▲                              ▲
              │                              │
┌─────────────────────────┐    ┌─────────────────────────┐
│  SuffixTranslationHandler│    │   EavTranslationHandler │
├─────────────────────────┤    ├─────────────────────────┤
│ 處理 _translations 表   │    │ 處理 *_metas + ztm_*    │
└─────────────────────────┘    └─────────────────────────┘
```

---

## TranslationHandler 介面

```php
<?php

namespace Elonphp\LaravelOcadminModules\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

interface TranslationHandlerInterface
{
    public function __construct(Model $model);

    /**
     * 取得翻譯值
     */
    public function get(string $key, ?string $locale = null): mixed;

    /**
     * 設定翻譯值
     */
    public function set(string $key, mixed $value, ?string $locale = null): void;

    /**
     * Scope: 預載入翻譯
     */
    public function scopeWith(Builder $query, ?string $locale = null): Builder;

    /**
     * Scope: 搜尋翻譯欄位
     */
    public function scopeSearch(Builder $query, string $field, string $keyword, ?string $locale = null): Builder;
}
```

---

## SuffixTranslationHandler

```php
<?php

namespace Elonphp\LaravelOcadminModules\Support\Translation;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Elonphp\LaravelOcadminModules\Contracts\TranslationHandlerInterface;

class SuffixTranslationHandler implements TranslationHandlerInterface
{
    public function __construct(
        protected Model $model
    ) {}

    public function get(string $key, ?string $locale = null): mixed
    {
        $locale = $locale ?? app()->getLocale();

        return $this->model->translations
            ->firstWhere('locale', $locale)
            ?->{$key};
    }

    public function set(string $key, mixed $value, ?string $locale = null): void
    {
        $locale = $locale ?? app()->getLocale();

        $this->model->translations()->updateOrCreate(
            ['locale' => $locale],
            [$key => $value]
        );
    }

    public function scopeWith(Builder $query, ?string $locale = null): Builder
    {
        $locale = $locale ?? app()->getLocale();

        return $query->with([
            'translations' => fn($q) => $q->where('locale', $locale)
        ]);
    }

    public function scopeSearch(Builder $query, string $field, string $keyword, ?string $locale = null): Builder
    {
        $locale = $locale ?? app()->getLocale();

        return $query->whereHas('translations', function ($q) use ($field, $keyword, $locale) {
            $q->where('locale', $locale)
              ->where($field, 'like', "%{$keyword}%");
        });
    }
}
```

---

## EavTranslationHandler

```php
<?php

namespace Elonphp\LaravelOcadminModules\Support\Translation;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Elonphp\LaravelOcadminModules\Contracts\TranslationHandlerInterface;

class EavTranslationHandler implements TranslationHandlerInterface
{
    protected string $profileTable;

    public function __construct(
        protected Model $model
    ) {
        $prefix = config('ocadmin.localization.content.eav.profile_prefix', 'ztm_');
        $this->profileTable = $prefix . $model->getTable() . '_profiles';
    }

    public function get(string $key, ?string $locale = null): mixed
    {
        $locale = $this->resolveLocale($key, $locale);

        return $this->model->metas
            ->where('key.name', $key)
            ->where('locale', $locale)
            ->first()?->value;
    }

    public function set(string $key, mixed $value, ?string $locale = null): void
    {
        $keyModel = $this->resolveMetaKey($key);
        $locale = $locale ?? ($keyModel->is_translatable ? app()->getLocale() : '');

        $this->model->metas()->updateOrCreate(
            ['key_id' => $keyModel->id, 'locale' => $locale],
            ['value' => $value]
        );

        $this->syncProfile($locale);
    }

    public function scopeWith(Builder $query, ?string $locale = null): Builder
    {
        $locale = $locale ?? app()->getLocale();

        return $query->with([
            'profiles' => fn($q) => $q->where('locale', $locale)
        ]);
    }

    public function scopeSearch(Builder $query, string $field, string $keyword, ?string $locale = null): Builder
    {
        $locale = $locale ?? app()->getLocale();
        $table = $this->model->getTable();
        $foreignKey = $this->model->getForeignKey();

        return $query
            ->join("{$this->profileTable} as p", "{$table}.id", '=', "p.{$foreignKey}")
            ->where('p.locale', $locale)
            ->where("p.{$field}", 'like', "%{$keyword}%");
    }

    protected function resolveLocale(string $key, ?string $locale): string
    {
        if ($locale !== null) {
            return $locale;
        }

        return $this->isTranslatable($key) ? app()->getLocale() : '';
    }

    protected function isTranslatable(string $key): bool
    {
        return $this->resolveMetaKey($key)?->is_translatable ?? false;
    }

    protected function resolveMetaKey(string $key)
    {
        return app('ocadmin.meta_keys')->firstWhere('name', $key);
    }

    protected function syncProfile(string $locale): void
    {
        $values = $this->model->metas()
            ->where('locale', $locale)
            ->orWhere('locale', '')
            ->with('key')
            ->get()
            ->pluck('value', 'key.name')
            ->toArray();

        $this->model->profiles()->updateOrCreate(
            ['locale' => $locale],
            $values
        );
    }
}
```

---

## HasTranslations Trait（簡潔版）

Trait 本身只負責委派，不包含模式判斷邏輯：

```php
<?php

namespace Elonphp\LaravelOcadminModules\Traits;

use Elonphp\LaravelOcadminModules\Contracts\TranslationHandlerInterface;
use Elonphp\LaravelOcadminModules\Support\Translation\TranslationHandlerFactory;

trait HasTranslations
{
    protected ?TranslationHandlerInterface $translationHandler = null;

    /**
     * 取得 Handler 實例
     */
    protected function getTranslationHandler(): TranslationHandlerInterface
    {
        return $this->translationHandler ??= TranslationHandlerFactory::make($this);
    }

    /**
     * 取得翻譯值
     */
    public function trans(string $key, ?string $locale = null): mixed
    {
        return $this->getTranslationHandler()->get($key, $locale);
    }

    /**
     * 設定翻譯值
     */
    public function setTrans(string $key, mixed $value, ?string $locale = null): void
    {
        $this->getTranslationHandler()->set($key, $value, $locale);
    }

    /**
     * Scope: 預載入翻譯
     */
    public function scopeWithTrans($query, ?string $locale = null)
    {
        return $this->getTranslationHandler()->scopeWith($query, $locale);
    }

    /**
     * Scope: 搜尋翻譯欄位
     */
    public function scopeSearchByTrans($query, string $field, string $keyword, ?string $locale = null)
    {
        return $this->getTranslationHandler()->scopeSearch($query, $field, $keyword, $locale);
    }

    /**
     * 魔術方法：直接存取翻譯欄位
     */
    public function getAttribute($key)
    {
        if (in_array($key, $this->getTranslatableAttributes())) {
            return $this->trans($key);
        }

        return parent::getAttribute($key);
    }

    /**
     * 取得可翻譯欄位（需在 Model 定義）
     */
    public function getTranslatableAttributes(): array
    {
        return $this->translatable ?? [];
    }
}
```

---

## TranslationHandlerFactory

```php
<?php

namespace Elonphp\LaravelOcadminModules\Support\Translation;

use Illuminate\Database\Eloquent\Model;
use Elonphp\LaravelOcadminModules\Contracts\TranslationHandlerInterface;

class TranslationHandlerFactory
{
    protected static array $handlers = [
        'SUFFIX' => SuffixTranslationHandler::class,
        'EAV' => EavTranslationHandler::class,
    ];

    public static function make(Model $model): TranslationHandlerInterface
    {
        $mode = config('ocadmin.localization.content.mode', 'SUFFIX');
        $handlerClass = static::$handlers[$mode] ?? SuffixTranslationHandler::class;

        return new $handlerClass($model);
    }

    /**
     * 註冊自訂 Handler
     */
    public static function register(string $mode, string $handlerClass): void
    {
        static::$handlers[$mode] = $handlerClass;
    }
}
```

---

## Model 範例

### 使用統一介面

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Elonphp\LaravelOcadminModules\Traits\HasTranslations;

class Product extends Model
{
    use HasTranslations;

    protected $fillable = ['sku', 'price'];

    // 可翻譯欄位
    protected array $translatable = ['name', 'description'];

    public function getTranslatableAttributes(): array
    {
        return $this->translatable;
    }
}
```

### 使用

```php
// 查詢（不管什麼模式，用法相同）
$products = Product::withTrans()->get();

foreach ($products as $product) {
    echo $product->name;         // 自動取得當前語系
    echo $product->trans('name', 'en');  // 指定語系
}

// 寫入
$product->setTrans('name', '商品A', 'zh_Hant');
$product->setTrans('name', 'Product A', 'en');
```

---

## 切換模式

### 從 SUFFIX 切換到 EAV

1. 修改 `.env`：
```env
TRANSLATION_MODE=EAV
```

2. 建立 EAV 相關表：
```bash
php artisan migrate
```

3. 遷移資料：
```bash
php artisan translations:migrate-to-eav Product
```

### 從 EAV 切換到 SUFFIX

```bash
php artisan translations:migrate-to-suffix Product
```

---

## 表單處理

### Controller（模式無關）

```php
public function form(Request $request)
{
    $product = $request->id
        ? Product::withTrans()->findOrFail($request->id)
        : new Product();

    $locales = config('ocadmin.localization.supported');

    return view('products.form', compact('product', 'locales'));
}

public function save(Request $request)
{
    $validated = $request->validate([
        'sku' => 'required',
        'price' => 'required|numeric',
        'translations' => 'required|array',
        'translations.*.name' => 'required|string',
    ]);

    $product = $request->id
        ? Product::findOrFail($request->id)
        : new Product();

    $product->fill($validated)->save();

    // 儲存翻譯（統一介面，模式無關）
    foreach ($validated['translations'] as $locale => $trans) {
        foreach ($trans as $key => $value) {
            $product->setTrans($key, $value, $locale);
        }
    }

    return redirect()->back()->with('success', '儲存成功');
}
```

### Blade 表單（模式無關）

```php
<div class="card">
    <div class="card-header">
        <ul class="nav nav-tabs card-header-tabs">
            @foreach ($locales as $i => $locale)
                <li class="nav-item">
                    <a class="nav-link @if($i === 0) active @endif"
                       data-bs-toggle="tab" href="#lang-{{ $locale }}">
                        {{ config("ocadmin.localization.names.{$locale}") }}
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
    <div class="card-body tab-content">
        @foreach ($locales as $i => $locale)
            <div class="tab-pane fade @if($i === 0) show active @endif"
                 id="lang-{{ $locale }}">
                <div class="mb-3">
                    <label>名稱 ({{ $locale }})</label>
                    <input type="text"
                           name="translations[{{ $locale }}][name]"
                           value="{{ $product->trans('name', $locale) }}"
                           class="form-control">
                </div>
                <div class="mb-3">
                    <label>描述 ({{ $locale }})</label>
                    <textarea name="translations[{{ $locale }}][description]"
                              class="form-control">{{ $product->trans('description', $locale) }}</textarea>
                </div>
            </div>
        @endforeach
    </div>
</div>
```

---

## 搜尋

已整合到 `HasTranslations` trait，直接使用：

```php
// 搜尋名稱包含「商品」的產品
Product::searchByTrans('name', '商品')->get();

// 指定語系搜尋
Product::searchByTrans('name', 'Product', 'en')->get();

// 搭配其他條件
Product::where('price', '>', 100)
    ->searchByTrans('name', '特價')
    ->get();
```

Handler 會自動根據設定的模式（SUFFIX / EAV）使用正確的查詢方式。

---

## EAV 專屬功能

### 動態欄位

EAV 模式可在不修改資料表的情況下新增欄位：

```php
// 新增欄位定義
MetaKey::create([
    'name' => 'short_description',
    'table_name' => 'products',
    'data_type' => 'text',
    'is_translatable' => true,
]);

// 立即可用
$product->setMeta('short_description', '簡短描述', 'zh_Hant');
```

### 重建快取表

```bash
# 重建單一表
php artisan profiles:rebuild products

# 重建全部
php artisan profiles:rebuild --all
```

### 備份排除快取表

```bash
# 排除 ztm_ 前綴表
mysqldump --ignore-table=db.ztm_product_profiles ...
```

---

## 最佳實踐

### 選擇模式

| 情境 | 建議模式 |
|------|----------|
| 欄位固定，純翻譯需求 | SUFFIX |
| 需要動態擴展欄位 | EAV |
| 與現有 EAV 系統整合 | EAV |
| 簡單專案快速開發 | SUFFIX |

### 效能考量

| 項目 | SUFFIX | EAV |
|------|--------|-----|
| 讀取 | 1 JOIN | 1 JOIN（快取表） |
| 寫入 | 1 INSERT/UPDATE | 2 INSERT/UPDATE + 同步 |
| 搜尋 | JOIN 翻譯表 | JOIN 快取表（已索引） |

### 注意事項

1. **EAV 模式的 ztm_ 表是快取**，真正資料在 `*_metas`
2. **SUFFIX 模式更直覺**，適合大部分場景
3. **統一介面讓切換模式不影響程式碼**

---

*文件版本：v2.1 - 更新語系格式規範（底線取代橫線）*
