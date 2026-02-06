<?php

namespace Database\Seeders;

use App\Models\Company;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('zh_TW');

        $companies = [
            // 1. 集團母公司
            [
                'parent_id'   => null,
                'code'        => 'TX',
                'business_no' => $faker->numerify('########'),
                'phone'       => $faker->phoneNumber(),
                'address'     => $faker->address(),
                'sort_order'  => 1,
                'translations' => [
                    'zh_Hant' => ['name' => '天行集團股份有限公司', 'short_name' => '天行集團'],
                    'en'      => ['name' => 'TianXing Group Corp.', 'short_name' => 'TX Group'],
                ],
            ],
            // 2. 子公司 A
            [
                'parent_id'   => 1,
                'code'        => 'XH',
                'business_no' => $faker->numerify('########'),
                'phone'       => $faker->phoneNumber(),
                'address'     => $faker->address(),
                'sort_order'  => 2,
                'translations' => [
                    'zh_Hant' => ['name' => '星河科技股份有限公司', 'short_name' => '星河科技'],
                    'en'      => ['name' => 'XingHe Technology Co., Ltd.', 'short_name' => 'XingHe Tech'],
                ],
            ],
            // 3. 子公司 B
            [
                'parent_id'   => 1,
                'code'        => 'YD',
                'business_no' => $faker->numerify('########'),
                'phone'       => $faker->phoneNumber(),
                'address'     => $faker->address(),
                'sort_order'  => 3,
                'translations' => [
                    'zh_Hant' => ['name' => '雲端數位股份有限公司', 'short_name' => '雲端數位'],
                    'en'      => ['name' => 'YunDuan Digital Co., Ltd.', 'short_name' => 'YunDuan'],
                ],
            ],
            // 4. 獨立公司
            [
                'parent_id'   => null,
                'code'        => 'CG',
                'business_no' => $faker->numerify('########'),
                'phone'       => $faker->phoneNumber(),
                'address'     => $faker->address(),
                'sort_order'  => 10,
                'translations' => [
                    'zh_Hant' => ['name' => '晨光創意股份有限公司', 'short_name' => '晨光創意'],
                    'en'      => ['name' => 'ChenGuang Creative Co., Ltd.', 'short_name' => 'ChenGuang'],
                ],
            ],
        ];

        foreach ($companies as $companyData) {
            $translations = $companyData['translations'];
            unset($companyData['translations']);

            $company = Company::create($companyData);
            $company->saveTranslations($translations);
        }
    }
}
