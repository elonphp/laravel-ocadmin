<?php

namespace App\Portals\Ocadmin\Core\Controllers\Config;

use App\Helpers\Classes\LocaleHelper;
use App\Helpers\Classes\OrmHelper;
use App\Models\Config\Taxonomy;
use App\Models\Config\Term;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Portals\Ocadmin\Core\Controllers\OcadminController;

class TermController extends OcadminController
{
    protected function setLangFiles(): array
    {
        return ['common', 'config/term'];
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
                'href' => route('lang.ocadmin.config.term.index'),
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
        $data['taxonomies'] = Taxonomy::with('translations')->orderBy('sort_order')->get();

        return view('ocadmin::config.term.index', $data);
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
        $query = Term::with(['taxonomy.translations', 'parent.translations', 'translations']);
        $filter_data = $this->filterData($request, ['equal_taxonomy_id', 'equal_is_active']);

        // 預設排序
        $filter_data['sort'] = $request->query('sort', 'sort_order');
        $filter_data['order'] = $request->query('order', 'asc');

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

            unset(
                $filter_data['search'],
                $filter_data['filter_code'],
                $filter_data['filter_name']
            );
        }

        // OrmHelper 自動處理 filter_*, equal_* 及排序
        OrmHelper::prepare($query, $filter_data);

        // 分頁結果
        $terms = OrmHelper::getResult($query, $filter_data);
        $terms->withPath(route('lang.ocadmin.config.term.list'));

        $data['lang'] = $this->lang;
        $data['terms'] = $terms;
        $data['pagination'] = $terms->links('ocadmin::pagination.default');

        // 建構 URL 參數與排序連結
        $url = $this->buildUrlParams($request);
        $baseUrl = route('lang.ocadmin.config.term.list');
        $data['sort'] = $filter_data['sort'];
        $data['order'] = $filter_data['order'];
        $nextOrder = ($data['order'] == 'asc') ? 'desc' : 'asc';

        $data['sort_taxonomy_id'] = $baseUrl . "?sort=taxonomy_id&order={$nextOrder}" . str_replace('?', '&', $url);
        $data['sort_code'] = $baseUrl . "?sort=code&order={$nextOrder}" . str_replace('?', '&', $url);
        $data['sort_name'] = $baseUrl . "?sort=name&order={$nextOrder}" . str_replace('?', '&', $url);
        $data['sort_sort_order'] = $baseUrl . "?sort=sort_order&order={$nextOrder}" . str_replace('?', '&', $url);

