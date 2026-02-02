<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * 訂單聊天室相關資料表
     */
    public function up(): void
    {
        // 訂單參與者（誰可以進入聊天室）
        Schema::create('order_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role', 30)->comment('參與時的角色');
            $table->timestamp('joined_at')->useCurrent();

            $table->unique(['order_id', 'user_id']);
            $table->index('user_id');
        });

        // 聊天訊息
        Schema::create('order_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role', 30)->comment('發送時的角色（快照）');
            $table->text('content');
            $table->string('type', 20)->default('text')->comment('text, image, file, system');
            $table->json('metadata')->nullable()->comment('附加資料（檔案 URL 等）');
            $table->json('read_by')->nullable()->comment('已讀的 user_ids');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['order_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_messages');
        Schema::dropIfExists('order_participants');
    }
};
