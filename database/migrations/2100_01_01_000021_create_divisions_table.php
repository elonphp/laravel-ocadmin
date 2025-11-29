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
        Schema::create('divisions', function (Blueprint $table) {
            $table->id();
            $table->string('country_code', 2)->comment('iso_code_2');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->tinyInteger('level');
            $table->string('name', 128);
            $table->string('native_name');
            $table->string('code', 32);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('country_code')->references('iso_code_2')->on('countries')->onDelete('cascade');
            $table->index(['country_code', 'is_active']);
            $table->index('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('divisions');
    }
};
