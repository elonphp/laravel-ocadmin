<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * ACL（Access Control List）權限控制相關資料表
     * 採用 OpenCart 風格的路由型權限設計
     */
    public function up(): void
    {
        // // 角色表
        // Schema::create('acl_roles', function (Blueprint $table) {
        //     $table->id();
        //     $table->string('code', 50)->unique()->comment('角色識別碼，格式：{portal}.{role_code}');
        //     $table->integer('sort_order')->default(0)->comment('排序');
        //     $table->boolean('is_active')->default(true)->comment('啟用狀態');
        //     $table->timestamps();
        // });

        // // 角色路由權限表
        // Schema::create('acl_role_routes', function (Blueprint $table) {
        //     $table->foreignId('role_id')->constrained('acl_roles')->cascadeOnDelete();
        //     $table->string('route', 100)->comment('路由識別碼');
        //     $table->boolean('access')->default(false)->comment('可訪問（查看）');
        //     $table->boolean('modify')->default(false)->comment('可修改（新增/編輯/刪除）');

        //     $table->primary(['role_id', 'route']);
        // });

        // // 使用者角色關聯表
        // Schema::create('acl_user_roles', function (Blueprint $table) {
        //     $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        //     $table->foreignId('role_id')->constrained('acl_roles')->cascadeOnDelete();
        //     $table->timestamp('created_at')->useCurrent();

        //     $table->primary(['user_id', 'role_id']);
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Schema::dropIfExists('acl_user_roles');
        // Schema::dropIfExists('acl_role_routes');
        // Schema::dropIfExists('acl_roles');
    }
};
