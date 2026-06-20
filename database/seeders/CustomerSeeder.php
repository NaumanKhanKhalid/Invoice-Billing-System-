<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $customers = [
            ['name' => 'Walk-in Retail',            'phone' => '0000000000', 'type' => 'retail',     'credit_days' => 0,  'credit_limit' => 0],
            ['name' => 'Hotel Al-Harmain',           'phone' => '03111234567','type' => 'hotel',      'credit_days' => 30, 'credit_limit' => 50000, 'whatsapp_number' => '923111234567'],
            ['name' => 'Restaurant Bundu Khan',      'phone' => '03122345678','type' => 'restaurant', 'credit_days' => 15, 'credit_limit' => 30000, 'whatsapp_number' => '923122345678'],
            ['name' => 'Ali General Store',          'phone' => '03133456789','type' => 'reseller',   'credit_days' => 7,  'credit_limit' => 20000],
            ['name' => 'Karachi Hotel Supplies Co',  'phone' => '03144567890','type' => 'company',    'credit_days' => 30, 'credit_limit' => 100000,'whatsapp_number' => '923144567890'],
        ];

        foreach ($customers as $c) {
            Customer::create(array_merge($c, [
                'current_balance' => 0,
                'is_active'       => true,
                'is_blacklisted'  => false,
            ]));
        }
    }
}
