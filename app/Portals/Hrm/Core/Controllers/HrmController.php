<?php

namespace App\Portals\Hrm\Core\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class HrmController extends BaseController
{
    protected array $breadcrumbs = [];

    public function __construct()
    {
        if (basename($_SERVER['SCRIPT_NAME'] ?? '') == 'artisan') {
            return;
        }

        $this->middleware(function ($request, $next) {
            $this->setBreadcrumbs();
            return $next($request);
        });
    }

    /**
     * 設定麵包屑導航（由子類別覆寫）
     */
    protected function setBreadcrumbs(): void
    {
        // 由子類別覆寫
    }

    /**
     * 建構 URL 參數字串（用於分頁和排序）
     */
    protected function buildUrlParams(Request $request): string
    {
        $params = $request->except(['page', 'sort', 'order']);

        if (empty($params)) {
            return '';
        }

        return '?' . http_build_query($params);
    }

    /**
     * 取得分頁大小
     */
    protected function getPerPage(Request $request): int
    {
        return (int) $request->get('per_page', 20);
    }
}
