<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();          // basic / pro / business
            $table->string('name');
            $table->unsignedInteger('price')->default(0);
            $table->integer('max_users')->default(1);  // -1 = unlimited
            $table->boolean('staff_module')->default(false);
            $table->boolean('google_backup')->default(false);
            $table->boolean('priority_support')->default(false);
            $table->boolean('is_popular')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // Seed from the existing config/plans.php so nothing breaks on day one.
        $order = 0;
        foreach ((array) config('plans', []) as $key => $p) {
            \Illuminate\Support\Facades\DB::table('plans')->insert([
                'key'              => $key,
                'name'             => $p['name'] ?? ucfirst($key),
                'price'            => (int) ($p['price'] ?? 0),
                'max_users'        => ($p['max_users'] ?? 1) === PHP_INT_MAX ? -1 : (int) ($p['max_users'] ?? 1),
                'staff_module'     => (bool) ($p['staff_module'] ?? false),
                'google_backup'    => (bool) ($p['google_backup'] ?? false),
                'priority_support' => $key === 'business',
                'is_popular'       => $key === 'pro',
                'is_active'        => true,
                'sort_order'       => $order++,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
