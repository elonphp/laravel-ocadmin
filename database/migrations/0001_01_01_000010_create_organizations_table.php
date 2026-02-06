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
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->string('business_no', 20)->nullable()->comment('統一編號');
            $table->string('shipping_state')->nullable()->comment('州/省/縣市');
            $table->string('shipping_city')->nullable()->comment('區/鄉/鎮');
            $table->string('shipping_address1')->nullable()->comment('地址1');
            $table->string('shipping_address2')->nullable()->comment('地址2');
            $table->timestamps();
        });

        Schema::create('organization_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->string('locale', 10);
            $table->string('name', 200);
            $table->string('short_name', 100)->nullable();
            $table->unique(['organization_id', 'locale']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organization_translations');
        Schema::dropIfExists('organizations');
    }
};
