<?php

namespace App\Portals\Ocadmin\Modules\Hrm\CalendarDay;

use App\Helpers\Classes\OrmHelper;
use App\Models\Hrm\CalendarDay;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Portals\Ocadmin\Core\Controllers\OcadminController;

class CalendarDayController extends OcadminController
{
    protected function setLangFiles(): array
    {
        return ['common', 'hrm/calendar_day'];
    }

    protected function setBreadcrumbs(): void
    {
        $this->breadcrumbs = [
            (object)[
                'text' => $this->lang->text_home,
                'href' => route('lang.ocadmin.dashboard'),
            ],
            (object)[
                'text' => $this->lang->text_hrm,
                'href' => 'javascript:void(0)',
            ],
            (object)[
                'text' => $this->lang->heading_title,
                'href' => route('lang.ocadmin.hrm.calendar-day.index'),
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

        return view('ocadmin.hrm.calendarday::index', $data);
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
        $query = CalendarDay::query();
        $filter_data = $request->all();

        // 預設排序
        $filter_data['sort'] = $request->get('sort', 'date');
        $filter_data['order'] = $request->get('order', 'desc');

        // search 關鍵字查詢
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                OrmHelper::filterOrEqualColumn($q, 'filter_name', $search);
                $q->orWhere(function ($q2) use ($search) {
                    OrmHelper::filterOrEqualColumn($q2, 'filter_description', $search);
                });
            });

            unset(
                $filter_data['search'],
                $filter_data['filter_name'],
                $filter_data['filter_description']
            );
        }

        // 年份、月份特殊處理（查詢 DATE 欄位）
        if ($request->filled('equal_year')) {
            $query->whereYear('date', $request->input('equal_year'));
            unset($filter_data['equal_year']);
        }
        if ($request->filled('equal_month')) {
            $query->whereMonth('date', $request->input('equal_month'));
            unset($filter_data['equal_month']);
        }

        // OrmHelper 自動處理 filter_*, equal_* 及排序
        OrmHelper::prepare($query, $filter_data);

        // 分頁結果
        $calendarDays = OrmHelper::getResult($query, $filter_data);
        $calendarDays->withPath(route('lang.ocadmin.hrm.calendar-day.list'));

        $data['lang'] = $this->lang;
        $data['calendarDays'] = $calendarDays;
        $data['pagination'] = $calendarDays->links('ocadmin::pagination.default');

        // 建構 URL 參數與排序連結
        $url = $this->buildUrlParams($request);
        $baseUrl = route('lang.ocadmin.hrm.calendar-day.list');
        $data['sort'] = $filter_data['sort'];
        $data['order'] = $filter_data['order'];
        $nextOrder = ($data['order'] == 'asc') ? 'desc' : 'asc';

        $data['sort_date'] = $baseUrl . "?sort=date&order={$nextOrder}" . str_replace('?', '&', $url);
        $data['sort_day_type'] = $baseUrl . "?sort=day_type&order={$nextOrder}" . str_replace('?', '&', $url);
        $data['sort_name'] = $baseUrl . "?sort=name&order={$nextOrder}" . str_replace('?', '&', $url);

        // 日期類型選項
        $data['dayTypeOptions'] = $this->getDayTypeOptions();

        return view('ocadmin.hrm.calendarday::list', $data)->render();
    }

    /**
     * 新增表單
     */
    public function create(): View
    {
        $data['lang'] = $this->lang;
        $data['breadcrumbs'] = $this->breadcrumbs;
        $data['calendarDay'] = new CalendarDay();
        $data['dayTypeOptions'] = $this->getDayTypeOptions();

        return view('ocadmin.hrm.calendarday::form', $data);
    }

    /**
     * 儲存新資料
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'date' => 'required|date|unique:hrm_calendar_days,date',
            'day_type' => 'required|string|in:workday,weekend,holiday,company_holiday,makeup_workday,typhoon_day',
            'is_workday' => 'boolean',
            'name' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:20',
        ]);

        $validated['is_workday'] = $request->has('is_workday');

        $calendarDay = CalendarDay::create($validated);

        return response()->json([
            'success' => true,
            'message' => $this->lang->text_success_add,
            'replace_url' => route('lang.ocadmin.hrm.calendar-day.edit', $calendarDay),
            'form_action' => route('lang.ocadmin.hrm.calendar-day.update', $calendarDay),
        ]);
    }

    /**
     * 編輯表單
     */
    public function edit(CalendarDay $calendarDay): View
    {
        $data['lang'] = $this->lang;
        $data['breadcrumbs'] = $this->breadcrumbs;
        $data['calendarDay'] = $calendarDay;
        $data['dayTypeOptions'] = $this->getDayTypeOptions();

        return view('ocadmin.hrm.calendarday::form', $data);
    }

    /**
     * 更新資料
     */
    public function update(Request $request, CalendarDay $calendarDay): JsonResponse
    {
        $validated = $request->validate([
            'date' => 'required|date|unique:hrm_calendar_days,date,' . $calendarDay->id,
            'day_type' => 'required|string|in:workday,weekend,holiday,company_holiday,makeup_workday,typhoon_day',
            'is_workday' => 'boolean',
            'name' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:20',
        ]);

        $validated['is_workday'] = $request->has('is_workday');

        $calendarDay->update($validated);

        return response()->json([
            'success' => true,
            'message' => $this->lang->text_success_edit,
        ]);
    }

    /**
     * 刪除資料
     */
    public function destroy(CalendarDay $calendarDay): JsonResponse
    {
        $calendarDay->delete();

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

        CalendarDay::whereIn('id', $ids)->delete();

        return response()->json(['success' => true, 'message' => $this->lang->text_success_delete]);
    }

    /**
     * 取得日期類型選項
     */
    protected function getDayTypeOptions(): array
    {
        return [
            'workday' => $this->lang->option_workday ?? '工作日',
            'weekend' => $this->lang->option_weekend ?? '週末',
            'holiday' => $this->lang->option_holiday ?? '國定假日',
            'company_holiday' => $this->lang->option_company_holiday ?? '公司假日',
            'makeup_workday' => $this->lang->option_makeup_workday ?? '補班日',
            'typhoon_day' => $this->lang->option_typhoon_day ?? '颱風假',
        ];
    }
}
