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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('locale', 10)->default('')->comment('語系代碼，空值表示全域');
            $table->string('group', 100)->nullable()->comment('群組分類');
            $table->string('code')->comment('唯一識別碼');
            $table->text('content')->nullable()->comment('設定值');
            $table->string('type', 20)->default('text')->comment('資料類型');
            $table->string('note')->nullable()->comment('備註說明');
            $table->timestamps();

            // 同一語系下，代碼必須唯一
            $table->unique(['locale', 'code']);
            $table->index('group');
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
