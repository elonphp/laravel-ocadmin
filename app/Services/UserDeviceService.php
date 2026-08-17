<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserDevice;
use Illuminate\Http\Request;
use Jenssegers\Agent\Agent;

class UserDeviceService
{
    /**
     * 記錄登入裝置
     */
    public function recordDevice(Request $request, User $user): UserDevice
    {
        $fingerprint = hash('sha256', $request->session()->getId() . $request->userAgent());

        // 解析 User-Agent 取得裝置名稱
        $agent = new Agent();
        $agent->setUserAgent($request->userAgent());
        $deviceName = $this->buildDeviceName($agent);

        // 查找同 fingerprint → 有則更新，無則新增
        $device = UserDevice::updateOrCreate(
            [
                'user_id'            => $user->id,
                'device_fingerprint' => $fingerprint,
            ],
            [
                'device_name'    => $deviceName,
                'ip_address'     => $request->ip(),
                'last_active_at' => now(),
            ]
        );

        // 標記 is_current = true，同使用者其他裝置 is_current = false
        UserDevice::where('user_id', $user->id)
            ->where('id', '!=', $device->id)
            ->update(['is_current' => false]);

        $device->update(['is_current' => true]);

        return $device;
    }

    /**
     * 刪除指定裝置（排除 is_current）
     */
    public function revokeDevices(array $deviceIds, int $userId): int
    {
        return UserDevice::where('user_id', $userId)
            ->whereIn('id', $deviceIds)
            ->where('is_current', false)
            ->delete();
    }

    /**
     * 刪除非 is_current 的所有裝置
     */
    public function revokeOtherDevices(int $userId): int
    {
        return UserDevice::where('user_id', $userId)
            ->where('is_current', false)
            ->delete();
    }

    /**
     * 產生裝置名稱，例如 "Chrome on Windows"
     */
    protected function buildDeviceName(Agent $agent): string
    {
        $browser = $agent->browser() ?: 'Unknown Browser';
        $platform = $agent->platform() ?: 'Unknown OS';

        return "{$browser} on {$platform}";
    }
}
