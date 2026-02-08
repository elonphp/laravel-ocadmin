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
        Schema::create('hrm_calendar_days', function (Blueprint $table) {
            $table->id();

            // 基本資訊
            $table->date('date')->unique()->comment('日期（YYYY-MM-DD）');
            $table->string('day_type', 20)->default('workday')->comment('日期類型：workday=工作日, weekend=週末, holiday=國定假日, company_holiday=公司假日, makeup_workday=補班日, typhoon_day=颱風假');
            $table->boolean('is_workday')->default(true)->comment('是否為工作日');

            // 特殊日期標記
            $table->string('name')->nullable()->comment('特殊日期名稱');
            $table->text('description')->nullable()->comment('說明');
            $table->string('color', 7)->nullable()->comment('顏色標記（#RRGGBB）');

            $table->timestamps();

            // 索引
            $table->index('date');
            $table->index('is_workday');
            $table->index('day_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hrm_calendar_days');
    }
};
