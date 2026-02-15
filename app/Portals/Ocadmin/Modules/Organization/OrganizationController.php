<?php

namespace App\Portals\Ocadmin\Modules\Organization;

use App\Helpers\Classes\LocaleHelper;
use App\Helpers\Classes\OrmHelper;
use App\Models\Organization;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Portals\Ocadmin\Core\Controllers\OcadminController;

class OrganizationController extends OcadminController
{
    protected function setLangFiles(): array
    {
        return ['common', 'organization'];
    }

    protected function setBreadcrumbs(): void
    {
        $this->breadcrumbs = [
            (object)[
                'text' => $this->lang->text_home,
                'href' => route('lang.ocadmin.dashboard'),
            ],
            (object)[
                'text' => $this->lang->heading_title,
                'href' => route('lang.ocadmin.organization.index'),
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

        return view('ocadmin.organization::index', $data);
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
        $query = Organization::with('translations');
        $filter_data = $this->filterData($request, ['equal_is_active']);

        // 預設排序
        $filter_data['sort'] = $request->query('sort', 'id');
        $filter_data['order'] = $request->query('order', 'asc');

        // search 關鍵字查詢
        if ($request->filled('search')) {
            $search = $request->search;
            $locale = app()->getLocale();

            $query->where(function ($q) use ($search, $locale) {
                OrmHelper::filterOrEqualColumn($q, 'filter_business_no', $search);

                $q->orWhereHas('translations', function ($tq) use ($search, $locale) {
                    $tq->where('locale', $locale);
                    $tq->where(function ($sq) use ($search) {
                        OrmHelper::filterOrEqualColumn($sq, 'filter_name', $search);
                        $sq->orWhere(function ($sq2) use ($search) {
                            OrmHelper::filterOrEqualColumn($sq2, 'filter_short_name', $search);
                        });
                    });
                });
            });

            unset($filter_data['search'], $filter_data['filter_business_no'], $filter_data['filter_name'], $filter_data['filter_short_name']);
        }

        // OrmHelper 自動處理 filter_*, equal_* 及排序
        OrmHelper::prepare($query, $filter_data);

        // 分頁結果
        $organizations = OrmHelper::getResult($query, $filter_data);
        $organizations->withPath(route('lang.ocadmin.organization.list'));

        $data['lang'] = $this->lang;
        $data['organizations'] = $organizations;
        $data['pagination'] = $organizations->links('ocadmin::pagination.default');

        // 建構 URL 參數與排序連結
        $url = $this->buildUrlParams($request);
        $baseUrl = route('lang.ocadmin.organization.list');
        $data['sort'] = $filter_data['sort'];
        $data['order'] = $filter_data['order'];
        $nextOrder = ($data['order'] == 'asc') ? 'desc' : 'asc';

        $data['sort_name'] = $baseUrl . "?sort=name&order={$nextOrder}" . str_replace('?', '&', $url);
        $data['sort_business_no'] = $baseUrl . "?sort=business_no&order={$nextOrder}" . str_replace('?', '&', $url);

        return view('ocadmin.organization::list', $data)->render();
    }

    /**
     * 新增表單
     */
    public function create(): View
    {
        $data['lang'] = $this->lang;
        $data['breadcrumbs'] = $this->breadcrumbs;
        $data['organization'] = new Organization();

        return view('ocadmin.organization::form', $data);
    }

    /**
     * 儲存新資料
     */
    public function store(Request $request): JsonResponse
    {
        $rules = [
            'business_no' => 'nullable|string|max:20',
            'shipping_state' => 'nullable|string|max:255',
            'shipping_city' => 'nullable|string|max:255',
            'shipping_address1' => 'nullable|string|max:255',
            'shipping_address2' => 'nullable|string|max:255',
        ];

        foreach (LocaleHelper::getSupportedLocales() as $locale) {
            $rules["translations.{$locale}.name"] = 'required|string|max:200';
            $rules["translations.{$locale}.short_name"] = 'nullable|string|max:100';
        }

        $validated = $request->validate($rules);

        $organization = Organization::create($validated);
        $organization->saveTranslations($validated['translations']);

        return response()->json([
            'success' => true,
            'message' => $this->lang->text_success_add,
            'replace_url' => route('lang.ocadmin.organization.edit', $organization),
            'form_action' => route('lang.ocadmin.organization.update', $organization),
        ]);
    }

    /**
     * 編輯表單
     */
    public function edit(Organization $organization): View
    {
        $organization->load('translations');

        $data['lang'] = $this->lang;
        $data['breadcrumbs'] = $this->breadcrumbs;
        $data['organization'] = $organization;

        return view('ocadmin.organization::form', $data);
    }

    /**
     * 更新資料
     */
    public function update(Request $request, Organization $organization): JsonResponse
    {
        $rules = [
            'business_no' => 'nullable|string|max:20',
            'shipping_state' => 'nullable|string|max:255',
            'shipping_city' => 'nullable|string|max:255',
            'shipping_address1' => 'nullable|string|max:255',
            'shipping_address2' => 'nullable|string|max:255',
        ];

        foreach (LocaleHelper::getSupportedLocales() as $locale) {
            $rules["translations.{$locale}.name"] = 'required|string|max:200';
            $rules["translations.{$locale}.short_name"] = 'nullable|string|max:100';
        }

        $validated = $request->validate($rules);

        $organization->update($validated);
        $organization->saveTranslations($validated['translations']);

        return response()->json([
            'success' => true,
            'message' => $this->lang->text_success_edit,
        ]);
    }

    /**
     * 刪除資料
     */
    public function destroy(Organization $organization): JsonResponse
    {
        $organization->delete();

        return response()->json(['success' => true, 'message' => $this->lang->text_success_delete]);
    }

    /**
     * 批次刪除
     */
    public function batchDelete(Request $request): JsonResponse
    {
        $ids = $request->input('selected', []);

        if (empty($ids)) {
            return response()->json(['success' => false, 'message' => $this->lang->error_select_delete]);
        }

        Organization::whereIn('id', $ids)->delete();

        return response()->json(['success' => true, 'message' => $this->lang->text_success_delete]);
    }

}
