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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('code', 32)->unique()->comment('訂單編號');
            $table->foreignId('organization_id')->constrained()->comment('經銷商');
            $table->foreignId('user_id')->constrained()->comment('建立者');
            $table->string('status', 20)->default('draft')->comment('狀態');
            $table->decimal('total_amount', 12, 2)->default(0)->comment('總金額');
            $table->text('note')->nullable()->comment('備註');
            $table->timestamps();

            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
