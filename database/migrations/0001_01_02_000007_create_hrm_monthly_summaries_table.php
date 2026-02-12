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
        Schema::create('hrm_monthly_summaries', function (Blueprint $table) {
            $table->id();

            // 基本資訊
            $table->foreignId('employee_id')->comment('員工 ID')
                ->constrained('hrm_employees')->onDelete('cascade');
            $table->string('year_month', 7)->comment('年月（YYYY-MM）');

            // 出勤天數統計
            $table->integer('scheduled_workdays')->default(0)->comment('應出勤天數');
            $table->integer('actual_workdays')->default(0)->comment('實際出勤天數');
            $table->integer('absent_days')->default(0)->comment('缺勤天數');
            $table->integer('holiday_workdays')->default(0)->comment('假日上班天數');

            // 工時統計（單位：分鐘）
            $table->integer('scheduled_minutes')->default(0)->comment('應工作分鐘數');
            $table->integer('work_minutes')->default(0)->comment('實際工作分鐘數');
            $table->integer('overtime_minutes')->default(0)->comment('總加班分鐘數');

            // 加班細分
            $table->integer('weekday_overtime_minutes')->default(0)->comment('平日加班分鐘數');
            $table->integer('holiday_overtime_minutes')->default(0)->comment('假日加班分鐘數');

            // 異常統計
            $table->integer('late_count')->default(0)->comment('遲到次數');
            $table->integer('late_minutes')->default(0)->comment('遲到總分鐘數');
            $table->integer('early_leave_count')->default(0)->comment('早退次數');
            $table->integer('early_leave_minutes')->default(0)->comment('早退總分鐘數');

            // 請假統計（與請假系統整合）
            $table->integer('annual_leave_days')->default(0)->comment('特休天數');
            $table->integer('sick_leave_days')->default(0)->comment('病假天數');
            $table->integer('personal_leave_days')->default(0)->comment('事假天數');
            $table->integer('other_leave_days')->default(0)->comment('其他假別天數');

            // 狀態
            $table->string('status', 20)->default('draft')->comment('狀態：draft=草稿, pending=待審核, approved=已審核, locked=已鎖定');

            $table->text('note')->nullable()->comment('備註');
            $table->foreignId('reviewed_by')->nullable()->comment('審核人 ID')
                ->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable()->comment('審核時間');
            $table->timestamp('calculated_at')->nullable()->comment('最後計算時間');

            $table->timestamps();
            $table->softDeletes();

            // 唯一約束
            $table->unique(['employee_id', 'year_month']);

            // 索引
            $table->index('year_month');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hrm_monthly_summaries');
    }
};
