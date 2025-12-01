<?php

namespace Portals\Ocadmin\Http\Controllers\System\Taxonomy;

use App\Models\Common\Term;
use App\Models\Common\Taxonomy;
use App\Models\System\Database\MetaKey;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use App\Helpers\Classes\OrmHelper;
use Portals\Ocadmin\Services\System\Taxonomy\TermService;

class TermController extends Controller
{
    public function __construct(
        private TermService $termService
    ) {}

    /**
     * 列表頁面 - 完整頁面渲染
     * 可接收 taxonomy_id 參數篩選特定分類法的詞彙
     */
    public function index(Request $request): View
    {
        $data['list'] = $this->getList($request);
        $data['taxonomies'] = Taxonomy::with('translation')->orderBy('sort_order')->get();
        $data['currentTaxonomyId'] = $request->get('filter_taxonomy_id');

        // 如果有指定 taxonomy_id，取得該分類法資訊
        if ($request->has('filter_taxonomy_id') && $request->filter_taxonomy_id) {
            $data['currentTaxonomy'] = Taxonomy::with('translation')->find($request->filter_taxonomy_id);
        }

        return view('ocadmin::system.taxonomy.term.index', $data);
    }

    /**
     * AJAX 請求入口 - 僅返回表格 HTML
     */
    public function list(Request $request): string
    {
        return $this->getList($request);
    }

