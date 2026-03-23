<?php

namespace App\Portals\Ocadmin\Core\Controllers\System;

use App\Models\User;
use App\Portals\Ocadmin\Core\Controllers\OcadminController;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AccessTokenController extends OcadminController
{
    protected function setLangFiles(): array
    {
        return ['system/access_token'];
    }

    /**
     * 列表頁（初始載入）
     */
    public function index(Request $request): View
    {
        $data['lang'] = $this->lang;
        $data['list'] = $this->getList($request);

        $data['list_url'] = route('lang.ocadmin.system.access-tokens.list');
        $data['index_url'] = route('lang.ocadmin.system.access-tokens.index');
        $data['add_url'] = route('lang.ocadmin.system.access-tokens.form');
        $data['revoke_url'] = route('lang.ocadmin.system.access-tokens.revoke');

        return view('ocadmin::system.access-token.index', $data);
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
        $data['lang'] = $this->lang;

        $query = DB::table('personal_access_tokens')
            ->leftJoin('users', 'personal_access_tokens.tokenable_id', '=', 'users.id')
            ->select('personal_access_tokens.*', 'users.name as user_name');

        // search 關鍵字查詢
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('personal_access_tokens.name', 'like', '%' . $search . '%')
                  ->orWhere('users.name', 'like', '%' . $search . '%');
            });
        }

        // sort, order
        $sort = $request->get('sort', 'id');
        $order = $request->get('order', 'desc');
        $query->orderBy("personal_access_tokens.{$sort}", $order);

        $tokens = $query->paginate($request->get('limit', 20));

        // 補充資料
        foreach ($tokens as $row) {
            $row->user_name = $row->user_name ?: $this->lang->text_user_deleted;
            $abilities = json_decode($row->abilities, true) ?: [];
            $row->abilities_display = implode(', ', $abilities);
            $row->edit_url = route('lang.ocadmin.system.access-tokens.form', [$row->id]);
        }

        // buildUrlParams
        $url = $this->buildUrlParams($request);
        $data['urlParams'] = $this->buildEditUrlParams($request);

        $data['tokens'] = $tokens;

        // 分頁連結
        $query_data = $request->except(['page']);
        $data['pagination'] = $tokens->withPath(route('lang.ocadmin.system.access-tokens.list'))->appends($query_data)->links('ocadmin::pagination.default');

        // 排序連結
        $next_order = ($order == 'asc') ? 'desc' : 'asc';
        $data['sort'] = $sort;
        $data['order'] = $order;

        $base_url = route('lang.ocadmin.system.access-tokens.list');
        $data['sort_id'] = $base_url . "?sort=id&order={$next_order}" . str_replace('?', '&', $url);
        $data['sort_name'] = $base_url . "?sort=name&order={$next_order}" . str_replace('?', '&', $url);
        $data['sort_last_used_at'] = $base_url . "?sort=last_used_at&order={$next_order}" . str_replace('?', '&', $url);
        $data['sort_created_at'] = $base_url . "?sort=created_at&order={$next_order}" . str_replace('?', '&', $url);

        return view('ocadmin::system.access-token.list', $data)->render();
    }

    /**
     * 新增 / 編輯表單
     */
    public function form($id = null): View
    {
        $data['lang'] = $this->lang;

        // Portal 權限選項（依 config/vars.php 的 portal_keys 動態產生）
        $portalKeys = config('vars.portal_keys', []);
        $data['portal_abilities'] = [];
        foreach ($portalKeys as $portal => $config) {
            $data['portal_abilities']["portal:{$portal}"] = ucfirst($portal);
        }

        $data['token_id'] = $id;
        $data['token_record'] = null;
        $data['token_user_name'] = '';
        $data['token_abilities'] = [];

        if ($id) {
            $record = DB::table('personal_access_tokens')
                ->leftJoin('users', 'personal_access_tokens.tokenable_id', '=', 'users.id')
                ->where('personal_access_tokens.id', $id)
                ->select('personal_access_tokens.*', 'users.name as user_name')
                ->first();

            if (!$record) {
                abort(404);
            }

            $data['token_record'] = $record;
            $data['token_user_name'] = $record->user_name ?: $this->lang->text_user_deleted;
            $data['token_abilities'] = json_decode($record->abilities, true) ?: [];
        }

        $data['save_url'] = $id
            ? route('lang.ocadmin.system.access-tokens.save', $id)
            : route('lang.ocadmin.system.access-tokens.save');
        $data['back_url'] = route('lang.ocadmin.system.access-tokens.index');
        $data['search_users_url'] = route('lang.ocadmin.system.access-tokens.search-users');

        return view('ocadmin::system.access-token.form', $data);
    }

    /**
     * 建立 / 更新 token
     */
    public function save(Request $request, $id = null): JsonResponse
    {
        // 編輯模式
        if ($id) {
            $request->validate([
                'name' => 'required|string|max:255',
                'abilities' => 'required|array|min:1',
                'expires_at' => 'nullable|date',
            ], [
                'name.required' => $this->lang->error_name_required,
                'abilities.required' => $this->lang->error_abilities_required,
                'abilities.min' => $this->lang->error_abilities_required,
            ]);

            $exists = DB::table('personal_access_tokens')->where('id', $id)->exists();
            if (!$exists) {
                return response()->json(['error' => $this->lang->error_not_found ?? 'Not found'], 404);
            }

            DB::table('personal_access_tokens')->where('id', $id)->update([
                'name' => $request->input('name'),
                'abilities' => json_encode($request->input('abilities', [])),
                'expires_at' => $request->input('expires_at')
                    ? Carbon::parse($request->input('expires_at'))
                    : null,
                'updated_at' => now(),
            ]);

            return response()->json([
                'success' => $this->lang->text_success_update,
                'redirect' => route('lang.ocadmin.system.access-tokens.index'),
            ]);
        }

        // 新增模式
        $userMode = $request->input('user_mode', 'existing');

        // 共用驗證規則
        $rules = [
            'name' => 'required|string|max:255',
            'abilities' => 'required|array|min:1',
            'expires_at' => 'nullable|date|after:today',
        ];
        $messages = [
            'name.required' => $this->lang->error_name_required,
            'abilities.required' => $this->lang->error_abilities_required,
            'abilities.min' => $this->lang->error_abilities_required,
            'expires_at.after' => $this->lang->error_expires_at_after,
        ];

        if ($userMode === 'create') {
            $rules['username'] = 'required|string|max:255|unique:users,username';
            $rules['local_name'] = 'required|string|max:255';
            $messages['username.required'] = $this->lang->error_username_required;
            $messages['username.unique'] = $this->lang->error_username_unique;
            $messages['local_name.required'] = $this->lang->error_local_name_required;
        } else {
            $rules['user_id'] = 'required|exists:users,id';
            $messages['user_id.required'] = $this->lang->error_user_id_required;
            $messages['user_id.exists'] = $this->lang->error_user_id_exists;
        }

        $request->validate($rules, $messages);

        // 取得或建立 user_id
        if ($userMode === 'create') {
            $user = User::create([
                'username' => $request->input('username'),
                'name'     => $request->input('local_name'),
                'password' => bcrypt(Str::random(32)),
            ]);
            $userId = $user->id;
        } else {
            $userId = $request->input('user_id');
        }

        $plainText = Str::random(40);

        $tokenId = DB::table('personal_access_tokens')->insertGetId([
            'tokenable_type' => User::class,
            'tokenable_id' => $userId,
            'name' => $request->input('name'),
            'token' => hash('sha256', $plainText),
            'abilities' => json_encode($request->input('abilities', [])),
            'expires_at' => $request->input('expires_at')
                ? Carbon::parse($request->input('expires_at'))
                : null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => $this->lang->text_success_create,
            'token' => $tokenId . '|' . $plainText,
            'warning' => $this->lang->text_token_warning,
        ]);
    }

    /**
     * 撤銷（刪除）選取的 token
     */
    public function revoke(Request $request): JsonResponse
    {
        $ids = $request->input('selected', []);

        if (empty($ids)) {
            return response()->json(['error' => $this->lang->error_select_revoke], 400);
        }

        DB::table('personal_access_tokens')->whereIn('id', $ids)->delete();

        return response()->json(['success' => $this->lang->text_success_revoke]);
    }

    /**
     * 使用者搜尋（Autocomplete）
     */
    public function searchUsers(Request $request): JsonResponse
    {
        $keyword = $request->input('q', '');

        $users = User::where('email', 'like', "%{$keyword}%")
            ->orWhere('username', 'like', "%{$keyword}%")
            ->orWhere('name', 'like', "%{$keyword}%")
            ->select('id', 'name', 'email', 'username')
            ->limit(10)
            ->get();

        return response()->json($users);
    }
}
