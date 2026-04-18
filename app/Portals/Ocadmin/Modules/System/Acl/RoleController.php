<?php

namespace App\Portals\Ocadmin\Modules\System\Acl;

use App\Helpers\Classes\LocaleHelper;
use App\Helpers\Classes\OrmHelper;
use App\Models\Acl\Permission;
use App\Models\Acl\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use App\Portals\Ocadmin\Core\Controllers\OcadminController;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\PermissionRegistrar;

class RoleController extends OcadminController
{
    /** 引用 Model 常數 */
    protected const SYSTEM_ROLES = Role::SYSTEM_ROLES;

    protected function setLangFiles(): array
    {
        return ['acl/role'];
    }

    /**
     * 列表頁（初始載入）
     */
    public function index(Request $request): View
    {
        $data['lang'] = $this->lang;
        $data['list'] = $this->getList($request);
        $data['portal_options'] = $this->getPortalOptions();

        $data['list_url'] = route('lang.ocadmin.system.roles.list');
        $data['index_url'] = route('lang.ocadmin.system.roles.index');
        $data['add_url'] = route('lang.ocadmin.system.roles.create');
        $data['batch_delete_url'] = route('lang.ocadmin.system.roles.batch-delete');

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
     * AJAX 搜尋角色（供 Select2 使用）
     */
    public function search(Request $request): JsonResponse
    {
        $search = $request->query('q', '');
        $locale = app()->getLocale();

        $roles = Role::with('translation')
            ->whereNotIn('name', self::SYSTEM_ROLES)
            ->where('is_active', true)
            ->when($search, function ($query) use ($search, $locale) {
                $query->where(function ($q) use ($search, $locale) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhereHas('translations', function ($tq) use ($search, $locale) {
                          $tq->where('locale', $locale)
                            ->where('display_name', 'like', "%{$search}%");
                      });
                });
            })
            ->orderBy('name')
            ->limit(50)
            ->get();

        return response()->json($roles->map(fn ($r) => [
            'id' => $r->id,
            'text' => $r->display_name . ' (' . $r->name . ')',
        ]));
    }

    /**
     * 核心查詢邏輯
     */
    protected function getList(Request $request): string
    {
        $query = Role::with('translations')->whereNotIn('name', self::SYSTEM_ROLES);
        $filter_data = $this->filterData($request, ['equal_is_active']);

        // 預設排序
        $filter_data['sort'] = $request->query('sort', 'name');
        $filter_data['order'] = $request->query('order', 'asc');

        // Portal 前綴過濾
        if ($request->filled('filter_portal') && $request->filter_portal !== '*') {
            $query->where('name', 'like', $request->filter_portal . '.%');
        }
        unset($filter_data['filter_portal']);

        // search 關鍵字查詢（優先處理，涵蓋的欄位從 filter_data 移除避免 prepare 重複處理）
        if ($request->filled('search')) {
            $search = $request->search;
            $locale = app()->getLocale();

            $query->where(function ($q) use ($search, $locale) {
                OrmHelper::filterOrEqualColumn($q, 'filter_name', $search);

                $q->orWhereHas('translations', function ($tq) use ($search, $locale) {
                    $tq->where('locale', $locale);
                    OrmHelper::filterOrEqualColumn($tq, 'filter_display_name', $search);
                });
            });

            unset($filter_data['search'], $filter_data['filter_name']);
        }

        // OrmHelper 自動處理 filter_*, equal_* 及排序
        OrmHelper::prepare($query, $filter_data);

        // 分頁結果
        $roles = OrmHelper::getResult($query, $filter_data);

        $data['lang'] = $this->lang;
        $data['roles'] = $roles;
        $data['sort'] = $filter_data['sort'];
        $data['order'] = $filter_data['order'];

        // 建構 URL 參數（篩選條件，不含 sort/order）
        $filterUrl = $this->buildUrlParams($request);
        $baseUrl = route('lang.ocadmin.system.roles.list');
        $data['urlParams'] = $this->buildEditUrlParams($request);

        // 分頁連結：帶上篩選 + sort/order
        $sortParams = 'sort=' . urlencode($data['sort']) . '&order=' . urlencode($data['order']);
        $paginationUrl = $filterUrl
            ? $filterUrl . '&' . $sortParams
            : '?' . $sortParams;
        $roles->withPath($baseUrl . $paginationUrl);
        $data['pagination'] = $roles->links('ocadmin::pagination.default');

        // 排序連結
        $nextOrder = ($data['order'] == 'asc') ? 'desc' : 'asc';
        $filterSuffix = $filterUrl ? '&' . substr($filterUrl, 1) : '';
        $data['sort_name'] = $baseUrl . "?sort=name&order={$nextOrder}" . $filterSuffix;

        return view('ocadmin::acl.role.list', $data)->render();
    }

    /**
     * 新增表單
     */
    public function create(): View
    {
        $data['lang'] = $this->lang;
        $data['role'] = new Role();
        $data['rolePermissions'] = [];
        $data['isSuperAdmin'] = false;
        $data['permissionGroups'] = collect();

        $data['save_url'] = route('lang.ocadmin.system.roles.store');
        $data['back_url'] = route('lang.ocadmin.system.roles.index');

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

            'is_active' => 'required|boolean',
            'permissions' => 'nullable|array',
            'permissions.*' => 'integer|exists:acl_permissions,id',
        ];

        foreach (LocaleHelper::getSupportedLocales() as $locale) {
            $rules["translations.{$locale}.display_name"] = 'required|string|max:100';
            $rules["translations.{$locale}.note"] = 'nullable|string|max:255';
        }

        $validated = $request->validate($rules);
        $this->validateRolePortalPrefix($validated['name']);

        $validated['guard_name'] = $validated['guard_name'] ?: 'web';


