<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 商品選項關聯表
        Schema::create('ctl_product_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('ctl_products')->cascadeOnDelete();
            $table->foreignId('option_id')->constrained('ctl_options')->cascadeOnDelete();
            $table->text('value')->nullable();
            $table->boolean('required')->default(false);
        });

        // 商品選項值表
        Schema::create('ctl_product_option_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_option_id')->constrained('ctl_product_options')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('ctl_products')->cascadeOnDelete();
            $table->foreignId('option_id')->constrained('ctl_options')->cascadeOnDelete();
            $table->foreignId('option_value_id')->constrained('ctl_option_values')->cascadeOnDelete();
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
        Schema::dropIfExists('ctl_product_option_values');
        Schema::dropIfExists('ctl_product_options');
    }
};
