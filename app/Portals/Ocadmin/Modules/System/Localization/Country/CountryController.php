<?php

namespace App\Portals\Ocadmin\Modules\System\Localization\Country;

use App\Models\System\Localization\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use App\Helpers\Classes\OrmHelper;
use App\Portals\Ocadmin\Core\Controllers\Controller;

class CountryController extends Controller
{
    public function __construct(private CountryService $countryService)
    {
        parent::__construct();

        $this->getLang(['common', 'system/localization/country']);
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
                'text' => $this->lang->text_localization,
                'href' => 'javascript:void(0)',
            ],
            (object)[
                'text' => $this->lang->heading_title,
                'href' => route('lang.ocadmin.system.localization.country.index'),
            ],
        ];
    }

    /**
     * 列表頁面 - 完整頁面渲染
     */
    public function index(Request $request): View
    {
        // $this->authorize('viewAny', Country::class);

        $data['lang'] = $this->lang;
        $data['list'] = $this->getList($request);
        $data['breadcrumbs'] = $this->breadcrumbs;

        return view('ocadmin.system.localization.country::index', $data);
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
        $data['lang'] = $this->lang;
        $data['countries'] = $countries;
        $data['action'] = route('lang.ocadmin.system.localization.country.list') . $url;
        $data['url_params'] = $url;

        // 返回表格部分視圖
        return view('ocadmin.system.localization.country::list', $data)->render();
    }

    /**
     * 新增頁面
     */
    public function create()
    {
        // $this->authorize('create', Country::class);

        return view('ocadmin.system.localization.country::form', [
            'lang' => $this->lang,
            'country' => new Country(),
            'breadcrumbs' => $this->breadcrumbs,
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
            'success' => $this->lang->text_add_success,
            'redirect_url' => route('lang.ocadmin.system.localization.country.edit', $country->id),
            'form_action' => route('lang.ocadmin.system.localization.country.update', $country->id),
        ]);
    }

    /**
     * 編輯頁面
     */
    public function edit(Country $country)
    {
        // $this->authorize('update', $country);

        return view('ocadmin.system.localization.country::form', [
            'lang' => $this->lang,
            'country' => $country,
            'breadcrumbs' => $this->breadcrumbs,
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
            'success' => $this->lang->text_edit_success,
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

}
