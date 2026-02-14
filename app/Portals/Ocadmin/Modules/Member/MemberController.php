<?php

namespace App\Portals\Ocadmin\Modules\Member;

use App\Helpers\Classes\OrmHelper;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Portals\Ocadmin\Core\Controllers\OcadminController;

class MemberController extends OcadminController
{
    /**
     * 會員查詢的基礎條件：id > 50
     */
    protected const MEMBER_MIN_ID = 50;

    protected function setLangFiles(): array
    {
        return ['common', 'member'];
    }

    protected function setBreadcrumbs(): void
    {
        $this->breadcrumbs = [
            (object)[
                'text' => $this->lang->text_home,
                'href' => route('lang.ocadmin.dashboard'),
            ],
            (object)[
                'text' => $this->lang->text_member,
                'href' => 'javascript:void(0)',
            ],
            (object)[
                'text' => $this->lang->heading_title,
                'href' => route('lang.ocadmin.member.index'),
            ],
        ];
    }

    /**
     * 列表頁
     */
    public function index(Request $request): View
    {
        $data['lang'] = $this->lang;
        $data['breadcrumbs'] = $this->breadcrumbs;
        $data['list'] = $this->getList($request);

        return view('ocadmin.member::index', $data);
    }

    /**
     * AJAX 列表刷新
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
        $query = User::where('id', '>', self::MEMBER_MIN_ID);
        $filter_data = $request->all();

        $filter_data['sort'] = $request->get('sort', 'id');
        $filter_data['order'] = $request->get('order', 'desc');

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

        OrmHelper::prepare($query, $filter_data);

        $members = OrmHelper::getResult($query, $filter_data);
        $members->withPath(route('lang.ocadmin.member.list'));

        $data['lang'] = $this->lang;
        $data['members'] = $members;
        $data['pagination'] = $members->links('ocadmin::pagination.default');

        $url = $this->buildUrlParams($request);
        $baseUrl = route('lang.ocadmin.member.list');
        $data['sort'] = $filter_data['sort'];
        $data['order'] = $filter_data['order'];
        $nextOrder = ($data['order'] == 'asc') ? 'desc' : 'asc';

        $data['sort_username'] = $baseUrl . "?sort=username&order={$nextOrder}" . str_replace('?', '&', $url);
        $data['sort_email'] = $baseUrl . "?sort=email&order={$nextOrder}" . str_replace('?', '&', $url);
        $data['sort_created_at'] = $baseUrl . "?sort=created_at&order={$nextOrder}" . str_replace('?', '&', $url);

        return view('ocadmin.member::list', $data)->render();
    }

    /**
     * 新增表單
     */
    public function create(): View
    {
        $data['lang'] = $this->lang;
        $data['breadcrumbs'] = $this->breadcrumbs;
        $data['user'] = new User();

        return view('ocadmin.member::form', $data);
    }

    /**
     * 儲存新資料
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'username' => 'required|string|max:100|unique:users,username',
            'email' => 'required|email|max:255|unique:users,email',
            'first_name' => 'nullable|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::create($validated);

        return response()->json([
            'success' => true,
            'message' => $this->lang->text_success_add,
            'replace_url' => route('lang.ocadmin.member.edit', $user),
            'form_action' => route('lang.ocadmin.member.update', $user),
        ]);
    }

    /**
     * 編輯表單
     */
    public function edit(User $user): View
    {
        if ($user->id <= self::MEMBER_MIN_ID) {
            abort(404);
        }

        $data['lang'] = $this->lang;
        $data['breadcrumbs'] = $this->breadcrumbs;
        $data['user'] = $user;
        $data['hasAdminRole'] = $this->hasAdminRole($user);

        return view('ocadmin.member::form', $data);
    }

    /**
     * 更新資料
     */
    public function update(Request $request, User $user): JsonResponse
    {
        if ($user->id <= self::MEMBER_MIN_ID) {
            abort(404);
        }

        $isAdmin = $this->hasAdminRole($user);

        $validated = $request->validate([
            'username' => 'required|string|max:100|unique:users,username,' . $user->id,
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'first_name' => 'nullable|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'password' => $isAdmin ? 'exclude' : 'nullable|string|min:6|confirmed',
        ]);

        if (!$isAdmin && empty($validated['password'])) {
            unset($validated['password']);
        }

        $user->update($validated);

        return response()->json([
            'success' => true,
            'message' => $this->lang->text_success_edit,
        ]);
    }

    /**
     * 刪除資料
     */
    public function destroy(User $user): JsonResponse
    {
        if ($user->id <= self::MEMBER_MIN_ID) {
            return response()->json(['success' => false, 'message' => $this->lang->error_protected_user]);
        }

        $user->delete();

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

        // 只刪除 id > MEMBER_MIN_ID 的帳號
        User::whereIn('id', $ids)->where('id', '>', self::MEMBER_MIN_ID)->delete();

        return response()->json(['success' => true, 'message' => $this->lang->text_success_delete]);
    }

    /**
     * 判斷使用者是否擁有後台管理角色（任何非 web. 開頭的角色）
     */
    protected function hasAdminRole(User $user): bool
    {
        $user->loadMissing('roles');

        return $user->roles->contains(fn ($role) => !str_starts_with($role->name, 'web.'));
    }
}
