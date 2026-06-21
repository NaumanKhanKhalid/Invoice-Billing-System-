<?php

namespace Database\Seeders;

use App\Models\DailyRate;
use Illuminate\Database\Seeder;

class DailyRateSeeder extends Seeder
{
    public function run(): void
    {
        DailyRate::updateOrCreate(
            ['date' => today()->toDateString()],
            [
                'live_rate_per_kg'   => 380.00,
                'retail_rate_per_kg' => 420.00,
                'supply_rate_per_kg' => 550.00,
                'notes'              => 'Opening rate',
            ]
        );
    }
}
