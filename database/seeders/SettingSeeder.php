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
            ['group' => 'portal', 'code' => 'admin_allowed_ips', 'name' => 'Admin Portal IP 白名單', 'name_translations' => ['zh_Hant' => 'Admin Portal IP 白名單', 'en' => 'Admin Portal IP Whitelist'], 'value' => '127.0.0.1,::1', 'type' => SettingType::Array, 'is_autoload' => true, 'is_active' => true, 'note' => 'Admin Portal IP 白名單（逗號分隔 IP/CIDR）'],
            ['group' => 'portal', 'code' => 'api_allowed_ips',   'name' => 'API Portal IP 白名單',   'name_translations' => ['zh_Hant' => 'API Portal IP 白名單',   'en' => 'API Portal IP Whitelist'],   'value' => '',               'type' => SettingType::Array, 'is_autoload' => true, 'is_active' => true, 'note' => 'API Portal IP 白名單（逗號分隔 IP/CIDR）'],

            // 一般設定
            ['group' => 'config', 'code' => 'config_admin_per_page',       'name' => '後台列表每頁筆數', 'name_translations' => ['zh_Hant' => '後台列表每頁筆數', 'en' => 'Admin List Per Page'],  'value' => '10',   'type' => SettingType::Int,  'is_autoload' => false, 'is_active' => true, 'note' => '後台列表每頁筆數'],
            ['group' => 'config', 'code' => 'config_login_attempts',       'name' => '登入錯誤次數',     'name_translations' => ['zh_Hant' => '登入錯誤次數',     'en' => 'Login Attempts'],       'value' => '5',    'type' => SettingType::Int,  'is_autoload' => false, 'is_active' => true, 'note' => '登入錯誤次數'],

            // 圖片尺寸
            ['group' => 'config', 'code' => 'config_image_thumb_width',    'name' => '縮圖寬',           'name_translations' => ['zh_Hant' => '縮圖寬',           'en' => 'Thumbnail Width'],      'value' => '300',  'type' => SettingType::Int,  'is_autoload' => false, 'is_active' => true, 'note' => '縮圖寬'],
            ['group' => 'config', 'code' => 'config_image_thumb_height',   'name' => '縮圖高',           'name_translations' => ['zh_Hant' => '縮圖高',           'en' => 'Thumbnail Height'],     'value' => '300',  'type' => SettingType::Int,  'is_autoload' => false, 'is_active' => true, 'note' => '縮圖高'],
            ['group' => 'config', 'code' => 'config_image_popup_width',    'name' => '彈窗圖片寬',       'name_translations' => ['zh_Hant' => '彈窗圖片寬',       'en' => 'Popup Image Width'],    'value' => '1280', 'type' => SettingType::Int,  'is_autoload' => false, 'is_active' => true, 'note' => '彈窗圖片寬'],
            ['group' => 'config', 'code' => 'config_image_popup_height',   'name' => '彈窗圖片高',       'name_translations' => ['zh_Hant' => '彈窗圖片高',       'en' => 'Popup Image Height'],   'value' => '1280', 'type' => SettingType::Int,  'is_autoload' => false, 'is_active' => true, 'note' => '彈窗圖片高'],

            // 郵件
            ['group' => 'config', 'code' => 'config_mail_engine',          'name' => '郵件引擎',         'name_translations' => ['zh_Hant' => '郵件引擎',         'en' => 'Mail Engine'],          'value' => 'smtp', 'type' => SettingType::Text, 'is_autoload' => false, 'is_active' => true, 'note' => '郵件引擎（mail / smtp）'],
            ['group' => 'config', 'code' => 'config_mail_smtp_hostname',   'name' => 'SMTP 主機名稱',    'name_translations' => ['zh_Hant' => 'SMTP 主機名稱',    'en' => 'SMTP Hostname'],        'value' => '',     'type' => SettingType::Text, 'is_autoload' => false, 'is_active' => true, 'note' => 'SMTP 主機名稱'],
            ['group' => 'config', 'code' => 'config_mail_smtp_username',   'name' => 'SMTP 帳號',        'name_translations' => ['zh_Hant' => 'SMTP 帳號',        'en' => 'SMTP Username'],        'value' => '',     'type' => SettingType::Text, 'is_autoload' => false, 'is_active' => true, 'note' => 'SMTP 帳號'],
            ['group' => 'config', 'code' => 'config_mail_smtp_password',   'name' => 'SMTP 密碼',        'name_translations' => ['zh_Hant' => 'SMTP 密碼',        'en' => 'SMTP Password'],        'value' => '',     'type' => SettingType::Text, 'is_autoload' => false, 'is_active' => true, 'note' => 'SMTP 密碼'],
            ['group' => 'config', 'code' => 'config_mail_smtp_port',       'name' => 'SMTP Port',        'name_translations' => ['zh_Hant' => 'SMTP Port',        'en' => 'SMTP Port'],            'value' => '25',   'type' => SettingType::Int,  'is_autoload' => false, 'is_active' => true, 'note' => 'SMTP Port'],
            ['group' => 'config', 'code' => 'config_mail_smtp_timeout',    'name' => 'SMTP 逾時秒數',    'name_translations' => ['zh_Hant' => 'SMTP 逾時秒數',    'en' => 'SMTP Timeout'],         'value' => '60',   'type' => SettingType::Int,  'is_autoload' => false, 'is_active' => true, 'note' => 'SMTP 逾時秒數'],

            // 上傳
            ['group' => 'config', 'code' => 'config_file_max_size',        'name' => '上傳檔案最大容量', 'name_translations' => ['zh_Hant' => '上傳檔案最大容量', 'en' => 'Max Upload Size'],      'value' => '2',    'type' => SettingType::Int,  'is_autoload' => false, 'is_active' => true, 'note' => '上傳檔案最大容量（MB）'],
        ];

        foreach ($items as $item) {
            Setting::updateOrCreate(
                ['code' => $item['code']],
                $item
            );
        }
    }
}
