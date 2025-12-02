<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 使用 sysdata 連線
     */
    protected $connection = 'sysdata';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::connection($this->connection)->create('logs', function (Blueprint $table) {
            $table->id();
            $table->string('request_trace_id', 64)->nullable()->index()->comment('請求追蹤 ID');
            $table->string('area', 32)->nullable()->comment('環境 (local/staging/production)');
            $table->text('url')->nullable()->comment('請求 URL');
            $table->string('method', 10)->nullable()->comment('HTTP 方法');
            $table->json('data')->nullable()->comment('請求資料');
            $table->string('status', 32)->nullable()->comment('狀態');
            $table->text('note')->nullable()->comment('備註');
            $table->string('client_ip', 45)->nullable()->comment('客戶端 IP');
            $table->string('api_ip', 45)->nullable()->comment('API 伺服器 IP');
            $table->timestamp('created_at')->nullable()->index();

            // 複合索引：按日期和狀態查詢
            $table->index(['created_at', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('logs');
    }
};
