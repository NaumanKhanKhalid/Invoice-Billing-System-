<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            ['name' => 'Web Development', 'description' => 'Full-stack web application development', 'unit_price' => 5000, 'unit' => 'hour', 'tax_rate' => 5],
            ['name' => 'SEO Package', 'description' => 'Complete SEO optimization and monthly management', 'unit_price' => 15000, 'unit' => 'month', 'tax_rate' => 5],
            ['name' => 'Logo Design', 'description' => 'Professional logo design with multiple revisions', 'unit_price' => 12000, 'unit' => 'project', 'tax_rate' => 5],
            ['name' => 'Mobile App Development', 'description' => 'iOS/Android app development', 'unit_price' => 8000, 'unit' => 'hour', 'tax_rate' => 5],
            ['name' => 'Social Media Management', 'description' => 'Monthly social media content and management', 'unit_price' => 20000, 'unit' => 'month', 'tax_rate' => 5],
            ['name' => 'UI/UX Design', 'description' => 'User interface and experience design', 'unit_price' => 6000, 'unit' => 'hour', 'tax_rate' => 5],
            ['name' => 'Content Writing', 'description' => 'Professional content writing and copywriting', 'unit_price' => 2500, 'unit' => 'article', 'tax_rate' => 0],
            ['name' => 'Digital Marketing Package', 'description' => 'Complete digital marketing including PPC, SEO, and social media', 'unit_price' => 35000, 'unit' => 'month', 'tax_rate' => 5],
        ];

        foreach ($products as $data) {
            Product::create(array_merge($data, ['user_id' => 1, 'is_active' => true]));
        }
    }
}
