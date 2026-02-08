<?php

namespace App\Portals\Hrm\Modules\Calendar;

use App\Helpers\Classes\OrmHelper;
use App\Models\Hrm\CalendarDay;
use App\Portals\Hrm\Core\Controllers\HrmController;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class CalendarDayController extends HrmController
{
    public function __construct(
        protected CalendarDayService $calendarService
    ) {
        parent::__construct();
    }

    protected function setBreadcrumbs(): void
    {
        $this->breadcrumbs = [
            (object)[
                'text' => 'HRM 首頁',
                'href' => route('hrm.dashboard'),
            ],
            (object)[
                'text' => '行事曆管理',
                'href' => route('hrm.calendar.index'),
            ],
        ];
    }

    /**
     * 列表頁
     */
    public function index(Request $request): Response
    {
        $query = CalendarDay::query();
        $filter_data = $request->all();

        // 預設排序
        $filter_data['sort'] = $request->get('sort', 'date');
        $filter_data['order'] = $request->get('order', 'desc');

        // 特殊處理：年份篩選（equal_year）
        if ($request->filled('equal_year')) {
            $query->whereYear('date', $request->input('equal_year'));
            unset($filter_data['equal_year']);
        }

        // 特殊處理：月份篩選（equal_month）
        if ($request->filled('equal_month')) {
            $query->whereMonth('date', $request->input('equal_month'));
            unset($filter_data['equal_month']);
        }

        // 搜尋：名稱（使用 filter_name）
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                OrmHelper::filterOrEqualColumn($q, 'filter_name', $search);
            });
            unset($filter_data['search'], $filter_data['filter_name']);
        }

        // OrmHelper 自動處理其他 filter_*, equal_* 及排序
        OrmHelper::prepare($query, $filter_data);

        // 分頁結果
        $calendars = OrmHelper::getResult($query, $filter_data);

        return Inertia::render('Calendar/Index', [
            'calendars' => $calendars,
            'filters' => $request->only(['equal_year', 'equal_month', 'equal_day_type', 'equal_is_workday', 'search', 'sort', 'order']),
            'breadcrumbs' => $this->breadcrumbs,
        ]);
    }

    /**
     * 新增表單
     */
    public function create(): Response
    {
        return Inertia::render('Calendar/Create', [
            'breadcrumbs' => $this->breadcrumbs,
            'dayTypes' => $this->getDayTypeOptions(),
        ]);
    }

    /**
     * 儲存新資料
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'date' => 'required|date|unique:hrm_calendar_days,date',
            'day_type' => ['required', 'string', Rule::in(['workday', 'weekend', 'holiday', 'company_holiday', 'makeup_workday', 'typhoon_day'])],
            'is_workday' => 'required|boolean',
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
        ]);

        $calendarDay = $this->calendarService->createCalendarDay($validated);

        return response()->json([
            'success' => true,
            'message' => '行事曆記錄新增成功',
            'data' => $calendarDay,
        ]);
    }

    /**
     * 查看單筆
     */
    public function show(CalendarDay $calendarDay): Response
    {
        return Inertia::render('Calendar/Show', [
            'calendarDay' => $calendarDay,
            'breadcrumbs' => $this->breadcrumbs,
        ]);
    }

    /**
     * 編輯表單
     */
    public function edit(CalendarDay $calendarDay): Response
    {
        return Inertia::render('Calendar/Edit', [
            'calendarDay' => $calendarDay,
            'breadcrumbs' => $this->breadcrumbs,
            'dayTypes' => $this->getDayTypeOptions(),
        ]);
    }

    /**
     * 更新資料
     */
    public function update(Request $request, CalendarDay $calendarDay): JsonResponse
    {
        $validated = $request->validate([
            'date' => 'required|date|unique:hrm_calendar_days,date,' . $calendarDay->id,
            'day_type' => ['required', 'string', Rule::in(['workday', 'weekend', 'holiday', 'company_holiday', 'makeup_workday', 'typhoon_day'])],
            'is_workday' => 'required|boolean',
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
        ]);

        $calendarDay = $this->calendarService->updateCalendarDay($calendarDay, $validated);

        return response()->json([
            'success' => true,
            'message' => '行事曆記錄更新成功',
            'data' => $calendarDay,
        ]);
    }

    /**
     * 刪除資料
     */
    public function destroy(CalendarDay $calendarDay): JsonResponse
    {
        $this->calendarService->deleteCalendarDay($calendarDay);

        return response()->json([
            'success' => true,
            'message' => '行事曆記錄刪除成功',
        ]);
    }

    /**
     * 批次刪除
     */
    public function batchDelete(Request $request): JsonResponse
    {
        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return response()->json([
                'success' => false,
                'message' => '請選擇要刪除的記錄',
            ], 422);
        }

        CalendarDay::whereIn('id', $ids)->delete();

        return response()->json([
            'success' => true,
            'message' => '批次刪除成功',
        ]);
    }

    /**
     * 批次建立工作日
     */
    public function batchCreate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'weekends' => 'nullable|array',
            'weekends.*' => 'integer|min:0|max:6',
        ]);

        $startDate = Carbon::parse($validated['start_date']);
        $endDate = Carbon::parse($validated['end_date']);
        $weekends = $validated['weekends'] ?? [0, 6]; // 預設週日、週六

        $createdCount = $this->calendarService->batchCreateWorkdays($startDate, $endDate, $weekends);

        return response()->json([
            'success' => true,
            'message' => "批次建立成功，共建立 {$createdCount} 筆記錄",
            'count' => $createdCount,
        ]);
    }

    /**
     * 匯入國定假日
     */
    public function importHolidays(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'holidays' => 'required|array',
            'holidays.*.date' => 'required|date',
            'holidays.*.name' => 'nullable|string|max:255',
        ]);

        $updatedCount = $this->calendarService->importHolidays($validated['holidays']);

        return response()->json([
            'success' => true,
            'message' => "匯入成功，共處理 {$updatedCount} 筆假日",
            'count' => $updatedCount,
        ]);
    }

    /**
     * 設定補班日
     */
    public function setMakeupWorkday(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'name' => 'nullable|string|max:255',
        ]);

        $calendarDay = $this->calendarService->setMakeupWorkday(
            $validated['date'],
            $validated['name'] ?? null
        );

        return response()->json([
            'success' => true,
            'message' => '補班日設定成功',
            'data' => $calendarDay,
        ]);
    }

    /**
     * 取得月曆資料
     */
    public function getMonth(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'year' => 'required|integer|min:2000|max:2100',
            'month' => 'required|integer|min:1|max:12',
        ]);

        $calendars = $this->calendarService->getCalendarByMonth(
            $validated['year'],
            $validated['month']
        );

        return response()->json([
            'success' => true,
            'data' => $calendars,
        ]);
    }

    /**
     * 取得日期類型選項
     */
    protected function getDayTypeOptions(): array
    {
        return [
            ['value' => 'workday', 'label' => '工作日'],
            ['value' => 'weekend', 'label' => '週末'],
            ['value' => 'holiday', 'label' => '國定假日'],
            ['value' => 'company_holiday', 'label' => '公司假日'],
            ['value' => 'makeup_workday', 'label' => '補班日'],
            ['value' => 'typhoon_day', 'label' => '颱風假'],
        ];
    }
}
