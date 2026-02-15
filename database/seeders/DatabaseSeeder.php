<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AclPermissionSeeder::class,
            AclRoleSeeder::class,
            UserSeeder::class,
            OrganizationSeeder::class,
            CompanySeeder::class,
            DepartmentSeeder::class,
            EmployeeSeeder::class,
            TaxonomyTermSeeder::class,
            OptionSeeder::class,
            OptionValueGroupSeeder::class,
        ]);
    }
}
