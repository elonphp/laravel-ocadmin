<?php

namespace App\Portals\Ocadmin\Modules\Catalog\Option;

use App\Helpers\Classes\LocaleHelper;
use App\Helpers\Classes\OrmHelper;
use App\Models\Catalog\Option;
use App\Models\Catalog\OptionValue;
use App\Models\Catalog\OptionValueLink;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Portals\Ocadmin\Core\Controllers\OcadminController;

class OptionController extends OcadminController
{
    protected function setLangFiles(): array
    {
        return ['common', 'catalog/option'];
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
                'href' => route('lang.ocadmin.catalog.option.index'),
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

        return view('ocadmin.catalog.option::index', $data);
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
        $query = Option::with('translations')->withCount('optionValues');
        $filter_data = $request->all();

        // 預設排序
        $filter_data['sort'] = $request->get('sort', 'sort_order');
        $filter_data['order'] = $request->get('order', 'asc');

        // search 關鍵字查詢
        if ($request->filled('search')) {
            $search = $request->search;
            $locale = app()->getLocale();

            $query->where(function ($q) use ($search, $locale) {
                $q->whereHas('translations', function ($tq) use ($search, $locale) {
                    $tq->where('locale', $locale);
                    $tq->where(function ($sq) use ($search) {
                        OrmHelper::filterOrEqualColumn($sq, 'filter_name', $search);
                    });
                });
            });

            unset($filter_data['search'], $filter_data['filter_name']);
        }

        // OrmHelper 自動處理 filter_*, equal_* 及排序
        OrmHelper::prepare($query, $filter_data);

        // 分頁結果
        $options = OrmHelper::getResult($query, $filter_data);
        $options->withPath(route('lang.ocadmin.catalog.option.list'));

        $data['lang'] = $this->lang;
        $data['options'] = $options;
        $data['pagination'] = $options->links('ocadmin::pagination.default');

        // 建構 URL 參數與排序連結
        $url = $this->buildUrlParams($request);
        $baseUrl = route('lang.ocadmin.catalog.option.list');
        $data['sort'] = $filter_data['sort'];
        $data['order'] = $filter_data['order'];
        $nextOrder = ($data['order'] == 'asc') ? 'desc' : 'asc';

        $data['sort_name'] = $baseUrl . "?sort=name&order={$nextOrder}" . str_replace('?', '&', $url);
        $data['sort_sort_order'] = $baseUrl . "?sort=sort_order&order={$nextOrder}" . str_replace('?', '&', $url);

        return view('ocadmin.catalog.option::list', $data)->render();
    }

    /**
     * 新增表單
     */
    public function create(): View
    {
        $data['lang'] = $this->lang;
        $data['breadcrumbs'] = $this->breadcrumbs;
        $data['option'] = new Option();
        $data['optionValues'] = [];

        return view('ocadmin.catalog.option::form', $data);
    }

    /**
     * 儲存新資料
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate($this->validationRules($request));

        $option = Option::create([
            'type' => $validated['type'],
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);
        $option->saveTranslations($validated['translations']);

        // 儲存選項值（僅選擇型）
        if (in_array($validated['type'], Option::CHOICE_TYPES)) {
            $this->saveOptionValues($option, $request->input('option_value', []));
        }

        return response()->json([
            'success' => true,
            'message' => $this->lang->text_success_add,
            'replace_url' => route('lang.ocadmin.catalog.option.edit', $option),
            'form_action' => route('lang.ocadmin.catalog.option.update', $option),
        ]);
    }

    /**
     * 編輯表單
     */
    public function edit(Option $option): View
    {
        $option->load(['translations', 'optionValues.translations']);

        $data['lang'] = $this->lang;
        $data['breadcrumbs'] = $this->breadcrumbs;
        $data['option'] = $option;
        $data['optionValues'] = $option->optionValues;

        return view('ocadmin.catalog.option::form', $data);
    }

