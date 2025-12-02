<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * 為所有 metas 表新增 locale 欄位，支援多語系 EAV
 *
 * locale 欄位設計：
 * - 不可為 NULL，必須使用空字串 '' 代替
 * - '' = 無語系（共用值）
 * - 'zh_Hant', 'en' 等 = 有語系（多語值）
 *
 * 原因：複合主鍵包含 NULL 時，MySQL 的 UNIQUE 約束會失效（NULL != NULL）
 */
return new class extends Migration
{
    public function up(): void
    {
        // 暫時停用外鍵檢查
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // ========== user_metas ==========
        // 取得外鍵名稱並刪除
        $this->dropForeignKeyIfExists('user_metas', 'key_id');

        // 移除主鍵
        Schema::table('user_metas', function (Blueprint $table) {
            $table->dropPrimary(['user_id', 'key_id']);
        });

        // 新增 locale 欄位 + 新主鍵 + 重建外鍵
        Schema::table('user_metas', function (Blueprint $table) {
            $table->string('locale', 10)->default('')->after('key_id');
            $table->primary(['user_id', 'key_id', 'locale']);
            $table->foreign('key_id')->references('id')->on('meta_keys')->cascadeOnDelete();
        });

        // ========== term_metas ==========
        // 取得外鍵名稱並刪除
        $this->dropForeignKeyIfExists('term_metas', 'key_id');

        // 移除 unique 約束
        Schema::table('term_metas', function (Blueprint $table) {
            $table->dropUnique(['term_id', 'key_id']);
        });

        // 新增 locale 欄位 + 新 unique + 重建外鍵
        Schema::table('term_metas', function (Blueprint $table) {
            $table->string('locale', 10)->default('')->after('key_id');
            $table->unique(['term_id', 'key_id', 'locale']);
            $table->foreign('key_id')->references('id')->on('meta_keys')->cascadeOnDelete();
        });

        // 重新啟用外鍵檢查
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    public function down(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // ========== user_metas ==========
        $this->dropForeignKeyIfExists('user_metas', 'key_id');

        Schema::table('user_metas', function (Blueprint $table) {
            $table->dropPrimary(['user_id', 'key_id', 'locale']);
        });

        Schema::table('user_metas', function (Blueprint $table) {
            $table->dropColumn('locale');
            $table->primary(['user_id', 'key_id']);
            $table->foreign('key_id')->references('id')->on('meta_keys')->cascadeOnDelete();
        });

        // ========== term_metas ==========
        $this->dropForeignKeyIfExists('term_metas', 'key_id');

        Schema::table('term_metas', function (Blueprint $table) {
            $table->dropUnique(['term_id', 'key_id', 'locale']);
        });

        Schema::table('term_metas', function (Blueprint $table) {
            $table->dropColumn('locale');
            $table->unique(['term_id', 'key_id']);
            $table->foreign('key_id')->references('id')->on('meta_keys')->cascadeOnDelete();
        });

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    /**
     * 查詢並刪除外鍵約束
     */
    private function dropForeignKeyIfExists(string $table, string $column): void
    {
        $database = DB::getDatabaseName();

        $foreignKeys = DB::select("
            SELECT CONSTRAINT_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = ?
              AND TABLE_NAME = ?
              AND COLUMN_NAME = ?
              AND REFERENCED_TABLE_NAME IS NOT NULL
        ", [$database, $table, $column]);

        foreach ($foreignKeys as $fk) {
            DB::statement("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$fk->CONSTRAINT_NAME}`");
        }
    }
};
