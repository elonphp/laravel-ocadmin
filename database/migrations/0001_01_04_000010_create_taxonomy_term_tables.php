<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sys_taxonomies', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('description', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('sys_taxonomy_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('taxonomy_id')->constrained('sys_taxonomies')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('locale', 10);
            $table->string('name', 100);

            $table->unique(['taxonomy_id', 'locale']);
        });

        Schema::create('sys_terms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('taxonomy_id')->constrained('sys_taxonomies')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('sys_terms')->nullOnDelete();
            $table->string('code', 50);
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['taxonomy_id', 'code']);
        });

        Schema::create('sys_term_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('term_id')->constrained('sys_terms')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('locale', 10);
            $table->string('name', 100);

            $table->unique(['term_id', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sys_term_translations');
        Schema::dropIfExists('sys_terms');
        Schema::dropIfExists('sys_taxonomy_translations');
        Schema::dropIfExists('sys_taxonomies');
    }
};
