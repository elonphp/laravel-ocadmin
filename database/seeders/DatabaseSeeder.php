<?php

namespace Database\Seeders;

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
            CountrySeeder::class,
            DivisionSeeder::class,
            RolePermissionSeeder::class,  // 必須在 UserSeeder 之前
            UserSeeder::class,
        ]);
    }
}
