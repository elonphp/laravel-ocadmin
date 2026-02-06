<?php

namespace App\Portals\Ocadmin\Modules\Corp\Company;

use App\Helpers\Classes\LocaleHelper;
use App\Helpers\Classes\OrmHelper;
use App\Models\Company;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Portals\Ocadmin\Core\Controllers\OcadminController;

class CompanyController extends OcadminController
{
    protected function setLangFiles(): array
    {
        return ['common', 'corp/company'];
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
                'href' => route('lang.ocadmin.corp.company.index'),
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

        return view('ocadmin.corp.company::index', $data);
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
        $query = Company::with(['translations', 'parent.translations']);
        $filter_data = $request->all();

        // 預設排序
        $filter_data['sort'] = $request->get('sort', 'sort_order');
        $filter_data['order'] = $request->get('order', 'asc');

        // search 關鍵字查詢
        if ($request->filled('search')) {
            $search = $request->search;
            $locale = app()->getLocale();

            $query->where(function ($q) use ($search, $locale) {
                OrmHelper::filterOrEqualColumn($q, 'filter_code', $search);

                $q->orWhere(function ($q2) use ($search) {
                    OrmHelper::filterOrEqualColumn($q2, 'filter_business_no', $search);
                });

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

            unset(
                $filter_data['search'],
                $filter_data['filter_code'],
                $filter_data['filter_business_no'],
                $filter_data['filter_name'],
                $filter_data['filter_short_name']
            );
        }

        // OrmHelper 自動處理 filter_*, equal_* 及排序
        OrmHelper::prepare($query, $filter_data);

        // 分頁結果
        $companies = OrmHelper::getResult($query, $filter_data);
        $companies->withPath(route('lang.ocadmin.corp.company.list'));

        $data['lang'] = $this->lang;
        $data['companies'] = $companies;
        $data['pagination'] = $companies->links('ocadmin::pagination.default');

        // 建構 URL 參數與排序連結
        $url = $this->buildUrlParams($request);
        $baseUrl = route('lang.ocadmin.corp.company.list');
        $data['sort'] = $filter_data['sort'];
        $data['order'] = $filter_data['order'];
        $nextOrder = ($data['order'] == 'asc') ? 'desc' : 'asc';

        $data['sort_name'] = $baseUrl . "?sort=name&order={$nextOrder}" . str_replace('?', '&', $url);
        $data['sort_code'] = $baseUrl . "?sort=code&order={$nextOrder}" . str_replace('?', '&', $url);
        $data['sort_sort_order'] = $baseUrl . "?sort=sort_order&order={$nextOrder}" . str_replace('?', '&', $url);

        return view('ocadmin.corp.company::list', $data)->render();
    }

    /**
     * 新增表單
     */
    public function create(): View
    {
        $data['lang'] = $this->lang;
        $data['breadcrumbs'] = $this->breadcrumbs;
        $data['company'] = new Company();
        $data['parentOptions'] = $this->getParentOptions();

        return view('ocadmin.corp.company::form', $data);
    }

    /**
     * 儲存新資料
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate($this->validationRules());

        $company = Company::create($validated);
        $company->saveTranslations($validated['translations']);

        return response()->json([
            'success' => true,
            'message' => $this->lang->text_success_add,
            'replace_url' => route('lang.ocadmin.corp.company.edit', $company),
            'form_action' => route('lang.ocadmin.corp.company.update', $company),
        ]);
    }

    /**
     * 編輯表單
     */
    public function edit(Company $company): View
    {
        $company->load('translations');

        $data['lang'] = $this->lang;
        $data['breadcrumbs'] = $this->breadcrumbs;
        $data['company'] = $company;
        $data['parentOptions'] = $this->getParentOptions($company->id);

        return view('ocadmin.corp.company::form', $data);
    }

    /**
     * 更新資料
     */
    public function update(Request $request, Company $company): JsonResponse
    {
        $validated = $request->validate($this->validationRules($company->id));

        $company->update($validated);
        $company->saveTranslations($validated['translations']);

        return response()->json([
            'success' => true,
            'message' => $this->lang->text_success_edit,
        ]);
    }

    /**
     * 刪除資料
     */
    public function destroy(Company $company): JsonResponse
    {
        $company->delete();

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

        Company::whereIn('id', $ids)->delete();

        return response()->json(['success' => true, 'message' => $this->lang->text_success_delete]);
    }

    /**
     * 驗證規則
     */
    protected function validationRules(?int $companyId = null): array
    {
        $rules = [
            'parent_id'   => 'nullable|exists:companies,id',
            'code'        => 'nullable|string|max:20|unique:companies,code' . ($companyId ? ",{$companyId}" : ''),
            'business_no' => 'nullable|string|max:20',
            'phone'       => 'nullable|string|max:30',
            'address'     => 'nullable|string|max:255',
            'is_active'   => 'required|boolean',
            'sort_order'  => 'required|integer|min:0',
        ];

        foreach (LocaleHelper::getSupportedLocales() as $locale) {
            $rules["translations.{$locale}.name"] = 'required|string|max:200';
            $rules["translations.{$locale}.short_name"] = 'nullable|string|max:100';
        }

        return $rules;
    }

    /**
     * 取得可選的上層公司列表（排除自己及子孫）
     */
    protected function getParentOptions(?int $excludeId = null): array
    {
        $query = Company::with('translations');

        if ($excludeId) {
            $descendantIds = $this->getDescendantIds($excludeId);
            $descendantIds[] = $excludeId;
            $query->whereNotIn('id', $descendantIds);
        }

        return $query->orderBy('sort_order')->get()->map(function ($c) {
            return (object)[
                'id'   => $c->id,
                'name' => $c->name,
            ];
        })->toArray();
    }

    /**
     * 遞迴取得所有子孫 ID
     */
    protected function getDescendantIds(int $parentId): array
    {
        $ids = [];
        $children = Company::where('parent_id', $parentId)->pluck('id');

        foreach ($children as $childId) {
            $ids[] = $childId;
            $ids = array_merge($ids, $this->getDescendantIds($childId));
        }

        return $ids;
    }

}
