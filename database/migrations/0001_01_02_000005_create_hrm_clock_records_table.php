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
        Schema::create('hrm_clock_records', function (Blueprint $table) {
            $table->id();

            // 基本資訊
            $table->foreignId('employee_id')->comment('員工 ID')->constrained('hrm_employees')->onDelete('cascade');
            $table->dateTime('clocked_at')->comment('打卡時間');
            $table->string('clock_type', 20)->nullable()->comment('打卡類型：null=未知, in=進, out=出');

            // 打卡方式與位置
            $table->string('clock_method', 20)->default('device')->comment('打卡方式：device=打卡機, web=網頁打卡, app=APP打卡, manual=手動補登, import=批次匯入');

            $table->string('device_id')->nullable()->comment('設備 ID');
            $table->string('device_name')->nullable()->comment('設備名稱');
            $table->string('ip_address', 45)->nullable()->comment('IP 位址');

            // GPS 定位（APP 打卡）
            // $table->decimal('latitude', 10, 8)->nullable()->comment('緯度');
            // $table->decimal('longitude', 11, 8)->nullable()->comment('經度');
            // $table->string('location_address')->nullable()->comment('地址');
            // $table->boolean('is_valid_location')->default(true)->comment('位置是否有效');

            // 狀態與備註
            $table->string('status', 20)->default('valid')->comment('狀態：valid=有效, invalid=無效, pending=待審核');
            // $table->text('note')->nullable()->comment('備註'); //原始打卡錄會非常多筆，不需要此欄位。
            $table->foreignId('created_by')->nullable()->comment('建立人（手動補登時）')->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // 索引
            $table->index(['employee_id', 'clocked_at']);
            $table->index('clocked_at');
            $table->index('clock_type');
            $table->index('clock_method');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hrm_clock_records');
    }
};
