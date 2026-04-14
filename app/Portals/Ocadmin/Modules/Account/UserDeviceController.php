<?php

namespace App\Portals\Ocadmin\Modules\Account;

use App\Portals\Ocadmin\Core\Controllers\OcadminController;
use App\Services\UserDeviceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class UserDeviceController extends OcadminController
{
    protected function setLangFiles(): array
    {
        return ['account/user_device'];
    }

    /**
     * 列表頁（初始載入）
     */
    public function index(Request $request): View
    {
        $data['lang'] = $this->lang;
        $data['list'] = $this->getList($request);

        $data['list_url'] = route('lang.ocadmin.account.user-devices.list');
        $data['index_url'] = route('lang.ocadmin.account.user-devices.index');
        $data['revoke_url'] = route('lang.ocadmin.account.user-devices.revoke');
        $data['revoke_others_url'] = route('lang.ocadmin.account.user-devices.revoke-others');

        return view('ocadmin::account.user-device.index', $data);
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
        $userId = auth()->id();

        $query = DB::table('user_devices')
            ->where('user_id', $userId)
            ->select('user_devices.*');

        // sort, order
        $sort = $request->get('sort', 'last_active_at');
        $order = $request->get('order', 'desc');
        $query->orderBy($sort, $order);

        $devices = $query->paginate($request->get('limit', 20));

        // buildUrlParams
        $url = $this->buildUrlParams($request);

        $data['devices'] = $devices;

        // 分頁連結
        $query_data = $request->except(['page']);
        $data['pagination'] = $devices->withPath(route('lang.ocadmin.account.user-devices.list'))->appends($query_data)->links('ocadmin::pagination.default');

        // 排序連結
        $next_order = ($order == 'asc') ? 'desc' : 'asc';
        $data['sort'] = $sort;
        $data['order'] = $order;

        $base_url = route('lang.ocadmin.account.user-devices.list');
        $data['sort_device_name'] = $base_url . "?sort=device_name&order={$next_order}" . str_replace('?', '&', $url);
        $data['sort_ip_address'] = $base_url . "?sort=ip_address&order={$next_order}" . str_replace('?', '&', $url);
        $data['sort_last_active_at'] = $base_url . "?sort=last_active_at&order={$next_order}" . str_replace('?', '&', $url);
        $data['sort_created_at'] = $base_url . "?sort=created_at&order={$next_order}" . str_replace('?', '&', $url);

        return view('ocadmin::account.user-device.list', $data)->render();
    }

    /**
     * 撤銷選取的裝置（不可刪 is_current）
     */
    public function revoke(Request $request): JsonResponse
    {
        $ids = $request->input('selected', []);

        if (empty($ids)) {
            return response()->json(['error' => $this->lang->error_select_revoke], 400);
        }

        $service = app(UserDeviceService::class);
        $deleted = $service->revokeDevices($ids, auth()->id());

        return response()->json(['success' => $this->lang->text_success_revoke]);
    }

    /**
     * 刪除所有非 is_current 裝置
     */
    public function revokeOthers(): JsonResponse
    {
        $service = app(UserDeviceService::class);
        $deleted = $service->revokeOtherDevices(auth()->id());

        return response()->json(['success' => $this->lang->text_success_revoke_others]);
    }
}
