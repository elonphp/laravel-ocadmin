<?php

namespace App\Portals\Ocadmin\Core\Controllers\Acl;

use App\Helpers\Classes\LocaleHelper;
use App\Models\Acl\Permission;
use Illuminate\Http\Request;
use App\Portals\Ocadmin\Core\Controllers\Controller;
use Spatie\Permission\PermissionRegistrar;

class PermissionController extends Controller
{
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
                'text' => '權限管理',
                'href' => route('lang.ocadmin.system.permission.index'),
            ],
        ];
    }

    public function index(Request $request)
    {
        $query = Permission::with('translations');

        if ($request->filled('filter_name')) {
            $query->where('name', 'like', '%' . $request->filter_name . '%');
        }

        if ($request->filled('filter_display_name')) {
            $query->whereTranslationLike('display_name', '%' . $request->filter_display_name . '%');
        }

        $sortBy = $request->get('sort', 'name');
        $order = $request->get('order', 'asc');
        $query->orderBy($sortBy, $order);

        $permissions = $query->paginate(20)->withQueryString();

        return view('ocadmin::acl.permission.index', [
            'permissions' => $permissions,
            'breadcrumbs' => $this->breadcrumbs,
        ]);
    }

    public function create()
    {
        return view('ocadmin::acl.permission.form', [
            'permission' => new Permission(),
            'breadcrumbs' => $this->breadcrumbs,
        ]);
    }

    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|string|max:100|unique:acl_permissions,name|regex:/^[a-z][a-z0-9_]*(\.[a-z][a-z0-9_]*)*$/',
            'guard_name' => 'nullable|string|max:50',
        ];

        foreach (LocaleHelper::getSupportedLocales() as $locale) {
            $rules["translations.{$locale}.display_name"] = 'required|string|max:100';
            $rules["translations.{$locale}.note"] = 'nullable|string|max:255';
        }

        $validated = $request->validate($rules);

        $validated['guard_name'] = $validated['guard_name'] ?: 'web';

        $permission = Permission::create($validated);
        $permission->saveTranslations($validated['translations']);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        return redirect()
            ->route('lang.ocadmin.system.permission.index')
            ->with('success', '權限新增成功！');
    }

    public function edit(Permission $permission)
    {
        $permission->load('translations');

        return view('ocadmin::acl.permission.form', [
            'permission' => $permission,
            'breadcrumbs' => $this->breadcrumbs,
        ]);
    }

    public function update(Request $request, Permission $permission)
    {
        $rules = [
            'name' => 'required|string|max:100|unique:acl_permissions,name,' . $permission->id . '|regex:/^[a-z][a-z0-9_]*(\.[a-z][a-z0-9_]*)*$/',
            'guard_name' => 'nullable|string|max:50',
        ];

        foreach (LocaleHelper::getSupportedLocales() as $locale) {
            $rules["translations.{$locale}.display_name"] = 'required|string|max:100';
            $rules["translations.{$locale}.note"] = 'nullable|string|max:255';
        }

        $validated = $request->validate($rules);

        $validated['guard_name'] = $validated['guard_name'] ?: 'web';

        $permission->update($validated);
        $permission->saveTranslations($validated['translations']);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        return redirect()
            ->route('lang.ocadmin.system.permission.index')
            ->with('success', '權限更新成功！');
    }

    public function destroy(Permission $permission)
    {
        if ($permission->roles()->exists()) {
            return response()->json([
                'success' => false,
                'message' => '此權限已指派給角色，請先移除角色指派',
            ]);
        }

        $permission->delete();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        return response()->json(['success' => true]);
    }

    public function batchDelete(Request $request)
    {
        $ids = $request->input('selected', []);

        if (empty($ids)) {
            return response()->json(['success' => false, 'message' => '請選擇要刪除的項目']);
        }

        $hasRoles = Permission::whereIn('id', $ids)->whereHas('roles')->exists();
        if ($hasRoles) {
            return response()->json([
                'success' => false,
                'message' => '部分權限已指派給角色，請先移除角色指派',
            ]);
        }

        Permission::whereIn('id', $ids)->delete();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        return response()->json(['success' => true]);
    }
}