        return view('ocadmin::config.term.list', $data)->render();
    }

    public function create(Request $request): View
    {
        $taxonomies = Taxonomy::with('translations')->orderBy('sort_order')->get();

        $parentTerms = [];
        if ($request->filled('taxonomy_id')) {
            $parentTerms = Term::with('translations')
                ->where('taxonomy_id', $request->taxonomy_id)
                ->orderBy('sort_order')
                ->get();
        }

        $term = new Term();
        $term->taxonomy_id = $request->taxonomy_id;

        $data['lang'] = $this->lang;
        $data['breadcrumbs'] = $this->breadcrumbs;
        $data['term'] = $term;
        $data['taxonomies'] = $taxonomies;
        $data['parentTerms'] = $parentTerms;

        return view('ocadmin::config.term.form', $data);
    }

    public function store(Request $request): JsonResponse
    {
        $rules = [
            'taxonomy_id' => 'required|exists:taxonomies,id',
            'parent_id' => 'nullable|exists:terms,id',
            'code' => 'required|string|max:50|regex:/^[a-z][a-z0-9_]*$/',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ];

        foreach (LocaleHelper::getSupportedLocales() as $locale) {
            $rules["translations.{$locale}.name"] = 'required|string|max:100';
        }

        $validated = $request->validate($rules);

        $exists = Term::where('taxonomy_id', $validated['taxonomy_id'])
            ->where('code', $validated['code'])
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => $this->lang->error_code_exists,
                'errors'  => ['code' => $this->lang->error_code_exists],
            ], 422);
        }

        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['is_active'] = $request->has('is_active');

        $term = Term::create($validated);
        $term->saveTranslations($validated['translations']);

        return response()->json([
            'success' => true,
            'message' => $this->lang->text_success_add,
            'replace_url' => route('lang.ocadmin.config.term.edit', $term),
            'form_action' => route('lang.ocadmin.config.term.update', $term),
        ]);
    }

    public function edit(Term $term): View
    {
        $term->load('translations');
        $taxonomies = Taxonomy::with('translations')->orderBy('sort_order')->get();

        $parentTerms = Term::with('translations')
            ->where('taxonomy_id', $term->taxonomy_id)
            ->where('id', '!=', $term->id)
            ->orderBy('sort_order')
            ->get();

        $data['lang'] = $this->lang;
        $data['breadcrumbs'] = $this->breadcrumbs;
        $data['term'] = $term;
        $data['taxonomies'] = $taxonomies;
        $data['parentTerms'] = $parentTerms;

        return view('ocadmin::config.term.form', $data);
    }

    public function update(Request $request, Term $term): JsonResponse
    {
        $rules = [
            'taxonomy_id' => 'required|exists:taxonomies,id',
            'parent_id' => 'nullable|exists:terms,id',
            'code' => 'required|string|max:50|regex:/^[a-z][a-z0-9_]*$/',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ];

        foreach (LocaleHelper::getSupportedLocales() as $locale) {
            $rules["translations.{$locale}.name"] = 'required|string|max:100';
        }

        $validated = $request->validate($rules);

        $exists = Term::where('taxonomy_id', $validated['taxonomy_id'])
            ->where('code', $validated['code'])
            ->where('id', '!=', $term->id)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => $this->lang->error_code_exists,
                'errors'  => ['code' => $this->lang->error_code_exists],
            ], 422);
        }

        if ($validated['parent_id'] == $term->id) {
            return response()->json([
                'success' => false,
                'message' => $this->lang->error_parent_self,
                'errors'  => ['parent_id' => $this->lang->error_parent_self],
            ], 422);
        }

        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['is_active'] = $request->has('is_active');

        $term->update($validated);
        $term->saveTranslations($validated['translations']);

        return response()->json([
            'success' => true,
            'message' => $this->lang->text_success_edit,
        ]);
    }

    public function destroy(Term $term): JsonResponse
    {
        if ($term->children()->exists()) {
            return response()->json([
                'success' => false,
                'message' => $this->lang->error_has_children,
            ]);
        }

        $term->delete();

        return response()->json(['success' => true, 'message' => $this->lang->text_success_delete]);
    }

    public function batchDelete(Request $request): JsonResponse
    {
        $ids = $request->input('selected', []);

        if (empty($ids)) {
            return response()->json(['success' => false, 'message' => $this->lang->error_select_delete]);
        }

        $hasChildren = Term::whereIn('id', $ids)->whereHas('children')->exists();
        if ($hasChildren) {
            return response()->json([
                'success' => false,
                'message' => $this->lang->error_batch_has_children,
            ]);
        }

        Term::whereIn('id', $ids)->delete();

        return response()->json(['success' => true, 'message' => $this->lang->text_success_delete]);
    }

    /**
     * JSON：取得某分類下的詞彙（供 AJAX 下拉選單）
     */
    public function byTaxonomy(Taxonomy $taxonomy): JsonResponse
    {
        $terms = $taxonomy->terms()
            ->with('translations')
            ->select('id', 'parent_id', 'code', 'taxonomy_id')
            ->get()
            ->map(fn ($term) => [
                'id' => $term->id,
                'parent_id' => $term->parent_id,
                'code' => $term->code,
                'name' => $term->name,
            ]);

        return response()->json($terms);
    }
}
