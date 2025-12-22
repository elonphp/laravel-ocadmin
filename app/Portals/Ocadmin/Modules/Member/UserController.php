<?php

namespace App\Portals\Ocadmin\Modules\Member;

use App\Models\Identity\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use App\Helpers\Classes\OrmHelper;
use App\Portals\Ocadmin\Core\Controllers\Controller;

class UserController extends Controller
{
    public function __construct(
        private UserService $userService
    ) {
        parent::__construct();
    }

    protected function setBreadcrumbs(): void
    {
        $this->breadcrumbs = [
            (object)[
                'text' => '首頁',
                'href' => route('lang.ocadmin.dashboard'),
            ],
            (object)[
                'text' => '會員管理',
                'href' => 'javascript:void(0)',
            ],
            (object)[
                'text' => '會員',
                'href' => route('lang.ocadmin.member.user.index'),
            ],
        ];
    }

    /**
     * 列表頁面 - 完整頁面渲染
     */
    public function index(Request $request): View
    {
        $data['list'] = $this->getList($request);
        $data['breadcrumbs'] = $this->breadcrumbs;

        return view('ocadmin.member::index', $data);
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
        $query = User::query()->with('roles');
        $filter_data = $request->all();

        // 如果當前用戶不是 sys_admin，則排除 sys_admin 用戶
        if (!auth()->user()->hasRole('ocadmin.sys_admin')) {
            $query->whereDoesntHave('roles', fn ($q) => $q->where('name', 'ocadmin.sys_admin'));
        }

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
                $q->orWhere(function ($subQ) use ($search) {
                    OrmHelper::filterOrEqualColumn($subQ, 'filter_mobile', $search);
                });
            });
        }

        // 預設排序
        $filter_data['sort'] = $request->get('sort', 'id');
        $filter_data['order'] = $request->get('order', 'desc');

        // 使用 OrmHelper 獲取結果
        $users = OrmHelper::getResult($query, $filter_data);

        // 設置分頁器路徑
        $users->withPath(route('lang.ocadmin.member.user.list'));

        // 建構 URL 參數
        $url = $this->buildUrlParams($request);

        // 準備資料
        $data['users'] = $users;
        $data['action'] = route('lang.ocadmin.member.user.list') . $url;
        $data['url_params'] = $url;

        return view('ocadmin.member::list', $data)->render();
    }

    /**
     * 新增頁面
     */
    public function create()
    {
        return view('ocadmin.member::form', [
            'user' => new User(),
            'breadcrumbs' => $this->breadcrumbs,
        ]);
    }

    /**
     * 儲存新增 (AJAX)
     */
    public function store(Request $request)
    {
        $validator = validator($request->all(), [
            'username'     => 'required|string|max:50|unique:users,username',
            'email'        => 'nullable|email|max:255|unique:users,email',
            'mobile'       => 'nullable|string|max:50|unique:users,mobile',
            'password'     => 'required|string|min:6|confirmed',
            'name'         => 'nullable|string|max:50',
            'display_name' => 'nullable|string|max:50',
            'is_active'    => 'boolean',
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
        $user = DB::transaction(fn () => $this->userService->create($validated));

        return response()->json([
            'success' => '會員新增成功！',
            'redirect_url' => route('lang.ocadmin.member.user.edit', $user->id),
            'form_action' => route('lang.ocadmin.member.user.update', $user->id),
        ]);
    }

    /**
     * 編輯頁面
     */
    public function edit(User $user)
    {
        // 保護 sys_admin：非 sys_admin 不能編輯 sys_admin
        if ($user->hasRole('ocadmin.sys_admin') && !auth()->user()->hasRole('ocadmin.sys_admin')) {
            abort(403);
        }

        return view('ocadmin.member::form', [
            'user' => $user,
            'breadcrumbs' => $this->breadcrumbs,
        ]);
    }

    /**
     * 儲存編輯 (AJAX)
     */
    public function update(Request $request, User $user)
    {
        // 保護 sys_admin：非 sys_admin 不能編輯 sys_admin
        if ($user->hasRole('ocadmin.sys_admin') && !auth()->user()->hasRole('ocadmin.sys_admin')) {
            abort(403);
        }

        $validator = validator($request->all(), [
            'username'     => 'required|string|max:50|unique:users,username,' . $user->id,
            'email'        => 'nullable|email|max:255|unique:users,email,' . $user->id,
            'mobile'       => 'nullable|string|max:50|unique:users,mobile,' . $user->id,
            'password'     => 'nullable|string|min:6|confirmed',
            'name'         => 'nullable|string|max:50',
            'display_name' => 'nullable|string|max:50',
            'is_active'    => 'boolean',
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
        DB::transaction(fn () => $this->userService->update($user, $validated));

        return response()->json([
            'success' => '會員更新成功！',
        ]);
    }

    /**
     * 刪除
     */
    public function destroy(User $user)
    {
        // 保護 sys_admin：非 sys_admin 不能刪除 sys_admin
        if ($user->hasRole('ocadmin.sys_admin') && !auth()->user()->hasRole('ocadmin.sys_admin')) {
            return response()->json(['success' => false, 'message' => '無法刪除系統管理員']);
        }

        DB::transaction(fn () => $this->userService->delete($user));

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

        // 如果當前用戶不是 sys_admin，過濾掉 sys_admin 用戶
        if (!auth()->user()->hasRole('ocadmin.sys_admin')) {
            $sysAdminIds = User::whereHas('roles', fn ($q) => $q->where('name', 'ocadmin.sys_admin'))
                ->whereIn('id', $ids)
                ->pluck('id')
                ->toArray();
            $ids = array_diff($ids, $sysAdminIds);

            if (empty($ids)) {
                return response()->json(['success' => false, 'message' => '無法刪除系統管理員']);
            }
        }

        DB::transaction(fn () => $this->userService->batchDelete($ids));

        return response()->json(['success' => true]);
    }

}
