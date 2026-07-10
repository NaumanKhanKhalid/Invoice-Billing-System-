<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            SettingSeeder::class,
            UserSeeder::class,
            ChickenTypeSeeder::class,
            DailyRateSeeder::class,
            SupplierSeeder::class,
            CustomerSeeder::class,
            StaffSeeder::class,
            ClientSeeder::class,
            ProductSeeder::class,
            SampleDataSeeder::class,
            InvoiceSeeder::class,
            PaymentSeeder::class,
        ]);
    }
}
