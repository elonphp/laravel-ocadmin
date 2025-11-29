<?php

namespace Portals\Ocadmin\Services\System\Localization;

use App\Models\System\Localization\Division;

class DivisionService
{
    /**
     * 建立行政區域
     * 注意：不包含 Transaction，由 Controller 控制
     */
    public function create(array $data): Division
    {
        $data = Division::withDefaults($data);

        return Division::create($data);
    }

    /**
     * 更新行政區域
     */
    public function update(Division $division, array $data): Division
    {
        $data = Division::withDefaults($data);

        $division->update($data);

        return $division;
    }

    /**
     * 刪除行政區域
     */
    public function delete(Division $division): void
    {
        $division->delete();
    }

    /**
     * 批次刪除
     */
    public function batchDelete(array $ids): int
    {
        return Division::whereIn('id', $ids)->delete();
    }
}
