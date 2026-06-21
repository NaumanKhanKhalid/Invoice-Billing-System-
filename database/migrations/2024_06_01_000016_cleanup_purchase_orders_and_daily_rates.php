<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. purchase_orders — drop dressed_weight_kg, waste_weight_kg, yield_percentage
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn(['dressed_weight_kg', 'waste_weight_kg', 'yield_percentage']);
        });

        // 2. daily_rates — rename rate_per_kg → live_rate_per_kg,
        //    drop rate_per_kg_dressed, add retail_rate_per_kg + supply_rate_per_kg
        Schema::table('daily_rates', function (Blueprint $table) {
            $table->renameColumn('rate_per_kg', 'live_rate_per_kg');
            $table->dropColumn('rate_per_kg_dressed');
        });
        Schema::table('daily_rates', function (Blueprint $table) {
            $table->decimal('retail_rate_per_kg', 8, 2)->default(0)->after('live_rate_per_kg');
            $table->decimal('supply_rate_per_kg', 8, 2)->default(0)->after('retail_rate_per_kg');
        });
    }

    public function down(): void
    {
        Schema::table('daily_rates', function (Blueprint $table) {
            $table->dropColumn(['retail_rate_per_kg', 'supply_rate_per_kg']);
        });
        Schema::table('daily_rates', function (Blueprint $table) {
            $table->decimal('rate_per_kg_dressed', 8, 2)->default(0);
            $table->renameColumn('live_rate_per_kg', 'rate_per_kg');
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->decimal('dressed_weight_kg', 8, 3)->default(0);
            $table->decimal('waste_weight_kg', 8, 3)->default(0);
            $table->decimal('yield_percentage', 5, 2)->default(0);
        });
    }
};
