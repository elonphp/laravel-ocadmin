<?php

namespace App\Portals\Ocadmin\Modules\Catalog\OptionValueGroup;

use App\Helpers\Classes\LocaleHelper;
use App\Helpers\Classes\OrmHelper;
use App\Models\Catalog\Option;
use App\Models\Catalog\OptionValueGroup;
use App\Models\Catalog\OptionValueGroupLevel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Portals\Ocadmin\Core\Controllers\OcadminController;

class OptionValueGroupController extends OcadminController
{
    protected function setLangFiles(): array
    {
        return ['common', 'catalog/option-value-group'];
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
                'href' => route('lang.ocadmin.catalog.option-value-group.index'),
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
        $data['url_create'] = route('lang.ocadmin.catalog.option-value-group.create');
        $data['url_list'] = route('lang.ocadmin.catalog.option-value-group.list');
        $data['url_batch_delete'] = route('lang.ocadmin.catalog.option-value-group.batch-delete');
        $data['list'] = $this->getList($request);

        return view('ocadmin.catalog.option-value-group::index', $data);
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
        $query = OptionValueGroup::with('translations')
            ->withCount('levels');
        $filter_data = $request->all();

        // 預設排序
        $filter_data['sort'] = $request->get('sort', 'sort_order');
        $filter_data['order'] = $request->get('order', 'asc');

        // search 關鍵字查詢
        if ($request->filled('search')) {
            $search = $request->search;
            $locale = app()->getLocale();

            $query->where(function ($q) use ($search, $locale) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhereHas('translations', function ($tq) use ($search, $locale) {
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
        $groups = OrmHelper::getResult($query, $filter_data);
        $groups->withPath(route('lang.ocadmin.catalog.option-value-group.list'));

        $data['lang'] = $this->lang;
        $data['groups'] = $groups;
        $data['pagination'] = $groups->links('ocadmin::pagination.default');

        // 建構 URL 參數與排序連結
        $url = $this->buildUrlParams($request);
        $baseUrl = route('lang.ocadmin.catalog.option-value-group.list');
        $data['sort'] = $filter_data['sort'];
        $data['order'] = $filter_data['order'];
        $nextOrder = ($data['order'] == 'asc') ? 'desc' : 'asc';

        $data['sort_name'] = $baseUrl . "?sort=name&order={$nextOrder}" . str_replace('?', '&', $url);
        $data['sort_sort_order'] = $baseUrl . "?sort=sort_order&order={$nextOrder}" . str_replace('?', '&', $url);
        $data['url_edit'] = route('lang.ocadmin.catalog.option-value-group.edit', ['option_value_group' => '__ID__']);

        return view('ocadmin.catalog.option-value-group::list', $data)->render();
    }

    /**
     * 新增表單
     */
    public function create(): View
    {
        $data['lang'] = $this->lang;
        $data['breadcrumbs'] = $this->breadcrumbs;
        $data['group'] = new OptionValueGroup();
        $data['levels'] = [];
        $data['options'] = Option::with('translations')->orderBy('sort_order')->get();
        $data['url_action'] = route('lang.ocadmin.catalog.option-value-group.store');
        $data['url_back'] = route('lang.ocadmin.catalog.option-value-group.index');

        return view('ocadmin.catalog.option-value-group::form', $data);
    }

    /**
     * 儲存新資料
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate($this->validationRules($request));

        $group = OptionValueGroup::create([
            'code' => $validated['code'],
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => $validated['is_active'] ?? false,
        ]);
        $group->saveTranslations($validated['translations']);

        // 儲存層級
        $this->saveLevels($group, $request->input('levels', []));

        return response()->json([
            'success' => true,
            'message' => $this->lang->text_success_add,
            'replace_url' => route('lang.ocadmin.catalog.option-value-group.edit', $group),
            'form_action' => route('lang.ocadmin.catalog.option-value-group.update', $group),
        ]);
    }

    /**
     * 編輯表單
     */
    public function edit(OptionValueGroup $option_value_group): View
    {
        $option_value_group->load(['translations', 'levels.option.translations']);

        $data['lang'] = $this->lang;
        $data['breadcrumbs'] = $this->breadcrumbs;
        $data['group'] = $option_value_group;
        $data['levels'] = $option_value_group->levels;
        $data['options'] = Option::with('translations')->orderBy('sort_order')->get();
        $data['url_action'] = route('lang.ocadmin.catalog.option-value-group.update', $option_value_group);
        $data['url_back'] = route('lang.ocadmin.catalog.option-value-group.index');

        return view('ocadmin.catalog.option-value-group::form', $data);
    }

    /**
     * 更新資料
     */
    public function update(Request $request, OptionValueGroup $option_value_group): JsonResponse
    {
        $validated = $request->validate($this->validationRules($request, $option_value_group));

        $option_value_group->update([
            'code' => $validated['code'],
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => $validated['is_active'] ?? false,
        ]);
        $option_value_group->saveTranslations($validated['translations']);

        // 刪除舊層級，重建
        $option_value_group->levels()->delete();
        $this->saveLevels($option_value_group, $request->input('levels', []));

        return response()->json([
            'success' => true,
            'message' => $this->lang->text_success_edit,
        ]);
    }

    /**
     * 刪除資料
     */
    public function destroy(OptionValueGroup $option_value_group): JsonResponse
    {
        $option_value_group->delete();

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

        OptionValueGroup::whereIn('id', $ids)->delete();

        return response()->json(['success' => true, 'message' => $this->lang->text_success_delete]);
    }

    /**
     * 驗證規則
     */
    protected function validationRules(Request $request, ?OptionValueGroup $group = null): array
    {
        $rules = [
            'code' => 'required|string|max:50|unique:clg_option_value_groups,code' . ($group ? ',' . $group->id : ''),
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'levels' => 'required|array|min:1',
            'levels.*.option_id' => 'required|integer|exists:clg_options,id',
        ];

        foreach (LocaleHelper::getSupportedLocales() as $locale) {
            $rules["translations.{$locale}.name"] = 'required|string|max:128';
            $rules["translations.{$locale}.description"] = 'nullable|string|max:500';
        }

        return $rules;
    }

    /**
     * 儲存層級
     */
    protected function saveLevels(OptionValueGroup $group, array $levels): void
    {
        foreach ($levels as $index => $levelData) {
            $group->levels()->create([
                'option_id' => $levelData['option_id'],
                'level' => $index,
            ]);
        }
    }
}
