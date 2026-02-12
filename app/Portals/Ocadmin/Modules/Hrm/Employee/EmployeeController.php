<?php

namespace App\Portals\Ocadmin\Modules\Hrm\Employee;

use App\Enums\Common\Gender;
use App\Helpers\Classes\OrmHelper;
use App\Models\Hrm\Company;
use App\Models\Hrm\Department;
use App\Models\Hrm\Employee;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use App\Portals\Ocadmin\Core\Controllers\OcadminController;

class EmployeeController extends OcadminController
{
    protected function setLangFiles(): array
    {
        return ['common', 'hrm/employee'];
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
                'href' => route('lang.ocadmin.hrm.employee.index'),
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

        return view('ocadmin.hrm.employee::index', $data);
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
        $query = Employee::with(['user', 'company.translation', 'department']);
        $filter_data = $request->all();

        // 預設排序
        $filter_data['sort'] = $request->get('sort', 'id');
        $filter_data['order'] = $request->get('order', 'asc');

        // search 關鍵字查詢
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                OrmHelper::filterOrEqualColumn($q, 'filter_employee_no', $search);
                $q->orWhere(function ($q2) use ($search) {
                    OrmHelper::filterOrEqualColumn($q2, 'filter_first_name', $search);
                });
                $q->orWhere(function ($q2) use ($search) {
                    OrmHelper::filterOrEqualColumn($q2, 'filter_last_name', $search);
                });
                $q->orWhere(function ($q2) use ($search) {
                    OrmHelper::filterOrEqualColumn($q2, 'filter_email', $search);
                });
            });

            unset(
                $filter_data['search'],
                $filter_data['filter_employee_no'],
                $filter_data['filter_first_name'],
                $filter_data['filter_last_name'],
                $filter_data['filter_email']
            );
        }

        // OrmHelper 自動處理 filter_*, equal_* 及排序
        OrmHelper::prepare($query, $filter_data);

        // 分頁結果
        $employees = OrmHelper::getResult($query, $filter_data);
        $employees->withPath(route('lang.ocadmin.hrm.employee.list'));

        $data['lang'] = $this->lang;
        $data['employees'] = $employees;
        $data['pagination'] = $employees->links('ocadmin::pagination.default');

        // 建構 URL 參數與排序連結
        $url = $this->buildUrlParams($request);
        $baseUrl = route('lang.ocadmin.hrm.employee.list');
        $data['sort'] = $filter_data['sort'];
        $data['order'] = $filter_data['order'];
        $nextOrder = ($data['order'] == 'asc') ? 'desc' : 'asc';

        $data['sort_employee_no'] = $baseUrl . "?sort=employee_no&order={$nextOrder}" . str_replace('?', '&', $url);
        $data['sort_first_name'] = $baseUrl . "?sort=first_name&order={$nextOrder}" . str_replace('?', '&', $url);
        $data['sort_email'] = $baseUrl . "?sort=email&order={$nextOrder}" . str_replace('?', '&', $url);
        $data['sort_hire_date'] = $baseUrl . "?sort=hire_date&order={$nextOrder}" . str_replace('?', '&', $url);

        return view('ocadmin.hrm.employee::list', $data)->render();
    }

    /**
     * 新增表單
     */
    public function create(): View
    {
        $data['lang'] = $this->lang;
        $data['breadcrumbs'] = $this->breadcrumbs;
        $data['employee'] = new Employee();
        $data['companies'] = Company::with('translation')->get();
        $data['departments'] = Department::where('is_active', true)->get();
        $data['genderOptions'] = Gender::cases();

        return view('ocadmin.hrm.employee::form', $data);
    }

    /**
     * 儲存新資料
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'employee_no'     => 'nullable|string|max:20|unique:hrm_employees',
            'first_name'      => 'required|string|max:50',
            'last_name'       => 'nullable|string|max:50',
            'email'           => 'nullable|email|max:100',
            'phone'           => 'nullable|string|max:30',
            'user_id'         => 'nullable|exists:users,id',
            'company_id'      => 'nullable|exists:hrm_companies,id',
            'department_id'   => 'nullable|exists:hrm_departments,id',
            'hire_date'       => 'nullable|date',
            'birth_date'      => 'nullable|date',
            'gender'          => ['nullable', Rule::enum(Gender::class)],
            'job_title'       => 'nullable|string|max:100',
            'address'         => 'nullable|string',
            'note'            => 'nullable|string',
            'is_active'       => 'boolean',
        ]);

        $employee = Employee::create($validated);

        return response()->json([
            'success' => true,
            'message' => $this->lang->text_success_add,
            'replace_url' => route('lang.ocadmin.hrm.employee.edit', $employee),
            'form_action' => route('lang.ocadmin.hrm.employee.update', $employee),
        ]);
    }

    /**
     * 編輯表單
     */
    public function edit(Employee $employee): View
    {
        $employee->load(['user', 'company', 'department']);

        $data['lang'] = $this->lang;
        $data['breadcrumbs'] = $this->breadcrumbs;
        $data['employee'] = $employee;
        $data['companies'] = Company::with('translation')->get();
        $data['departments'] = Department::where('is_active', true)->get();
        $data['genderOptions'] = Gender::cases();

        return view('ocadmin.hrm.employee::form', $data);
    }

    /**
     * 更新資料
     */
    public function update(Request $request, Employee $employee): JsonResponse
    {
        $validated = $request->validate([
            'employee_no'     => 'nullable|string|max:20|unique:hrm_employees,employee_no,' . $employee->id,
            'first_name'      => 'required|string|max:50',
            'last_name'       => 'nullable|string|max:50',
            'email'           => 'nullable|email|max:100',
            'phone'           => 'nullable|string|max:30',
            'user_id'         => 'nullable|exists:users,id',
            'company_id'      => 'nullable|exists:hrm_companies,id',
            'department_id'   => 'nullable|exists:hrm_departments,id',
            'hire_date'       => 'nullable|date',
            'birth_date'      => 'nullable|date',
            'gender'          => ['nullable', Rule::enum(Gender::class)],
            'job_title'       => 'nullable|string|max:100',
            'address'         => 'nullable|string',
            'note'            => 'nullable|string',
            'is_active'       => 'boolean',
        ]);

        $employee->update($validated);

        return response()->json([
            'success' => true,
            'message' => $this->lang->text_success_edit,
        ]);
    }

    /**
     * 刪除資料
     */
    public function destroy(Employee $employee): JsonResponse
    {
        $employee->delete();

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

        Employee::whereIn('id', $ids)->delete();

        return response()->json(['success' => true, 'message' => $this->lang->text_success_delete]);
    }

    /**
     * AJAX 使用者查找
     */
    public function searchUsers(Request $request): JsonResponse
    {
        $keyword = $request->input('q', '');

        $users = User::where('email', 'like', "%{$keyword}%")
            ->orWhere('username', 'like', "%{$keyword}%")
            ->orWhere('name', 'like', "%{$keyword}%")
            ->select('id', 'name', 'email', 'username')
            ->limit(10)
            ->get();

        return response()->json($users);
    }
}
