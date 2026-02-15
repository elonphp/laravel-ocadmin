<?php

namespace App\Portals\Ocadmin\Modules\Catalog\Product;

use App\Helpers\Classes\LocaleHelper;
use App\Helpers\Classes\OrmHelper;
use App\Models\Catalog\Option;
use App\Models\Catalog\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Portals\Ocadmin\Core\Controllers\OcadminController;

class ProductController extends OcadminController
{
    protected function setLangFiles(): array
    {
        return ['common', 'catalog/product'];
    }

    protected function setBreadcrumbs(): void
    {
        $this->breadcrumbs = [
            (object)[
                'text' => $this->lang->text_home,
                'href' => route('lang.ocadmin.dashboard'),
            ],
            (object)[
                'text' => $this->lang->text_catalog,
                'href' => 'javascript:void(0)',
            ],
            (object)[
                'text' => $this->lang->heading_title,
                'href' => route('lang.ocadmin.catalog.product.index'),
            ],
        ];
    }

    /**
     * 列表頁（初始載入）
     */
    public function index(Request $request): View
    {
        $data['lang'] = $this->lang;
        $data['breadcrumbs'] = $this->breadcrumbs;
        $data['list'] = $this->getList($request);

        return view('ocadmin.catalog.product::index', $data);
    }

    /**
     * AJAX 入口（列表刷新）
     */
    public function list(Request $request): string
    {
        return $this->getList($request);
    }

    /**
     * 核心查詢邏輯
     */
    protected function getList(Request $request): string
    {
        $query = Product::with('translations');
        $filter_data = $this->filterData($request, ['filter_name', 'filter_model', 'equal_status', 'equal_is_active']);

        // 預設排序
        $filter_data['sort'] = $request->query('sort', 'sort_order');
        $filter_data['order'] = $request->query('order', 'asc');

        // filter_name 透過翻譯表搜尋
        if ($request->filled('filter_name')) {
            $name = $request->filter_name;
            $locale = app()->getLocale();

            $query->whereHas('translations', function ($tq) use ($name, $locale) {
                $tq->where('locale', $locale);
                $tq->where(function ($sq) use ($name) {
                    OrmHelper::filterOrEqualColumn($sq, 'filter_name', $name);
                });
            });

            unset($filter_data['filter_name']);
        }

        // OrmHelper 自動處理 filter_model, equal_status 及排序
        OrmHelper::prepare($query, $filter_data);

        // 分頁結果
        $products = OrmHelper::getResult($query, $filter_data);
        $products->withPath(route('lang.ocadmin.catalog.product.list'));

        $data['lang'] = $this->lang;
        $data['products'] = $products;
        $data['pagination'] = $products->links('ocadmin::pagination.default');

        // 建構 URL 參數與排序連結
        $url = $this->buildUrlParams($request);
        $baseUrl = route('lang.ocadmin.catalog.product.list');
        $data['sort'] = $filter_data['sort'];
        $data['order'] = $filter_data['order'];
        $nextOrder = ($data['order'] == 'asc') ? 'desc' : 'asc';

        $data['sort_name'] = $baseUrl . "?sort=name&order={$nextOrder}" . str_replace('?', '&', $url);
        $data['sort_model'] = $baseUrl . "?sort=model&order={$nextOrder}" . str_replace('?', '&', $url);
        $data['sort_price'] = $baseUrl . "?sort=price&order={$nextOrder}" . str_replace('?', '&', $url);
        $data['sort_quantity'] = $baseUrl . "?sort=quantity&order={$nextOrder}" . str_replace('?', '&', $url);
        $data['sort_sort_order'] = $baseUrl . "?sort=sort_order&order={$nextOrder}" . str_replace('?', '&', $url);

        return view('ocadmin.catalog.product::list', $data)->render();
    }

    /**
     * 新增表單
     */
    public function create(): View
    {
        $data['lang'] = $this->lang;
        $data['breadcrumbs'] = $this->breadcrumbs;
        $data['product'] = new Product();
        $data['productOptions'] = collect();
        $data['availableOptions'] = $this->getAvailableOptions();

        return view('ocadmin.catalog.product::form', $data);
    }

    /**
     * 儲存新資料
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate($this->validationRules());

        $product = Product::create([
            'model'     => $validated['model'],
            'price'     => $validated['price'] ?? 0,
            'quantity'  => $validated['quantity'] ?? 0,
            'minimum'   => $validated['minimum'] ?? 1,
            'subtract'  => $validated['subtract'] ?? true,
            'shipping'  => $validated['shipping'] ?? true,
            'status'    => $validated['status'] ?? true,
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);
        $product->saveTranslations($validated['translations']);

        // 儲存商品選項
        $this->saveProductOptions($product, $request->input('product_option', []));

        return response()->json([
            'success'     => true,
            'message'     => $this->lang->text_success_add,
            'replace_url' => route('lang.ocadmin.catalog.product.edit', $product),
            'form_action' => route('lang.ocadmin.catalog.product.update', $product),
        ]);
    }

    /**
     * 編輯表單
     */
    public function edit(Product $product): View
    {
        $product->load([
            'translations',
            'productOptions.option.translations',
            'productOptions.option.optionValues.translations',
            'productOptions.productOptionValues.optionValue.translations',
        ]);

        $data['lang'] = $this->lang;
        $data['breadcrumbs'] = $this->breadcrumbs;
        $data['product'] = $product;
        $data['productOptions'] = $product->productOptions;
        $data['availableOptions'] = $this->getAvailableOptions();

        return view('ocadmin.catalog.product::form', $data);
    }

