<?php

namespace App\Helpers\Traits;

use Illuminate\Http\Request;

/**
 * 列表查詢字串 ↔ URL 雙向同步：給 Controller 用來保留列表瀏覽狀態（filter / sort / order / page），
 * 進編輯 / 檢視 / 新增表單後按「返回」能回到原本的列表狀態。
 *
 * 命名慣例（與 OrmHelper / Ocadmin 對齊）：
 *   - filter_xxx : 模糊比對 (OrmHelper::filterOrEqualColumn)
 *   - equal_xxx  : 完全比對；多選用「逗號分隔」字串（如 equal_statuses=draft,confirmed）
 *   - search     : 多欄 OR 關鍵字
 *   - page / sort / order / limit : 分頁與排序
 *
 * 多選欄位的編碼策略：URL 一律輸出 `equal_xxx=v1,v2`（不用 `[]`）。
 * 接收端（Controller list/getList）若要保留向後相容，需同時接受 array（舊 form serialize
 * 出來的 `equal_statuses[]`）與 string（新版逗號分隔）。
 */
trait HasBackUrl
{
    /**
     * 將當前 request 的 filter_* / equal_* / search / limit 序列化為 query string（含 ? 前綴）。
     */
    protected function buildUrlParams(Request $request): string
    {
        $params = [];

        foreach ($request->all() as $key => $value) {
            if (! (str_starts_with($key, 'filter_') || str_starts_with($key, 'equal_'))) {
                continue;
            }
            if (is_array($value)) {
                $filtered = array_filter($value, fn ($v) => $v !== null && $v !== '');
                if (! empty($filtered)) {
                    $params[] = $key . '=' . urlencode(implode(',', $filtered));
                }
            } elseif ($value !== null && $value !== '') {
                $params[] = $key . '=' . urlencode($value);
            }
        }

        if ($request->filled('search')) {
            $params[] = 'search=' . urlencode($request->search);
        }

        if ($request->filled('limit')) {
            $params[] = 'limit=' . urlencode($request->limit);
        }

        return $params ? '?' . implode('&', $params) : '';
    }

    /**
     * 在 buildUrlParams 結果之上追加 sort / order / page，供 form 編輯頁的「返回」按鈕保留列表狀態。
     */
    protected function buildEditUrlParams(Request $request): string
    {
        $url = $this->buildUrlParams($request);

        $extra = [];
        if ($request->filled('sort')) {
            $extra[] = 'sort=' . urlencode($request->sort);
        }
        if ($request->filled('order')) {
            $extra[] = 'order=' . urlencode($request->order);
        }
        if ($request->filled('page') && (int) $request->page > 1) {
            $extra[] = 'page=' . (int) $request->page;
        }

        if (empty($extra)) {
            return $url;
        }
        $extraStr = implode('&', $extra);
        return $url ? $url . '&' . $extraStr : '?' . $extraStr;
    }

    /**
     * 給 edit / create / show form 用的「返回 URL」：route(name, params) + 列表當前查詢字串。
     *
     *     $data['back_url'] = $this->backUrl('lang.ocadmin.xxx.index', $request);
     */
    protected function backUrl(string $routeName, Request $request, array $routeParams = []): string
    {
        return route($routeName, $routeParams) . $this->buildEditUrlParams($request);
    }
}
