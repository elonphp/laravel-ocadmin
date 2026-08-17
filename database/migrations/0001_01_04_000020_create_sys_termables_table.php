<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 通用多型分類掛載表（補完 sys_taxonomy/sys_term 缺的「掛載」那一層）。
     * 任何實體皆可多對多掛 term（衍生專案的 cms_articles / products / 業務表…）。
     *
     * 0001 通用層 MM=04（緊接 sys_taxonomy/sys_term，必須跑在它們之後）。
     * 關聯走語言中立的 term_id；顯示名在 sys_term_translations、對外把手用 sys_terms.code。
     * termable_type 存 morph map 短別名（見 AppServiceProvider::boot），非 FQCN。
     */
    public function up(): void
    {
        Schema::create('sys_termables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('term_id')->constrained('sys_terms')->cascadeOnDelete();
            // 手動展開 morphs()：termable_type 收窄成 64（morph map 存短別名，不存 FQCN）
            $table->string('termable_type', 64);
            $table->unsignedBigInteger('termable_id');
            $table->index(['termable_type', 'termable_id']);
            $table->integer('sort_order')->default(0);  // 同一實體上多個 term 的排序

            // 同一 term 不重複掛同一實體
            $table->unique(['term_id', 'termable_type', 'termable_id'], 'sys_termables_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sys_termables');
    }
};
