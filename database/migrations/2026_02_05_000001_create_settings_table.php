<?php

use App\Enums\System\SettingType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('locale', 10)->default('')->comment('語言代碼');
            $table->string('group', 100)->nullable()->comment('群組');
            $table->string('code');
            $table->text('value')->nullable();

            $table->enum('type', SettingType::values())
                ->default(SettingType::Text->value)
                ->comment('設定值類型');

            $table->string('note')->nullable()->comment('備註');
            $table->timestamps();

            $table->unique(['locale', 'code'], 'settings_unique_code');
        });

        DB::table('settings')->insert([
            'group' => 'config',
            'locale' => '',
            'code' => 'config_admin_limit',
            'value' => '10',
            'note' => '一頁幾筆',
            'type' => SettingType::Text->value,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
