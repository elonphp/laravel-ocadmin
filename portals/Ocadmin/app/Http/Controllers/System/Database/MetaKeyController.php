<?php

namespace Portals\Ocadmin\Http\Controllers\System\Database;

use App\Models\System\Database\MetaKey;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use App\Helpers\Classes\OrmHelper;
use Portals\Ocadmin\Services\System\Database\MetaKeyService;

class MetaKeyController extends Controller
{
    public function __construct(
        private MetaKeyService $metaKeyService
    ) {}

    /**
     * 列表頁面 - 完整頁面渲染
     */
    public function index(Request $request): View
    {
        // $this->authorize('viewAny', MetaKey::class);

        $data['list'] = $this->getList($request);
        $data['tableNames'] = MetaKey::getDistinctTableNames();

        return view('ocadmin::system.database.meta_key.index', $data);
    }

    /**
     * AJAX 請求入口 - 僅返回表格 HTML
     */
    public function list(Request $request): string
    {
        // $this->authorize('viewAny', MetaKey::class);

        return $this->getList($request);
    }

    /**
     * 核心查詢邏輯 - 處理資料查詢並渲染表格部分
     */
    protected function getList(Request $request): string
    {
        // 使用 OrmHelper 自動解析前端參數
        $query = MetaKey::query();
        $filter_data = $request->all();

        OrmHelper::prepare($query, $filter_data);

        // 關鍵字查詢（需手動指定查詢欄位）
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                OrmHelper::filterOrEqualColumn($q, 'filter_name', $search);

                $q->orWhere(function ($subQ) use ($search) {
                    OrmHelper::filterOrEqualColumn($subQ, 'filter_description', $search);
                });
            });
        }

        // table_name 篩選（支援 null 值）
        if ($request->has('filter_table_name') && $request->filter_table_name !== '') {
            if ($request->filter_table_name === '_shared') {
                $query->whereNull('table_name');
            } else {
                $query->where('table_name', $request->filter_table_name);
            }
        }

        // 預設排序
        $filter_data['sort'] = $request->get('sort', 'id');
        $filter_data['order'] = $request->get('order', 'asc');

        // 使用 OrmHelper 獲取結果（自動處理分頁）
        $metaKeys = OrmHelper::getResult($query, $filter_data);

        // 設置分頁器路徑為 list 路由（確保 AJAX 分頁正常運作）
        $metaKeys->withPath(route('ocadmin.system.database.meta_key.list'));

        // 建構 URL 參數
        $url = $this->buildUrlParams($request);

        // 準備資料
        $data['metaKeys'] = $metaKeys;
        $data['action'] = route('ocadmin.system.database.meta_key.list') . $url;
        $data['url_params'] = $url;

        // 返回表格部分視圖
        return view('ocadmin::system.database.meta_key.list', $data)->render();
    }

    /**
     * 新增頁面
     */
    public function create()
    {
        // $this->authorize('create', MetaKey::class);

        return view('ocadmin::system.database.meta_key.form', [
            'metaKey' => new MetaKey(),
            'tableNames' => MetaKey::getDistinctTableNames(),
        ]);
    }

    /**
     * 儲存新增 (AJAX)
     */
    public function store(Request $request)
    {
        // $this->authorize('create', MetaKey::class);

        $validator = validator($request->all(), [
            'name'        => 'required|string|max:50|unique:meta_keys,name|regex:/^[a-z][a-z0-9_]*$/',
            'table_name'  => 'nullable|string|max:30|regex:/^[a-z][a-z0-9_]*$/',
            'description' => 'nullable|string|max:100',
        ], [
            'name.regex' => '欄位名稱只能使用小寫英文、數字和底線，且必須以英文字母開頭',
            'table_name.regex' => '資料表名稱只能使用小寫英文、數字和底線，且必須以英文字母開頭',
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

        // 空字串轉為 null
        if (empty($validated['table_name'])) {
            $validated['table_name'] = null;
        }

        $metaKey = DB::transaction(fn () => $this->metaKeyService->create($validated));

        return response()->json([
            'success' => '欄位定義新增成功！',
            'redirect' => route('ocadmin.system.database.meta_key.edit', $metaKey->id),
        ]);
    }

    /**
     * 編輯頁面
     */
    public function edit(MetaKey $metaKey)
    {
        // $this->authorize('update', $metaKey);

        return view('ocadmin::system.database.meta_key.form', [
            'metaKey' => $metaKey,
            'tableNames' => MetaKey::getDistinctTableNames(),
        ]);
    }

    /**
     * 儲存編輯 (AJAX)
     */
    public function update(Request $request, MetaKey $metaKey)
    {
        // $this->authorize('update', $metaKey);

        $validator = validator($request->all(), [
            'name'        => 'required|string|max:50|unique:meta_keys,name,' . $metaKey->id . '|regex:/^[a-z][a-z0-9_]*$/',
            'table_name'  => 'nullable|string|max:30|regex:/^[a-z][a-z0-9_]*$/',
            'description' => 'nullable|string|max:100',
        ], [
            'name.regex' => '欄位名稱只能使用小寫英文、數字和底線，且必須以英文字母開頭',
            'table_name.regex' => '資料表名稱只能使用小寫英文、數字和底線，且必須以英文字母開頭',
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

        // 空字串轉為 null
        if (empty($validated['table_name'])) {
            $validated['table_name'] = null;
        }

        DB::transaction(fn () => $this->metaKeyService->update($metaKey, $validated));

        return response()->json([
            'success' => '欄位定義更新成功！',
        ]);
    }

    /**
     * 刪除
     */
    public function destroy(MetaKey $metaKey)
    {
        // $this->authorize('delete', $metaKey);

        DB::transaction(fn () => $this->metaKeyService->delete($metaKey));

        return response()->json(['success' => true]);
    }

    /**
     * 批次刪除
     */
    public function batchDelete(Request $request)
    {
        // $this->authorize('delete', MetaKey::class);

        $ids = $request->input('selected', []);

        if (empty($ids)) {
            return response()->json(['success' => false, 'message' => '請選擇要刪除的項目']);
        }

        DB::transaction(fn () => $this->metaKeyService->batchDelete($ids));

        return response()->json(['success' => true]);
    }

    /**
     * 取得所有欄位定義（用於 select2 下拉選單）
     */
    public function all(Request $request)
    {
        $query = MetaKey::query()->orderBy('id');

        // 可選：依 table_name 篩選
        if ($request->has('table_name')) {
            $tableName = $request->table_name;
            $query->where(function ($q) use ($tableName) {
                $q->whereNull('table_name')
                  ->orWhere('table_name', $tableName);
            });
        }

        $metaKeys = $query->get(['id', 'name', 'table_name', 'description']);

        return response()->json($metaKeys);
    }

    /**
     * 取得所有不同的 table_name（用於 select2 下拉選單）
     */
    public function tableNames()
    {
        // 從資料庫 Schema 取得所有資料表
        $tableNames = collect(DB::select('SHOW TABLES'))
            ->map(fn ($table) => array_values((array) $table)[0])
            ->sort()
            ->values();

        return response()->json($tableNames);
    }

    /**
     * 輔助方法：建構 URL 參數字串（OpenCart 風格）
     */
    protected function buildUrlParams(Request $request): string
    {
        $params = [];

        // 收集所有 filter_* 和 equal_* 參數
        foreach ($request->all() as $key => $value) {
            if (str_starts_with($key, 'filter_') || str_starts_with($key, 'equal_')) {
                if ($value !== null && $value !== '') {
                    $params[] = $key . '=' . urlencode($value);
                }
            }
        }

        // 關鍵字查詢
        if ($request->has('search') && $request->search) {
            $params[] = 'search=' . urlencode($request->search);
        }

        // 分頁參數
        if ($request->has('limit') && $request->limit) {
            $params[] = 'limit=' . $request->limit;
        }

        if ($request->has('page') && $request->page) {
            $params[] = 'page=' . $request->page;
        }

        // 排序參數
        if ($request->has('sort') && $request->sort) {
            $params[] = 'sort=' . urlencode($request->sort);
        }

        if ($request->has('order') && $request->order) {
            $params[] = 'order=' . urlencode($request->order);
        }

        return $params ? '?' . implode('&', $params) : '';
    }
}
