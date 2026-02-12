<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ctl_product_terms', function (Blueprint $table) {
            $table->foreignId('product_id')->constrained('ctl_products')->cascadeOnDelete();
            $table->foreignId('term_id')->constrained('terms')->cascadeOnDelete();

            $table->primary(['product_id', 'term_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ctl_product_terms');
    }
};
