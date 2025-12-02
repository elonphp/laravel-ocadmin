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
        // 使用者認證表（只放認證/識別相關欄位）
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username', 50)->unique()->nullable()->comment('使用者名稱');
            $table->string('mobile', 50)->unique()->nullable()->comment('手機號碼');
            $table->string('email')->unique()->nullable()->comment('電子郵件');
            $table->timestamp('email_verified_at')->nullable()->comment('Email 驗證時間');
            $table->timestamp('mobile_verified_at')->nullable()->comment('手機驗證時間');
            $table->string('password')->nullable()->comment('密碼');
            $table->string('name',100)->nullable()->comment('名稱');
            $table->boolean('is_active')->default(true)->comment('帳號啟用狀態');
            $table->timestamp('last_login_at')->nullable()->comment('最後登入時間');
            $table->string('last_login_ip', 45)->nullable()->comment('最後登入 IP');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        Schema::create('user_metas', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('meta_key_id');
            $table->string('locale', 10)->default('');
            $table->text('meta_value')->nullable();

            $table->index('meta_key_id');
            $table->unique(['user_id', 'meta_key_id', 'locale']);
            $table->foreign('meta_key_id')->references('id')->on('meta_keys')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_metas');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
