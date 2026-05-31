<?php

namespace Database\Seeders;

use App\Models\Client;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = 1;

        $clients = [
            ['name' => 'Ahmed Raza', 'email' => 'ahmed@techsolutions.pk', 'phone' => '+92 300 1234567', 'company_name' => 'Tech Solutions Pvt Ltd', 'city' => 'Karachi', 'tax_number' => '1234567-0'],
            ['name' => 'Fatima Ali', 'email' => 'fatima@digitalcraft.pk', 'phone' => '+92 321 9876543', 'company_name' => 'Digital Craft Studio', 'city' => 'Lahore', 'tax_number' => '7654321-0'],
            ['name' => 'Muhammad Usman', 'email' => 'usman@pkretail.com', 'phone' => '+92 333 4567890', 'company_name' => 'PK Retail Group', 'city' => 'Islamabad', 'tax_number' => '2345678-0'],
            ['name' => 'Sana Malik', 'email' => 'sana@creativeagency.pk', 'phone' => '+92 315 6789012', 'company_name' => 'Creative Agency Pakistan', 'city' => 'Karachi', 'tax_number' => '8765432-0'],
            ['name' => 'Bilal Khan', 'email' => 'bilal@ecommercepk.com', 'phone' => '+92 345 2345678', 'company_name' => 'E-Commerce PK', 'city' => 'Lahore', 'tax_number' => '3456789-0'],
            ['name' => 'Ayesha Siddiqui', 'email' => 'ayesha@medicalplus.pk', 'phone' => '+92 311 3456789', 'company_name' => 'Medical Plus Clinic', 'city' => 'Faisalabad', 'tax_number' => '9876543-0'],
            ['name' => 'Omar Sheikh', 'email' => 'omar@constructpk.com', 'phone' => '+92 322 4567891', 'company_name' => 'Construct Pakistan', 'city' => 'Rawalpindi', 'tax_number' => '4567890-0'],
            ['name' => 'Hira Baig', 'email' => 'hira@fashionhub.pk', 'phone' => '+92 334 5678902', 'company_name' => 'Fashion Hub Pakistan', 'city' => 'Karachi', 'tax_number' => '0123456-0'],
            ['name' => 'Tariq Mehmood', 'email' => 'tariq@logisticspk.com', 'phone' => '+92 301 6789013', 'company_name' => 'Logistics PK', 'city' => 'Multan', 'tax_number' => '5678901-0'],
            ['name' => 'Zainab Qureshi', 'email' => 'zainab@edutech.pk', 'phone' => '+92 312 7890124', 'company_name' => 'EduTech Pakistan', 'city' => 'Peshawar', 'tax_number' => '6789012-0'],
        ];

        foreach ($clients as $i => $data) {
            Client::create(array_merge($data, [
                'user_id' => $adminId,
                'country' => 'Pakistan',
                'address' => 'Block ' . ($i + 1) . ', Main Boulevard',
                'is_active' => $i < 8,
            ]));
        }
    }
}
