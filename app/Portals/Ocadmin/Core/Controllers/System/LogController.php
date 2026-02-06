<?php

namespace App\Portals\Ocadmin\Core\Controllers\System;

use App\Helpers\Classes\OrmHelper;
use App\Models\System\RequestLog;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Portals\Ocadmin\Core\Controllers\OcadminController;

class LogController extends OcadminController
{
    protected function setLangFiles(): array
    {
        return ['common', 'system/log'];
    }

    protected function setBreadcrumbs(): void
    {
        $this->breadcrumbs = [
            (object)[
                'text' => $this->lang->text_home,
                'href' => route('lang.ocadmin.dashboard'),
            ],
            (object)[
                'text' => $this->lang->text_system,
                'href' => 'javascript:void(0)',
            ],
            (object)[
                'text' => $this->lang->heading_title,
                'href' => route('lang.ocadmin.system.log.index'),
            ],
        ];
    }

    /**
     * 列表頁（初始載入）
     */
    public function index(Request $request): View
    {
        $data['lang'] = $this->lang;
        $data['breadcrumbs'] = $this->breadcrumbs;
        $data['list'] = $this->getList($request);

        // 篩選選項
        $data['portals'] = ['ocadmin', 'ess'];
        $data['methods'] = ['POST', 'PUT', 'DELETE', 'PATCH'];
        $data['statuses'] = ['success', 'warning', 'error'];

        return view('ocadmin::system.log.index', $data);
    }

    /**
     * AJAX 入口（列表刷新）
     */
    public function list(Request $request): string
    {
        return $this->getList($request);
    }

    /**
     * 核心查詢邏輯
     */
    protected function getList(Request $request): string
    {
        $query = RequestLog::where('app_name', config('app.name'));
        $filter_data = $request->all();

        // 預設排序
        $filter_data['sort'] = $request->get('sort', 'created_at');
        $filter_data['order'] = $request->get('order', 'desc');

        // 關鍵字搜尋（URL 或備註）
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('url', 'like', "%{$search}%")
                  ->orWhere('note', 'like', "%{$search}%");
            });
            unset($filter_data['search']);
        }

        // 日期範圍
        if ($request->filled('filter_date_start')) {
            $query->where('created_at', '>=', $request->filter_date_start . ' 00:00:00');
            unset($filter_data['filter_date_start']);
        }
        if ($request->filled('filter_date_end')) {
            $query->where('created_at', '<=', $request->filter_date_end . ' 23:59:59');
            unset($filter_data['filter_date_end']);
        }

        // OrmHelper 自動處理 equal_* 及排序
        OrmHelper::prepare($query, $filter_data);

        // 分頁結果
        $logs = OrmHelper::getResult($query, $filter_data);
        $logs->withPath(route('lang.ocadmin.system.log.list'));

        $data['lang'] = $this->lang;
        $data['logs'] = $logs;
        $data['pagination'] = $logs->links('ocadmin::pagination.default');

        // 建構 URL 參數與排序連結
        $url = $this->buildUrlParams($request);
        $baseUrl = route('lang.ocadmin.system.log.list');
        $data['sort'] = $filter_data['sort'];
        $data['order'] = $filter_data['order'];
        $nextOrder = ($data['order'] == 'asc') ? 'desc' : 'asc';

        $data['sort_created_at'] = $baseUrl . "?sort=created_at&order={$nextOrder}" . str_replace('?', '&', $url);
        $data['sort_method'] = $baseUrl . "?sort=method&order={$nextOrder}" . str_replace('?', '&', $url);
        $data['sort_status_code'] = $baseUrl . "?sort=status_code&order={$nextOrder}" . str_replace('?', '&', $url);

        return view('ocadmin::system.log.list', $data)->render();
    }

    /**
     * 詳情頁（唯讀）
     */
    public function form(RequestLog $requestLog): View
    {
        $data['lang'] = $this->lang;
        $data['breadcrumbs'] = $this->breadcrumbs;
        $data['log'] = $requestLog;

        return view('ocadmin::system.log.form', $data);
    }
}
