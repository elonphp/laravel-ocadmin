<?php

namespace Portals\Ocadmin\Http\Controllers\System\Localization;

use App\Models\System\Localization\Country;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use App\Helpers\Classes\OrmHelper;
use Portals\Ocadmin\Services\System\Localization\CountryService;

class CountryController extends Controller
{
    public function __construct(
        private CountryService $countryService
    ) {}

    /**
     * 列表頁面 - 完整頁面渲染
     */
    public function index(Request $request): View
    {
        // $this->authorize('viewAny', Country::class);

        $data['list'] = $this->getList($request);

        return view('ocadmin::system.localization.country.index', $data);
    }

    /**
     * AJAX 請求入口 - 僅返回表格 HTML
     */
    public function list(Request $request): string
    {
        // $this->authorize('viewAny', Country::class);

        return $this->getList($request);
    }

    /**
     * 核心查詢邏輯 - 處理資料查詢並渲染表格部分
     */
    protected function getList(Request $request): string
    {
        // 使用 OrmHelper 自動解析前端參數
        $query = Country::query();
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
                    OrmHelper::filterOrEqualColumn($subQ, 'filter_native_name', $search);
                });
                $q->orWhere(function ($subQ) use ($search) {
                    OrmHelper::filterOrEqualColumn($subQ, 'filter_iso_code_2', $search);
                });
                $q->orWhere(function ($subQ) use ($search) {
                    OrmHelper::filterOrEqualColumn($subQ, 'filter_iso_code_3', $search);
                });
            });
        }

        // 預設排序
        $filter_data['sort'] = $request->get('sort', 'sort_order');
        $filter_data['order'] = $request->get('order', 'asc');

        // 使用 OrmHelper 獲取結果（自動處理分頁）
        $countries = OrmHelper::getResult($query, $filter_data);

        // 設置分頁器路徑為 list 路由（確保 AJAX 分頁正常運作）
        $countries->withPath(route('lang.ocadmin.system.localization.country.list'));

        // 建構 URL 參數
        $url = $this->buildUrlParams($request);

        // 準備資料
        $data['countries'] = $countries;
        $data['action'] = route('lang.ocadmin.system.localization.country.list') . $url;
        $data['url_params'] = $url;

        // 返回表格部分視圖
        return view('ocadmin::system.localization.country.list', $data)->render();
    }

    /**
     * 新增頁面
     */
    public function create()
    {
        // $this->authorize('create', Country::class);

        return view('ocadmin::system.localization.country.form', [
            'country' => new Country(),
        ]);
    }

    /**
     * 儲存新增 (AJAX)
     */
    public function store(Request $request)
    {
        // $this->authorize('create', Country::class);

        $validator = validator($request->all(), [
            'name'              => 'required|string|max:128',
            'native_name'       => 'nullable|string|max:128',
            'iso_code_2'        => 'required|string|size:2|unique:countries,iso_code_2',
            'iso_code_3'        => 'required|string|size:3',
            'address_format'    => 'nullable|string|max:1000',
            'postcode_required' => 'boolean',
            'is_active'         => 'boolean',
            'sort_order'        => 'integer|min:0',
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
        $country = DB::transaction(fn () => $this->countryService->create($validated));

        return response()->json([
            'success' => '國家新增成功！',
            'redirect' => route('lang.ocadmin.system.localization.country.edit', $country->id),
        ]);
    }

    /**
     * 編輯頁面
     */
    public function edit(Country $country)
    {
        // $this->authorize('update', $country);

        return view('ocadmin::system.localization.country.form', [
            'country' => $country,
        ]);
    }

    /**
     * 儲存編輯 (AJAX)
     */
    public function update(Request $request, Country $country)
    {
        // $this->authorize('update', $country);

        $validator = validator($request->all(), [
            'name'              => 'required|string|max:128',
            'native_name'       => 'nullable|string|max:128',
            'iso_code_2'        => 'required|string|size:2|unique:countries,iso_code_2,' . $country->id,
            'iso_code_3'        => 'required|string|size:3',
            'address_format'    => 'nullable|string|max:1000',
            'postcode_required' => 'boolean',
            'is_active'         => 'boolean',
            'sort_order'        => 'integer|min:0',
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
        DB::transaction(fn () => $this->countryService->update($country, $validated));

        return response()->json([
            'success' => '國家更新成功！',
        ]);
    }

    /**
     * 刪除
     */
    public function destroy(Country $country)
    {
        $this->authorize('delete', $country);

        DB::transaction(fn () => $this->countryService->delete($country));

        return response()->json(['success' => true]);
    }

    /**
     * 批次刪除
     */
    public function batchDelete(Request $request)
    {
        $this->authorize('delete', Country::class);

        $ids = $request->input('selected', []);

        if (empty($ids)) {
            return response()->json(['success' => false, 'message' => '請選擇要刪除的項目']);
        }

        DB::transaction(fn () => $this->countryService->batchDelete($ids));

        return response()->json(['success' => true]);
    }

    /**
     * 取得所有國家（用於 select2 下拉選單）
     */
    public function all()
    {
        $countries = Country::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['iso_code_2 as id', 'name', 'native_name']);

        return response()->json($countries);
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
