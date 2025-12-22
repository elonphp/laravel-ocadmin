<?php

namespace App\Portals\Ocadmin\Modules\System\Access\User;

use App\Models\Identity\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use App\Helpers\Classes\OrmHelper;
use App\Portals\Ocadmin\Core\Controllers\Controller;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function __construct(private UserService $userService)
    {
        parent::__construct();

        $this->getLang(['common', 'system/access/user']);
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
                'href' => route('lang.ocadmin.system.access.user.index'),
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

        return view('ocadmin.system.access.user::index', $data);
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
     * 只顯示有 ocadmin 角色的使用者
     */
    protected function getList(Request $request): string
    {
        // 只查詢有 ocadmin 角色的使用者
        $query = User::query()
            ->with('roles')
            ->whereHas('roles', function ($q) {
                $q->where('name', 'ocadmin');
            });

        $filter_data = $request->all();

        OrmHelper::prepare($query, $filter_data);

        // 關鍵字查詢
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                OrmHelper::filterOrEqualColumn($q, 'filter_username', $search);
                $q->orWhere(function ($subQ) use ($search) {
                    OrmHelper::filterOrEqualColumn($subQ, 'filter_name', $search);
                });
                $q->orWhere(function ($subQ) use ($search) {
                    OrmHelper::filterOrEqualColumn($subQ, 'filter_email', $search);
                });
            });
        }

        // 預設排序
        $filter_data['sort'] = $request->get('sort', 'id');
        $filter_data['order'] = $request->get('order', 'desc');

        $users = OrmHelper::getResult($query, $filter_data);
        $users->withPath(route('lang.ocadmin.system.access.user.list'));

        $url = $this->buildUrlParams($request);

        $data['lang'] = $this->lang;
        $data['users'] = $users;
        $data['action'] = route('lang.ocadmin.system.access.user.list') . $url;
        $data['url_params'] = $url;

        return view('ocadmin.system.access.user::list', $data)->render();
    }

    /**
     * 新增頁面
     */
    public function create()
    {
        // 取得所有角色（排除 ocadmin，因為 ocadmin 會自動加入）
        $roles = Role::where('name', '!=', 'ocadmin')
            ->orderBy('name')
            ->get();

        return view('ocadmin.system.access.user::form', [
            'lang' => $this->lang,
            'ocadminUser' => null,
            'roles' => $roles,
            'userRoles' => collect(),
            'breadcrumbs' => $this->breadcrumbs,
        ]);
    }

    /**
     * 儲存新增 (AJAX)
     * 將使用者加入 ocadmin 角色
     */
    public function store(Request $request)
    {
        $validator = validator($request->all(), [
            'user_id' => 'required|integer|exists:users,id',
            'roles'   => 'nullable|array',
            'roles.*' => 'integer|exists:roles,id',
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

        // 檢查使用者是否已有 ocadmin 角色
        $user = User::findOrFail($validated['user_id']);
        if ($user->hasRole('ocadmin')) {
            return response()->json([
                'error_warning' => $this->lang->error_already_ocadmin,
            ]);
        }

        DB::transaction(fn () => $this->userService->addOcadminUser($user, $validated['roles'] ?? []));

        return response()->json([
            'success' => $this->lang->text_add_success,
            'redirect_url' => route('lang.ocadmin.system.access.user.edit', $user->id),
            'form_action' => route('lang.ocadmin.system.access.user.update', $user->id),
        ]);
    }

    /**
     * 編輯頁面
     */
    public function edit(int $id)
    {
        $user = User::with('roles')->findOrFail($id);

        // 確認使用者有 ocadmin 角色
        if (!$user->hasRole('ocadmin')) {
            abort(404);
        }

        // 取得所有角色（排除 ocadmin）
        $roles = Role::where('name', '!=', 'ocadmin')
            ->orderBy('name')
            ->get();

        // 使用者目前的角色（排除 ocadmin）
        $userRoles = $user->roles->where('name', '!=', 'ocadmin')->pluck('id');

        return view('ocadmin.system.access.user::form', [
            'lang' => $this->lang,
            'ocadminUser' => $user,
            'roles' => $roles,
            'userRoles' => $userRoles,
            'breadcrumbs' => $this->breadcrumbs,
        ]);
    }

    /**
     * 儲存編輯 (AJAX)
     */
    public function update(Request $request, int $id)
    {
        $user = User::findOrFail($id);

        // 確認使用者有 ocadmin 角色
        if (!$user->hasRole('ocadmin')) {
            abort(404);
        }

        $validator = validator($request->all(), [
            'roles'   => 'nullable|array',
            'roles.*' => 'integer|exists:roles,id',
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

        DB::transaction(fn () => $this->userService->updateOcadminRoles($user, $validated['roles'] ?? []));

        return response()->json([
            'success' => $this->lang->text_edit_success,
        ]);
    }

    /**
     * 移除使用者的 ocadmin 角色（從訪問控制移除）
     */
    public function destroy(int $id)
    {
        $user = User::findOrFail($id);

        // 確認使用者有 ocadmin 角色
        if (!$user->hasRole('ocadmin')) {
            return response()->json(['success' => false, 'message' => $this->lang->error_not_ocadmin]);
        }

        DB::transaction(fn () => $this->userService->removeOcadminUser($user));

        return response()->json(['success' => true]);
    }

    /**
     * 批次移除
     */
    public function batchDelete(Request $request)
    {
        $ids = $request->input('selected', []);

        if (empty($ids)) {
            return response()->json(['success' => false, 'message' => $this->lang->error_select_required]);
        }

        DB::transaction(fn () => $this->userService->batchRemoveOcadminUsers($ids));

        return response()->json(['success' => true]);
    }

    /**
     * AJAX 搜尋使用者（用於 Select2 自動完成）
     * 只搜尋沒有 ocadmin 角色的使用者
     */
    public function search(Request $request)
    {
        $search = $request->get('q', '');

        if (strlen($search) < 2) {
            return response()->json([]);
        }

        $users = User::query()
            ->whereDoesntHave('roles', function ($q) {
                $q->where('name', 'ocadmin');
            })
            ->where('is_active', true)
            ->where(function ($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%");
            })
            ->limit(10)
            ->get(['id', 'username', 'email', 'name', 'mobile']);

        return response()->json($users->map(function ($user) {
            $displayParts = [];
            if ($user->email) {
                $displayParts[] = $user->email;
            }
            if ($user->name) {
                $displayParts[] = $user->name;
            }
            if ($user->username && $user->username !== $user->email) {
                $displayParts[] = "({$user->username})";
            }

            return [
                'id' => $user->id,
                'text' => implode(' - ', $displayParts),
                'email' => $user->email,
                'name' => $user->name,
                'username' => $user->username,
            ];
        }));
    }

}
