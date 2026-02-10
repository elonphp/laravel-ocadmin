<?php

namespace App\Portals\Hrm\Modules\MonthlySummary;

use App\Models\Hrm\CalendarDay;
use App\Models\Hrm\DailyAttendance;
use App\Models\Hrm\MonthlySummary;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * 每月出勤統計服務
 *
 * 從 DailyAttendance + CalendarDay 彙總到 MonthlySummary
 */
class MonthlySummaryService
{
    /**
     * 驗證並格式化年月
     *
     * @param string $yearMonth 格式: 'YYYYMM' 或 'YYYY-MM'
     * @return string 格式: 'YYYY-MM'
     * @throws \InvalidArgumentException
     */
    public function getValidYearMonth(string $yearMonth): string
    {
        if (strlen($yearMonth) === 6) {
            $year = substr($yearMonth, 0, 4);
            $month = substr($yearMonth, 4, 2);
        } elseif (strlen($yearMonth) === 7 && str_contains($yearMonth, '-')) {
            [$year, $month] = explode('-', $yearMonth);
        } else {
            throw new \InvalidArgumentException("年月格式錯誤，應為 YYYYMM 或 YYYY-MM，例如：202602 或 2026-02");
        }

        if ((int) $month < 1 || (int) $month > 12) {
            throw new \InvalidArgumentException("月份必須在 1-12 之間，收到：{$month}");
        }

        return sprintf('%04d-%02d', (int) $year, (int) $month);
    }

    /**
     * 計算所有員工指定月份的月報
     *
     * @param string $yearMonth 格式: 'YYYYMM' 或 'YYYY-MM'
     * @return int 處理的員工數
     */
    public function calculateAll(string $yearMonth): int
    {
        $formattedYearMonth = $this->getValidYearMonth($yearMonth);
        [$year, $month] = explode('-', $formattedYearMonth);

        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth()->toDateString();
        $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth()->toDateString();

        // 從 CalendarDay 取得當月應出勤天數
        $scheduledWorkdays = CalendarDay::whereBetween('date', [$startDate, $endDate])
            ->where('is_workday', true)
            ->count();

        // 從 DailyAttendance JOIN CalendarDay 彙總每位員工的月統計
        $data = DailyAttendance::from('hrm_daily_attendances as da')
            ->selectRaw('
                da.employee_id,
                COUNT(CASE WHEN da.is_absent = 0 AND da.work_minutes > 0 THEN 1 END) AS actual_workdays,
                COUNT(CASE WHEN da.is_absent = 1 THEN 1 END) AS absent_days,
                COUNT(CASE WHEN cd.is_workday = 0 AND da.work_minutes > 0 THEN 1 END) AS holiday_workdays,
                COALESCE(SUM(da.scheduled_minutes), 0) AS scheduled_minutes,
                COALESCE(SUM(da.work_minutes), 0) AS work_minutes,
                COALESCE(SUM(da.overtime_minutes), 0) AS overtime_minutes,
                COALESCE(SUM(CASE WHEN cd.is_workday = 1 THEN da.overtime_minutes ELSE 0 END), 0) AS weekday_overtime_minutes,
                COALESCE(SUM(CASE WHEN cd.is_workday = 0 THEN da.overtime_minutes ELSE 0 END), 0) AS holiday_overtime_minutes,
                COUNT(CASE WHEN da.is_late = 1 THEN 1 END) AS late_count,
                COALESCE(SUM(da.late_minutes), 0) AS late_minutes,
                COUNT(CASE WHEN da.is_early_leave = 1 THEN 1 END) AS early_leave_count,
                COALESCE(SUM(da.early_leave_minutes), 0) AS early_leave_minutes
            ')
            ->leftJoin('hrm_calendar_days as cd', 'da.work_date', '=', 'cd.date')
            ->whereBetween('da.work_date', [$startDate, $endDate])
            ->whereNull('da.deleted_at')
            ->groupBy('da.employee_id')
            ->get();

        $status = $this->determineStatus($formattedYearMonth);

        DB::beginTransaction();

        try {
            foreach ($data as $row) {
                $existing = MonthlySummary::where('employee_id', $row->employee_id)
                    ->where('year_month', $formattedYearMonth)
                    ->first();

                $currentStatus = $existing?->status;

                MonthlySummary::updateOrCreate(
                    [
                        'employee_id' => $row->employee_id,
                        'year_month' => $formattedYearMonth,
                    ],
                    [
                        'scheduled_workdays' => $scheduledWorkdays,
                        'actual_workdays' => $row->actual_workdays,
                        'absent_days' => $row->absent_days,
                        'holiday_workdays' => $row->holiday_workdays,
                        'scheduled_minutes' => $row->scheduled_minutes,
                        'work_minutes' => $row->work_minutes,
                        'overtime_minutes' => $row->overtime_minutes,
                        'weekday_overtime_minutes' => $row->weekday_overtime_minutes,
                        'holiday_overtime_minutes' => $row->holiday_overtime_minutes,
                        'late_count' => $row->late_count,
                        'late_minutes' => $row->late_minutes,
                        'early_leave_count' => $row->early_leave_count,
                        'early_leave_minutes' => $row->early_leave_minutes,
                        'status' => $this->resolveStatus($status, $currentStatus),
                        'calculated_at' => now(),
                    ]
                );
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return $data->count();
    }

    /**
     * 判斷月報狀態
     *
     * 月份未結束 → draft
     * 月份已結束 → pending
     */
    protected function determineStatus(string $formattedYearMonth): string
    {
        $monthEnd = Carbon::parse($formattedYearMonth . '-01')->endOfMonth();

        return Carbon::now()->lte($monthEnd) ? 'draft' : 'pending';
    }

    /**
     * 解析最終狀態（已審核或已鎖定的記錄不覆蓋）
     */
    protected function resolveStatus(string $newStatus, ?string $currentStatus): string
    {
        // 已審核或已鎖定的記錄，保持原狀態
        if (in_array($currentStatus, ['approved', 'locked'])) {
            return $currentStatus;
        }

        return $newStatus;
    }
}
