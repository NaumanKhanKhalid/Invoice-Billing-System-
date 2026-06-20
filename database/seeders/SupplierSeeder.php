<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = [
            ['name' => 'Ali Poultry Farm',       'phone' => '03001234567', 'address' => 'Korangi, Karachi',       'credit_days' => 15],
            ['name' => 'Hassan Chicken Supply',   'phone' => '03012345678', 'address' => 'Landhi, Karachi',        'credit_days' => 15],
            ['name' => 'Karachi Broiler Center',  'phone' => '03023456789', 'address' => 'Orangi Town, Karachi',   'credit_days' => 7],
        ];

        foreach ($suppliers as $s) {
            Supplier::create(array_merge($s, ['balance' => 0, 'is_active' => true]));
        }
    }
}
