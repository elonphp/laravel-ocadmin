<?php

namespace App\Portals\Ocadmin\Core\Controllers\Config;

use App\Helpers\Classes\LocaleHelper;
use App\Helpers\Classes\OrmHelper;
use App\Models\Config\Taxonomy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Portals\Ocadmin\Core\Controllers\OcadminController;

class TaxonomyController extends OcadminController
{
    protected function setLangFiles(): array
    {
        return ['common', 'config/taxonomy'];
    }

    protected function setBreadcrumbs(): void
    {
        $this->breadcrumbs = [
            (object)[
                'text' => $this->lang->text_home,
                'href' => route('lang.ocadmin.dashboard'),
            ],
            (object)[
                'text' => $this->lang->text_system,
                'href' => 'javascript:void(0)',
            ],
            (object)[
                'text' => $this->lang->heading_title,
                'href' => route('lang.ocadmin.config.taxonomy.index'),
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

        return view('ocadmin::config.taxonomy.index', $data);
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
        $query = Taxonomy::with('translations')->withCount('terms');
        $filter_data = $request->all();

        // 預設排序
        $filter_data['sort'] = $request->get('sort', 'sort_order');
        $filter_data['order'] = $request->get('order', 'asc');

        // search 關鍵字查詢
        if ($request->filled('search')) {
            $search = $request->search;
            $locale = app()->getLocale();

            $query->where(function ($q) use ($search, $locale) {
                OrmHelper::filterOrEqualColumn($q, 'filter_code', $search);

                $q->orWhereHas('translations', function ($tq) use ($search, $locale) {
                    $tq->where('locale', $locale);
                    $tq->where(function ($sq) use ($search) {
                        OrmHelper::filterOrEqualColumn($sq, 'filter_name', $search);
                    });
                });
            });

            unset($filter_data['search'], $filter_data['filter_code'], $filter_data['filter_name']);
        }

        // OrmHelper 自動處理 filter_*, equal_* 及排序
        OrmHelper::prepare($query, $filter_data);

        // 分頁結果
        $taxonomies = OrmHelper::getResult($query, $filter_data);
        $taxonomies->withPath(route('lang.ocadmin.config.taxonomy.list'));

        $data['lang'] = $this->lang;
        $data['taxonomies'] = $taxonomies;
        $data['pagination'] = $taxonomies->links('ocadmin::pagination.default');

        // 建構 URL 參數與排序連結
        $url = $this->buildUrlParams($request);
        $baseUrl = route('lang.ocadmin.config.taxonomy.list');
        $data['sort'] = $filter_data['sort'];
        $data['order'] = $filter_data['order'];
        $nextOrder = ($data['order'] == 'asc') ? 'desc' : 'asc';

        $data['sort_code'] = $baseUrl . "?sort=code&order={$nextOrder}" . str_replace('?', '&', $url);
        $data['sort_name'] = $baseUrl . "?sort=name&order={$nextOrder}" . str_replace('?', '&', $url);
        $data['sort_sort_order'] = $baseUrl . "?sort=sort_order&order={$nextOrder}" . str_replace('?', '&', $url);

        return view('ocadmin::config.taxonomy.list', $data)->render();
    }

    public function create(): View
    {
        $data['lang'] = $this->lang;
        $data['breadcrumbs'] = $this->breadcrumbs;
        $data['taxonomy'] = new Taxonomy();

        return view('ocadmin::config.taxonomy.form', $data);
    }

    public function store(Request $request): JsonResponse
    {
        $rules = [
            'code' => 'required|string|max:50|unique:taxonomies,code|regex:/^[a-z][a-z0-9_]*$/',
            'description' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ];

        foreach (LocaleHelper::getSupportedLocales() as $locale) {
            $rules["translations.{$locale}.name"] = 'required|string|max:100';
        }

        $validated = $request->validate($rules);

        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['is_active'] = $request->has('is_active');

        $taxonomy = Taxonomy::create($validated);
        $taxonomy->saveTranslations($validated['translations']);

        return response()->json([
            'success' => true,
            'message' => $this->lang->text_success_add,
            'replace_url' => route('lang.ocadmin.config.taxonomy.edit', $taxonomy),
            'form_action' => route('lang.ocadmin.config.taxonomy.update', $taxonomy),
        ]);
    }

    public function edit(Taxonomy $taxonomy): View
    {
        $taxonomy->load('translations');

        $data['lang'] = $this->lang;
        $data['breadcrumbs'] = $this->breadcrumbs;
        $data['taxonomy'] = $taxonomy;

        return view('ocadmin::config.taxonomy.form', $data);
    }

    public function update(Request $request, Taxonomy $taxonomy): JsonResponse
    {
        $rules = [
            'code' => 'required|string|max:50|unique:taxonomies,code,' . $taxonomy->id . '|regex:/^[a-z][a-z0-9_]*$/',
            'description' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ];

        foreach (LocaleHelper::getSupportedLocales() as $locale) {
            $rules["translations.{$locale}.name"] = 'required|string|max:100';
        }

        $validated = $request->validate($rules);

        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['is_active'] = $request->has('is_active');

        $taxonomy->update($validated);
        $taxonomy->saveTranslations($validated['translations']);

        return response()->json([
            'success' => true,
            'message' => $this->lang->text_success_edit,
        ]);
    }

    public function destroy(Taxonomy $taxonomy): JsonResponse
    {
        if ($taxonomy->terms()->exists()) {
            return response()->json([
                'success' => false,
                'message' => $this->lang->error_has_terms,
            ]);
        }

        $taxonomy->delete();

        return response()->json(['success' => true, 'message' => $this->lang->text_success_delete]);
    }

    public function batchDelete(Request $request): JsonResponse
    {
        $ids = $request->input('selected', []);

        if (empty($ids)) {
            return response()->json(['success' => false, 'message' => $this->lang->error_select_delete]);
        }

        $hasTerms = Taxonomy::whereIn('id', $ids)->whereHas('terms')->exists();
        if ($hasTerms) {
            return response()->json([
                'success' => false,
                'message' => $this->lang->error_batch_has_terms,
            ]);
        }

        Taxonomy::whereIn('id', $ids)->delete();

        return response()->json(['success' => true, 'message' => $this->lang->text_success_delete]);
    }
}
