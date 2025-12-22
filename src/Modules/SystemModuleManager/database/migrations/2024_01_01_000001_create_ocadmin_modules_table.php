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
        Schema::create('ocadmin_modules', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->comment('模組名稱');
            $table->string('alias', 100)->unique()->comment('模組別名');
            $table->enum('source', ['package', 'custom'])->default('package')->comment('來源');
            $table->string('version', 20)->nullable()->comment('版本號');
            $table->boolean('enabled')->default(false)->comment('是否啟用');
            $table->timestamp('installed_at')->nullable()->comment('安裝時間');
            $table->json('config')->nullable()->comment('模組設定');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ocadmin_modules');
    }
};
