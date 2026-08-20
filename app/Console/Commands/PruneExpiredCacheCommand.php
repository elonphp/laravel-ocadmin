<?php

namespace App\Console\Commands;

use App\Repositories\LogDatabaseRepository;
use FilesystemIterator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * 清理過期快取（file / database store）。
 *
 * Laravel 沒有「只刪過期」的命令——`cache:clear` 是整個清光（含仍有效的），
 * `cache:prune-stale-tags` 只處理 Redis 的 tag。而兩個 store 的刪除都是 lazy 的：
 * 只有在「某個 key 剛好又被讀到、發現過期」時才刪那一筆。
 *
 * → **再也不會被讀到的 key 永遠不會消失**。key 只要含日期、id、參數雜湊這類會變動的
 *   東西，就會不斷產生這種孤兒。本命令就是補上那個沒人做的回收。
 *
 * 兩條刪除規則：
 *   1. **已過期** → 刪
 *   2. **未過期但超過 N 天沒被寫入** → 也刪（`--days`，預設 90）
 *      連 `forever` 也不例外，避免「寫一次就永遠佔著」的項目累積。
 *
 * 規則 2 只有 file store 做得到：判準是檔案 mtime，而 `cache` 資料表只有
 * key / value / expiration 三欄，沒有任何年齡資訊（見 docs/common/10029）。
 *
 * ⚠ 掃描範圍嚴格限定各 file store 的 `path`。編譯後的 blade（storage/framework/views）
 * 與 config/route 快取（bootstrap/cache）不是 cache store 的東西，各有自己的
 * `view:clear` / `config:clear`，本命令一律不碰。
 */
class PruneExpiredCacheCommand extends Command
{
    protected $signature = 'cache:prune-expired
                            {--days=90 : 未過期但超過幾天未寫入也刪除（0 = 只刪已過期）}
                            {--store=* : 只處理指定 store（預設處理所有 file / database store）}
                            {--dry-run : 預覽刪除數量，不實際執行}';

    protected $description = '清除已過期（及逾齡）的快取項目，file 與 database store 皆適用';

    /** 快取檔開頭固定 10 碼的到期 timestamp（Illuminate\Cache\FileStore::put） */
    protected const EXPIRY_HEADER_BYTES = 10;

    protected bool $dryRun = false;

    /** 各 store 的統計：['expired' => n, 'stale' => n, 'corrupt' => n] */
    protected array $stats = [];

    public function handle(): int
    {
        $this->dryRun = (bool) $this->option('dry-run');
        $days         = max(0, (int) $this->option('days'));
        $only         = (array) $this->option('store');

        // 逾齡門檻換算成 timestamp；0 表示不套用規則 2
        $staleBefore = $days > 0 ? now()->subDays($days)->getTimestamp() : null;

        foreach (config('cache.stores', []) as $name => $config) {
            if ($only && ! in_array($name, $only, true)) {
                continue;
            }

            match ($config['driver'] ?? null) {
                'file'     => $this->pruneFileStore($name, $config, $staleBefore),
                'database' => $this->pruneDatabaseStore($name, $config, $days),
                default    => null,   // array / redis / memcached 等不在本命令職責內
            };
        }

        return $this->report($days);
    }

    /**
     * File store：讀每個檔前 10 bytes 判定到期，再以 mtime 判定逾齡。
     *
     * 只讀檔頭、不讀內容——快取檔可能有數 MB，而判斷過期只需要那 10 個位元組。
     */
    protected function pruneFileStore(string $name, array $config, ?int $staleBefore): void
    {
        $path = $config['path'] ?? null;

        if (! $path || ! is_dir($path)) {
            return;
        }

        $now = now()->getTimestamp();

        // 先登記這個 store，讓「掃過但沒東西可刪」與「根本沒掃到」在輸出上分得出來
        $this->tally($name, 'expired', 0);

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST,
        );

        foreach ($files as $file) {
            if (! $file->isFile()) {
                continue;
            }

            // .gitignore 等非快取檔（目錄本身進 git，內容不進）
            if (str_starts_with($file->getFilename(), '.')) {
                continue;
            }

            $expiry = $this->readExpiry($file->getPathname());

            if ($expiry === null) {
                // 檔頭讀不出 10 碼數字＝寫入中斷或損毀，留著也沒有人能用
                $this->tally($name, 'corrupt');
                $this->delete($file->getPathname());
                continue;
            }

            if ($expiry <= $now) {
                $this->tally($name, 'expired');
                $this->delete($file->getPathname());
                continue;
            }

            // 規則 2：未過期但太久沒被寫入。mtime 是「最後一次寫入」，
            // 讀取不會更新它（FileStore::get 只讀不寫），詳 docs/common/10029
            if ($staleBefore !== null && $file->getMTime() < $staleBefore) {
                $this->tally($name, 'stale');
                $this->delete($file->getPathname());
            }
        }

