<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
            $table->date('date');
            $table->string('invoice_number')->unique();
            $table->decimal('live_weight_kg', 8, 3);
            $table->decimal('dressed_weight_kg', 8, 3);
            $table->decimal('waste_weight_kg', 8, 3)->default(0);
            $table->decimal('yield_percentage', 5, 2)->default(0);
            $table->decimal('dead_on_arrival_kg', 8, 3)->default(0);
            $table->foreignId('chicken_type_id')->constrained('chicken_types');
            $table->decimal('rate_per_kg_live', 8, 2);
            $table->decimal('total_amount', 10, 2);
            $table->decimal('amount_paid', 10, 2)->default(0);
            $table->decimal('amount_due', 10, 2)->default(0);
            $table->date('due_date');
            $table->enum('payment_status', ['unpaid', 'partial', 'paid'])->default('unpaid');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
