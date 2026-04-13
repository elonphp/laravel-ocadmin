<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sys_menus', function (Blueprint $table) {
            $table->id();
            $table->string('portal', 20);
            $table->foreignId('parent_id')->nullable()->constrained('sys_menus')->cascadeOnDelete();
            $table->string('permission_name')->nullable()->comment('關聯 acl_permissions.name');
            $table->string('route_name')->nullable()->comment('本系統 named route');
            $table->string('href')->nullable()->comment('外部連結或靜態 URL');
            $table->string('icon')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['portal', 'parent_id', 'sort_order']);
        });

        Schema::create('sys_menu_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id')->constrained('sys_menus')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('locale', 10);
            $table->string('display_name', 100);
            $table->unique(['menu_id', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sys_menu_translations');
        Schema::dropIfExists('sys_menus');
    }
};
