<?php

namespace App\Portals\Ocadmin\Modules\System\Database\MetaKey;

use App\Models\System\Database\MetaKey;
use App\Services\System\Database\TranslationTableSyncService;

class MetaKeyService
{
    public function __construct(
        protected TranslationTableSyncService $syncService
    ) {}

    /**
     * 建立欄位定義（僅儲存，不同步）
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
     * 更新欄位定義（僅儲存，不同步）
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
     * 同步 translations 表結構
     * 應在 transaction 之外呼叫
     */
    public function syncTranslations(MetaKey $metaKey, ?string $oldTableName = null): void
    {
        // 同步新表結構
        if ($metaKey->table_name) {
            $this->syncService->syncTableStructure($metaKey->table_name);
        }

        // 若 table_name 改變，也要同步舊表（移除欄位）
        if ($oldTableName && $oldTableName !== $metaKey->table_name) {
            $this->syncService->syncTableStructure($oldTableName);
        }
    }

    /**
     * 刪除欄位定義（僅刪除，不同步）
     * @return string|null 被刪除的 table_name（用於後續同步）
     */
    public function delete(MetaKey $metaKey): ?string
    {
        $tableName = $metaKey->table_name;

        $metaKey->delete();

        // 清除快取
        MetaKey::clearCache();

        return $tableName;
    }

    /**
     * 批次刪除（僅刪除，不同步）
     * @return array ['count' => int, 'tableNames' => array]
     */
    public function batchDelete(array $ids): array
    {
        // 先取得所有受影響的 table_name
        $tableNames = MetaKey::whereIn('id', $ids)
            ->whereNotNull('table_name')
            ->distinct()
            ->pluck('table_name')
            ->toArray();

        $count = MetaKey::whereIn('id', $ids)->delete();

        // 清除快取
        MetaKey::clearCache();

        return ['count' => $count, 'tableNames' => $tableNames];
    }

    /**
     * 同步指定 table 的 translations 結構
     */
    public function syncTable(string $tableName): void
    {
        $this->syncService->syncTableStructure($tableName);
    }
}
