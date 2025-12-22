<?php

use App\Enums\System\SettingType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('locale', 10)->default('')->comment('語言代碼');
            $table->string('code', 50)->comment('設定命名空間 / 模組代碼');
            $table->string('key', 100)->comment('設定鍵');
            $table->text('content')->nullable();
            
            $table->enum('type', SettingType::values())
                ->default(SettingType::Text->value)
                ->comment('設定值類型');

            $table->string('note')->nullable()->comment('備註');
            $table->timestamps();

            $table->unique(['locale', 'code', 'key'], 'uniq_locale_code_key');
        });

        // 新增預設資料
        DB::table('settings')->insert([
            'locale' => '',
            'code' => 'ocadmin.config',
            'key' => 'per_page',
            'content' => '10',
            'note' => '一頁幾筆',
            'type' => SettingType::Text->value,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