    /**
     * 更新資料
     */
    public function update(Request $request, Product $product): JsonResponse
    {
        $validated = $request->validate($this->validationRules());

        $product->update([
            'model'     => $validated['model'],
            'price'     => $validated['price'] ?? 0,
            'quantity'  => $validated['quantity'] ?? 0,
            'minimum'   => $validated['minimum'] ?? 1,
            'subtract'  => $validated['subtract'] ?? true,
            'shipping'  => $validated['shipping'] ?? true,
            'status'    => $validated['status'] ?? true,
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);
        $product->saveTranslations($validated['translations']);

        // 刪除舊選項，重建
        $product->productOptions()->delete();
        $this->saveProductOptions($product, $request->input('product_option', []));

        return response()->json([
            'success' => true,
            'message' => $this->lang->text_success_edit,
        ]);
    }

    /**
     * 刪除資料
     */
    public function destroy(Product $product): JsonResponse
    {
        $product->delete();

        return response()->json(['success' => true, 'message' => $this->lang->text_success_delete]);
    }

    /**
     * 批次刪除
     */
    public function batchDelete(Request $request): JsonResponse
    {
        $ids = $request->input('selected', []);

        if (empty($ids)) {
            return response()->json(['success' => false, 'message' => $this->lang->error_select_delete]);
        }

        Product::whereIn('id', $ids)->delete();

        return response()->json(['success' => true, 'message' => $this->lang->text_success_delete]);
    }

    /**
     * 驗證規則
     */
    protected function validationRules(): array
    {
        $rules = [
            'model'      => 'required|string|max:64',
            'price'      => 'nullable|numeric|min:0',
            'quantity'   => 'nullable|integer|min:0',
            'minimum'    => 'nullable|integer|min:1',
            'subtract'   => 'nullable|boolean',
            'shipping'   => 'nullable|boolean',
            'status'     => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ];

        foreach (LocaleHelper::getSupportedLocales() as $locale) {
            $rules["translations.{$locale}.name"] = 'required|string|max:255';
            $rules["translations.{$locale}.description"] = 'nullable|string';
            $rules["translations.{$locale}.meta_title"] = 'nullable|string|max:255';
            $rules["translations.{$locale}.meta_keyword"] = 'nullable|string|max:255';
            $rules["translations.{$locale}.meta_description"] = 'nullable|string';
        }

        return $rules;
    }

    /**
     * 儲存商品選項
     */
    protected function saveProductOptions(Product $product, array $options): void
    {
        foreach ($options as $optionData) {
            $productOption = $product->productOptions()->create([
                'option_id' => $optionData['option_id'],
                'value'     => $optionData['value'] ?? null,
                'required'  => $optionData['required'] ?? false,
            ]);

            if (!empty($optionData['product_option_value'])) {
                foreach ($optionData['product_option_value'] as $valueData) {
                    $productOption->productOptionValues()->create([
                        'product_id'      => $product->id,
                        'option_id'       => $optionData['option_id'],
                        'option_value_id' => $valueData['option_value_id'],
                        'quantity'        => $valueData['quantity'] ?? 0,
                        'subtract'        => $valueData['subtract'] ?? false,
                        'price'           => $valueData['price'] ?? 0,
                        'price_prefix'    => $valueData['price_prefix'] ?? '+',
                        'weight'          => $valueData['weight'] ?? 0,
                        'weight_prefix'   => $valueData['weight_prefix'] ?? '+',
                    ]);
                }
            }
        }
    }

    /**
     * 取得所有可用選項（含選項值）供表單使用
     */
    protected function getAvailableOptions(): array
    {
        return Option::with(['translations', 'optionValues.translations'])
            ->orderBy('sort_order')
            ->get()
            ->map(function ($opt) {
                return [
                    'option_id'     => $opt->id,
                    'name'          => $opt->getTranslatedAttribute('name'),
                    'type'          => $opt->type,
                    'option_values' => $opt->isChoiceType()
                        ? $opt->optionValues->map(fn ($ov) => [
                            'option_value_id' => $ov->id,
                            'name'            => $ov->getTranslatedAttribute('name'),
                        ])->toArray()
                        : [],
                ];
            })
            ->toArray();
    }
}
