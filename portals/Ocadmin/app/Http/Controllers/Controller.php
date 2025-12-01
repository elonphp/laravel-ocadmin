<?php

namespace Portals\Ocadmin\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use App\Libraries\TranslationLibrary;
use App\Libraries\TranslationData;

class Controller extends BaseController
{
    protected array $breadcrumbs = [];
    protected ?TranslationData $lang = null;

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
     * 載入多語言檔案
     *
     * 使用方式：
     *   $this->getLang(['common', 'system/localization/country']);
     *
     * 路徑格式（使用 / 分隔子目錄）：
     *   - 'common'                      → lang/zh_Hant/common.php
     *   - 'system/localization/country' → lang/zh_Hant/system/localization/country.php
     *
     * Cascading Override：後載入的語言檔覆蓋前面的同名 key
     *
     * 注意：需搭配 LocaleHelper::setLocale() 在路由定義階段設定 locale，
     *       否則在 __construct() 時 locale 可能還是預設值。
     *
     * @param string|array $paths 語言檔路徑
     * @return TranslationData
     */
    protected function getLang(string|array $paths): TranslationData
    {
        if ($this->lang === null) {
            $this->lang = (new TranslationLibrary())->getLang($paths);
        }

        return $this->lang;
    }

    protected function setBreadcrumbs(): void
    {
        // 由子類別覆寫
    }

    /**
     * 輔助方法：建構 URL 參數字串（OpenCart 風格）
     *
     * 收集 filter_*、equal_*、search、分頁、排序等參數，
     * 組成 URL query string。
     *
     * 子類別可覆寫此方法以處理特殊參數。
     */
    protected function buildUrlParams(Request $request): string
    {
        $params = [];

        // 收集所有 filter_* 和 equal_* 參數
        foreach ($request->all() as $key => $value) {
            if (str_starts_with($key, 'filter_') || str_starts_with($key, 'equal_')) {
                if ($value !== null && $value !== '') {
                    $params[] = $key . '=' . urlencode($value);
                }
            }
        }

        // 關鍵字查詢
        if ($request->has('search') && $request->search) {
            $params[] = 'search=' . urlencode($request->search);
        }

        // 分頁參數
        if ($request->has('limit') && $request->limit) {
            $params[] = 'limit=' . $request->limit;
        }

        if ($request->has('page') && $request->page) {
            $params[] = 'page=' . $request->page;
        }

        // 排序參數
        if ($request->has('sort') && $request->sort) {
            $params[] = 'sort=' . urlencode($request->sort);
        }

        if ($request->has('order') && $request->order) {
            $params[] = 'order=' . urlencode($request->order);
        }

        return $params ? '?' . implode('&', $params) : '';
    }
}
