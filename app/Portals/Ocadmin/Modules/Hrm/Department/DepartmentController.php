<?php

namespace App\Portals\Ocadmin\Modules\Hrm\Department;

use App\Helpers\Classes\OrmHelper;
use App\Models\Hrm\Company;
use App\Models\Hrm\Department;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Portals\Ocadmin\Core\Controllers\OcadminController;

class DepartmentController extends OcadminController
{
    protected function setLangFiles(): array
    {
        return ['common', 'hrm/department'];
    }

    protected function setBreadcrumbs(): void
    {
        $this->breadcrumbs = [
            (object)[
                'text' => $this->lang->text_home,
                'href' => route('lang.ocadmin.dashboard'),
            ],
            (object)[
                'text' => $this->lang->text_hrm,
                'href' => 'javascript:void(0)',
            ],
            (object)[
                'text' => $this->lang->heading_title,
                'href' => route('lang.ocadmin.hrm.department.index'),
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
        $data['companies'] = Company::orderBy('sort_order')->get();

        return view('ocadmin.hrm.department::index', $data);
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
        $query = Department::with(['company', 'parent']);
        $filter_data = $this->filterData($request, ['equal_company_id', 'equal_is_active']);

        // 預設排序
        $filter_data['sort'] = $request->query('sort', 'sort_order');
        $filter_data['order'] = $request->query('order', 'asc');

        // search 關鍵字查詢
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                OrmHelper::filterOrEqualColumn($q, 'filter_name', $search);
                $q->orWhere(function ($q2) use ($search) {
                    OrmHelper::filterOrEqualColumn($q2, 'filter_code', $search);
                });
            });

            unset(
                $filter_data['search'],
                $filter_data['filter_name'],
                $filter_data['filter_code']
            );
        }

        // OrmHelper 自動處理 filter_*, equal_* 及排序
        OrmHelper::prepare($query, $filter_data);

        // 分頁結果
        $departments = OrmHelper::getResult($query, $filter_data);
        $departments->withPath(route('lang.ocadmin.hrm.department.list'));

        $data['lang'] = $this->lang;
        $data['departments'] = $departments;
        $data['pagination'] = $departments->links('ocadmin::pagination.default');

        // 建構 URL 參數與排序連結
        $url = $this->buildUrlParams($request);
        $baseUrl = route('lang.ocadmin.hrm.department.list');
        $data['sort'] = $filter_data['sort'];
        $data['order'] = $filter_data['order'];
        $nextOrder = ($data['order'] == 'asc') ? 'desc' : 'asc';

        $data['sort_name'] = $baseUrl . "?sort=name&order={$nextOrder}" . str_replace('?', '&', $url);
        $data['sort_code'] = $baseUrl . "?sort=code&order={$nextOrder}" . str_replace('?', '&', $url);
        $data['sort_sort_order'] = $baseUrl . "?sort=sort_order&order={$nextOrder}" . str_replace('?', '&', $url);

        return view('ocadmin.hrm.department::list', $data)->render();
    }

    /**
     * 新增表單
     */
    public function create(): View
    {
        $data['lang'] = $this->lang;
        $data['breadcrumbs'] = $this->breadcrumbs;
        $data['department'] = new Department();
        $data['companies'] = Company::orderBy('sort_order')->get();
        $data['parentOptions'] = $this->getParentOptions();

        return view('ocadmin.hrm.department::form', $data);
    }

    /**
     * 儲存新資料
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate($this->validationRules());

        $department = Department::create($validated);

        return response()->json([
            'success' => true,
            'message' => $this->lang->text_success_add,
            'replace_url' => route('lang.ocadmin.hrm.department.edit', $department),
            'form_action' => route('lang.ocadmin.hrm.department.update', $department),
        ]);
    }

    /**
     * 編輯表單
     */
    public function edit(Department $department): View
    {
        $department->load(['company', 'parent']);

        $data['lang'] = $this->lang;
        $data['breadcrumbs'] = $this->breadcrumbs;
        $data['department'] = $department;
        $data['companies'] = Company::orderBy('sort_order')->get();
        $data['parentOptions'] = $this->getParentOptions($department->id, $department->company_id);

        return view('ocadmin.hrm.department::form', $data);
    }

    /**
     * 更新資料
     */
    public function update(Request $request, Department $department): JsonResponse
    {
        $validated = $request->validate($this->validationRules($department->id));

        $department->update($validated);

        return response()->json([
            'success' => true,
            'message' => $this->lang->text_success_edit,
        ]);
    }

    /**
     * 刪除資料
     */
    public function destroy(Department $department): JsonResponse
    {
        $department->delete();

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

        Department::whereIn('id', $ids)->delete();

        return response()->json(['success' => true, 'message' => $this->lang->text_success_delete]);
    }

    /**
     * AJAX 依公司取得部門（供表單上層部門連動）
     */
    public function byCompany(Request $request): JsonResponse
    {
        $companyId = $request->query('company_id');
        $excludeId = $request->query('exclude_id');

        $query = Department::where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('sort_order');

        if ($excludeId) {
            $descendantIds = $this->getDescendantIds((int) $excludeId);
            $descendantIds[] = (int) $excludeId;
            $query->whereNotIn('id', $descendantIds);
        }

        $departments = $query->get(['id', 'name', 'parent_id']);

        return response()->json($departments);
    }

    /**
     * 驗證規則
     */
    protected function validationRules(?int $departmentId = null): array
    {
        return [
            'company_id'  => 'required|exists:hrm_companies,id',
            'parent_id'   => 'nullable|exists:hrm_departments,id',
            'name'        => 'required|string|max:100',
            'code'        => 'nullable|string|max:20',
            'is_active'   => 'required|boolean',
            'sort_order'  => 'required|integer|min:0',
        ];
    }

    /**
     * 取得可選的上層部門列表（排除自己及子孫）
     */
    protected function getParentOptions(?int $excludeId = null, ?int $companyId = null): array
    {
        $query = Department::query();

        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        if ($excludeId) {
            $descendantIds = $this->getDescendantIds($excludeId);
            $descendantIds[] = $excludeId;
            $query->whereNotIn('id', $descendantIds);
        }

        return $query->orderBy('sort_order')->get()->map(function ($d) {
            return (object)[
                'id'         => $d->id,
                'name'       => $d->name,
                'company_id' => $d->company_id,
            ];
        })->toArray();
    }

    /**
     * 遞迴取得所有子孫 ID
     */
    protected function getDescendantIds(int $parentId): array
    {
        $ids = [];
        $children = Department::where('parent_id', $parentId)->pluck('id');

        foreach ($children as $childId) {
            $ids[] = $childId;
            $ids = array_merge($ids, $this->getDescendantIds($childId));
        }

        return $ids;
    }
}
