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
        Schema::create('options', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique()->comment('識別碼');
            $table->string('type', 20)->default('select')->comment('輸入類型: select, radio, checkbox');
            $table->integer('sort_order')->default(0)->comment('排序');
            $table->boolean('is_authorizable')->default(false)->comment('是否需要授權檢查');
            $table->boolean('is_active')->default(true)->comment('啟用狀態');
            $table->timestamps();
        });

        Schema::create('option_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('option_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 10)->comment('語系代碼: zh-TW, en');
            $table->string('name', 100)->comment('顯示名稱');
            $table->text('description')->nullable()->comment('說明');
            $table->timestamps();

            $table->unique(['option_id', 'locale']);
        });

        Schema::create('option_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('option_id')->constrained()->cascadeOnDelete();
            $table->string('code', 50)->comment('識別碼');
            $table->string('image', 255)->nullable()->comment('選項圖片');
            $table->integer('sort_order')->default(0)->comment('排序');
            $table->boolean('is_active')->default(true)->comment('啟用狀態');
            $table->timestamps();

            $table->unique(['option_id', 'code']);
        });

        Schema::create('option_value_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('option_value_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 10)->comment('語系代碼: zh-TW, en');
            $table->string('name', 100)->comment('顯示名稱');
            $table->text('description')->nullable()->comment('說明');
            $table->timestamps();

            $table->unique(['option_value_id', 'locale']);
        });

        Schema::create('option_value_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_option_value_id')->constrained('option_values')->cascadeOnDelete()->comment('父選項值');
            $table->foreignId('child_option_value_id')->constrained('option_values')->cascadeOnDelete()->comment('子選項值');
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['parent_option_value_id', 'child_option_value_id'], 'option_value_links_unique');

            // 加入索引以優化查詢效能
            $table->index('child_option_value_id');
        });

        Schema::create('option_value_organizations', function (Blueprint $table) {
            $table->foreignId('option_value_id')->constrained()->cascadeOnDelete()->comment('選項值');
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete()->comment('組織（經銷商）');
            $table->timestamp('created_at')->useCurrent();

            $table->primary(['option_value_id', 'organization_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('option_value_organizations');
        Schema::dropIfExists('option_value_links');
        Schema::dropIfExists('option_value_translations');
        Schema::dropIfExists('option_values');
        Schema::dropIfExists('option_translations');
        Schema::dropIfExists('options');
    }
};
