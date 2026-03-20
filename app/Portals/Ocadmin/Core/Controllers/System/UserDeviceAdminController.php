<?php

namespace App\Portals\Ocadmin\Core\Controllers\System;

use App\Models\User;
use App\Models\UserDevice;
use App\Portals\Ocadmin\Core\Controllers\OcadminController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class UserDeviceAdminController extends OcadminController
{
    protected function setLangFiles(): array
    {
        return ['system/user_device'];
    }

    /**
     * 列表頁（初始載入）
     */
    public function index(Request $request): View
    {
        $data['lang'] = $this->lang;
        $data['list'] = $this->getList($request);

        return view('ocadmin::system.user-device.index', $data);
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

        $query = DB::table('user_devices')
            ->leftJoin('users', 'user_devices.user_id', '=', 'users.id')
            ->select('user_devices.*', 'users.name as user_name');

        // search 關鍵字查詢
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('user_devices.device_name', 'like', '%' . $search . '%')
                  ->orWhere('user_devices.ip_address', 'like', '%' . $search . '%')
                  ->orWhere('users.name', 'like', '%' . $search . '%');
            });
        }

        // filter_user_id 篩選
        if ($request->filled('filter_user_id')) {
            $query->where('user_devices.user_id', $request->filter_user_id);
        }

        // sort, order
        $sort = $request->get('sort', 'last_active_at');
        $order = $request->get('order', 'desc');
        $query->orderBy("user_devices.{$sort}", $order);

        $devices = $query->paginate($request->get('limit', 20));

        // 補充資料
        foreach ($devices as $row) {
            $row->user_name = $row->user_name ?: $this->lang->text_user_deleted;
        }

        // buildUrlParams
        $url = $this->buildUrlParams($request);

        $data['devices'] = $devices;

        // 分頁連結
        $query_data = $request->except(['page']);
        $data['pagination'] = $devices->withPath(route('lang.ocadmin.system.user-devices.list'))->appends($query_data)->links('ocadmin::pagination.default');

        // 排序連結
        $next_order = ($order == 'asc') ? 'desc' : 'asc';
        $data['sort'] = $sort;
        $data['order'] = $order;

        $base_url = route('lang.ocadmin.system.user-devices.list');
        $data['sort_id'] = $base_url . "?sort=id&order={$next_order}" . str_replace('?', '&', $url);
        $data['sort_device_name'] = $base_url . "?sort=device_name&order={$next_order}" . str_replace('?', '&', $url);
        $data['sort_ip_address'] = $base_url . "?sort=ip_address&order={$next_order}" . str_replace('?', '&', $url);
        $data['sort_last_active_at'] = $base_url . "?sort=last_active_at&order={$next_order}" . str_replace('?', '&', $url);
        $data['sort_created_at'] = $base_url . "?sort=created_at&order={$next_order}" . str_replace('?', '&', $url);

        return view('ocadmin::system.user-device.list', $data)->render();
    }

    /**
     * 管理員批次強制刪除裝置
     */
    public function forceRevoke(Request $request): JsonResponse
    {
        $ids = $request->input('selected', []);

        if (empty($ids)) {
            return response()->json(['error' => $this->lang->error_select_revoke], 400);
        }

        UserDevice::whereIn('id', $ids)->delete();

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
