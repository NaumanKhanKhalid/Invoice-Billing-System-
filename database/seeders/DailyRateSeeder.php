<?php

namespace Database\Seeders;

use App\Models\ChickenType;
use App\Models\DailyRate;
use Illuminate\Database\Seeder;

class DailyRateSeeder extends Seeder
{
    public function run(): void
    {
        $broiler = ChickenType::where('name', 'Broiler')->first();
        $desi    = ChickenType::where('name', 'Desi')->first();

        DailyRate::insert([
            [
                'chicken_type_id'    => $broiler->id,
                'live_rate_per_kg'   => 380.00,
                'retail_rate_per_kg' => 420.00,
                'supply_rate_per_kg' => 550.00,
                'date'               => today()->toDateString(),
                'notes'              => 'Opening rate',
                'created_at'         => now(),
                'updated_at'         => now(),
            ],
            [
                'chicken_type_id'    => $desi->id,
                'live_rate_per_kg'   => 600.00,
                'retail_rate_per_kg' => 680.00,
                'supply_rate_per_kg' => 850.00,
                'date'               => today()->toDateString(),
                'notes'              => 'Opening rate',
                'created_at'         => now(),
                'updated_at'         => now(),
            ],
        ]);
    }
}
