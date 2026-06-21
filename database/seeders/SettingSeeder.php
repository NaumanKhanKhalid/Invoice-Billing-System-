<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            'company_name'    => 'Anwar Chicken Center',
            'company_email'   => 'anwar@example.pk',
            'company_phone'   => '+92 21 1234567',
            'company_address' => 'Main Market, Karachi, Pakistan',
        ];

        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(['setting_key' => $key], ['setting_value' => $value]);
        }
    }
}
