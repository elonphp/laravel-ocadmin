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
        Schema::create('hrm_employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->string('employee_no', 20)->nullable()->unique();
            $table->string('first_name', 50);
            $table->string('last_name', 50)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('phone', 30)->nullable();
            $table->date('hire_date')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('gender', 10)->nullable()->comment('male / female / other');
            $table->string('job_title', 100)->nullable();
            $table->text('address')->nullable();
            $table->text('note')->nullable();
            $table->boolean('is_active')->default(true);

            // 排班預設時間
            $table->time('default_work_start')->nullable()->comment('預設上班時間');
            $table->time('default_work_end')->nullable()->comment('預設下班時間');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hrm_employees');
    }
};
