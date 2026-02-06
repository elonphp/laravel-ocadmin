<?php

namespace App\Portals\Ocadmin\Core\Controllers\Acl;

use App\Helpers\Classes\LocaleHelper;
use App\Helpers\Classes\OrmHelper;
use App\Models\Acl\Permission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Portals\Ocadmin\Core\Controllers\OcadminController;
use Spatie\Permission\PermissionRegistrar;

class PermissionController extends OcadminController
{
    protected function setLangFiles(): array
    {
        return ['common', 'acl/permission'];
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
                'href' => route('lang.ocadmin.system.permission.index'),
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

        return view('ocadmin::acl.permission.index', $data);
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
        $query = Permission::with('translations');
        $filter_data = $request->all();

        // 預設排序
        $filter_data['sort'] = $request->get('sort', 'name');
        $filter_data['order'] = $request->get('order', 'asc');

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
        $permissions = OrmHelper::getResult($query, $filter_data);
        $permissions->withPath(route('lang.ocadmin.system.permission.list'));

        $data['lang'] = $this->lang;
        $data['permissions'] = $permissions;
        $data['pagination'] = $permissions->links('ocadmin::pagination.default');

        // 建構 URL 參數與排序連結
        $url = $this->buildUrlParams($request);
        $baseUrl = route('lang.ocadmin.system.permission.list');
        $data['sort'] = $filter_data['sort'];
        $data['order'] = $filter_data['order'];
        $nextOrder = ($data['order'] == 'asc') ? 'desc' : 'asc';

        $data['sort_name'] = $baseUrl . "?sort=name&order={$nextOrder}" . str_replace('?', '&', $url);
        $data['sort_display_name'] = $baseUrl . "?sort=display_name&order={$nextOrder}" . str_replace('?', '&', $url);

        return view('ocadmin::acl.permission.list', $data)->render();
    }

    /**
     * 新增表單
     */
    public function create(): View
    {
        $data['lang'] = $this->lang;
        $data['breadcrumbs'] = $this->breadcrumbs;
        $data['permission'] = new Permission();

        return view('ocadmin::acl.permission.form', $data);
    }

    /**
     * 儲存新資料
     */
    public function store(Request $request): JsonResponse
    {
        $rules = [
            'name' => 'required|string|max:100|unique:acl_permissions,name|regex:/^[a-z][a-z0-9_]*(\.[a-z][a-z0-9_]*)*$/',
            'guard_name' => 'nullable|string|max:50',
        ];

        foreach (LocaleHelper::getSupportedLocales() as $locale) {
            $rules["translations.{$locale}.display_name"] = 'required|string|max:100';
            $rules["translations.{$locale}.note"] = 'nullable|string|max:255';
        }

        $validator = validator($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'error_warning' => $validator->errors()->first(),
                'errors' => $this->formatErrors($validator),
            ]);
        }

        $validated = $validator->validated();
        $validated['guard_name'] = $validated['guard_name'] ?: 'web';

        $permission = Permission::create($validated);
        $permission->saveTranslations($validated['translations']);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        return response()->json([
            'success' => $this->lang->text_success_add,
            'redirect_url' => route('lang.ocadmin.system.permission.edit', $permission),
            'form_action' => route('lang.ocadmin.system.permission.update', $permission),
        ]);
    }

    /**
     * 編輯表單
     */
    public function edit(Permission $permission): View
    {
        $permission->load('translations');

        $data['lang'] = $this->lang;
        $data['breadcrumbs'] = $this->breadcrumbs;
        $data['permission'] = $permission;

        return view('ocadmin::acl.permission.form', $data);
    }

    /**
     * 更新資料
     */
    public function update(Request $request, Permission $permission): JsonResponse
    {
        $rules = [
            'name' => 'required|string|max:100|unique:acl_permissions,name,' . $permission->id . '|regex:/^[a-z][a-z0-9_]*(\.[a-z][a-z0-9_]*)*$/',
            'guard_name' => 'nullable|string|max:50',
        ];

        foreach (LocaleHelper::getSupportedLocales() as $locale) {
            $rules["translations.{$locale}.display_name"] = 'required|string|max:100';
            $rules["translations.{$locale}.note"] = 'nullable|string|max:255';
        }

        $validator = validator($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'error_warning' => $validator->errors()->first(),
                'errors' => $this->formatErrors($validator),
            ]);
        }

        $validated = $validator->validated();
        $validated['guard_name'] = $validated['guard_name'] ?: 'web';

        $permission->update($validated);
        $permission->saveTranslations($validated['translations']);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        return response()->json([
            'success' => $this->lang->text_success_edit,
        ]);
    }

    /**
     * 刪除資料
     */
    public function destroy(Permission $permission): JsonResponse
    {
        if ($permission->roles()->exists()) {
            return response()->json([
                'success' => false,
                'message' => $this->lang->error_has_roles,
            ]);
        }

        $permission->delete();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        return response()->json(['success' => true]);
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

        $hasRoles = Permission::whereIn('id', $ids)->whereHas('roles')->exists();
        if ($hasRoles) {
            return response()->json([
                'success' => false,
                'message' => $this->lang->error_batch_has_roles,
            ]);
        }

        Permission::whereIn('id', $ids)->delete();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        return response()->json(['success' => true]);
    }

    /**
     * 格式化驗證錯誤（將巢狀翻譯欄位轉為扁平 key）
     *
     * 翻譯欄位：translations.zh_Hant.display_name → display_name-zh_Hant
     * 一般欄位：name → name（不變）
     *
     * JS 端 handleFormErrors() 會將 _ 全部轉為 -：
     * display_name-zh_Hant → display-name-zh-Hant → 對應 #error-display-name-zh-Hant
     */
    protected function formatErrors($validator): array
    {
        $errors = [];

        foreach ($validator->errors()->messages() as $field => $messages) {
            if (str_starts_with($field, 'translations.')) {
                // translations.zh_Hant.display_name → ['translations', 'zh_Hant', 'display_name']
                $parts = explode('.', $field);
                $locale = $parts[1];  // zh_Hant
                $column = $parts[2];  // display_name
                $key = $column . '-' . $locale;
            } else {
                $key = $field;
            }

            $errors[$key] = $messages[0];
        }

        return $errors;
    }
}
