<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 訂單主表
        Schema::create('ord_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('order_no', 32)->unique();
            $table->smallInteger('status')->default(0);
            $table->string('currency_code', 3)->default('TWD');
            $table->decimal('subtotal', 15, 4)->default(0);
            $table->decimal('total', 15, 4)->default(0);
            $table->text('comment')->nullable();
            $table->string('shipping_name', 100)->nullable();
            $table->string('shipping_phone', 30)->nullable();
            $table->text('shipping_address')->nullable();
            $table->string('payment_method', 50)->nullable();
            $table->string('shipping_method', 50)->nullable();
            $table->timestamps();
        });

        // 訂單商品明細
        Schema::create('ord_order_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('ord_orders')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('ctl_products')->nullOnDelete();
            $table->string('name', 255);
            $table->string('model', 64)->default('');
            $table->integer('quantity')->default(1);
            $table->decimal('price', 15, 4)->default(0);
            $table->decimal('total', 15, 4)->default(0);
        });

        // 訂單商品選項
        Schema::create('ord_order_product_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_product_id')->constrained('ord_order_products')->cascadeOnDelete();
            $table->foreignId('option_id')->nullable()->constrained('ctl_options')->nullOnDelete();
            $table->foreignId('option_value_id')->nullable()->constrained('ctl_option_values')->nullOnDelete();
            $table->string('name', 128);
            $table->string('value', 255);
            $table->string('type', 20);
            $table->decimal('price', 15, 4)->default(0);
            $table->string('price_prefix', 1)->default('+');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ord_order_product_options');
        Schema::dropIfExists('ord_order_products');
        Schema::dropIfExists('ord_orders');
    }
};
