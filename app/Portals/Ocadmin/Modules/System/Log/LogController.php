<?php

namespace App\Portals\Ocadmin\Modules\System\Log;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Carbon\Carbon;
use App\Portals\Ocadmin\Core\Controllers\Controller;

class LogController extends Controller
{
    public function __construct(
        private LogService $logService
    ) {
        parent::__construct();
    }

    protected function setBreadcrumbs(): void
    {
        $this->breadcrumbs = [
            (object)[
                'text' => '首頁',
                'href' => route('lang.ocadmin.dashboard'),
            ],
            (object)[
                'text' => '系統管理',
                'href' => 'javascript:void(0)',
            ],
            (object)[
                'text' => '系統日誌',
                'href' => 'javascript:void(0)',
            ],
        ];
    }

    /**
     * 資料庫日誌主頁面
     */
    public function database(Request $request)
    {
        $filterDate = $request->get('filter_date', '');
        $filterMethod = $request->get('filter_method', '');
        $filterStatus = $request->get('filter_status', '');
        $filterKeyword = $request->get('filter_keyword', '');

        $list = $this->getDatabaseList($request);

        return view('ocadmin.system.log::database', [
            'filter_date' => $filterDate,
            'filter_method' => $filterMethod,
            'filter_status' => $filterStatus,
            'filter_keyword' => $filterKeyword,
            'list' => $list,
            'list_url' => route('lang.ocadmin.system.log.database.list'),
            'breadcrumbs' => $this->breadcrumbs,
        ]);
    }

    /**
     * 資料庫日誌列表（AJAX）
     */
    public function databaseList(Request $request)
    {
        return $this->getDatabaseList($request);
    }

    /**
     * 取得資料庫日誌列表
     */
    private function getDatabaseList(Request $request)
    {
        $filters = [
            'date' => $request->get('filter_date', ''),
            'method' => $request->get('filter_method', ''),
            'status' => $request->get('filter_status', ''),
            'keyword' => $request->get('filter_keyword', ''),
        ];
        $perPage = (int) $request->get('limit', 50);

        $logs = $this->logService->getDatabaseLogs($filters, $perPage);

        return view('ocadmin.system.log::database_list', [
            'logs' => $logs,
            'filters' => $filters,
        ]);
    }

    /**
     * 資料庫日誌詳情
     */
    public function databaseForm(Request $request)
    {
        $id = $request->get('id');

        if (!$id) {
            return response()->json(['error' => '參數錯誤'], 400);
        }

        $log = $this->logService->getDatabaseLog((int)$id);

        if (!$log) {
            return response()->json(['error' => '找不到日誌'], 404);
        }

        return view('ocadmin.system.log::database_form', [
            'log' => $log,
            'breadcrumbs' => $this->breadcrumbs,
        ]);
    }

    /**
     * 歷史壓縮檔列表
     */
    public function archived(Request $request)
    {
        $files = $this->logService->getArchivedFiles();
        $selectedFile = $request->get('file');
        $fileContents = null;

        if ($selectedFile) {
            $fileContents = $this->logService->readArchivedFile($selectedFile);
        }

        return view('ocadmin.system.log::archived', [
            'files' => $files,
            'selectedFile' => $selectedFile,
            'fileContents' => $fileContents,
            'breadcrumbs' => $this->breadcrumbs,
        ]);
    }

    /**
     * 下載壓縮檔
     */
    public function archivedDownload(string $filename)
    {
        $path = $this->logService->getArchivedFilePath($filename);

        if (!$path) {
            abort(404, '找不到檔案');
        }

        return response()->download($path);
    }

    /**
     * 查看壓縮檔內的日誌
     */
    public function archivedView(Request $request, string $filename)
    {
        $logFile = $request->get('log_file');

        if (!$logFile) {
            return response()->json(['error' => '請指定日誌檔案'], 400);
        }

        $result = $this->logService->readLogsFromArchive($filename, $logFile);

        if (!$result['success']) {
            return response()->json(['error' => $result['message']], 404);
        }

        return view('ocadmin.system.log::archived_view', [
            'logs' => $result['logs'],
            'filename' => $filename,
            'logFile' => $logFile,
            'breadcrumbs' => $this->breadcrumbs,
        ]);
    }

    /**
     * 排程的程式頁面
     */
    public function scheduler()
    {
        $schedulerInfo = $this->logService->getSchedulerInfo();

        return view('ocadmin.system.log::scheduler', [
            'schedulerInfo' => $schedulerInfo,
            'breadcrumbs' => $this->breadcrumbs,
        ]);
    }

    /**
     * 手動執行排程指令
     */
    public function schedulerRun(Request $request, string $command)
    {
        $allowedCommands = ['logs:archive', 'logs:cleanup', 'app:delete-file-logs'];

        if (!in_array($command, $allowedCommands)) {
            return response()->json([
                'success' => false,
                'message' => '不允許的指令',
            ], 400);
        }

        try {
            Artisan::call($command);
            $output = Artisan::output();

            return response()->json([
                'success' => true,
                'message' => '指令執行完成',
                'output' => $output,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '執行失敗：' . $e->getMessage(),
            ], 500);
        }
    }
}