    /**
     * 核心查詢邏輯 - 處理資料查詢並渲染表格部分
     */
    protected function getList(Request $request): string
    {
        $query = Term::query()->with(['taxonomy.translation', 'translation', 'parent.translation']);
        $filter_data = $request->all();

        OrmHelper::prepare($query, $filter_data);

        // 篩選：taxonomy_id（重要！以 Taxonomy 為入口）
        if ($request->has('filter_taxonomy_id') && $request->filter_taxonomy_id) {
            $query->where('taxonomy_id', $request->filter_taxonomy_id);
        }

        // 關鍵字查詢
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhereHas('translations', function ($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // 篩選：code
        if ($request->has('filter_code') && $request->filter_code) {
            $query->where('code', 'like', '%' . $request->filter_code . '%');
        }

        // 篩選：啟用狀態
        if ($request->has('filter_is_active') && $request->filter_is_active !== '') {
            $query->where('is_active', $request->filter_is_active);
        }

        // 篩選：父層
        if ($request->has('filter_parent_id')) {
            if ($request->filter_parent_id === '0' || $request->filter_parent_id === '') {
                // 只顯示頂層
            } elseif ($request->filter_parent_id === '_all') {
                // 顯示全部（不篩選）
            } else {
                $query->where('parent_id', $request->filter_parent_id);
            }
        }

        // 預設排序
        $filter_data['sort'] = $request->get('sort', 'sort_order');
        $filter_data['order'] = $request->get('order', 'asc');

        $terms = OrmHelper::getResult($query, $filter_data);

        $terms->withPath(route('lang.ocadmin.system.taxonomy.term.list'));

        $url = $this->buildUrlParams($request);

        $data['terms'] = $terms;
        $data['action'] = route('lang.ocadmin.system.taxonomy.term.list') . $url;
        $data['url_params'] = $url;
        $data['currentTaxonomyId'] = $request->get('filter_taxonomy_id');

        return view('ocadmin::system.taxonomy.term.list', $data)->render();
    }

    /**
     * 新增頁面
     */
    public function create(Request $request)
    {
        $locales = config('localization.supported_locales', ['zh_Hant', 'en']);
        $taxonomies = Taxonomy::with('translation')->orderBy('sort_order')->get();
        $metaKeys = MetaKey::getForTable('terms');

        // 預選的 taxonomy
        $selectedTaxonomyId = $request->get('taxonomy_id');

        // 取得可選的父層 terms
        $parentTerms = collect();
        if ($selectedTaxonomyId) {
            $parentTerms = Term::where('taxonomy_id', $selectedTaxonomyId)
                ->with('translation')
                ->orderBy('sort_order')
                ->get();
        }

        return view('ocadmin::system.taxonomy.term.form', [
            'term' => new Term(['taxonomy_id' => $selectedTaxonomyId]),
            'taxonomies' => $taxonomies,
            'parentTerms' => $parentTerms,
            'locales' => $locales,
            'metaKeys' => $metaKeys,
        ]);
    }

    /**
     * 儲存新增 (AJAX)
     */
    public function store(Request $request)
    {
        $validator = validator($request->all(), [
            'taxonomy_id' => 'required|exists:taxonomies,id',
            'parent_id' => 'nullable|exists:terms,id',
            'code' => 'required|string|max:50|regex:/^[a-z][a-z0-9_]*$/',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'translations' => 'required|array',
            'translations.*.name' => 'required|string|max:100',
            'translations.*.short_name' => 'nullable|string|max:50',
            'metas' => 'nullable|array',
        ], [
            'code.regex' => '代碼只能使用小寫英文、數字和底線，且必須以英文字母開頭',
            'translations.*.name.required' => '名稱為必填',
        ]);

        if ($validator->fails()) {
            $errors = [];
            foreach ($validator->errors()->toArray() as $field => $messages) {
                $errors[$field] = $messages[0];
            }
            return response()->json([
                'error_warning' => $validator->errors()->first(),
                'errors' => $errors,
            ]);
        }

        $validated = $validator->validated();

        // 檢查 code 在同一 taxonomy 下是否唯一
        $exists = Term::where('taxonomy_id', $validated['taxonomy_id'])
            ->where('code', $validated['code'])
            ->exists();

        if ($exists) {
            return response()->json([
                'error_warning' => '此代碼在該分類法下已存在',
                'errors' => ['code' => '此代碼在該分類法下已存在'],
            ]);
        }

        $term = DB::transaction(fn () => $this->termService->create(
            $validated,
            $validated['translations'] ?? [],
            $validated['metas'] ?? []
        ));

        return response()->json([
            'success' => '詞彙新增成功！',
            'redirect' => route('lang.ocadmin.system.taxonomy.term.edit', $term->id),
        ]);
    }

    /**
     * 編輯頁面
     */
    public function edit($id)
    {
        $term = Term::findOrFail($id);
        $term->load(['translations', 'metas.metaKey', 'taxonomy.translation']);
        $locales = config('localization.supported_locales', ['zh_Hant', 'en']);
        $taxonomies = Taxonomy::with('translation')->orderBy('sort_order')->get();
        $metaKeys = MetaKey::getForTable('terms');

        // 取得可選的父層 terms（排除自己和子層）
        $parentTerms = Term::where('taxonomy_id', $term->taxonomy_id)
            ->where('id', '!=', $term->id)
            ->with('translation')
            ->orderBy('sort_order')
            ->get();

        return view('ocadmin::system.taxonomy.term.form', [
            'term' => $term,
            'taxonomies' => $taxonomies,
            'parentTerms' => $parentTerms,
            'locales' => $locales,
            'metaKeys' => $metaKeys,
        ]);
    }

    /**
     * 儲存編輯 (AJAX)
     */
    public function update(Request $request, $id)
    {
        $term = Term::findOrFail($id);

        $validator = validator($request->all(), [
            'taxonomy_id' => 'required|exists:taxonomies,id',
            'parent_id' => 'nullable|exists:terms,id',
            'code' => 'required|string|max:50|regex:/^[a-z][a-z0-9_]*$/',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'translations' => 'required|array',
            'translations.*.name' => 'required|string|max:100',
            'translations.*.short_name' => 'nullable|string|max:50',
            'metas' => 'nullable|array',
        ], [
            'code.regex' => '代碼只能使用小寫英文、數字和底線，且必須以英文字母開頭',
            'translations.*.name.required' => '名稱為必填',
        ]);

        if ($validator->fails()) {
            $errors = [];
            foreach ($validator->errors()->toArray() as $field => $messages) {
                $errors[$field] = $messages[0];
            }
            return response()->json([
                'error_warning' => $validator->errors()->first(),
                'errors' => $errors,
            ]);
        }

        $validated = $validator->validated();

        // 檢查 code 在同一 taxonomy 下是否唯一（排除自己）
        $exists = Term::where('taxonomy_id', $validated['taxonomy_id'])
            ->where('code', $validated['code'])
            ->where('id', '!=', $id)
            ->exists();

        if ($exists) {
            return response()->json([
                'error_warning' => '此代碼在該分類法下已存在',
                'errors' => ['code' => '此代碼在該分類法下已存在'],
            ]);
        }

        // 檢查 parent_id 不能是自己或子層
        if (!empty($validated['parent_id'])) {
            if ($validated['parent_id'] == $id) {
                return response()->json([
                    'error_warning' => '父層不能是自己',
                    'errors' => ['parent_id' => '父層不能是自己'],
                ]);
            }
            // TODO: 檢查是否為子層（防止循環引用）
        }

        DB::transaction(fn () => $this->termService->update(
            $term,
            $validated,
            $validated['translations'] ?? [],
            $validated['metas'] ?? []
        ));

        return response()->json([
            'success' => '詞彙更新成功！',
        ]);
    }

    /**
     * 刪除
     */
    public function destroy($id)
    {
        $term = Term::findOrFail($id);

        // 檢查是否有子層
        if ($term->children()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => '此詞彙下有子項目，無法刪除',
            ]);
        }

        DB::transaction(fn () => $this->termService->delete($term));

        return response()->json(['success' => true]);
    }

