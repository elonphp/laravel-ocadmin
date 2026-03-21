<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('acl_portal_users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('portal', 20)->comment('Portal 識別碼（admin, hrm, www, ...）');
            $table->timestamp('enrolled_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->unique(['user_id', 'portal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('acl_portal_users');
    }
};
