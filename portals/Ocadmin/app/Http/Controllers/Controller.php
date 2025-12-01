<?php

namespace Portals\Ocadmin\Http\Controllers;

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
}