        $this->removeEmptyDirectories($path);
    }

    /**
     * 取快取檔的到期 timestamp；非預期格式回 null。
     *
     * 檔案格式為 `str_pad(expiration, 10, '0', STR_PAD_LEFT) . serialize($value)`，
     * `9999999999` 代表永不過期（`forever`）。
     */
    protected function readExpiry(string $path): ?int
    {
        $handle = @fopen($path, 'rb');

        if ($handle === false) {
            return null;
        }

        $head = fread($handle, self::EXPIRY_HEADER_BYTES);
        fclose($handle);

        if ($head === false || strlen($head) < self::EXPIRY_HEADER_BYTES || ! ctype_digit($head)) {
            return null;
        }

        return (int) $head;
    }

    /**
     * Database store：清 cache 表與 cache_locks 表的過期列。
     *
     * **規則 2 在此不適用**——表結構只有 key / value / expiration，沒有寫入時間，
     * forever 的列（expiration = 9999999999）完全無從判斷年齡。要支援得改 schema
     * 並改掉框架的寫入行為，不划算。
     */
    protected function pruneDatabaseStore(string $name, array $config, int $days): void
    {
        $connection = $config['connection'] ?? null;
        $now        = now()->getTimestamp();

        foreach ([$config['table'] ?? 'cache', $config['lock_table'] ?? 'cache_locks'] as $table) {
            if (! Schema::connection($connection)->hasTable($table)) {
                continue;
            }

            $query = DB::connection($connection)->table($table)->where('expiration', '<=', $now);

            $count = $this->dryRun ? $query->count() : $query->delete();

            $this->tally($name, 'expired', $count);
        }

        if ($days > 0) {
            $this->tally($name, 'stale_unsupported');
        }
    }

    /**
     * 刪空目錄——file store 以 key 的雜湊分兩層目錄，清完檔案後會留下大量空殼。
     */
    protected function removeEmptyDirectories(string $path): void
    {
        if ($this->dryRun) {
            return;
        }

        $dirs = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST,
        );

        foreach ($dirs as $dir) {
            if (! $dir->isDir()) {
                continue;
            }

            // 迭代器是 CHILD_FIRST，子目錄先被清掉，父目錄這一輪就跟著判定為空
            if (! (new FilesystemIterator($dir->getPathname()))->valid()) {
                @rmdir($dir->getPathname());
            }
        }
    }

    protected function delete(string $path): void
    {
        if (! $this->dryRun) {
            @unlink($path);
        }
    }

    protected function tally(string $store, string $bucket, int $count = 1): void
    {
        $this->stats[$store][$bucket] = ($this->stats[$store][$bucket] ?? 0) + $count;
    }

    /**
     * 輸出各 store 的結果，並寫一筆排程日誌。
     *
     * 分開報「過期」與「逾齡」而非只給總數：兩者代表的意義不同——前者是正常回收，
     * 後者持續偏高則表示有人把不該長存的東西寫成了 forever。
     */
    protected function report(int $days): int
    {
        $prefix = $this->dryRun ? '[Dry Run] ' : '';
        $total  = 0;
        $notes  = [];

        foreach ($this->stats as $store => $buckets) {
            $expired = $buckets['expired'] ?? 0;
            $stale   = $buckets['stale'] ?? 0;
            $corrupt = $buckets['corrupt'] ?? 0;
            $total  += $expired + $stale + $corrupt;

            $line = "{$prefix}[{$store}] 過期 {$expired} 筆";

            if ($days > 0) {
                $line .= isset($buckets['stale_unsupported'])
                    ? "、逾齡 —（此 driver 無寫入時間，不適用）"
                    : "、逾齡（>{$days} 天）{$stale} 筆";
            }

            if ($corrupt > 0) {
                $line .= "、損毀 {$corrupt} 筆";
            }

            $this->info($line);
            $notes[] = $line;
        }

        if (! $this->stats) {
            $this->info('沒有可處理的 file / database store。');
            return self::SUCCESS;
        }

        if (! $this->dryRun) {
            LogDatabaseRepository::logSchedule('cache:prune-expired', 'success', implode('；', $notes));
        }

        $this->info("{$prefix}合計 {$total} 筆。");

        return self::SUCCESS;
    }
}
