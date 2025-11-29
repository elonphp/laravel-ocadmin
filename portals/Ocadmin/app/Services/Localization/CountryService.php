<?php

namespace Portals\Ocadmin\Services\Localization;

use App\Models\Localization\Country;

class CountryService
{
    /**
     * 建立國家
     * 注意：不包含 Transaction，由 Controller 控制
     */
    public function create(array $data): Country
    {
        $data = Country::withDefaults($data);

        return Country::create($data);
    }

    /**
     * 更新國家
     */
    public function update(Country $country, array $data): Country
    {
        $data = Country::withDefaults($data);

        $country->update($data);

        return $country;
    }

    /**
     * 刪除國家
     */
    public function delete(Country $country): void
    {
        // 若有關聯表（如 zones），需先刪除
        // $country->zones()->delete();

        $country->delete();
    }

    /**
     * 批次刪除
     */
    public function batchDelete(array $ids): int
    {
        return Country::whereIn('id', $ids)->delete();
    }
}
