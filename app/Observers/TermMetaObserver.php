<?php

namespace App\Observers;

use App\Models\Common\Term;
use App\Models\Common\TermMeta;
use App\Services\System\Database\TranslationTableSyncService;

/**
 * TermMetaObserver
 *
 * 當 term_metas 變更時，自動同步到 sysdata.term_translations
 * 判斷依據：locale 欄位有值才需要同步
 */
class TermMetaObserver
{
    public function __construct(
        protected TranslationTableSyncService $syncService
    ) {}

    /**
     * 儲存後（新增或更新）
     */
    public function saved(TermMeta $meta): void
    {
        $this->syncIfTranslation($meta);
    }

    /**
     * 刪除後
     */
    public function deleted(TermMeta $meta): void
    {
        $this->syncIfTranslation($meta);
    }

    /**
     * locale 有值才同步到 sysdata
     */
    protected function syncIfTranslation(TermMeta $meta): void
    {
        // 檢查 Term 的 translation_mode 是否為 3
        $term = Term::find($meta->term_id);
        if (!$term || ($term->translation_mode ?? 1) !== 3) {
            return;
        }

        // locale 為空表示非翻譯資料，不需同步
        if (empty($meta->locale)) {
            return;
        }

        // 同步該筆資料到 translations
        $this->syncService->syncData('terms', $meta->term_id);
    }
}
