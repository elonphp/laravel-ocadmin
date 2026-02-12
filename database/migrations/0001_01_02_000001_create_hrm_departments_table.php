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
        Schema::create('hrm_departments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('hrm_companies')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()
                  ->constrained('hrm_departments')->nullOnDelete();
            $table->string('name', 100);
            $table->string('code', 20)->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hrm_departments');
    }
};
