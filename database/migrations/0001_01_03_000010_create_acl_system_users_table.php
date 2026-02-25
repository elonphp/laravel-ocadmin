<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('acl_system_users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('app', 20)->comment('角色群組識別（super_admin, admin, ess, ...）'); //可以用portal名稱，但有可能小系統全部使用admin.前綴，因此欄位名稱使用app，可以自訂應用。
            $table->timestamp('enrolled_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->index(['user_id', 'app']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('acl_system_users');
    }
};
