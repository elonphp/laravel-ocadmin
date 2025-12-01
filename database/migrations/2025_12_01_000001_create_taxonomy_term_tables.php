<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ========== taxonomies ==========
        Schema::create('taxonomies', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('is_active');
            $table->index('sort_order');
        });

        // ========== taxonomy_translations ==========
        Schema::create('taxonomy_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('taxonomy_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 10);
            $table->string('name', 100);

            $table->unique(['taxonomy_id', 'locale']);
        });

        // ========== terms ==========
        Schema::create('terms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('taxonomy_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('terms')->nullOnDelete();
            $table->string('code', 50);
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['taxonomy_id', 'code']);
            $table->index('taxonomy_id');
            $table->index('parent_id');
            $table->index('is_active');
            $table->index('sort_order');
        });

        // ========== term_translations ==========
        Schema::create('term_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('term_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 10);
            $table->string('name', 100);
            $table->string('short_name', 50)->nullable();

            $table->unique(['term_id', 'locale']);
        });

        // ========== term_metas ==========
        Schema::create('term_metas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('term_id')->constrained()->cascadeOnDelete();
            $table->smallInteger('key_id')->unsigned();
            $table->text('value')->nullable();

            $table->unique(['term_id', 'key_id']);
            $table->index('term_id');
            $table->index('key_id');

            $table->foreign('key_id')->references('id')->on('meta_keys')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('term_metas');
        Schema::dropIfExists('term_translations');
        Schema::dropIfExists('terms');
        Schema::dropIfExists('taxonomy_translations');
        Schema::dropIfExists('taxonomies');
    }
};
