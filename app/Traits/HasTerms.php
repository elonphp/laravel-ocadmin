<?php

namespace App\Traits;

use App\Models\System\Term;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * 通用分類／標籤 Trait（多對多，走通用多型 pivot sys_termables）。
 *
 * 任何 Model use 之即可掛 sys_term（分類法 sys_taxonomy 底下的詞）。
 * 關聯走語言中立的 term_id；顯示名在 sys_term_translations、對外把手用 sys_terms.code。
 *
 * 使用方式：
 *   class Product extends Model { use HasTerms; }
 *   $product->syncTerms([$id1, $id2]);
 *   $product->termsInTaxonomy('category');
 *
 * ⚠️ termable 側多型無 DB 級 FK；強制刪除時由 bootHasTerms 清孤兒 pivot。
 * ⚠️ 使用前須在 AppServiceProvider 的 morph map（enforceMorphMap）註冊該 model 的別名。
 */
trait HasTerms
{
    /**
     * 多型 pivot 的 termable 側無 DB 級 FK，刪除實體不會自動清 sys_termables。
     * 這裡補上：強制刪除（或無軟刪除的模型）時清掉孤兒 pivot；軟刪除則保留以利還原。
     */
    public static function bootHasTerms(): void
    {
        static::deleting(function ($model) {
            if (method_exists($model, 'isForceDeleting') && ! $model->isForceDeleting()) {
                return; // 軟刪除：保留 pivot
            }
            $model->terms()->detach();
        });
    }

    /**
     * 掛在本模型的所有 term（依 pivot sort_order 排序）。
     */
    public function terms(): MorphToMany
    {
        return $this->morphToMany(Term::class, 'termable', 'sys_termables')
            ->withPivot('sort_order')
            ->orderBy('sys_termables.sort_order');
    }

    /**
     * 取得某分類法（taxonomy code）底下掛在本模型的 terms。
     */
    public function termsInTaxonomy(string $taxonomyCode): Collection
    {
        return $this->terms()
            ->whereHas('taxonomy', fn ($q) => $q->where('code', $taxonomyCode))
            ->get();
    }

    /**
     * 以 term id 陣列覆寫同步本模型的標籤（多對多 sync）。
     *
     * @return array{attached: array, detached: array, updated: array}
     */
    public function syncTerms(array $termIds): array
    {
        return $this->terms()->sync($termIds);
    }
}
