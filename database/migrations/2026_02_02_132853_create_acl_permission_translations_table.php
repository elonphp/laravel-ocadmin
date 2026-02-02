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
        Schema::create('acl_permission_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permission_id')->constrained('acl_permissions')->cascadeOnDelete();
            $table->string('locale', 10);
            $table->string('display_name', 100);
            $table->text('note')->nullable();

            $table->unique(['permission_id', 'locale']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('acl_permission_translations');
    }
};
