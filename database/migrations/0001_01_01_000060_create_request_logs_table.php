<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'sysdata';

    public function up(): void
    {
        Schema::dropIfExists('request_logs');
        
        Schema::create('request_logs', function (Blueprint $table) {
            $table->id();
            $table->string('request_trace_id', 64)->nullable()->index()->comment('請求追蹤 ID');
            $table->unsignedBigInteger('user_id')->nullable()->index()->comment('操作者');
            $table->string('app_name', 64)->nullable()->comment('應用程式名稱 (APP_NAME)');
            $table->string('portal', 32)->nullable()->index()->comment('來源 Portal');
            $table->string('area', 32)->nullable()->comment('環境 (local/staging/production)');
            $table->text('url')->nullable()->comment('請求 URL');
            $table->string('method', 10)->nullable()->comment('HTTP 方法');
            $table->unsignedSmallInteger('status_code')->nullable()->comment('HTTP 回應狀態碼');
            $table->json('request_data')->nullable()->comment('請求資料');
            $table->json('response_data')->nullable()->comment('回應資料');
            $table->string('status', 32)->nullable()->comment('狀態');
            $table->text('note')->nullable()->comment('備註');
            $table->string('client_ip', 45)->nullable()->comment('客戶端 IP');
            $table->string('api_ip', 45)->nullable()->comment('API 伺服器 IP');
            $table->timestamp('created_at')->nullable()->index();

            // 複合索引
            $table->index(['created_at', 'status']);
            $table->index(['app_name', 'portal', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('request_logs');
    }
};