    /**
     * 更新資料
     */
    public function update(Request $request, Option $option): JsonResponse
    {
        $validated = $request->validate($this->validationRules($request));

        $option->update([
            'type' => $validated['type'],
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);
        $option->saveTranslations($validated['translations']);

        // 刪除舊選項值，重建
        $option->optionValues()->delete();

        if (in_array($validated['type'], Option::CHOICE_TYPES)) {
            $this->saveOptionValues($option, $request->input('option_value', []));
        }

        return response()->json([
            'success' => true,
            'message' => $this->lang->text_success_edit,
        ]);
    }

    /**
     * 刪除資料
     */
    public function destroy(Option $option): JsonResponse
    {
        $option->delete();

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

        Option::whereIn('id', $ids)->delete();

        return response()->json(['success' => true, 'message' => $this->lang->text_success_delete]);
    }

    /**
     * 驗證規則
     */
    protected function validationRules(Request $request): array
    {
        $rules = [
            'type' => 'required|string|in:' . implode(',', Option::TYPES),
            'sort_order' => 'nullable|integer|min:0',
        ];

        foreach (LocaleHelper::getSupportedLocales() as $locale) {
            $rules["translations.{$locale}.name"] = 'required|string|max:128';
        }

        // 選擇型才驗證選項值
        if (in_array($request->input('type'), Option::CHOICE_TYPES)) {
            $rules['option_value'] = 'required|array|min:1';

            foreach (LocaleHelper::getSupportedLocales() as $locale) {
                $rules["option_value.*.translations.{$locale}.name"] = 'required|string|max:128';
                $rules["option_value.*.translations.{$locale}.short_name"] = 'nullable|string|max:128';
            }
        }

        return $rules;
    }

    /**
     * 連動設定頁面
     */
    public function cascade(): View
    {
        $options = Option::with('optionValues.translations')
            ->whereIn('type', Option::CHOICE_TYPES)
            ->orderBy('sort_order')
            ->get();

        // 組合連動層級對（廠牌→車型, 車型→車款, ...）
        $levels = collect();
        for ($i = 0; $i < $options->count() - 1; $i++) {
            $levels->push([
                'parent' => $options[$i],
                'child'  => $options[$i + 1],
            ]);
        }

        // 準備 JSON 資料供前端使用
        $levelsJson = $levels->map(fn ($l) => [
            'parent_id' => $l['parent']->id,
            'parent_name' => $l['parent']->name,
            'parent_values' => $l['parent']->optionValues->sortBy('sort_order')->values()->map(fn ($v) => ['id' => $v->id, 'name' => $v->name]),
            'child_id' => $l['child']->id,
            'child_name' => $l['child']->name,
            'child_values' => $l['child']->optionValues->sortBy('sort_order')->values()->map(fn ($v) => ['id' => $v->id, 'name' => $v->name]),
        ]);

        $data['lang'] = $this->lang;
        $data['breadcrumbs'] = $this->breadcrumbs;
        $data['levels'] = $levels;
        $data['levelsJson'] = $levelsJson;

        return view('ocadmin.catalog.option::cascade', $data);
    }

    /**
     * 連動設定 API — 取得父值的已連動子值 ID
     */
    public function cascadeLinks(OptionValue $optionValue): JsonResponse
    {
        $linkedIds = OptionValueLink::where('parent_option_value_id', $optionValue->id)
            ->pluck('child_option_value_id');

        return response()->json(['linked_ids' => $linkedIds]);
    }

    /**
     * 連動設定 API — 儲存連動關係
     */
    public function saveCascadeLinks(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'parent_value_id' => 'required|integer|exists:ctl_option_values,id',
            'child_ids' => 'nullable|array',
            'child_ids.*' => 'integer|exists:ctl_option_values,id',
        ]);

        $parentId = $validated['parent_value_id'];
        $childIds = $validated['child_ids'] ?? [];

        // 刪除舊的連動
        OptionValueLink::where('parent_option_value_id', $parentId)->delete();

        // 建立新的連動
        foreach ($childIds as $childId) {
            OptionValueLink::create([
                'parent_option_value_id' => $parentId,
                'child_option_value_id'  => $childId,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => $this->lang->cascade_text_save_success,
        ]);
    }

    /**
     * 儲存選項值
     */
    protected function saveOptionValues(Option $option, array $values): void
    {
        foreach ($values as $index => $valueData) {
            $optionValue = $option->optionValues()->create([
                'image' => $valueData['image'] ?? null,
                'sort_order' => $valueData['sort_order'] ?? $index,
            ]);

            if (!empty($valueData['translations'])) {
                $optionValue->saveTranslations($valueData['translations']);
            }
        }
    }
}
