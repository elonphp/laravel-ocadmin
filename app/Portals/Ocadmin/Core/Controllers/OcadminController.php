<?php

namespace App\Portals\Ocadmin\Core\Controllers;

use App\Libraries\TranslationLibrary;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class OcadminController extends BaseController
{
    protected array $breadcrumbs = [];
    protected $lang;

    public function __construct()
    {
        if (basename($_SERVER['SCRIPT_NAME'] ?? '') == 'artisan') {
            return;
        }

        $this->middleware(function ($request, $next) {
            $this->getLang($this->setLangFiles());
            $this->setBreadcrumbs();
            return $next($request);
        });
    }

    /**
     * 語言檔列表，子類別覆寫以載入模組語言檔
     *
     * 後者覆蓋前者（common 先載入，模組語言檔覆蓋共用翻譯）
     */
    protected function setLangFiles(): array
    {
        return ['common'];
    }

    /**
     * 載入語言檔
     */
    protected function getLang(string|array $groups): void
    {
        if (!isset($this->lang)) {
            $this->lang = app(TranslationLibrary::class)->load($groups);
        }
    }

    protected function setBreadcrumbs(): void
    {
        // 由子類別覆寫
    }

    /**
     * 從 Request 取得白名單過濾參數
     *
     * 共用參數（search, sort, order, page, limit, per_page）自動允許，
     * 各 Controller 只需指定額外允許的 filter_* / equal_* 欄位。
     */
    protected function filterData(Request $request, array $allowedFilters = []): array
    {
        return $request->only(array_merge(
            ['search', 'sort', 'order', 'page', 'limit', 'per_page'],
            $allowedFilters
        ));
    }

    protected function buildUrlParams(Request $request): string
    {
        $params = [];

        foreach ($request->all() as $key => $value) {
            if (str_starts_with($key, 'filter_') || str_starts_with($key, 'equal_')) {
                if ($value !== null && $value !== '') {
                    $params[] = $key . '=' . urlencode($value);
                }
            }
        }

        if ($request->has('search') && $request->search) {
            $params[] = 'search=' . urlencode($request->search);
        }

        if ($request->has('limit') && $request->limit) {
            $params[] = 'limit=' . $request->limit;
        }

        if ($request->has('page') && $request->page) {
            $params[] = 'page=' . $request->page;
        }

        if ($request->has('sort') && $request->sort) {
            $params[] = 'sort=' . urlencode($request->sort);
        }

        if ($request->has('order') && $request->order) {
            $params[] = 'order=' . urlencode($request->order);
        }

        return $params ? '?' . implode('&', $params) : '';
    }
}
