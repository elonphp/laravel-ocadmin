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
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('name', 128)->comment('國家名稱');
            $table->string('native_name', 128)->nullable()->comment('本地名稱');
            $table->string('iso_code_2', 2)->unique()->comment('ISO 3166-1 alpha-2');
            $table->string('iso_code_3', 3)->index()->comment('ISO 3166-1 alpha-3');
            $table->string('address_format', 1000)->nullable()->comment('地址格式範本');
            $table->boolean('postcode_required')->default(false)->comment('郵遞區號是否必填');
            $table->boolean('is_active')->default(true)->comment('啟用狀態');
            $table->integer('sort_order')->default(0)->comment('排序');
            $table->timestamps();

            $table->index('is_active');
            $table->index('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};
