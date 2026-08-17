<?php

namespace App\Portals\Ocadmin\Core\Controllers\System;

use App\Portals\Ocadmin\Core\Controllers\OcadminController;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

/**
 * Ocadmin 後台「系統管理 → 遷移功能」（super_admin-only）。
 *
 * URL: /{locale}/admin/system/transition
 *
 * 把原本要 SSH 進伺服器跑的 `php artisan db:transition` 包成後台頁面
 * （正式區走 Git 自動部署時 code 會自己到位 → 消滅最後一步 SSH）：
 *   - 列出 database/transitions/ 全部腳本，join schema_transitions 顯示已執行 / 失敗 / 待執行
 *   - 「預覽」= --dry-run，只列出這次會跑哪幾支，不動資料
 *   - 「執行」= 同步呼叫 db:transition，完整回傳指令輸出（成功 / 失敗訊息一字不漏）
 *
 * 執行採「同步、立刻」（非佇列）：日常小 transition 秒殺即可；長任務（ETL / backfill）仍
 * 由維運者自行斟酌（離峰或 SSH），不在本頁承接——故不引入背景佇列。
 *
 * 權限 {prefix}.system.transition.*：seeder 建立但**不指派給任何角色** → 只有 super_admin /
 * developer 可用（走 AppServiceProvider 的 Gate::before 無條件放行）。route 層以
 * can: middleware 強制（access 管 index/preview、run 管 run）。
 *
 * @see docs/common/00007_資料庫Transition變更機制.md
 */
class TransitionController extends OcadminController
{
    protected function setLangFiles(): array
    {
        return ['system/transition'];
    }

    /**
     * 列表：所有 transition 腳本與其執行狀態
     */
    public function index(): View
    {
        $rows = $this->buildRows();

        $data['lang']        = $this->lang;
        $data['rows']        = $rows;
        $data['pending']     = collect($rows)->where('status', 'pending')->count();
        $data['failed']      = collect($rows)->where('status', 'failed')->count();
        $data['preview_url'] = route('lang.ocadmin.system.transition.preview');
        $data['run_url']     = route('lang.ocadmin.system.transition.run');

        return view('ocadmin::system.transition.index', $data);
    }

    /**
     * 預覽（--dry-run）：只列出待執行者，不動資料
     */
    public function preview(): JsonResponse
    {
        $exit   = Artisan::call('db:transition', ['--dry-run' => true]);
        $output = Artisan::output();

        return $this->result($exit, $output);
    }

    /**
     * 實際執行：同步跑 db:transition，完整回傳輸出
     */
    public function run(): JsonResponse
    {
        // CLI 環境 max_execution_time=0；web 請求需自行解除，長腳本才不會被 PHP 砍
        // （前端 nginx/php-fpm 仍有各自 timeout 天花板，超長腳本請走 SSH／離峰執行）
        @set_time_limit(0);

        $exit   = Artisan::call('db:transition');
        $output = Artisan::output();

        return $this->result($exit, $output);
    }

    /**
     * 統一組回應：指令輸出 + 重新計算後的清單，讓前端刷新狀態表
     */
    protected function result(int $exit, string $output): JsonResponse
    {
        $rows = $this->buildRows();

        return response()->json([
            'success' => $exit === 0,
            'exit'    => $exit,
            'output'  => rtrim($output),
            'rows'    => $rows,
            'pending' => collect($rows)->where('status', 'pending')->count(),
            'failed'  => collect($rows)->where('status', 'failed')->count(),
        ]);
    }

    /**
     * 讀 database/transitions/ 全部腳本，join schema_transitions 標狀態
     *
     * status：success（已成功）/ failed（執行失敗、下次會重試）/ pending（尚未執行）。
     * 排除 example.php（範本，無 version）。與 DbTransitionCommand 同樣以檔名排序、
     * require 取得 version/description（require 每次重新執行、回傳陣列，closure 不會被呼叫）。
     */
    protected function buildRows(): array
    {
        $dir = database_path('transitions');

        if (!is_dir($dir)) {
            return [];
        }

        $records = collect();
        if (Schema::hasTable('schema_transitions')) {
            $records = DB::table('schema_transitions')->get()->keyBy('version');
        }

        $files = array_filter(
            glob($dir . '/*.php'),
            fn($f) => basename($f) !== 'example.php',
        );
        sort($files);

        $rows = [];
        foreach ($files as $file) {
            $transition = require $file;

            if (!is_array($transition) || !isset($transition['version'])) {
                continue;
            }

            $version = (string) $transition['version'];
            $rec     = $records->get($version);

            $rows[] = [
                'version'     => $version,
                'description' => $transition['description'] ?? '',
                'file'        => basename($file),
                'status'      => $rec->status ?? 'pending',
                'executed_at' => isset($rec->executed_at)
                    ? Carbon::parse($rec->executed_at)->format('Y-m-d H:i')
                    : null,
                'error'       => $rec->error_message ?? null,
            ];
        }

        usort($rows, fn($a, $b) => strcmp($a['version'], $b['version']));

        return $rows;
    }
}
