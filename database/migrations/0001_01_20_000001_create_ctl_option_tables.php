<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ctl_options', function (Blueprint $table) {
            $table->id();
            $table->string('type', 20)->default('select');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('ctl_option_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('option_id')->constrained('ctl_options')->cascadeOnDelete();
            $table->string('locale', 10);
            $table->string('name', 128);
            $table->string('short_name', 128)->nullable();

            $table->unique(['option_id', 'locale']);
        });

        Schema::create('ctl_option_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('option_id')->constrained('ctl_options')->cascadeOnDelete();
            $table->string('image', 255)->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('ctl_option_value_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('option_value_id')->constrained('ctl_option_values')->cascadeOnDelete();
            $table->string('locale', 10);
            $table->string('name', 128);
            $table->string('short_name', 128)->nullable();

            $table->unique(['option_value_id', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ctl_option_value_translations');
        Schema::dropIfExists('ctl_option_values');
        Schema::dropIfExists('ctl_option_translations');
        Schema::dropIfExists('ctl_options');
    }
};
