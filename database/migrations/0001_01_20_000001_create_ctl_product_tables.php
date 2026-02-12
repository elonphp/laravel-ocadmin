<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 商品主表
        Schema::create('ctl_products', function (Blueprint $table) {
            $table->id();
            $table->string('model', 64)->default('');
            $table->string('image', 255)->nullable();
            $table->decimal('price', 15, 4)->default(0);
            $table->integer('quantity')->default(0);
            $table->integer('minimum')->default(1);
            $table->boolean('subtract')->default(true);
            $table->boolean('shipping')->default(true);
            $table->boolean('status')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // 商品翻譯表
        Schema::create('ctl_product_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('ctl_products')->cascadeOnDelete();
            $table->string('locale', 10);
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->string('meta_title', 255)->nullable();
            $table->string('meta_keyword', 255)->nullable();
            $table->text('meta_description')->nullable();

            $table->unique(['product_id', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ctl_product_translations');
        Schema::dropIfExists('ctl_products');
    }
};
