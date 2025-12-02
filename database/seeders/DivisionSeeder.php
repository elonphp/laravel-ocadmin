<?php

namespace Database\Seeders;

use App\Models\System\Localization\Division;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DivisionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $csvPath = database_path('seeders/data/divisions.csv');

        if (!file_exists($csvPath)) {
            $this->command->error("CSV 檔案不存在: {$csvPath}");
            return;
        }

        // 讀取 CSV（處理 BOM）
        $content = file_get_contents($csvPath);
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content); // 移除 UTF-8 BOM

        $lines = array_filter(explode("\n", $content));
        $header = str_getcsv(array_shift($lines));

        // 建立 old_id => new_id 映射（用於 parent_id）
        $idMap = [];

        // 先清空表
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Division::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->command->info('開始匯入行政區域...');

        // 先匯入 level 1（縣市）
        $level1Data = [];
        $level2Data = [];

        foreach ($lines as $line) {
            $row = str_getcsv($line);
            if (count($row) < 10) continue;

            $data = array_combine($header, $row);

            if ((int) $data['level'] === 1) {
                $level1Data[] = $data;
            } else {
                $level2Data[] = $data;
            }
        }

        // 匯入 level 1
        foreach ($level1Data as $data) {
            $division = Division::create([
                'code'         => $data['code'] ?: null,
                'country_code' => $data['country_code'],
                'parent_id'    => null,
                'level'        => (int) $data['level'],
                'name'         => $data['name'],
                'native_name'  => $data['native_name'],
                'is_active'    => (bool) $data['is_active'],
                'sort_order'   => (int) $data['sort_order'],
            ]);

            $idMap[$data['id']] = $division->id;
        }

        $this->command->info('  - Level 1 (縣市): ' . count($level1Data) . ' 筆');

        // 匯入 level 2（使用映射後的 parent_id）
        foreach ($level2Data as $data) {
            $oldParentId = $data['parent_id'];
            $newParentId = $idMap[$oldParentId] ?? null;

            $division = Division::create([
                'code'         => $data['code'] ?: null,
                'country_code' => $data['country_code'],
                'parent_id'    => $newParentId,
                'level'        => (int) $data['level'],
                'name'         => $data['name'],
                'native_name'  => $data['native_name'],
                'is_active'    => (bool) $data['is_active'],
                'sort_order'   => (int) $data['sort_order'],
            ]);

            $idMap[$data['id']] = $division->id;
        }

        $this->command->info('  - Level 2 (鄉鎮區): ' . count($level2Data) . ' 筆');
        $this->command->info('匯入完成！總計 ' . (count($level1Data) + count($level2Data)) . ' 筆');
    }
}
