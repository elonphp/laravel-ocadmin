<?php

namespace App\Portals\Ocadmin\Core\Controllers;

use App\Helpers\Traits\HasBackUrl;
use App\Libraries\TranslationLibrary;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class OcadminController extends BaseController
{
    use HasBackUrl;

    protected $lang;

    public function __construct()
    {
        if (basename($_SERVER['SCRIPT_NAME'] ?? '') == 'artisan') {
            return;
        }

        $this->middleware(function ($request, $next) {
            $this->getLang($this->setLangFiles());
            return $next($request);
        });
    }

    /**
     * 語言檔列表，子類別覆寫以載入模組語言檔
     *
     * default 由 TranslationLibrary 自動載入，子類別只需指定模組語言檔
     */
    protected function setLangFiles(): array
    {
        return [];
    }

    /**
     * 載入語言檔
     */
    protected function getLang(string|array $groups): void
    {
        if (!isset($this->lang)) {
            $groups = (array) $groups;
            $groups = array_map(fn($g) => "admin/{$g}", $groups);
            $this->lang = app(TranslationLibrary::class)->load($groups, defaultGroup: 'admin/default');
        }
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

    /**
     * 取得篩選參數陣列，供分頁 appends 使用
     */
    protected function getFilterQueryParams(Request $request): array
    {
        return collect($request->all())
            ->filter(function ($v, $k) {
                if (! (str_starts_with($k, 'filter_') || str_starts_with($k, 'equal_') || $k === 'search')) {
                    return false;
                }
                if (is_array($v)) {
                    return true; // 陣列（如 equal_statuses[]）保留，即使空值（代表使用者刻意清空）
                }
                return $v !== null && $v !== '';
            })
            ->all();
    }
}
