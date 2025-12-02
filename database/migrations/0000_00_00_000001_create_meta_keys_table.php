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
        Schema::create('meta_keys', function (Blueprint $table) {
            $table->smallIncrements('id');                              // 1-65535，足夠使用
            $table->string('name', 50)->comment('欄位名稱');            // 全域唯一
            $table->string('table_name', 30)->nullable()->comment('所屬資料表'); // null=共用，指定=限定
            $table->enum('data_type', [
                'varchar', 'text', 'longtext', 'json',                  // 字串類
                'tinyint', 'smallint', 'mediumint', 'int', 'bigint',    // 整數類
                'decimal',                                               // 小數
                'boolean',                                               // 布林
            ])->default('varchar')->comment('資料類型');
            $table->string('precision', 10)->nullable()->comment('精度，如 255 或 13.4');
            $table->string('description', 100)->nullable()->comment('欄位說明');
            $table->timestamps();

            $table->index('table_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meta_keys');
    }
};
