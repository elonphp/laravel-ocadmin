<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('device_name');
            $table->string('device_fingerprint', 64);
            $table->string('ip_address', 45);
            $table->string('location')->nullable();
            $table->timestamp('last_active_at')->nullable();
            $table->boolean('is_current')->default(false);
            $table->timestamp('trusted_until')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'device_fingerprint']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_devices');
    }
};
