<?php

namespace App\Portals\Ocadmin\Core\Controllers\Acl;

use App\Helpers\Classes\LocaleHelper;
use App\Helpers\Classes\OrmHelper;
use App\Models\Acl\Permission;
use App\Models\Acl\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Portals\Ocadmin\Core\Controllers\OcadminController;
use Spatie\Permission\PermissionRegistrar;

class RoleController extends OcadminController
{
    protected function setLangFiles(): array
    {
        return ['common', 'acl/role'];
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
                'text' => $this->lang->heading_title,
                'href' => route('lang.ocadmin.system.role.index'),
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

        return view('ocadmin::acl.role.index', $data);
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
        $query = Role::with('translations');
        $filter_data = $this->filterData($request, ['equal_is_active']);

        // 預設排序
        $filter_data['sort'] = $request->query('sort', 'sort_order');
        $filter_data['order'] = $request->query('order', 'asc');

        // search 關鍵字查詢（優先處理，涵蓋的欄位從 filter_data 移除避免 prepare 重複處理）
        if ($request->filled('search')) {
            $search = $request->search;
            $locale = app()->getLocale();

            $query->where(function ($q) use ($search, $locale) {
                OrmHelper::filterOrEqualColumn($q, 'filter_name', $search);

                $q->orWhereHas('translations', function ($tq) use ($search, $locale) {
                    $tq->where('locale', $locale);
                    $tq->where(function ($sq) use ($search) {
                        OrmHelper::filterOrEqualColumn($sq, 'filter_display_name', $search);
                        $sq->orWhere(function ($sq2) use ($search) {
                            OrmHelper::filterOrEqualColumn($sq2, 'filter_note', $search);
                        });
                    });
                });
            });

            unset($filter_data['search'], $filter_data['filter_name'], $filter_data['filter_display_name'], $filter_data['filter_note']);
        }

        // OrmHelper 自動處理 filter_*, equal_* 及排序
        OrmHelper::prepare($query, $filter_data);

        // 分頁結果
        $roles = OrmHelper::getResult($query, $filter_data);
        $roles->withPath(route('lang.ocadmin.system.role.list'));

        $data['lang'] = $this->lang;
        $data['roles'] = $roles;
        $data['pagination'] = $roles->links('ocadmin::pagination.default');

        // 建構 URL 參數與排序連結
        $url = $this->buildUrlParams($request);
        $baseUrl = route('lang.ocadmin.system.role.list');
        $data['sort'] = $filter_data['sort'];
        $data['order'] = $filter_data['order'];
        $nextOrder = ($data['order'] == 'asc') ? 'desc' : 'asc';

        $data['sort_name'] = $baseUrl . "?sort=name&order={$nextOrder}" . str_replace('?', '&', $url);
        $data['sort_display_name'] = $baseUrl . "?sort=display_name&order={$nextOrder}" . str_replace('?', '&', $url);
        $data['sort_sort_order'] = $baseUrl . "?sort=sort_order&order={$nextOrder}" . str_replace('?', '&', $url);

        return view('ocadmin::acl.role.list', $data)->render();
    }

    /**
     * 新增表單
     */
    public function create(): View
    {
        $data['lang'] = $this->lang;
        $data['breadcrumbs'] = $this->breadcrumbs;
        $data['role'] = new Role();
        $data['rolePermissions'] = [];

        $this->loadPermissionGroups($data);

        return view('ocadmin::acl.role.form', $data);
    }

    /**
     * 儲存新資料
     */
    public function store(Request $request): JsonResponse
    {
        $rules = [
            'name' => 'required|string|max:100|unique:acl_roles,name|regex:/^[a-z][a-z0-9_]*(\.[a-z][a-z0-9_]*)*$/',
            'guard_name' => 'nullable|string|max:50',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'required|boolean',
            'permissions' => 'nullable|array',
            'permissions.*' => 'integer|exists:acl_permissions,id',
        ];

        foreach (LocaleHelper::getSupportedLocales() as $locale) {
            $rules["translations.{$locale}.display_name"] = 'required|string|max:100';
            $rules["translations.{$locale}.note"] = 'nullable|string|max:255';
        }

        $validated = $request->validate($rules);
        $validated['guard_name'] = $validated['guard_name'] ?: 'web';
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        $role = Role::create($validated);
        $role->saveTranslations($validated['translations']);

        if (!empty($validated['permissions'])) {
            $role->syncPermissions(Permission::whereIn('id', $validated['permissions'])->get());
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        return response()->json([
            'success' => true,
            'message' => $this->lang->text_success_add,
            'replace_url' => route('lang.ocadmin.system.role.edit', $role),
            'form_action' => route('lang.ocadmin.system.role.update', $role),
        ]);
    }

    /**
     * 編輯表單
     */
    public function edit(Role $role): View
    {
        $role->load('translations', 'permissions');

        $data['lang'] = $this->lang;
        $data['breadcrumbs'] = $this->breadcrumbs;
        $data['role'] = $role;
        $data['rolePermissions'] = $role->permissions->pluck('id')->toArray();

        $this->loadPermissionGroups($data);

        return view('ocadmin::acl.role.form', $data);
    }

    /**
     * 更新資料
     */
    public function update(Request $request, Role $role): JsonResponse
    {
        $rules = [
            'name' => 'required|string|max:100|unique:acl_roles,name,' . $role->id . '|regex:/^[a-z][a-z0-9_]*(\.[a-z][a-z0-9_]*)*$/',
            'guard_name' => 'nullable|string|max:50',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'required|boolean',
            'permissions' => 'nullable|array',
            'permissions.*' => 'integer|exists:acl_permissions,id',
        ];

        foreach (LocaleHelper::getSupportedLocales() as $locale) {
            $rules["translations.{$locale}.display_name"] = 'required|string|max:100';
            $rules["translations.{$locale}.note"] = 'nullable|string|max:255';
        }

        $validated = $request->validate($rules);
        $validated['guard_name'] = $validated['guard_name'] ?: 'web';
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        $role->update($validated);
        $role->saveTranslations($validated['translations']);

        $permissionIds = $validated['permissions'] ?? [];
        $role->syncPermissions(Permission::whereIn('id', $permissionIds)->get());

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        return response()->json([
            'success' => true,
            'message' => $this->lang->text_success_edit,
        ]);
    }

    /**
     * 刪除資料
     */
    public function destroy(Role $role): JsonResponse
    {
        if ($role->users()->exists()) {
            return response()->json([
                'success' => false,
                'message' => $this->lang->error_has_users,
            ]);
        }

        $role->delete();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

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

        $hasUsers = Role::whereIn('id', $ids)->whereHas('users')->exists();
        if ($hasUsers) {
            return response()->json([
                'success' => false,
                'message' => $this->lang->error_batch_has_users,
            ]);
        }

        Role::whereIn('id', $ids)->delete();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        return response()->json(['success' => true, 'message' => $this->lang->text_success_delete]);
    }

    /**
     * 載入權限分組資料
     *
     * 取前兩段作為群組名稱：
     * - ess.leave.list      → 群組 ess.leave
     * - ess.leave.type.list → 群組 ess.leave
     * - employee.list       → 群組 employee
     * - super_admin         → 群組 super_admin
     */
    protected function loadPermissionGroups(array &$data): void
    {
        $permissions = Permission::with('translation')->orderBy('name')->get();

        $data['permissionGroups'] = $permissions->groupBy(function ($p) {
            $parts = explode('.', $p->name);
            return count($parts) >= 2 ? $parts[0] . '.' . $parts[1] : $parts[0];
        });
    }

}
