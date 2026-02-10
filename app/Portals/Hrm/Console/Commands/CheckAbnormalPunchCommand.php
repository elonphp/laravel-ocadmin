<?php

namespace App\Portals\Hrm\Console\Commands;

use App\Models\Hrm\CalendarDay;
use App\Models\Hrm\DailyAttendance;
use App\Models\Hrm\MonthlySummary;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckAbnormalPunchCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hrm:check-abnormal-punch
                            {work_date? : 指定要檢查的日期（Y-m-d），不帶參數預設今天}
                            {--month= : 指定月份（YYYY-MM）檢查整月}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '檢查員工打卡異常，標記 is_abnormal/abnormal_reason 並更新月報異常天數';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        try {
            $monthOption = $this->option('month');
            $workDate = $this->argument('work_date');

            // 判斷查詢範圍
            if ($monthOption) {
                $startDate = Carbon::parse($monthOption . '-01')->startOfMonth()->toDateString();
                $endDate = Carbon::parse($monthOption . '-01')->endOfMonth()->toDateString();
                $label = "月份 {$monthOption}";
                $month = $monthOption;
            } else {
                $workDate = $workDate ?? Carbon::today()->toDateString();
                $startDate = $workDate;
                $endDate = $workDate;
                $label = "日期 {$workDate}";
                $month = Carbon::parse($workDate)->format('Y-m');
            }

            $this->info("檢查範圍：{$label}（{$startDate} ~ {$endDate}）");

            // 重置範圍內未核准記錄的異常狀態
            DailyAttendance::whereBetween('work_date', [$startDate, $endDate])
                ->whereNotIn('status', ['approved', 'rejected'])
                ->update([
                    'is_abnormal' => false,
                    'abnormal_reason' => null,
                    'is_late' => false,
                    'is_early_leave' => false,
                    'is_absent' => false,
                    'late_minutes' => 0,
                    'early_leave_minutes' => 0,
                ]);

            // 預載工作日資訊
            $calendarDays = CalendarDay::whereBetween('date', [$startDate, $endDate])
                ->pluck('is_workday', 'date')
                ->mapWithKeys(fn ($val, $key) => [Carbon::parse($key)->toDateString() => $val]);

            // 逐筆檢查異常
            $records = DailyAttendance::whereBetween('work_date', [$startDate, $endDate])
                ->whereNotIn('status', ['approved', 'rejected'])
                ->get();

            $abnormalCount = 0;

            foreach ($records as $record) {
                $reasons = $this->checkRecord($record, $calendarDays);

                if (!empty($reasons)) {
                    $record->update([
                        'is_abnormal' => true,
                        'abnormal_reason' => implode('；', $reasons),
                    ]);
                    $abnormalCount++;
                }
            }

            $this->info("打卡異常檢查完成，共標記 {$abnormalCount} 筆異常。");

            // 更新月報的異常相關統計
            $this->updateMonthlySummary($month);

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("錯誤：{$e->getMessage()}");
            return self::FAILURE;
        }
    }

    /**
     * 檢查單筆出勤記錄的異常
     *
     * @return array 異常原因列表
     */
    protected function checkRecord(DailyAttendance $record, $calendarDays): array
    {
        $reasons = [];
        $dateStr = $record->work_date->toDateString();
        $isWorkday = $calendarDays[$dateStr] ?? true;

        // 取得有效時間（審核修正優先）
        $clockedIn = $record->approved_clocked_in ?? $record->clocked_in;
        $clockedOut = $record->approved_clocked_out ?? $record->clocked_out;
        $scheduledStart = $record->scheduled_start;
        $scheduledEnd = $record->scheduled_end;

        // 1. 缺勤檢查：工作日沒有上下班打卡
        if ($isWorkday && !$clockedIn && !$clockedOut) {
            $record->is_absent = true;
            $reasons[] = '工作日未打卡（缺勤）';
        }

        // 2. 缺少打卡：有其中一項但缺另一項
        if ($clockedIn && !$clockedOut) {
            $reasons[] = '缺少下班打卡';
        } elseif (!$clockedIn && $clockedOut) {
            $reasons[] = '缺少上班打卡';
        }

        // 3. 遲到檢查
        if ($clockedIn && $scheduledStart) {
            $scheduledStartCarbon = Carbon::parse($scheduledStart);
            $clockedInCarbon = Carbon::parse($clockedIn);

            if ($clockedInCarbon->gt($scheduledStartCarbon)) {
                $lateMinutes = (int) $clockedInCarbon->diffInMinutes($scheduledStartCarbon);
                $record->is_late = true;
                $record->late_minutes = $lateMinutes;
                $reasons[] = "遲到 {$lateMinutes} 分鐘";
            }
        }

        // 4. 早退檢查
        if ($clockedOut && $scheduledEnd) {
            $scheduledEndCarbon = Carbon::parse($scheduledEnd);
            $clockedOutCarbon = Carbon::parse($clockedOut);

            if ($clockedOutCarbon->lt($scheduledEndCarbon)) {
                $earlyMinutes = (int) $scheduledEndCarbon->diffInMinutes($clockedOutCarbon);
                $record->is_early_leave = true;
                $record->early_leave_minutes = $earlyMinutes;
                $reasons[] = "早退 {$earlyMinutes} 分鐘";
            }
        }

        // 儲存遲到/早退/缺勤欄位（不含 is_abnormal，由外層處理）
        if (!empty($reasons)) {
            $record->saveQuietly();
        }

        return $reasons;
    }

    /**
     * 更新月報的異常相關統計（遲到、早退、缺勤天數）
     */
    protected function updateMonthlySummary(string $month): void
    {
        $monthStart = Carbon::parse($month . '-01')->startOfMonth()->toDateString();
        $monthEnd = Carbon::parse($month . '-01')->endOfMonth()->toDateString();

        $stats = DailyAttendance::selectRaw('
                employee_id,
                COUNT(CASE WHEN is_late = 1 THEN 1 END) AS late_count,
                COALESCE(SUM(late_minutes), 0) AS late_minutes,
                COUNT(CASE WHEN is_early_leave = 1 THEN 1 END) AS early_leave_count,
                COALESCE(SUM(early_leave_minutes), 0) AS early_leave_minutes,
                COUNT(CASE WHEN is_absent = 1 THEN 1 END) AS absent_days
            ')
            ->whereBetween('work_date', [$monthStart, $monthEnd])
            ->whereNull('deleted_at')
            ->groupBy('employee_id')
            ->get();

        foreach ($stats as $row) {
            MonthlySummary::updateOrCreate(
                [
                    'employee_id' => $row->employee_id,
                    'year_month' => $month,
                ],
                [
                    'late_count' => $row->late_count,
                    'late_minutes' => $row->late_minutes,
                    'early_leave_count' => $row->early_leave_count,
                    'early_leave_minutes' => $row->early_leave_minutes,
                    'absent_days' => $row->absent_days,
                ]
            );
        }

        $this->info("月份 {$month} 異常統計更新完成，共更新 {$stats->count()} 筆。");
    }
}
