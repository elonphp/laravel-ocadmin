<?php

namespace App\Portals\Ocadmin\Modules\Hrm\Company;

use App\Helpers\Classes\OrmHelper;
use App\Models\Hrm\Company;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Portals\Ocadmin\Core\Controllers\OcadminController;

class CompanyController extends OcadminController
{
    protected function setLangFiles(): array
    {
        return ['common', 'hrm/company'];
    }

    protected function setBreadcrumbs(): void
    {
        $this->breadcrumbs = [
            (object)[
                'text' => $this->lang->text_home,
                'href' => route('lang.ocadmin.dashboard'),
            ],
            (object)[
                'text' => $this->lang->heading_title,
                'href' => route('lang.ocadmin.hrm.company.index'),
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

        return view('ocadmin.hrm.company::index', $data);
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
        $query = Company::with('parent');
        $filter_data = $this->filterData($request, ['equal_parent_id', 'equal_is_active']);

        // 預設排序
        $filter_data['sort'] = $request->query('sort', 'sort_order');
        $filter_data['order'] = $request->query('order', 'asc');

        // search 關鍵字查詢
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                OrmHelper::filterOrEqualColumn($q, 'filter_code', $search);

                $q->orWhere(function ($q2) use ($search) {
                    OrmHelper::filterOrEqualColumn($q2, 'filter_business_no', $search);
                });

                $q->orWhere(function ($q2) use ($search) {
                    OrmHelper::filterOrEqualColumn($q2, 'filter_name', $search);
                });

                $q->orWhere(function ($q2) use ($search) {
                    OrmHelper::filterOrEqualColumn($q2, 'filter_short_name', $search);
                });
            });

            unset(
                $filter_data['search'],
                $filter_data['filter_code'],
                $filter_data['filter_business_no'],
                $filter_data['filter_name'],
                $filter_data['filter_short_name']
            );
        }

        // OrmHelper 自動處理 filter_*, equal_* 及排序
        OrmHelper::prepare($query, $filter_data);

        // 分頁結果
        $companies = OrmHelper::getResult($query, $filter_data);
        $companies->withPath(route('lang.ocadmin.hrm.company.list'));

        $data['lang'] = $this->lang;
        $data['companies'] = $companies;
        $data['pagination'] = $companies->links('ocadmin::pagination.default');

        // 建構 URL 參數與排序連結
        $url = $this->buildUrlParams($request);
        $baseUrl = route('lang.ocadmin.hrm.company.list');
        $data['sort'] = $filter_data['sort'];
        $data['order'] = $filter_data['order'];
        $nextOrder = ($data['order'] == 'asc') ? 'desc' : 'asc';

        $data['sort_name'] = $baseUrl . "?sort=name&order={$nextOrder}" . str_replace('?', '&', $url);
        $data['sort_code'] = $baseUrl . "?sort=code&order={$nextOrder}" . str_replace('?', '&', $url);
        $data['sort_sort_order'] = $baseUrl . "?sort=sort_order&order={$nextOrder}" . str_replace('?', '&', $url);

        return view('ocadmin.hrm.company::list', $data)->render();
    }

    /**
     * 新增表單
     */
    public function create(): View
    {
        $data['lang'] = $this->lang;
        $data['breadcrumbs'] = $this->breadcrumbs;
        $data['company'] = new Company();
        $data['parentOptions'] = $this->getParentOptions();

        return view('ocadmin.hrm.company::form', $data);
    }

    /**
     * 儲存新資料
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate($this->validationRules());

        $company = Company::create($validated);

        return response()->json([
            'success' => true,
            'message' => $this->lang->text_success_add,
            'replace_url' => route('lang.ocadmin.hrm.company.edit', $company),
            'form_action' => route('lang.ocadmin.hrm.company.update', $company),
        ]);
    }

    /**
     * 編輯表單
     */
    public function edit(Company $company): View
    {
        $data['lang'] = $this->lang;
        $data['breadcrumbs'] = $this->breadcrumbs;
        $data['company'] = $company;
        $data['parentOptions'] = $this->getParentOptions($company->id);

        return view('ocadmin.hrm.company::form', $data);
    }

    /**
     * 更新資料
     */
    public function update(Request $request, Company $company): JsonResponse
    {
        $validated = $request->validate($this->validationRules($company->id));

        $company->update($validated);

        return response()->json([
            'success' => true,
            'message' => $this->lang->text_success_edit,
        ]);
    }

    /**
     * 刪除資料
     */
    public function destroy(Company $company): JsonResponse
    {
        $company->delete();

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

        Company::whereIn('id', $ids)->delete();

        return response()->json(['success' => true, 'message' => $this->lang->text_success_delete]);
    }

    /**
     * 驗證規則
     */
    protected function validationRules(?int $companyId = null): array
    {
        return [
            'parent_id'   => 'nullable|exists:hrm_companies,id',
            'code'        => 'nullable|string|max:20|unique:hrm_companies,code' . ($companyId ? ",{$companyId}" : ''),
            'name'        => 'required|string|max:200',
            'short_name'  => 'nullable|string|max:100',
            'business_no' => 'nullable|string|max:20',
            'phone'       => 'nullable|string|max:30',
            'address'     => 'nullable|string|max:255',
            'is_active'   => 'required|boolean',
            'sort_order'  => 'required|integer|min:0',
        ];
    }

    /**
     * 取得可選的上層公司列表（排除自己及子孫）
     */
    protected function getParentOptions(?int $excludeId = null): array
    {
        $query = Company::query();

        if ($excludeId) {
            $descendantIds = $this->getDescendantIds($excludeId);
            $descendantIds[] = $excludeId;
            $query->whereNotIn('id', $descendantIds);
        }

        return $query->orderBy('sort_order')->get()->map(function ($c) {
            return (object)[
                'id'   => $c->id,
                'name' => $c->name,
            ];
        })->toArray();
    }

    /**
     * 遞迴取得所有子孫 ID
     */
    protected function getDescendantIds(int $parentId): array
    {
        $ids = [];
        $children = Company::where('parent_id', $parentId)->pluck('id');

        foreach ($children as $childId) {
            $ids[] = $childId;
            $ids = array_merge($ids, $this->getDescendantIds($childId));
        }

        return $ids;
    }
}
