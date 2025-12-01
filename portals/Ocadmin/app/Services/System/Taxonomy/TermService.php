<?php

namespace Portals\Ocadmin\Services\System\Taxonomy;

use App\Models\Common\Term;
use App\Models\Common\Taxonomy;

class TermService
{
    /**
     * 建立詞彙
     */
    public function create(array $data, array $translations = [], array $metas = []): Term
    {
        $term = Term::create([
            'taxonomy_id' => $data['taxonomy_id'],
            'parent_id' => $data['parent_id'] ?: null,
            'code' => $data['code'],
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => $data['is_active'] ?? true,
        ]);

        // 儲存翻譯
        if (!empty($translations)) {
            $term->setTranslations($translations);
        }

        // 儲存 metas
        if (!empty($metas)) {
            $term->setMetas($metas);
        }

        // 清除快取
        $taxonomyCode = Taxonomy::find($data['taxonomy_id'])?->code;
        if ($taxonomyCode) {
            Term::clearCache($taxonomyCode);
        }

        return $term;
    }

    /**
     * 更新詞彙
     */
    public function update(Term $term, array $data, array $translations = [], array $metas = []): Term
    {
        $oldTaxonomyId = $term->taxonomy_id;

        $term->update([
            'taxonomy_id' => $data['taxonomy_id'] ?? $term->taxonomy_id,
            'parent_id' => array_key_exists('parent_id', $data) ? ($data['parent_id'] ?: null) : $term->parent_id,
            'code' => $data['code'] ?? $term->code,
            'sort_order' => $data['sort_order'] ?? $term->sort_order,
            'is_active' => $data['is_active'] ?? $term->is_active,
        ]);

        // 儲存翻譯
        if (!empty($translations)) {
            $term->setTranslations($translations);
        }

        // 儲存 metas
        if (!empty($metas)) {
            $term->setMetas($metas);
        }

        // 清除快取
        $oldTaxonomyCode = Taxonomy::find($oldTaxonomyId)?->code;
        $newTaxonomyCode = Taxonomy::find($term->taxonomy_id)?->code;

        if ($oldTaxonomyCode) {
            Term::clearCache($oldTaxonomyCode);
        }
        if ($newTaxonomyCode && $newTaxonomyCode !== $oldTaxonomyCode) {
            Term::clearCache($newTaxonomyCode);
        }

        return $term;
    }

    /**
     * 刪除詞彙
     */
    public function delete(Term $term): void
    {
        $taxonomyCode = $term->taxonomy?->code;
        $term->delete();

        // 清除快取
        if ($taxonomyCode) {
            Term::clearCache($taxonomyCode);
        }
    }

    /**
     * 批次刪除
     */
    public function batchDelete(array $ids): int
    {
        // 取得相關的 taxonomy codes
        $taxonomyIds = Term::whereIn('id', $ids)->pluck('taxonomy_id')->unique()->toArray();
        $codes = Taxonomy::whereIn('id', $taxonomyIds)->pluck('code')->toArray();

        $count = Term::whereIn('id', $ids)->delete();

        // 清除快取
        foreach ($codes as $code) {
            Term::clearCache($code);
        }

        return $count;
    }
}
