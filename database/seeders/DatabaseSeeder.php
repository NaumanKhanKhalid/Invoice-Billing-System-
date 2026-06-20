<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            ChickenTypeSeeder::class,
            DailyRateSeeder::class,
            SupplierSeeder::class,
            CustomerSeeder::class,
            StaffSeeder::class,
            SampleDataSeeder::class,
        ]);
    }
}
