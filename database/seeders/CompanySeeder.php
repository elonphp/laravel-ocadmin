<?php

namespace Database\Seeders;

use App\Models\Hrm\Company;
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
                'name'        => '天行集團股份有限公司',
                'short_name'  => '天行集團',
                'business_no' => $faker->numerify('########'),
                'phone'       => $faker->phoneNumber(),
                'address'     => $faker->address(),
                'sort_order'  => 1,
            ],
            // 2. 子公司 A
            [
                'parent_id'   => 1,
                'code'        => 'XH',
                'name'        => '星河科技股份有限公司',
                'short_name'  => '星河科技',
                'business_no' => $faker->numerify('########'),
                'phone'       => $faker->phoneNumber(),
                'address'     => $faker->address(),
                'sort_order'  => 2,
            ],
            // 3. 子公司 B
            [
                'parent_id'   => 1,
                'code'        => 'YD',
                'name'        => '雲端數位股份有限公司',
                'short_name'  => '雲端數位',
                'business_no' => $faker->numerify('########'),
                'phone'       => $faker->phoneNumber(),
                'address'     => $faker->address(),
                'sort_order'  => 3,
            ],
            // 4. 獨立公司
            [
                'parent_id'   => null,
                'code'        => 'CG',
                'name'        => '晨光創意股份有限公司',
                'short_name'  => '晨光創意',
                'business_no' => $faker->numerify('########'),
                'phone'       => $faker->phoneNumber(),
                'address'     => $faker->address(),
                'sort_order'  => 10,
            ],
        ];

        foreach ($companies as $companyData) {
            Company::create($companyData);
        }
    }
}
