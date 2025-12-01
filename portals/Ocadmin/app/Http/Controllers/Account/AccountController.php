<?php

namespace Portals\Ocadmin\Http\Controllers\Account;

use App\Models\Identity\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use App\Helpers\Classes\OrmHelper;
use Portals\Ocadmin\Services\Account\AccountService;

class AccountController extends Controller
{
    public function __construct(
        private AccountService $accountService
    ) {}

    /**
     * 列表頁面 - 完整頁面渲染
     */
    public function index(Request $request): View
    {
        $data['list'] = $this->getList($request);

        return view('ocadmin::account.account.index', $data);
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
        $query = User::query();
        $filter_data = $request->all();

        // 預設顯示全部（包含停用）
        if (!isset($filter_data['equal_is_active'])) {
            $filter_data['equal_is_active'] = '*';
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
        $users->withPath(route('lang.ocadmin.account.account.list'));

        // 建構 URL 參數
        $url = $this->buildUrlParams($request);

        // 準備資料
        $data['users'] = $users;
        $data['action'] = route('lang.ocadmin.account.account.list') . $url;
        $data['url_params'] = $url;

        return view('ocadmin::account.account.list', $data)->render();
    }

    /**
     * 新增頁面
     */
    public function create()
    {
        return view('ocadmin::account.account.form', [
            'user' => new User(),
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
        $user = DB::transaction(fn () => $this->accountService->create($validated));

        return response()->json([
            'success' => '帳號新增成功！',
            'redirect' => route('lang.ocadmin.account.account.edit', $user->id),
        ]);
    }

    /**
     * 編輯頁面
     */
    public function edit(User $user)
    {
        return view('ocadmin::account.account.form', [
            'user' => $user,
        ]);
    }

    /**
     * 儲存編輯 (AJAX)
     */
    public function update(Request $request, User $user)
    {
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
        DB::transaction(fn () => $this->accountService->update($user, $validated));

        return response()->json([
            'success' => '帳號更新成功！',
        ]);
    }

    /**
     * 刪除
     */
    public function destroy(User $user)
    {
        DB::transaction(fn () => $this->accountService->delete($user));

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

        DB::transaction(fn () => $this->accountService->batchDelete($ids));

        return response()->json(['success' => true]);
    }

    /**
     * 輔助方法：建構 URL 參數字串
     */
    protected function buildUrlParams(Request $request): string
    {
        $params = [];

        // 收集所有 filter_* 和 equal_* 參數
        foreach ($request->all() as $key => $value) {
            if (str_starts_with($key, 'filter_') || str_starts_with($key, 'equal_')) {
                if ($value !== null && $value !== '') {
                    $params[] = $key . '=' . urlencode($value);
                }
            }
        }

        // 關鍵字查詢
        if ($request->has('search') && $request->search) {
            $params[] = 'search=' . urlencode($request->search);
        }

        // 分頁參數
        if ($request->has('limit') && $request->limit) {
            $params[] = 'limit=' . $request->limit;
        }

        if ($request->has('page') && $request->page) {
            $params[] = 'page=' . $request->page;
        }

        // 排序參數
        if ($request->has('sort') && $request->sort) {
            $params[] = 'sort=' . urlencode($request->sort);
        }

        if ($request->has('order') && $request->order) {
            $params[] = 'order=' . urlencode($request->order);
        }

        return $params ? '?' . implode('&', $params) : '';
    }
}
