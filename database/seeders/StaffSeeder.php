<?php

namespace Database\Seeders;

use App\Models\Staff;
use Illuminate\Database\Seeder;

class StaffSeeder extends Seeder
{
    public function run(): void
    {
        Staff::insert([
            ['name' => 'Ahmad',  'phone' => '03201234567', 'role' => 'manager', 'salary' => 35000, 'joining_date' => '2024-01-01', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Bilal',  'phone' => '03212345678', 'role' => 'butcher',  'salary' => 25000, 'joining_date' => '2024-01-01', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
