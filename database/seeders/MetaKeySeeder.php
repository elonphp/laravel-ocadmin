<?php

namespace Database\Seeders;

use App\Models\System\Database\MetaKey;
use App\Services\System\Database\TranslationTableSyncService;
use Illuminate\Database\Seeder;

class MetaKeySeeder extends Seeder
{
    public function run(): void
    {
        $keys = [
            // terms 表的 meta keys
            ['name' => 'name', 'table_name' => 'terms', 'data_type' => 'varchar', 'description' => '名稱'],
            ['name' => 'description', 'table_name' => 'terms', 'data_type' => 'text', 'description' => '描述'],

            // taxonomies 表的 meta keys
            ['name' => 'name', 'table_name' => 'taxonomies', 'data_type' => 'varchar', 'description' => '分類名稱'],
        ];

        foreach ($keys as $key) {
            MetaKey::updateOrCreate(
                ['name' => $key['name'], 'table_name' => $key['table_name']],
                $key
            );
        }

        // 同步 sysdata translations 表結構
        $syncService = app(TranslationTableSyncService::class);
        $tableNames = collect($keys)->pluck('table_name')->unique()->filter();
        foreach ($tableNames as $tableName) {
            $syncService->syncTableStructure($tableName);
        }
    }
}
