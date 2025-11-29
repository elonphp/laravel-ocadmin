<?php

namespace Portals\Ocadmin\Services\System\Database;

use App\Models\System\Database\MetaKey;

class MetaKeyService
{
    /**
     * 建立欄位定義
     * 注意：不包含 Transaction，由 Controller 控制
     */
    public function create(array $data): MetaKey
    {
        $data = MetaKey::withDefaults($data);

        $metaKey = MetaKey::create($data);

        // 清除快取
        MetaKey::clearCache();

        return $metaKey;
    }

    /**
     * 更新欄位定義
     */
    public function update(MetaKey $metaKey, array $data): MetaKey
    {
        $data = MetaKey::withDefaults($data);

        $metaKey->update($data);

        // 清除快取
        MetaKey::clearCache();

        return $metaKey;
    }

    /**
     * 刪除欄位定義
     */
    public function delete(MetaKey $metaKey): void
    {
        $metaKey->delete();

        // 清除快取
        MetaKey::clearCache();
    }

    /**
     * 批次刪除
     */
    public function batchDelete(array $ids): int
    {
        $count = MetaKey::whereIn('id', $ids)->delete();

        // 清除快取
        MetaKey::clearCache();

        return $count;
    }
}
