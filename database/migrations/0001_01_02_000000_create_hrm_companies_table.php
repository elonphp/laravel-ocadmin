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
        Schema::create('hrm_companies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()
                  ->constrained('hrm_companies')->nullOnDelete();
            $table->string('code', 20)->nullable()->unique();
            $table->string('business_no', 20)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('address')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('hrm_company_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('hrm_companies')->cascadeOnDelete();
            $table->string('locale', 10);
            $table->string('name', 200);
            $table->string('short_name', 100)->nullable();
            $table->unique(['company_id', 'locale']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hrm_company_translations');
        Schema::dropIfExists('hrm_companies');
    }
};
