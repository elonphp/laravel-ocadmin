<?php

namespace App\Portals\Ocadmin\Core\Controllers\Acl;

use App\Helpers\Classes\OrmHelper;
use App\Models\Acl\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Portals\Ocadmin\Core\Controllers\OcadminController;
use Spatie\Permission\PermissionRegistrar;

class UserController extends OcadminController
{
    protected function setLangFiles(): array
    {
        return ['common', 'ocadmin/acl/user'];
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
                'href' => route('lang.ocadmin.system.user.index'),
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

        return view('ocadmin::acl.user.index', $data);
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
        $query = User::with('roles.translation');
        $filter_data = $request->all();

        // 預設排序
        $filter_data['sort'] = $request->get('sort', 'id');
        $filter_data['order'] = $request->get('order', 'asc');

        // search 關鍵字查詢
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                OrmHelper::filterOrEqualColumn($q, 'filter_username', $search);
                $q->orWhere(function ($q2) use ($search) {
                    OrmHelper::filterOrEqualColumn($q2, 'filter_email', $search);
                });
                $q->orWhere(function ($q2) use ($search) {
                    OrmHelper::filterOrEqualColumn($q2, 'filter_first_name', $search);
                });
                $q->orWhere(function ($q2) use ($search) {
                    OrmHelper::filterOrEqualColumn($q2, 'filter_last_name', $search);
                });
            });

            unset($filter_data['search'], $filter_data['filter_username'], $filter_data['filter_email'], $filter_data['filter_first_name'], $filter_data['filter_last_name']);
        }

        // OrmHelper 自動處理 filter_*, equal_* 及排序
        OrmHelper::prepare($query, $filter_data);

        // 分頁結果
        $users = OrmHelper::getResult($query, $filter_data);
        $users->withPath(route('lang.ocadmin.system.user.list'));

        $data['lang'] = $this->lang;
        $data['users'] = $users;
        $data['pagination'] = $users->links('ocadmin::pagination.default');

        // 建構 URL 參數與排序連結
        $url = $this->buildUrlParams($request);
        $baseUrl = route('lang.ocadmin.system.user.list');
        $data['sort'] = $filter_data['sort'];
        $data['order'] = $filter_data['order'];
        $nextOrder = ($data['order'] == 'asc') ? 'desc' : 'asc';

        $data['sort_username'] = $baseUrl . "?sort=username&order={$nextOrder}" . str_replace('?', '&', $url);
        $data['sort_email'] = $baseUrl . "?sort=email&order={$nextOrder}" . str_replace('?', '&', $url);
        $data['sort_created_at'] = $baseUrl . "?sort=created_at&order={$nextOrder}" . str_replace('?', '&', $url);

        return view('ocadmin::acl.user.list', $data)->render();
    }

    /**
     * 新增表單
     */
    public function create(): View
    {
        $data['lang'] = $this->lang;
        $data['breadcrumbs'] = $this->breadcrumbs;
        $data['user'] = new User();
        $data['userRoles'] = [];
        $data['roles'] = Role::with('translation')->orderBy('sort_order')->get();

        return view('ocadmin::acl.user.form', $data);
    }

    /**
     * 儲存新資料
     */
    public function store(Request $request): JsonResponse
    {
        $rules = [
            'username' => 'required|string|max:100|unique:users,username',
            'email' => 'required|email|max:255|unique:users,email',
            'first_name' => 'nullable|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'password' => 'required|string|min:6|confirmed',
            'roles' => 'nullable|array',
            'roles.*' => 'integer|exists:acl_roles,id',
        ];

        $validator = validator($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'error_warning' => $validator->errors()->first(),
                'errors' => $validator->errors()->messages(),
            ]);
        }

        $validated = $validator->validated();

        $user = User::create($validated);

        if (!empty($validated['roles'])) {
            $role_names = Role::whereIn('id', $validated['roles'])->pluck('name')->toArray();
            $user->syncRoles($role_names);
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        return response()->json([
            'success' => $this->lang->text_success_add,
            'redirect_url' => route('lang.ocadmin.system.user.edit', $user),
            'form_action' => route('lang.ocadmin.system.user.update', $user),
        ]);
    }

    /**
     * 編輯表單
     */
    public function edit(User $user): View
    {
        $user->load('roles');

        $data['lang'] = $this->lang;
        $data['breadcrumbs'] = $this->breadcrumbs;
        $data['user'] = $user;
        $data['userRoles'] = $user->roles->pluck('id')->toArray();
        $data['roles'] = Role::with('translation')->orderBy('sort_order')->get();

        return view('ocadmin::acl.user.form', $data);
    }

    /**
     * 更新資料
     */
    public function update(Request $request, User $user): JsonResponse
    {
        $rules = [
            'username' => 'required|string|max:100|unique:users,username,' . $user->id,
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'first_name' => 'nullable|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'password' => 'nullable|string|min:6|confirmed',
            'roles' => 'nullable|array',
            'roles.*' => 'integer|exists:acl_roles,id',
        ];

        $validator = validator($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'error_warning' => $validator->errors()->first(),
                'errors' => $validator->errors()->messages(),
            ]);
        }

        $validated = $validator->validated();

        // 密碼留空不更新
        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $user->update($validated);

        $roleIds = $validated['roles'] ?? [];
        $role_names = Role::whereIn('id', $roleIds)->pluck('name')->toArray();
        $user->syncRoles($role_names);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        return response()->json([
            'success' => $this->lang->text_success_edit,
        ]);
    }

    /**
     * 刪除資料
     */
    public function destroy(User $user): JsonResponse
    {
        if ($user->id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => $this->lang->error_delete_self,
            ]);
        }

        $user->delete();

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

        // 排除自己
        $ids = array_diff($ids, [auth()->id()]);

        if (empty($ids)) {
            return response()->json([
                'success' => false,
                'message' => $this->lang->error_batch_has_self,
            ]);
        }

        User::whereIn('id', $ids)->delete();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        return response()->json(['success' => true]);
    }
}
