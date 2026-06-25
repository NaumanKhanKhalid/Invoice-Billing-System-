<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\User::updateOrCreate(
            ['email' => env('SUPER_ADMIN_EMAIL', 'admin@yourapp.com')],
            [
                'name'     => 'Super Admin',
                'password' => bcrypt(env('SUPER_ADMIN_PASSWORD', 'admin123')),
            ]
        );
    }
}
