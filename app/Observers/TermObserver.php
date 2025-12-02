<?php

namespace App\Observers;

use App\Models\Common\Term;
use App\Services\System\Database\TranslationTableSyncService;

/**
 * TermObserver
 *
 * 當 Term 刪除時，同步刪除 sysdata.term_translations 的資料
 */
class TermObserver
{
    public function __construct(
        protected TranslationTableSyncService $syncService
    ) {}

    /**
     * 刪除後
     */
    public function deleted(Term $term): void
    {
        // 只有 mode=3 才需要刪除 sysdata translations
        if (($term->translation_mode ?? 1) !== 3) {
            return;
        }

        // 刪除 sysdata translations 資料
        $this->syncService->deleteTranslation('terms', $term->id);
    }
}
