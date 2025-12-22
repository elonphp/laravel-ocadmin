<?php

namespace App\Portals\Ocadmin\Modules\System\Setting;

use App\Models\System\Setting;
use App\Services\BaseService;

class SettingService extends BaseService
{
    /**
     * 建立設定
     * 注意：不包含 Transaction，由 Controller 控制
     */
    public function create(array $data): Setting
    {
        // 預設 locale 為空字串
        $data['locale'] = $data['locale'] ?? '';

        return Setting::create($data);
    }

    /**
     * 更新設定
     */
    public function update(Setting $setting, array $data): Setting
    {
        // 預設 locale 為空字串
        $data['locale'] = $data['locale'] ?? '';

        $setting->update($data);

        return $setting;
    }

    /**
     * 刪除設定
     */
    public function delete(Setting $setting): void
    {
        $setting->delete();
    }

    /**
     * 批次刪除
     */
    public function batchDelete(array $ids): int
    {
        return Setting::whereIn('id', $ids)->delete();
    }

    /**
     * 檢查設定是否存在（排除指定 ID）
     */
    public function exists(string $locale, string $code, string $key, ?int $excludeId = null): bool
    {
        $query = Setting::where('locale', $locale)
            ->where('code', $code)
            ->where('key', $key);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }
}