    /**
     * 批次刪除
     */
    public function batchDelete(Request $request)
    {
        $ids = $request->input('selected', []);

        if (empty($ids)) {
            return response()->json(['success' => false, 'message' => '請選擇要刪除的項目']);
        }

        // 檢查是否有子層
        $hasChildren = Term::whereIn('parent_id', $ids)->exists();
        if ($hasChildren) {
            return response()->json([
                'success' => false,
                'message' => '選取的詞彙中有包含子項目的項目，無法刪除',
            ]);
        }

        DB::transaction(fn () => $this->termService->batchDelete($ids));

        return response()->json(['success' => true]);
    }

    /**
     * 取得特定 taxonomy 的所有 terms（用於下拉選單）
     */
    public function byTaxonomy(Request $request, $taxonomyId)
    {
        $terms = Term::where('taxonomy_id', $taxonomyId)
            ->with('translation')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get(['id', 'code', 'parent_id']);

        return response()->json($terms->map(fn($t) => [
            'id' => $t->id,
            'code' => $t->code,
            'name' => $t->name,
            'parent_id' => $t->parent_id,
        ]));
    }

    /**
     * 輔助方法：建構 URL 參數字串
     */
    protected function buildUrlParams(Request $request): string
    {
        $params = [];

        foreach ($request->all() as $key => $value) {
            if (str_starts_with($key, 'filter_') || str_starts_with($key, 'equal_')) {
                if ($value !== null && $value !== '') {
                    $params[] = $key . '=' . urlencode($value);
                }
            }
        }

        if ($request->has('search') && $request->search) {
            $params[] = 'search=' . urlencode($request->search);
        }

        if ($request->has('limit') && $request->limit) {
            $params[] = 'limit=' . $request->limit;
        }

        if ($request->has('page') && $request->page) {
            $params[] = 'page=' . $request->page;
        }

        if ($request->has('sort') && $request->sort) {
            $params[] = 'sort=' . urlencode($request->sort);
        }

        if ($request->has('order') && $request->order) {
            $params[] = 'order=' . urlencode($request->order);
        }

        return $params ? '?' . implode('&', $params) : '';
    }
}
