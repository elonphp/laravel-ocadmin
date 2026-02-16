<?php

namespace App\Portals\Ocadmin\Modules\Member\Member;

use App\Helpers\Classes\OrmHelper;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Portals\Ocadmin\Core\Controllers\OcadminController;

class MemberController extends OcadminController
{
    protected function setLangFiles(): array
    {
        return ['common', 'member/member'];
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
                'href' => route('lang.ocadmin.member.member.index'),
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

        return view('ocadmin.member.member::index', $data);
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
        $query = User::query();
        $filter_data = $this->filterData($request);

        // 預設排序
        $filter_data['sort'] = $request->query('sort', 'id');
        $filter_data['order'] = $request->query('order', 'asc');

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
        $users->withPath(route('lang.ocadmin.member.member.list'));

        $data['lang'] = $this->lang;
        $data['users'] = $users;
        $data['pagination'] = $users->links('ocadmin::pagination.default');

        // 建構 URL 參數與排序連結
        $url = $this->buildUrlParams($request);
        $baseUrl = route('lang.ocadmin.member.member.list');
        $data['sort'] = $filter_data['sort'];
        $data['order'] = $filter_data['order'];
        $nextOrder = ($data['order'] == 'asc') ? 'desc' : 'asc';

        $data['sort_username'] = $baseUrl . "?sort=username&order={$nextOrder}" . str_replace('?', '&', $url);
        $data['sort_email'] = $baseUrl . "?sort=email&order={$nextOrder}" . str_replace('?', '&', $url);
        $data['sort_created_at'] = $baseUrl . "?sort=created_at&order={$nextOrder}" . str_replace('?', '&', $url);

        return view('ocadmin.member.member::list', $data)->render();
    }

    /**
     * 新增表單
     */
    public function create(): View
    {
        $data['lang'] = $this->lang;
        $data['breadcrumbs'] = $this->breadcrumbs;
        $data['user'] = new User();

        return view('ocadmin.member.member::form', $data);
    }

    /**
     * 儲存新資料
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'username'   => 'nullable|string|max:100|unique:users,username',
            'email'      => 'required|email|max:255|unique:users,email',
            'first_name' => 'nullable|string|max:100',
            'last_name'  => 'nullable|string|max:100',
            'password'   => 'required|string|min:6|confirmed',
        ]);

        $user = User::create($validated);

        return response()->json([
            'success' => true,
            'message' => $this->lang->text_success_add,
            'replace_url' => route('lang.ocadmin.member.member.edit', $user),
            'form_action' => route('lang.ocadmin.member.member.update', $user),
        ]);
    }

    /**
     * 編輯表單
     */
    public function edit(User $user): View
    {
        $data['lang'] = $this->lang;
        $data['breadcrumbs'] = $this->breadcrumbs;
        $data['user'] = $user;

        return view('ocadmin.member.member::form', $data);
    }

    /**
     * 更新資料
     */
    public function update(Request $request, User $user): JsonResponse
    {
        // 後台角色使用者不可在此修改密碼
        if ($user->hasBackendRole() && $request->filled('password')) {
            return response()->json([
                'success' => false,
                'message' => $this->lang->error_password_backend,
            ]);
        }

        $validated = $request->validate([
            'username'   => 'nullable|string|max:100|unique:users,username,' . $user->id,
            'email'      => 'required|email|max:255|unique:users,email,' . $user->id,
            'first_name' => 'nullable|string|max:100',
            'last_name'  => 'nullable|string|max:100',
            'password'   => 'nullable|string|min:6|confirmed',
        ]);

        // 密碼留空不更新
        if (empty($validated['password'])) {
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
        if ($user->hasBackendRole()) {
            return response()->json([
                'success' => false,
                'message' => $this->lang->error_delete_backend,
            ]);
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

        // 排除擁有後台角色的使用者
        $backendUserIds = User::whereIn('id', $ids)
            ->whereHas('roles', fn($q) => $q->where('name', 'like', 'admin.%'))
            ->pluck('id')
            ->toArray();

        $deletableIds = array_diff($ids, $backendUserIds);

        if (empty($deletableIds)) {
            return response()->json([
                'success' => false,
                'message' => $this->lang->error_delete_backend,
            ]);
        }

        User::whereIn('id', $deletableIds)->delete();

        return response()->json(['success' => true, 'message' => $this->lang->text_success_delete]);
    }
}
