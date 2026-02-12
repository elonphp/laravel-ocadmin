<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('hrm_daily_attendances', function (Blueprint $table) {
            $table->id();

            // 基本資訊
            $table->foreignId('employee_id')->comment('員工 ID')->constrained('hrm_employees')->onDelete('cascade');
            $table->date('work_date')->comment('工作日期');

            // 預定時間（來自行事曆或班表）
            $table->time('scheduled_start')->nullable()->comment('預定上班時間');
            $table->time('scheduled_end')->nullable()->comment('預定下班時間');

            // 打卡時間（原始記錄）
            $table->dateTime('clocked_in')->nullable()->comment('上班打卡時間（原始）');
            $table->dateTime('clocked_out')->nullable()->comment('下班打卡時間（原始）');

            // 休息時間
            $table->dateTime('break_start')->nullable()->comment('休息開始時間');
            $table->dateTime('break_end')->nullable()->comment('休息結束時間');

            // 工作時間（計薪用）
            $table->dateTime('work_start')->nullable()->comment('工作開始時間（原始計算）');
            $table->dateTime('work_end')->nullable()->comment('工作結束時間（原始計算）');

            // 主管審核修正後的時間
            $table->dateTime('approved_clocked_in')->nullable()->comment('上班打卡時間（主管修正）');
            $table->dateTime('approved_clocked_out')->nullable()->comment('下班打卡時間（主管修正）');
            $table->dateTime('approved_break_start')->nullable()->comment('休息開始時間（主管修正）');
            $table->dateTime('approved_break_end')->nullable()->comment('休息結束時間（主管修正）');
            $table->dateTime('approved_work_start')->nullable()->comment('工作開始時間（主管修正）');
            $table->dateTime('approved_work_end')->nullable()->comment('工作結束時間（主管修正）');

            // 審核資訊
            $table->foreignId('reviewed_by')->nullable()->comment('審核人 ID')->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable()->comment('審核時間');

            // 修正追蹤
            $table->text('correction_reason')->nullable()->comment('修正原因');
            $table->foreignId('corrected_by')->nullable()->comment('修正人 ID')->constrained('users')->nullOnDelete();
            $table->timestamp('corrected_at')->nullable()->comment('修正時間');

            // 工時統計（單位：分鐘）
            $table->integer('scheduled_minutes')->default(0)->comment('預定工作分鐘數');
            $table->integer('work_minutes')->default(0)->comment('實際工作分鐘數');
            $table->integer('break_minutes')->default(0)->comment('休息分鐘數');
            $table->integer('overtime_minutes')->default(0)->comment('加班分鐘數');

            // 遲到早退
            $table->boolean('is_late')->default(false)->comment('是否遲到');
            $table->integer('late_minutes')->default(0)->comment('遲到分鐘數');
            $table->boolean('is_early_leave')->default(false)->comment('是否早退');
            $table->integer('early_leave_minutes')->default(0)->comment('早退分鐘數');

            // 異常狀態
            $table->boolean('is_absent')->default(false)->comment('是否缺勤');
            $table->boolean('is_abnormal')->default(false)->comment('是否有異常（嚴重）');
            $table->text('abnormal_reason')->nullable()->comment('異常原因');

            // 狀態與審核
            $table->string('status', 20)->default('scheduled')->comment('狀態：scheduled=已排班, in_progress=進行中, pending=待審核, approved=已審核, rejected=已駁回');

            $table->text('note')->nullable()->comment('備註');

            $table->timestamps();
            $table->softDeletes();

            // 唯一約束
            $table->unique(['employee_id', 'work_date']);

            // 索引
            $table->index('work_date');
            $table->index('status');
            $table->index(['employee_id', 'work_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hrm_daily_attendances');
    }
};
