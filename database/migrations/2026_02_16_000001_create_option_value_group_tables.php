<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clg_option_value_groups', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('clg_option_value_group_translations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('option_value_group_id');
            $table->string('locale', 10);
            $table->string('name', 128);
            $table->text('description')->nullable();

            $table->foreign('option_value_group_id', 'ovg_trans_group_id_fk')
                  ->references('id')->on('clg_option_value_groups')->cascadeOnDelete();
            $table->unique(['option_value_group_id', 'locale'], 'ovg_trans_group_locale_unique');
        });

        Schema::create('clg_option_value_group_levels', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('option_value_group_id');
            $table->unsignedBigInteger('option_id');
            $table->unsignedTinyInteger('level');

            $table->foreign('option_value_group_id', 'ovg_levels_group_id_fk')
                  ->references('id')->on('clg_option_value_groups')->cascadeOnDelete();
            $table->foreign('option_id', 'ovg_levels_option_id_fk')
                  ->references('id')->on('clg_options')->cascadeOnDelete();
            $table->unique(['option_value_group_id', 'option_id'], 'ovg_levels_group_option_unique');
            $table->unique(['option_value_group_id', 'level'], 'ovg_levels_group_level_unique');
        });

        Schema::create('clg_option_value_links', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_option_value_id');
            $table->unsignedBigInteger('child_option_value_id');

            $table->foreign('parent_option_value_id', 'ovl_parent_value_id_fk')
                  ->references('id')->on('clg_option_values')->cascadeOnDelete();
            $table->foreign('child_option_value_id', 'ovl_child_value_id_fk')
                  ->references('id')->on('clg_option_values')->cascadeOnDelete();
            $table->unique(['parent_option_value_id', 'child_option_value_id'], 'ovl_parent_child_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clg_option_value_links');
        Schema::dropIfExists('clg_option_value_group_levels');
        Schema::dropIfExists('clg_option_value_group_translations');
        Schema::dropIfExists('clg_option_value_groups');
    }
};
