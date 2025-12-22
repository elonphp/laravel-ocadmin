<?php

namespace App\Portals\Ocadmin\Modules\Common\Taxonomy;

use App\Models\Common\Taxonomy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use App\Helpers\Classes\OrmHelper;
use App\Portals\Ocadmin\Core\Controllers\Controller;

class TaxonomyController extends Controller
{
    public function __construct(
        private TaxonomyService $taxonomyService
    ) {
        parent::__construct();
    }

    protected function setBreadcrumbs(): void
    {
        $this->breadcrumbs = [
            (object)[
                'text' => '首頁',
                'href' => route('lang.ocadmin.dashboard'),
            ],
            (object)[
                'text' => '系統管理',
                'href' => 'javascript:void(0)',
            ],
            (object)[
                'text' => '詞彙管理',
                'href' => 'javascript:void(0)',
            ],
            (object)[
                'text' => '分類',
                'href' => route('lang.ocadmin.system.taxonomy.taxonomy.index'),
            ],
        ];
    }

    /**
     * 列表頁面 - 完整頁面渲染
     */
    public function index(Request $request): View
    {
        $data['list'] = $this->getList($request);
        $data['breadcrumbs'] = $this->breadcrumbs;

        return view('ocadmin.common.taxonomy::index', $data);
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
        $query = Taxonomy::query()->with(['translation', 'terms']);
        $filter_data = $request->all();

        OrmHelper::prepare($query, $filter_data);

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

        // 預設排序
        $filter_data['sort'] = $request->get('sort', 'sort_order');
        $filter_data['order'] = $request->get('order', 'asc');

        $taxonomies = OrmHelper::getResult($query, $filter_data);

        $taxonomies->withPath(route('lang.ocadmin.system.taxonomy.taxonomy.list'));

        $url = $this->buildUrlParams($request);

        $data['taxonomies'] = $taxonomies;
        $data['action'] = route('lang.ocadmin.system.taxonomy.taxonomy.list') . $url;
        $data['url_params'] = $url;

        return view('ocadmin.common.taxonomy::list', $data)->render();
    }

    /**
     * 新增頁面
     */
    public function create()
    {
        $locales = config('localization.supported_locales', ['zh_Hant', 'en']);

        return view('ocadmin.common.taxonomy::form', [
            'taxonomy' => new Taxonomy(),
            'locales' => $locales,
            'breadcrumbs' => $this->breadcrumbs,
        ]);
    }

    /**
     * 儲存新增 (AJAX)
     */
    public function store(Request $request)
    {
        $validator = validator($request->all(), [
            'code' => 'required|string|max:50|unique:taxonomies,code|regex:/^[a-z][a-z0-9_]*$/',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'translations' => 'required|array',
            'translations.*.name' => 'required|string|max:100',
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

        $taxonomy = DB::transaction(fn () => $this->taxonomyService->create(
            $validated,
            $validated['translations'] ?? []
        ));

        return response()->json([
            'success' => '分類法新增成功！',
            'redirect_url' => route('lang.ocadmin.system.taxonomy.taxonomy.edit', $taxonomy->id),
            'form_action' => route('lang.ocadmin.system.taxonomy.taxonomy.update', $taxonomy->id),
        ]);
    }

    /**
     * 編輯頁面
     */
    public function edit($id)
    {
        $taxonomy = Taxonomy::findOrFail($id);
        $taxonomy->load('translations');
        $locales = config('localization.supported_locales', ['zh_Hant', 'en']);

        return view('ocadmin.common.taxonomy::form', [
            'taxonomy' => $taxonomy,
            'locales' => $locales,
            'breadcrumbs' => $this->breadcrumbs,
        ]);
    }

    /**
     * 儲存編輯 (AJAX)
     */
    public function update(Request $request, $id)
    {
        $taxonomy = Taxonomy::findOrFail($id);

        $validator = validator($request->all(), [
            'code' => 'required|string|max:50|unique:taxonomies,code,' . $id . '|regex:/^[a-z][a-z0-9_]*$/',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'translations' => 'required|array',
            'translations.*.name' => 'required|string|max:100',
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

        DB::transaction(fn () => $this->taxonomyService->update(
            $taxonomy,
            $validated,
            $validated['translations'] ?? []
        ));

        return response()->json([
            'success' => '分類法更新成功！',
        ]);
    }

    /**
     * 刪除
     */
    public function destroy($id)
    {
        $taxonomy = Taxonomy::findOrFail($id);

        // 檢查是否有 terms
        if ($taxonomy->terms()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => '此分類法下有詞彙，無法刪除',
            ]);
        }

        DB::transaction(fn () => $this->taxonomyService->delete($taxonomy));

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

        // 檢查是否有 terms
        $hasTerms = Taxonomy::whereIn('id', $ids)->whereHas('terms')->exists();
        if ($hasTerms) {
            return response()->json([
                'success' => false,
                'message' => '選取的分類法中有包含詞彙的項目，無法刪除',
            ]);
        }

        DB::transaction(fn () => $this->taxonomyService->batchDelete($ids));

        return response()->json(['success' => true]);
    }

    /**
     * 取得所有分類法（用於下拉選單）
     */
    public function all(Request $request)
    {
        $taxonomies = Taxonomy::query()
            ->with('translation')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get(['id', 'code']);

        return response()->json($taxonomies->map(fn($t) => [
            'id' => $t->id,
            'code' => $t->code,
            'name' => $t->name,
        ]));
    }

}
