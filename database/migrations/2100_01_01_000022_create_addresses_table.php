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
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 20)->default('shipping')->comment('地址類型：shipping, billing');
            $table->string('name', 64)->comment('收件人姓名');
            $table->string('phone', 32)->nullable()->comment('聯絡電話');
            $table->string('country_code', 2)->comment('國家代碼 ISO 3166-1 alpha-2');
            $table->foreignId('state_id')->nullable()->comment('第一級行政區劃（州/省/縣市）');
            $table->foreignId('city_id')->nullable()->comment('第二級行政區劃（市/區/鄉鎮）');
            $table->string('postcode', 16)->nullable()->comment('郵遞區號');
            $table->string('address_1', 255)->comment('地址第一行');
            $table->string('address_2', 255)->nullable()->comment('地址第二行');
            $table->boolean('is_default')->default(false)->comment('是否為預設地址');
            $table->timestamps();

            $table->foreign('country_code')->references('iso_code_2')->on('countries');
            $table->foreign('state_id')->references('id')->on('divisions')->nullOnDelete();
            $table->foreign('city_id')->references('id')->on('divisions')->nullOnDelete();
            $table->index(['user_id', 'type']);
            $table->index(['user_id', 'is_default']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
