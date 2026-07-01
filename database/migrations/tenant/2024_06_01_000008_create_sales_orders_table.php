<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $sqlite = \Illuminate\Support\Facades\DB::connection()->getDriverName() === 'sqlite';
        Schema::create('sales_orders', function (Blueprint $table) use ($sqlite) {
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->date('date');
            $table->string('invoice_number')->unique();
            $table->enum('order_type', ['retail', 'supply'])->default('retail');
            if (!$sqlite) $table->foreignId('chicken_type_id')->constrained('chicken_types');
            $table->decimal('dressed_weight_kg', 8, 3);
            $table->decimal('rate_per_kg', 8, 2);
            $table->decimal('total_amount', 10, 2);
            $table->decimal('amount_paid', 10, 2)->default(0);
            $table->decimal('amount_due', 10, 2)->default(0);
            $table->date('due_date')->nullable();
            $table->enum('payment_status', ['unpaid', 'partial', 'paid'])->default('unpaid');
            $table->string('delivery_address')->nullable();
            $table->text('delivery_notes')->nullable();
            $table->boolean('whatsapp_notified')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_orders');
    }
};
