<?php

/**
 * Transition 檔案範本
 *
 * 執行：php artisan db:transition
 * 預覽：php artisan db:transition --dry-run
 *
 * 本檔案會被 DbTransitionCommand 自動跳過（依檔名排除）— 只作為新增 transition 時的複製範本。
 *
 * ────────────────────────────────────────────────────────────
 * 啟用時機：上線後才開始累積 transitions
 * ────────────────────────────────────────────────────────────
 *
 * **上線前（目前狀態）不使用本機制**。所有 schema 異動一律走：
 *   1. 改主表 migration（database/migrations/*.php）
 *   2. user 執行 `php artisan db:rebuild --force`
 *      → migrate:fresh --seed（從無到有重建 schema + seeder）
 *      → 跑 docs/migrate/migrate_*.sql（舊系統 chinabingstd ETL 移植）
 *
 * 主表 migration 是 single source of truth；本目錄保持空（僅 example.php）。
 *
 * **上線後**，正式資料無法 migrate:fresh，schema / 資料異動才改走：
 *   - 同步維護主表 migration（為將來新環境保留 schema 規格）
 *   - **同步**新增本目錄下一支 transition（套到正式 DB）
 *
 * 上線時機 = 第一次 prod 部署，本目錄從那刻起才開始累積 transition 檔。
 * 
 * ────────────────────────────────────────────────────────────
 * 檔名 / version 慣例
 * ────────────────────────────────────────────────────────────
 *
 * 檔名：database/transitions/YYYYMMDD_NNN_短描述.php
 *   - YYYYMMDD 為當日日期；NNN 為當日流水（001 起算，每人每天各自累積）
 *   - 短描述使用 snake_case，能對應檔案內容即可（例：fix_users_email_unique）
 *
 * version：對應 schema_transitions.version（VARCHAR(100) PK）
 *   - 慣例：YYYYMMDDNNN（去掉檔名底線、純數字字串）
 *   - 必須全專案唯一；多人同日加 transition 用 001 / 002 / 003 區隔
 *
 * ────────────────────────────────────────────────────────────
 * 撰寫原則
 * ────────────────────────────────────────────────────────────
 *
 * 1. 必須 idempotent — 重跑不會 fail。
 *    線上常因手動執行過部分 SQL 導致與 dev 分歧；transition 必須先檢查
 *    狀態（hasColumn / hasIndex / hasTable / where 條件等），已套用就 skip。
 *
 * 2. DDL（ALTER TABLE / DROP INDEX 等）必須設 'transaction' => false。
 *    MySQL 對 DDL 會觸發隱式 commit，把外層 transaction 吃掉，最後
 *    DbTransitionCommand 呼叫 commit() 會拋 "no active transaction"。
 *
 * 3. 純 DML（UPDATE / INSERT / DELETE）保持 'transaction' => true（預設）。
 *    多條 DML 失敗能整批 rollback。
 *
 * ────────────────────────────────────────────────────────────
 */

// use Illuminate\Support\Facades\DB;
// use Illuminate\Support\Facades\Schema;

return [
    'version'     => '',
    'description' => '',
    'up'          => null,

    // ── 範例 A：新增欄位（DDL，hasColumn 守門 + transaction:false）──
    //
    // 'version'     => '20260512001',
    // 'description' => 'users: 新增 note 欄位',
    // 'transaction' => false,
    // 'up' => function () {
    //     if (Schema::hasColumn('users', 'note')) {
    //         return; // 欄位已存在 → 視為通過
    //     }
    //     DB::statement("ALTER TABLE `users` ADD COLUMN `note` TEXT NULL AFTER `email`");
    // },

    // ── 範例 B：新增關聯表（DDL，hasTable 守門 + transaction:false）──
    //
    // 'version'     => '20260512002',
    // 'description' => 'roles: 新增 role_translations 多語系顯示名稱表',
    // 'transaction' => false,
    // 'up' => function () {
    //     if (Schema::hasTable('role_translations')) {
    //         return;
    //     }
    //     DB::statement("
    //         CREATE TABLE `role_translations` (
    //             `id`           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    //             `role_id`      BIGINT UNSIGNED NOT NULL,
    //             `locale`       VARCHAR(10)  NOT NULL,
    //             `display_name` VARCHAR(100) NOT NULL,
    //             UNIQUE KEY `uniq_role_locale` (`role_id`, `locale`),
    //             CONSTRAINT `fk_role_translations_role` FOREIGN KEY (`role_id`)
    //                 REFERENCES `roles`(`id`) ON DELETE CASCADE
    //         ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    //     ");
    // },

    // ── 範例 C：純資料變更（DML，預設帶 transaction，where 守門）──
    //
    // 'version'     => '20260512003',
    // 'description' => 'permissions: 命名統一 create_user → user.create',
    // 'up' => function () {
    //     DB::table('permissions')
    //         ->where('name', 'create_user')
    //         ->update(['name' => 'user.create']);
    // },
];
