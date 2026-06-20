<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_inventory', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->foreignId('chicken_type_id')->constrained('chicken_types');
            $table->decimal('opening_stock_kg', 8, 3)->default(0);
            $table->decimal('total_purchased_kg', 8, 3)->default(0);
            $table->decimal('total_sold_retail_kg', 8, 3)->default(0);
            $table->decimal('total_sold_supply_kg', 8, 3)->default(0);
            $table->decimal('dead_kg', 8, 3)->default(0);
            $table->decimal('spoilage_kg', 8, 3)->default(0);
            $table->decimal('cold_storage_kg', 8, 3)->default(0);
            $table->decimal('closing_stock_kg', 8, 3)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['date', 'chicken_type_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_inventory');
    }
};
