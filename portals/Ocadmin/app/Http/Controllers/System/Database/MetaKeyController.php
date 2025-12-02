<?php

namespace Portals\Ocadmin\Http\Controllers\System\Database;

use App\Models\System\Database\MetaKey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use App\Helpers\Classes\OrmHelper;
use Portals\Ocadmin\Http\Controllers\Controller;
use Portals\Ocadmin\Services\System\Database\MetaKeyService;

class MetaKeyController extends Controller
{
    public function __construct(
        private MetaKeyService $metaKeyService
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
                'text' => '資料庫',
                'href' => 'javascript:void(0)',
            ],
            (object)[
                'text' => '欄位定義',
                'href' => route('lang.ocadmin.system.database.meta_key.index'),
            ],
        ];
    }

    /**
     * 列表頁面 - 完整頁面渲染
     */
    public function index(Request $request): View
    {
        // $this->authorize('viewAny', MetaKey::class);

        $data['list'] = $this->getList($request);
        $data['tableNames'] = MetaKey::getDistinctTableNames();
        $data['breadcrumbs'] = $this->breadcrumbs;

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
        $metaKeys->withPath(route('lang.ocadmin.system.database.meta_key.list'));

        // 建構 URL 參數
        $url = $this->buildUrlParams($request);

        // 準備資料
        $data['metaKeys'] = $metaKeys;
        $data['action'] = route('lang.ocadmin.system.database.meta_key.list') . $url;
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
            'breadcrumbs' => $this->breadcrumbs,
        ]);
    }

    /**
     * 儲存新增 (AJAX)
     */
    public function store(Request $request)
    {
        // $this->authorize('create', MetaKey::class);

        $dataTypes = implode(',', array_keys(MetaKey::DATA_TYPES));
        $validator = validator($request->all(), [
            'name'        => 'required|string|max:50|regex:/^[a-z][a-z0-9_]*$/',
            'table_name'  => 'nullable|string|max:30|regex:/^[a-z][a-z0-9_]*$/',
            'data_type'   => "nullable|string|in:{$dataTypes}",
            'precision'   => 'nullable|string|max:10|regex:/^\d+(\.\d+)?$/',
            'description' => 'nullable|string|max:100',
        ], [
            'name.regex' => '欄位名稱只能使用小寫英文、數字和底線，且必須以英文字母開頭',
            'table_name.regex' => '資料表名稱只能使用小寫英文、數字和底線，且必須以英文字母開頭',
            'precision.regex' => '精度格式錯誤，應為數字或 數字.數字 格式',
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
        if (empty($validated['precision'])) {
            $validated['precision'] = null;
        }

        // 檢查 name + table_name 組合是否重複
        $exists = MetaKey::where('name', $validated['name'])
            ->where('table_name', $validated['table_name'])
            ->exists();

        if ($exists) {
            return response()->json([
                'error_warning' => '此欄位名稱在該資料表中已存在',
                'errors' => ['name' => '此欄位名稱在該資料表中已存在'],
            ]);
        }

        // 檢查 meta_key 名稱是否與本表欄位衝突
        if ($validated['table_name'] && Schema::hasColumn($validated['table_name'], $validated['name'])) {
            return response()->json([
                'error_warning' => "欄位名稱 \"{$validated['name']}\" 已存在於 {$validated['table_name']} 資料表中",
                'errors' => ['name' => "欄位名稱 \"{$validated['name']}\" 已存在於 {$validated['table_name']} 資料表中"],
            ]);
        }

        $metaKey = DB::transaction(fn () => $this->metaKeyService->create($validated));

        // 同步 sysdata translations 表結構（在 transaction 之後）
        $this->metaKeyService->syncTranslations($metaKey);

        return response()->json([
            'success' => '欄位定義新增成功！',
            'redirect_url' => route('lang.ocadmin.system.database.meta_key.edit', $metaKey->id),
            'form_action' => route('lang.ocadmin.system.database.meta_key.update', $metaKey->id),
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
            'breadcrumbs' => $this->breadcrumbs,
        ]);
    }

    /**
     * 儲存編輯 (AJAX)
     */
    public function update(Request $request, MetaKey $metaKey)
    {
        // $this->authorize('update', $metaKey);

        $dataTypes = implode(',', array_keys(MetaKey::DATA_TYPES));
        $validator = validator($request->all(), [
            'name'        => 'required|string|max:50|regex:/^[a-z][a-z0-9_]*$/',
            'table_name'  => 'nullable|string|max:30|regex:/^[a-z][a-z0-9_]*$/',
            'data_type'   => "nullable|string|in:{$dataTypes}",
            'precision'   => 'nullable|string|max:10|regex:/^\d+(\.\d+)?$/',
            'description' => 'nullable|string|max:100',
        ], [
            'name.regex' => '欄位名稱只能使用小寫英文、數字和底線，且必須以英文字母開頭',
            'table_name.regex' => '資料表名稱只能使用小寫英文、數字和底線，且必須以英文字母開頭',
            'precision.regex' => '精度格式錯誤，應為數字或 數字.數字 格式',
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
        if (empty($validated['precision'])) {
            $validated['precision'] = null;
        }

        // 填入資料檢查是否有變更
        $metaKey->fill($validated);
        $hasChanges = $metaKey->isDirty();

        if ($hasChanges) {
            // 檢查 name + table_name 組合是否重複（排除自己）
            $exists = MetaKey::where('name', $validated['name'])
                ->where('table_name', $validated['table_name'])
                ->where('id', '!=', $metaKey->id)
                ->exists();

            if ($exists) {
                return response()->json([
                    'error_warning' => '此欄位名稱在該資料表中已存在',
                    'errors' => ['name' => '此欄位名稱在該資料表中已存在'],
                ]);
            }

            // 檢查 meta_key 名稱是否與本表欄位衝突
            if ($validated['table_name'] && Schema::hasColumn($validated['table_name'], $validated['name'])) {
                return response()->json([
                    'error_warning' => "欄位名稱 \"{$validated['name']}\" 已存在於 {$validated['table_name']} 資料表中",
                    'errors' => ['name' => "欄位名稱 \"{$validated['name']}\" 已存在於 {$validated['table_name']} 資料表中"],
                ]);
            }

            // 記住舊的 table_name（用於同步）
            $oldTableName = $metaKey->getOriginal('table_name');

            DB::transaction(fn () => $this->metaKeyService->update($metaKey, $validated));

            // 同步 sysdata translations 表結構（在 transaction 之後）
            $this->metaKeyService->syncTranslations($metaKey, $oldTableName);
        } else {
            // 即使資料無變更，仍同步 translations 表結構（確保表存在）
            $this->metaKeyService->syncTranslations($metaKey);
        }

        return response()->json([
            'success' => $hasChanges ? '欄位定義更新成功！' : '資料無變更，已同步表結構',
        ]);
    }

    /**
     * 刪除
     */
    public function destroy(MetaKey $metaKey)
    {
        // $this->authorize('delete', $metaKey);

        $tableName = DB::transaction(fn () => $this->metaKeyService->delete($metaKey));

        // 同步 sysdata translations 表結構（在 transaction 之後）
        if ($tableName) {
            $this->metaKeyService->syncTable($tableName);
        }

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

        $result = DB::transaction(fn () => $this->metaKeyService->batchDelete($ids));

        // 同步受影響的 translations 表結構（在 transaction 之後）
        foreach ($result['tableNames'] as $tableName) {
            $this->metaKeyService->syncTable($tableName);
        }

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
        // 從資料庫 Schema 取得所有資料表，排除 *_metas 表
        $tableNames = collect(DB::select('SHOW TABLES'))
            ->map(fn ($table) => array_values((array) $table)[0])
            ->reject(fn ($name) => str_ends_with($name, '_metas'))
            ->sort()
            ->values();

        return response()->json($tableNames);
    }

}
