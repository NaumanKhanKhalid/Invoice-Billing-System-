<?php

namespace Database\Seeders;

use App\Models\ChickenType;
use Illuminate\Database\Seeder;

class ChickenTypeSeeder extends Seeder
{
    public function run(): void
    {
        ChickenType::insert([
            ['name' => 'Broiler', 'description' => 'Standard broiler chicken', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Desi',    'description' => 'Country/desi chicken',     'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
