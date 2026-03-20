<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = config('permission.table_names.permissions', 'permissions');

        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            $table->unsignedBigInteger('parent_id')->nullable()->after('id');
            $table->string('type')->nullable()->after('guard_name')->comment('menu or action');
            $table->string('icon')->nullable()->after('type');
            $table->unsignedInteger('sort_order')->default(0)->after('icon');
            $table->boolean('is_active')->default(true)->after('sort_order');

            $table->foreign('parent_id')
                ->references('id')
                ->on($tableName)
                ->nullOnDelete();

            $table->index('parent_id');
            $table->index('type');
        });
    }

    public function down(): void
    {
        $tableName = config('permission.table_names.permissions', 'permissions');

        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            $table->dropForeign([$tableName . '_parent_id_foreign']);
            $table->dropIndex([$tableName . '_parent_id_index']);
            $table->dropIndex([$tableName . '_type_index']);
            $table->dropColumn(['parent_id', 'type', 'icon', 'sort_order', 'is_active']);
        });
    }
};
