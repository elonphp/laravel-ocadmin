<?php

namespace Portals\Ocadmin\Http\Controllers\System\Localization;

use App\Models\System\Localization\Country;
use App\Models\System\Localization\Division;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use App\Helpers\Classes\OrmHelper;
use Portals\Ocadmin\Services\System\Localization\DivisionService;

class DivisionController extends Controller
{
    public function __construct(
        private DivisionService $divisionService
    ) {}

    /**
     * 列表頁面 - 完整頁面渲染
     */
    public function index(Request $request): View
    {
        // $this->authorize('viewAny', Division::class);

        $data['list'] = $this->getList($request);
        $data['countries'] = Country::active()->ordered()->get();

        return view('ocadmin::system.localization.division.index', $data);
    }

    /**
     * AJAX 請求入口 - 僅返回表格 HTML
     */
    public function list(Request $request): string
    {
        // $this->authorize('viewAny', Division::class);

        return $this->getList($request);
    }

    /**
     * 核心查詢邏輯 - 處理資料查詢並渲染表格部分
     */
    protected function getList(Request $request): string
    {
        // 使用 OrmHelper 自動解析前端參數
        $query = Division::query()->with('country');
        $filter_data = $request->all();

        // 預設顯示全部（包含停用），覆蓋 OrmHelper 預設的 is_active=1
        if (!isset($filter_data['equal_is_active'])) {
            $filter_data['equal_is_active'] = '*';
        }

        OrmHelper::prepare($query, $filter_data);

        // 關鍵字查詢（需手動指定查詢欄位）
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                OrmHelper::filterOrEqualColumn($q, 'filter_name', $search);

                $q->orWhere(function ($subQ) use ($search) {
                    OrmHelper::filterOrEqualColumn($subQ, 'filter_code', $search);
                });
            });
        }

        // 預設排序
        $filter_data['sort'] = $request->get('sort', 'sort_order');
        $filter_data['order'] = $request->get('order', 'asc');

        // 使用 OrmHelper 獲取結果（自動處理分頁）
        $divisions = OrmHelper::getResult($query, $filter_data);

        // 設置分頁器路徑為 list 路由（確保 AJAX 分頁正常運作）
        $divisions->withPath(route('ocadmin.system.localization.division.list'));

        // 建構 URL 參數
        $url = $this->buildUrlParams($request);

        // 準備資料
        $data['divisions'] = $divisions;
        $data['action'] = route('ocadmin.system.localization.division.list') . $url;
        $data['url_params'] = $url;

        // 返回表格部分視圖
        return view('ocadmin::system.localization.division.list', $data)->render();
    }

    /**
     * 新增頁面
     */
    public function create()
    {
        // $this->authorize('create', Division::class);

        return view('ocadmin::system.localization.division.form', [
            'division' => new Division(),
        ]);
    }

    /**
     * 儲存新增 (AJAX)
     */
    public function store(Request $request)
    {
        // $this->authorize('create', Division::class);

        $validator = validator($request->all(), [
            'country_code' => 'required|exists:countries,iso_code_2',
            'level'        => 'required|integer|min:1|max:3',
            'name'         => 'required|string|max:128',
            'native_name'  => 'required|string|max:255',
            'code'         => 'nullable|string|max:32',
            'is_active'    => 'boolean',
            'sort_order'   => 'integer|min:0',
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
        $division = DB::transaction(fn () => $this->divisionService->create($validated));

        return response()->json([
            'success' => '行政區域新增成功！',
            'redirect' => route('ocadmin.system.localization.division.edit', $division->id),
        ]);
    }

    /**
     * 編輯頁面
     */
    public function edit(Division $division)
    {
        // $this->authorize('update', $division);

        // 只載入已選國家（用於顯示名稱），不載入全部國家清單
        $division->load('country');

        return view('ocadmin::system.localization.division.form', [
            'division' => $division,
        ]);
    }

    /**
     * 儲存編輯 (AJAX)
     */
    public function update(Request $request, Division $division)
    {
        // $this->authorize('update', $division);

        $validator = validator($request->all(), [
            'country_code' => 'required|exists:countries,iso_code_2',
            'level'        => 'required|integer|min:1|max:3',
            'name'         => 'required|string|max:128',
            'native_name'  => 'required|string|max:255',
            'code'         => 'nullable|string|max:32',
            'is_active'    => 'boolean',
            'sort_order'   => 'integer|min:0',
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
        DB::transaction(fn () => $this->divisionService->update($division, $validated));

        return response()->json([
            'success' => '行政區域更新成功！',
        ]);
    }

    /**
     * 刪除
     */
    public function destroy(Division $division)
    {
        $this->authorize('delete', $division);

        DB::transaction(fn () => $this->divisionService->delete($division));

        return response()->json(['success' => true]);
    }

    /**
     * 批次刪除
     */
    public function batchDelete(Request $request)
    {
        $this->authorize('delete', Division::class);

        $ids = $request->input('selected', []);

        if (empty($ids)) {
            return response()->json(['success' => false, 'message' => '請選擇要刪除的項目']);
        }

        DB::transaction(fn () => $this->divisionService->batchDelete($ids));

        return response()->json(['success' => true]);
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
