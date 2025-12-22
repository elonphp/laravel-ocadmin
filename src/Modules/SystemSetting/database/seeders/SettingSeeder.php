<?php

namespace Elonphp\LaravelOcadminModules\Modules\SystemSetting\Database\Seeders;

use Illuminate\Database\Seeder;
use Elonphp\LaravelOcadminModules\Modules\SystemSetting\Setting;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            [
                'group' => 'ocadmin',
                'code' => 'ocadmin_perpage',
                'content' => '10',
                'type' => 'int',
                'note' => '後台列表每頁顯示筆數',
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['locale' => '', 'code' => $setting['code']],
                $setting
            );
        }
    }
}
