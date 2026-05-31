<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            'company_name' => 'InvoicePro Ltd.',
            'company_email' => 'billing@invoicepro.pk',
            'company_phone' => '+92 21 1234567',
            'company_address' => 'Suite 301, Business Center, Main Boulevard, Karachi, Pakistan',
            'invoice_prefix' => 'INV',
            'default_tax_rate' => '5',
            'default_terms' => 'Payment is due within 30 days of invoice date. Late payments may incur a 2% monthly fee. Please make payments via bank transfer or EasyPaisa.',
        ];

        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(['setting_key' => $key], ['setting_value' => $value]);
        }
    }
}
