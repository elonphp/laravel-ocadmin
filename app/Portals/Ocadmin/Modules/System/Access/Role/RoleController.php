<?php

namespace App\Portals\Ocadmin\Modules\System\Access\Role;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use App\Helpers\Classes\OrmHelper;
use App\Portals\Ocadmin\Core\Controllers\Controller;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function __construct(private RoleService $roleService)
    {
        parent::__construct();

        $this->getLang(['common', 'system/access/role']);
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
                'text' => $this->lang->text_access,
                'href' => 'javascript:void(0)',
            ],
            (object)[
                'text' => $this->lang->heading_title,
                'href' => route('lang.ocadmin.system.access.role.index'),
            ],
        ];
    }

    /**
     * 列表頁面 - 完整頁面渲染
     */
    public function index(Request $request): View
    {
        $data['lang'] = $this->lang;
        $data['list'] = $this->getList($request);
        $data['breadcrumbs'] = $this->breadcrumbs;

        return view('ocadmin.system.access.role::index', $data);
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
        $query = Role::query()->withCount('permissions');
        $filter_data = $request->all();

        // Role 沒有 is_active 欄位，設為 * 跳過
        $filter_data['equal_is_active'] = '*';

        OrmHelper::prepare($query, $filter_data);

        // 關鍵字查詢
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                OrmHelper::filterOrEqualColumn($q, 'filter_name', $search);
                $q->orWhere(function ($subQ) use ($search) {
                    OrmHelper::filterOrEqualColumn($subQ, 'filter_title', $search);
                });
            });
        }

        // 預設排序
        $filter_data['sort'] = $request->get('sort', 'name');
        $filter_data['order'] = $request->get('order', 'asc');

        $roles = OrmHelper::getResult($query, $filter_data);
        $roles->withPath(route('lang.ocadmin.system.access.role.list'));

        $url = $this->buildUrlParams($request);

        $data['lang'] = $this->lang;
        $data['roles'] = $roles;
        $data['action'] = route('lang.ocadmin.system.access.role.list') . $url;
        $data['url_params'] = $url;

        return view('ocadmin.system.access.role::list', $data)->render();
    }

    /**
     * 新增頁面
     */
    public function create()
    {
        $permissions = $this->roleService->getPermissionsGrouped();

        return view('ocadmin.system.access.role::form', [
            'lang' => $this->lang,
            'role' => new Role(),
            'permissions' => $permissions,
            'rolePermissions' => collect(),
            'breadcrumbs' => $this->breadcrumbs,
        ]);
    }

    /**
     * 儲存新增 (AJAX)
     */
    public function store(Request $request)
    {
        $validator = validator($request->all(), [
            'name'        => 'required|string|max:100|unique:roles,name',
            'guard_name'  => 'required|string|max:50',
            'title'       => 'nullable|string|max:100',
            'description' => 'nullable|string|max:1000',
            'permissions' => 'nullable|array',
            'permissions.*' => 'integer|exists:permissions,id',
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
        $role = DB::transaction(fn () => $this->roleService->create($validated));

        return response()->json([
            'success' => $this->lang->text_add_success,
            'redirect_url' => route('lang.ocadmin.system.access.role.edit', $role->id),
            'form_action' => route('lang.ocadmin.system.access.role.update', $role->id),
        ]);
    }

    /**
     * 編輯頁面
     */
    public function edit(int $id)
    {
        $role = Role::findOrFail($id);
        $permissions = $this->roleService->getPermissionsGrouped();
        $rolePermissions = $role->permissions->pluck('id');

        return view('ocadmin.system.access.role::form', [
            'lang' => $this->lang,
            'role' => $role,
            'permissions' => $permissions,
            'rolePermissions' => $rolePermissions,
            'breadcrumbs' => $this->breadcrumbs,
        ]);
    }

    /**
     * 儲存編輯 (AJAX)
     */
    public function update(Request $request, int $id)
    {
        $role = Role::findOrFail($id);

        $validator = validator($request->all(), [
            'name'        => 'required|string|max:100|unique:roles,name,' . $id,
            'guard_name'  => 'required|string|max:50',
            'title'       => 'nullable|string|max:100',
            'description' => 'nullable|string|max:1000',
            'permissions' => 'nullable|array',
            'permissions.*' => 'integer|exists:permissions,id',
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

        DB::transaction(fn () => $this->roleService->update($role, $validated));

        return response()->json([
            'success' => $this->lang->text_edit_success,
        ]);
    }

    /**
     * 刪除
     */
    public function destroy(int $id)
    {
        $role = Role::findOrFail($id);

        // 檢查是否有使用者使用此角色
        if ($role->users()->exists()) {
            return response()->json([
                'success' => false,
                'message' => $this->lang->error_has_users,
            ]);
        }

        DB::transaction(fn () => $this->roleService->delete($role));

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

        // 檢查是否有使用者使用這些角色
        $hasUsers = Role::whereIn('id', $ids)->whereHas('users')->exists();
        if ($hasUsers) {
            return response()->json([
                'success' => false,
                'message' => $this->lang->error_has_users,
            ]);
        }

        DB::transaction(fn () => $this->roleService->batchDelete($ids));

        return response()->json(['success' => true]);
    }

    /**
     * 取得所有角色（用於 select2 下拉選單）
     */
    public function all(Request $request)
    {
        $query = Role::query()->orderBy('name');

        $roles = $query->get(['id', 'name', 'title']);

        return response()->json($roles);
    }

}
