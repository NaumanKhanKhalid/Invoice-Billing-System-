<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('day_summaries', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique();
            $table->decimal('opening_cash', 12, 2)->default(0);
            $table->unsignedInteger('pos_sales_count')->default(0);
            $table->decimal('pos_revenue', 12, 2)->default(0);
            $table->decimal('purchase_cost', 12, 2)->default(0);
            $table->decimal('total_expenses', 12, 2)->default(0);
            $table->decimal('expected_cash', 12, 2)->default(0);
            $table->decimal('cash_received', 12, 2)->default(0);
            $table->decimal('cash_difference', 12, 2)->default(0);
            $table->decimal('net_profit', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->boolean('is_closed')->default(false);
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('day_summaries');
    }
};
