<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * 擴展 Spatie Permission 表結構
 *
 * 新增欄位：
 * - permissions: parent_id, title, description, sort_order, type
 * - roles: title, description
 *
 * 注意：icon 欄位不在此處，改用 term_metas（EAV 模式）儲存選單相關資料
 *
 * @see docs/md/Ocadmin/選單與權限機制.md
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tableNames = config('permission.table_names');

        if (empty($tableNames)) {
            throw new \Exception('Error: config/permission.php not loaded. Run [php artisan config:clear] and try again.');
        }

        // 擴展 permissions 表
        Schema::table($tableNames['permissions'], function (Blueprint $table) use ($tableNames) {
            // 父層 ID，NULL 為頂層（用於樹狀結構）
            $table->unsignedBigInteger('parent_id')
                ->nullable()
                ->after('id');

            // 顯示名稱（spatie 的 name 偏向代號概念，title 用於顯示）
            $table->string('title', 100)
                ->nullable()
                ->after('guard_name')
                ->comment('顯示名稱');

            // 權限說明
            $table->text('description')
                ->nullable()
                ->after('title')
                ->comment('權限說明');

            // 排序，數字越小越前面
            $table->integer('sort_order')
                ->default(0)
                ->after('description')
                ->comment('排序');

            // 類型：menu=選單權限, action=功能權限
            $table->enum('type', ['menu', 'action'])
                ->default('menu')
                ->after('sort_order')
                ->comment('類型：menu=選單, action=功能');

            // 外鍵約束：父層關聯
            $table->foreign('parent_id')
                ->references('id')
                ->on($tableNames['permissions'])
                ->onDelete('cascade');

            // 索引
            $table->index('parent_id');
            $table->index('type');
            $table->index('sort_order');
        });

        // 擴展 roles 表
        Schema::table($tableNames['roles'], function (Blueprint $table) {
            // 顯示名稱
            $table->string('title', 100)
                ->nullable()
                ->after('guard_name')
                ->comment('顯示名稱');

            // 角色說明
            $table->text('description')
                ->nullable()
                ->after('title')
                ->comment('角色說明');
        });

        // 清除 permission 快取
        app('cache')
            ->store(config('permission.cache.store') != 'default' ? config('permission.cache.store') : null)
            ->forget(config('permission.cache.key'));
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableNames = config('permission.table_names');

        if (empty($tableNames)) {
            throw new \Exception('Error: config/permission.php not found.');
        }

        // 移除 permissions 表的擴展欄位
        Schema::table($tableNames['permissions'], function (Blueprint $table) {
            // 先移除外鍵
            $table->dropForeign(['parent_id']);

            // 移除索引
            $table->dropIndex(['parent_id']);
            $table->dropIndex(['type']);
            $table->dropIndex(['sort_order']);

            // 移除欄位
            $table->dropColumn(['parent_id', 'title', 'description', 'sort_order', 'type']);
        });

        // 移除 roles 表的擴展欄位
        Schema::table($tableNames['roles'], function (Blueprint $table) {
            $table->dropColumn(['title', 'description']);
        });
    }
};
