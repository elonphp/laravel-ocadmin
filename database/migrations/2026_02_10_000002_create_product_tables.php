<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 商品主表
        Schema::create('clg_products', function (Blueprint $table) {
            $table->id();
            $table->string('model', 64)->default('');
            $table->string('image', 255)->nullable();
            $table->decimal('price', 15, 4)->default(0);
            $table->integer('quantity')->default(0);
            $table->integer('minimum')->default(1);
            $table->boolean('subtract')->default(true);
            // $table->unsignedInteger('stock_status_id')->nullable(); // 缺貨狀態（未來關聯）
            $table->boolean('shipping')->default(true);
            // $table->decimal('weight', 15, 8)->default(0);
            // $table->unsignedInteger('weight_class_id')->nullable(); // 重量單位（未來關聯）
            // $table->decimal('length', 15, 8)->default(0);
            // $table->decimal('width', 15, 8)->default(0);
            // $table->decimal('height', 15, 8)->default(0);
            // $table->unsignedInteger('length_class_id')->nullable(); // 長度單位（未來關聯）
            $table->boolean('status')->default(true);
            $table->integer('sort_order')->default(0);
            // $table->date('date_available')->nullable();
            $table->timestamps();
        });

        // 商品翻譯表
        Schema::create('clg_product_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('clg_products')->cascadeOnDelete();
            $table->string('locale', 10);
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->string('meta_title', 255)->nullable();
            $table->string('meta_keyword', 255)->nullable();
            $table->text('meta_description')->nullable();
            // $table->string('tag', 255)->nullable();

            $table->unique(['product_id', 'locale']);
        });

        // 商品選項關聯表
        Schema::create('clg_product_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('clg_products')->cascadeOnDelete();
            $table->foreignId('option_id')->constrained('clg_options')->cascadeOnDelete();
            $table->text('value')->nullable();
            $table->boolean('required')->default(false);
        });

        // 商品選項值表
        Schema::create('clg_product_option_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_option_id')->constrained('clg_product_options')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('clg_products')->cascadeOnDelete();
            $table->foreignId('option_id')->constrained('clg_options')->cascadeOnDelete();
            $table->foreignId('option_value_id')->constrained('clg_option_values')->cascadeOnDelete();
            $table->integer('quantity')->default(0);
            $table->boolean('subtract')->default(false);
            $table->decimal('price', 15, 4)->default(0);
            $table->string('price_prefix', 1)->default('+');
            $table->decimal('weight', 15, 8)->default(0);
            $table->string('weight_prefix', 1)->default('+');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clg_product_option_values');
        Schema::dropIfExists('clg_product_options');
        Schema::dropIfExists('clg_product_translations');
        Schema::dropIfExists('clg_products');
    }
};
