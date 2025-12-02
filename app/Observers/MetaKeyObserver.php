<?php

namespace App\Observers;

use App\Models\System\Database\MetaKey;
use App\Services\System\Database\TranslationTableSyncService;

/**
 * MetaKeyObserver
 *
 * 當 meta_keys 變更時，自動同步 sysdata 的 translations 表結構
 */
class MetaKeyObserver
{
    public function __construct(
        protected TranslationTableSyncService $syncService
    ) {}

    /**
     * 儲存後（新增或更新）
     */
    public function saved(MetaKey $metaKey): void
    {
        if ($metaKey->table_name) {
            // 同步表結構
            $this->syncService->syncTableStructure($metaKey->table_name);

            // 清除快取
            MetaKey::clearCache();
        }
    }

    /**
     * 刪除後
     */
    public function deleted(MetaKey $metaKey): void
    {
        if ($metaKey->table_name) {
            // 同步表結構（會移除對應欄位）
            $this->syncService->syncTableStructure($metaKey->table_name);

            // 清除快取
            MetaKey::clearCache();
        }
    }
}
