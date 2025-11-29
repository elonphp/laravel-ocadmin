<?php

namespace Portals\Ocadmin\Http\Controllers\System;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Repositories\LogFileRepository;
use Carbon\Carbon;

class LogController extends Controller
{
    public function __construct(
        private LogFileRepository $logFileRepository
    ) {
    }

    /**
     * 日誌主頁面
     */
    public function index(Request $request)
    {
        // 初始化篩選參數
        $filterDate = $request->get('filter_date', Carbon::today()->format('Y-m-d'));
        $filterMethod = $request->get('filter_method', '');
        $filterStatus = $request->get('filter_status', '');
        $filterKeyword = $request->get('filter_keyword', '');

        $list = $this->getList($request);

        return view('ocadmin::system.log.index', [
            'filter_date' => $filterDate,
            'filter_method' => $filterMethod,
            'filter_status' => $filterStatus,
            'filter_keyword' => $filterKeyword,
            'list' => $list,
            'list_url' => route('ocadmin.system.log.list'),
        ]);
    }

    /**
     * 日誌列表（AJAX）
     */
    public function list(Request $request)
    {
        return $this->getList($request);
    }

    /**
     * 取得日誌列表
     */
    private function getList(Request $request)
    {
        // 取得篩選參數
        $date = $request->get('filter_date', Carbon::today()->format('Y-m-d'));
        $method = $request->get('filter_method', '');
        $status = $request->get('filter_status', '');
        $keyword = $request->get('filter_keyword', '');
        $page = (int)$request->get('page', 1);
        $limit = (int)$request->get('limit', 50);
        $sort = $request->get('sort', 'time');
        $order = $request->get('order', 'desc');

        // 讀取日誌
        $result = $this->logFileRepository->readLogsByDate($date, 0);

        $logs = [];
        $total = 0;

        if ($result['success']) {
            $allLogs = $result['logs'];

            // 篩選
            if ($method || $status || $keyword) {
                $allLogs = array_filter($allLogs, function($log) use ($method, $status, $keyword) {
                    // Method 篩選
                    $matchMethod = !$method || ($log['method'] ?? '') === $method;

                    // 狀態篩選
                    $matchStatus = true;
                    if ($status) {
                        $logStatus = $log['status'] ?? '';
                        if ($status === 'empty') {
                            $matchStatus = empty($logStatus);
                        } else {
                            $matchStatus = $logStatus === $status;
                        }
                    }

                    // 關鍵字篩選
                    $matchKeyword = !$keyword || (
                        stripos(json_encode($log, JSON_UNESCAPED_UNICODE), $keyword) !== false
                    );

                    return $matchMethod && $matchStatus && $matchKeyword;
                });
            }

            // 排序
            if ($sort === 'time') {
                usort($allLogs, function($a, $b) use ($order) {
                    $timeA = $a['timestamp'] ?? '';
                    $timeB = $b['timestamp'] ?? '';

                    $result = strcmp($timeA, $timeB);

                    return $order === 'desc' ? -$result : $result;
                });
            }

            $total = count($allLogs);

            // 分頁
            $offset = ($page - 1) * $limit;
            $logs = array_slice($allLogs, $offset, $limit);
        }

        // 格式化日誌顯示
        foreach ($logs as &$log) {
            // 格式化時間
            if (isset($log['timestamp'])) {
                try {
                    $log['formatted_time'] = Carbon::parse($log['timestamp'])->format('H:i:s');
                } catch (\Exception $e) {
                    $log['formatted_time'] = '';
                }
            }

            // 簡短顯示 URL
            if (isset($log['url'])) {
                $log['short_url'] = mb_strlen($log['url']) > 60
                    ? mb_substr($log['url'], 0, 60) . '...'
                    : $log['url'];
            }

            // 簡短顯示 note
            if (isset($log['note'])) {
                $log['short_note'] = mb_strlen($log['note']) > 100
                    ? mb_substr($log['note'], 0, 100) . '...'
                    : $log['note'];
            }
        }

        // 分頁 URL
        $queryData = [
            'filter_date' => $date,
            'filter_method' => $method,
            'filter_status' => $status,
            'filter_keyword' => $keyword,
            'limit' => $limit,
            'sort' => $sort,
            'order' => $order,
        ];

        return view('ocadmin::system.log.list', [
            'logs' => $logs,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'total_pages' => $limit > 0 ? ceil($total / $limit) : 0,
            'sort' => $sort,
            'order' => $order,
            'pagination_url' => route('ocadmin.system.log.list') . '?' . http_build_query($queryData),
            'list_url' => route('ocadmin.system.log.list'),
        ]);
    }

    /**
     * 日誌詳情
     */
    public function form(Request $request)
    {
        $date = $request->get('date');
        $traceId = $request->get('trace_id');

        if (!$date || !$traceId) {
            return response()->json(['error' => '參數錯誤'], 400);
        }

        // 讀取日誌
        $result = $this->logFileRepository->readLogsByDate($date, 0);

        $log = null;
        if ($result['success']) {
            foreach ($result['logs'] as $item) {
                if (($item['request_trace_id'] ?? '') === $traceId) {
                    $log = $item;
                    break;
                }
            }
        }

        if (!$log) {
            return response()->json(['error' => '找不到日誌'], 404);
        }

        return view('ocadmin::system.log.form', [
            'log' => $log,
            'log_json' => json_encode($log, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
        ]);
    }

    /**
     * 取得可用的日誌檔案列表
     */
    public function files()
    {
        $files = $this->logFileRepository->listLogFiles();

        return response()->json([
            'success' => true,
            'files' => $files,
        ]);
    }
}
