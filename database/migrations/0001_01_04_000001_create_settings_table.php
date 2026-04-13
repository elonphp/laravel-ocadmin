<?php

use App\Enums\System\SettingType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sys_settings', function (Blueprint $table) {
            $table->id();
            $table->string('group', 100)->nullable()->comment('群組');
            $table->string('code')->unique()->comment('設定代碼');
            $table->text('value')->nullable();

            $table->enum('type', SettingType::values())
                ->default(SettingType::Text->value)
                ->comment('設定值類型');

            $table->boolean('is_autoload')->default(false)->comment('啟動時自動載入至 Config');
            $table->string('note')->nullable()->comment('備註');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sys_settings');
    }
};
