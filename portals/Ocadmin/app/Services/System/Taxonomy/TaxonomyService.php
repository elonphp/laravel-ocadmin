<?php

namespace Portals\Ocadmin\Services\System\Taxonomy;

use App\Models\Common\Taxonomy;

class TaxonomyService
{
    /**
     * 建立分類法
     */
    public function create(array $data, array $translations = []): Taxonomy
    {
        $taxonomy = Taxonomy::create([
            'code' => $data['code'],
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => $data['is_active'] ?? true,
        ]);

        // 儲存翻譯
        if (!empty($translations)) {
            $taxonomy->setTranslations($translations);
        }

        // 清除快取
        Taxonomy::clearCache($taxonomy->code);

        return $taxonomy;
    }

    /**
     * 更新分類法
     */
    public function update(Taxonomy $taxonomy, array $data, array $translations = []): Taxonomy
    {
        $oldCode = $taxonomy->code;

        $taxonomy->update([
            'code' => $data['code'] ?? $taxonomy->code,
            'sort_order' => $data['sort_order'] ?? $taxonomy->sort_order,
            'is_active' => $data['is_active'] ?? $taxonomy->is_active,
        ]);

        // 儲存翻譯
        if (!empty($translations)) {
            $taxonomy->setTranslations($translations);
        }

        // 清除快取（新舊 code 都要清）
        Taxonomy::clearCache($oldCode);
        if ($oldCode !== $taxonomy->code) {
            Taxonomy::clearCache($taxonomy->code);
        }

        return $taxonomy;
    }

    /**
     * 刪除分類法
     */
    public function delete(Taxonomy $taxonomy): void
    {
        $code = $taxonomy->code;
        $taxonomy->delete();

        // 清除快取
        Taxonomy::clearCache($code);
    }

    /**
     * 批次刪除
     */
    public function batchDelete(array $ids): int
    {
        // 取得要刪除的 codes
        $codes = Taxonomy::whereIn('id', $ids)->pluck('code')->toArray();

        $count = Taxonomy::whereIn('id', $ids)->delete();

        // 清除快取
        foreach ($codes as $code) {
            Taxonomy::clearCache($code);
        }

        return $count;
    }
}