        $role = Role::create($validated);
        $role->saveTranslations($validated['translations']);

        if (!empty($validated['permissions'])) {
            $role->syncPermissions(Permission::whereIn('id', $validated['permissions'])->get());
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        Cache::increment('role_perm_ver');

        return response()->json([
            'success' => true,
            'message' => $this->lang->text_success_add,
            'replace_url' => route('lang.ocadmin.system.roles.edit', $role),
            'form_action' => route('lang.ocadmin.system.roles.update', $role),
        ]);
    }

    /**
     * 編輯表單
     */
    public function edit(Role $role): View
    {
        if (in_array($role->name, self::SYSTEM_ROLES)) {
            abort(404);
        }

        $role->load('translations', 'permissions');

        $data['lang'] = $this->lang;
        $data['role'] = $role;
        $data['isSuperAdmin'] = $role->name === 'super_admin';
        $data['rolePermissions'] = $role->permissions->pluck('id')->toArray();

        $portalPrefix = str_contains($role->name, '.') ? explode('.', $role->name)[0] : null;
        $this->loadPermissionGroups($data, $portalPrefix);

        $data['save_url'] = route('lang.ocadmin.system.roles.update', $role);
        $data['back_url'] = route('lang.ocadmin.system.roles.index');

        return view('ocadmin::acl.role.form', $data);
    }

    /**
     * 更新資料
     */
    public function update(Request $request, Role $role): JsonResponse
    {
        if (in_array($role->name, self::SYSTEM_ROLES)) {
            return response()->json(['success' => false, 'message' => '不允許修改系統保留角色']);
        }

        $rules = [
            'name' => 'required|string|max:100|unique:acl_roles,name,' . $role->id . '|regex:/^[a-z][a-z0-9_]*(\.[a-z][a-z0-9_]*)*$/',
            'guard_name' => 'nullable|string|max:50',

            'is_active' => 'required|boolean',
            'permissions' => 'nullable|array',
            'permissions.*' => 'integer|exists:acl_permissions,id',
        ];

        foreach (LocaleHelper::getSupportedLocales() as $locale) {
            $rules["translations.{$locale}.display_name"] = 'required|string|max:100';
            $rules["translations.{$locale}.note"] = 'nullable|string|max:255';
        }

        $validated = $request->validate($rules);
        $this->validateRolePortalPrefix($validated['name']);

        $validated['guard_name'] = $validated['guard_name'] ?: 'web';


        $role->update($validated);
        $role->saveTranslations($validated['translations']);

        $permissionIds = $validated['permissions'] ?? [];
        $portalPrefix = str_contains($role->name, '.') ? explode('.', $role->name)[0] : null;

        // 防呆：僅允許同 portal prefix 的權限
        $permissions = Permission::whereIn('id', $permissionIds)
            ->when($portalPrefix, fn ($q) => $q->where('name', 'like', $portalPrefix . '.%'))
            ->get();
        $role->syncPermissions($permissions);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        Cache::increment('role_perm_ver');

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
        if (in_array($role->name, self::SYSTEM_ROLES)) {
            return response()->json(['success' => false, 'message' => '不允許刪除系統保留角色']);
        }

        if ($role->users()->exists()) {
            return response()->json([
                'success' => false,
                'message' => $this->lang->error_has_users,
            ]);
        }

        $role->delete();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        Cache::increment('role_perm_ver');

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

        // 排除系統保留角色
        $ids = Role::whereIn('id', $ids)->whereNotIn('name', self::SYSTEM_ROLES)->pluck('id')->toArray();

        $hasUsers = Role::whereIn('id', $ids)->whereHas('users')->exists();
        if ($hasUsers) {
            return response()->json([
                'success' => false,
                'message' => $this->lang->error_batch_has_users,
            ]);
        }

        Role::whereIn('id', $ids)->delete();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        Cache::increment('role_perm_ver');

        return response()->json(['success' => true, 'message' => $this->lang->text_success_delete]);
    }

    /**
     * 驗證角色名稱前綴：除 super_admin 外，一律要求第一段前綴為 config/portals.php 中的 role_prefix（排除 global）。
     */
    protected function validateRolePortalPrefix(string $name): void
    {
        if ($name === 'super_admin' || in_array($name, self::SYSTEM_ROLES)) {
            return;
        }

        $validPrefixes = array_values(array_filter(array_column(
            array_diff_key(config('portals'), ['global' => null]),
            'role_prefix'
        )));

        $prefix = str_contains($name, '.') ? explode('.', $name, 2)[0] : null;

        if (!$prefix || !in_array($prefix, $validPrefixes)) {
            throw ValidationException::withMessages([
                'name' => '角色名稱必須以 Portal role_prefix 開頭（' . implode(', ', $validPrefixes) . '），例如 admin.role_name',
            ]);
        }
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
    protected function loadPermissionGroups(array &$data, ?string $portalPrefix = null): void
    {
        $permissions = Permission::with('translation')
            ->when($portalPrefix, fn ($q) => $q->where('name', 'like', $portalPrefix . '.%'))
            ->orderBy('name')
            ->get();

        $data['permissionGroups'] = $permissions->groupBy(function ($p) {
            $parts = explode('.', $p->name);
            return count($parts) >= 2 ? $parts[0] . '.' . $parts[1] : $parts[0];
        });
    }

    /**
     * 取得 Portal 下拉選項（從 config/portals.php 讀取有 role_prefix 的項目）
     */
    protected function getPortalOptions(): array
    {
        $options = [];
        foreach (config('portals') as $key => $portal) {
            if (!empty($portal['role_prefix'])) {
                $options[$portal['role_prefix']] = $portal['role_prefix'];
            }
        }
        return $options;
    }

}
