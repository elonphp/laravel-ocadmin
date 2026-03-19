<?php

namespace Database\Seeders;

use App\Enums\System\SettingType;
use App\Models\System\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            // Portal IP 白名單（is_autoload=true，由 SettingServiceProvider 預載）
            ['group' => 'portal', 'code' => 'admin_allowed_ips', 'value' => '127.0.0.1,::1', 'type' => SettingType::Array, 'is_autoload' => true, 'note' => 'Admin Portal IP 白名單（逗號分隔 IP/CIDR）'],
            ['group' => 'portal', 'code' => 'api_allowed_ips',   'value' => '',               'type' => SettingType::Array, 'is_autoload' => true, 'note' => 'API Portal IP 白名單（逗號分隔 IP/CIDR）'],

            // 一般設定
            ['group' => 'config', 'code' => 'config_admin_per_page',       'value' => '10',   'type' => SettingType::Int,  'note' => '後台列表每頁筆數'],
            ['group' => 'config', 'code' => 'config_login_attempts',       'value' => '5',    'type' => SettingType::Int,  'note' => '登入錯誤次數'],

            // 圖片尺寸
            ['group' => 'config', 'code' => 'config_image_thumb_width',    'value' => '300',  'type' => SettingType::Int,  'note' => '縮圖寬'],
            ['group' => 'config', 'code' => 'config_image_thumb_height',   'value' => '300',  'type' => SettingType::Int,  'note' => '縮圖高'],
            ['group' => 'config', 'code' => 'config_image_popup_width',    'value' => '1280', 'type' => SettingType::Int,  'note' => '彈窗圖片寬'],
            ['group' => 'config', 'code' => 'config_image_popup_height',   'value' => '1280', 'type' => SettingType::Int,  'note' => '彈窗圖片高'],

            // 郵件
            ['group' => 'config', 'code' => 'config_mail_engine',          'value' => 'smtp', 'type' => SettingType::Text, 'note' => '郵件引擎（mail / smtp）'],
            ['group' => 'config', 'code' => 'config_mail_smtp_hostname',   'value' => '',     'type' => SettingType::Text, 'note' => 'SMTP 主機名稱'],
            ['group' => 'config', 'code' => 'config_mail_smtp_username',   'value' => '',     'type' => SettingType::Text, 'note' => 'SMTP 帳號'],
            ['group' => 'config', 'code' => 'config_mail_smtp_password',   'value' => '',     'type' => SettingType::Text, 'note' => 'SMTP 密碼'],
            ['group' => 'config', 'code' => 'config_mail_smtp_port',       'value' => '25',   'type' => SettingType::Int,  'note' => 'SMTP Port'],
            ['group' => 'config', 'code' => 'config_mail_smtp_timeout',    'value' => '60',   'type' => SettingType::Int,  'note' => 'SMTP 逾時秒數'],

            // 上傳
            ['group' => 'config', 'code' => 'config_file_max_size',        'value' => '2',    'type' => SettingType::Int,  'note' => '上傳檔案最大容量（MB）'],
        ];

        foreach ($items as $item) {
            Setting::updateOrCreate(
                ['code' => $item['code']],
                $item
            );
        }
    }
}
