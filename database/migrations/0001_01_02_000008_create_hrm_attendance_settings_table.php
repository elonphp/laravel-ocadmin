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
        Schema::create('hrm_attendance_settings', function (Blueprint $table) {
            $table->id();

            // Polymorphic: Company 或 Department
            $table->morphs('settingable');

            // ── 工作時間 ──
            $table->json('workdays')->nullable()->comment('工作日 [1,2,3,4,5]（0=日,6=六）');
            $table->time('default_work_start')->nullable()->comment('預設上班時間');
            $table->time('default_work_end')->nullable()->comment('預設下班時間');
            $table->unsignedSmallInteger('default_break_minutes')->nullable()->comment('預設休息分鐘數');

            // ── 出勤政策 ──
            $table->unsignedSmallInteger('late_threshold_minutes')->nullable()->comment('遲到容許分鐘數');
            $table->unsignedSmallInteger('early_leave_threshold_minutes')->nullable()->comment('早退容許分鐘數');
            $table->boolean('count_early_arrival')->nullable()->comment('早到是否計入工時');
            $table->boolean('count_late_departure')->nullable()->comment('延遲下班是否計入工時');

            $table->timestamps();

            // 每個 settingable 只有一筆
            $table->unique(['settingable_type', 'settingable_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hrm_attendance_settings');
    }
};
