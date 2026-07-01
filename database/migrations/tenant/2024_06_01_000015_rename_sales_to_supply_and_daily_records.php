<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Rename sales_orders → supply_orders, drop order_type (supply only)
        Schema::rename('sales_orders', 'supply_orders');
        Schema::table('supply_orders', function (Blueprint $table) {
            $table->dropColumn('order_type');
            // customer_id should not be nullable for supply orders
        });

        // Rename sale_payments → supply_payments
        Schema::rename('sale_payments', 'supply_payments');
        Schema::table('supply_payments', function (Blueprint $table) {
            $table->renameColumn('sales_order_id', 'supply_order_id');
        });

        // Drop old daily_inventory
        Schema::dropIfExists('daily_inventory');

        // Create daily_records (Day End Entry — CORE feature)
        $sqlite = \Illuminate\Support\Facades\DB::connection()->getDriverName() === 'sqlite';
        Schema::create('daily_records', function (Blueprint $table) use ($sqlite) {
            $table->id();
            $table->date('date');
            if (!$sqlite) $table->foreignId('chicken_type_id')->constrained('chicken_types');

            // Opening stock (from previous day's closing)
            $table->decimal('opening_stock_live_kg', 8, 3)->default(0);
            $table->decimal('opening_stock_dressed_kg', 8, 3)->default(0);

            // Purchases today
            $table->decimal('total_purchased_live_kg', 8, 3)->default(0);
            $table->decimal('purchase_cost', 10, 2)->default(0);

            // Supply to hotels/companies (auto-summed from supply_orders)
            $table->decimal('total_supply_dressed_kg', 8, 3)->default(0);
            $table->decimal('total_supply_revenue', 10, 2)->default(0);

            // Retail counter cash (entered by owner at day end)
            $table->decimal('counter_cash', 10, 2)->default(0);

            // Closing stock (entered at day end)
            $table->decimal('closing_stock_live_kg', 8, 3)->default(0);
            $table->decimal('closing_stock_dressed_kg', 8, 3)->default(0);
            $table->decimal('closing_stock_value', 10, 2)->default(0);

            // Dead/waste
            $table->decimal('dead_kg', 8, 3)->default(0);
            $table->decimal('spoilage_kg', 8, 3)->default(0);
            $table->text('waste_notes')->nullable();

            // Auto-calculated
            $table->decimal('total_revenue', 10, 2)->default(0);    // supply + counter
            $table->decimal('total_cost', 10, 2)->default(0);       // purchase - closing_value
            $table->decimal('gross_profit', 10, 2)->default(0);     // revenue - cost
            $table->decimal('total_expenses', 10, 2)->default(0);   // expenses of the day
            $table->decimal('net_profit', 10, 2)->default(0);       // gross - expenses

            $table->boolean('is_closed')->default(false);
            $table->timestamp('closed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $sqlite ? $table->unique(['date']) : $table->unique(['date', 'chicken_type_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_records');
        Schema::table('supply_payments', function (Blueprint $table) {
            $table->renameColumn('supply_order_id', 'sale_order_id');
        });
        Schema::rename('supply_payments', 'sale_payments');
        Schema::table('supply_orders', function (Blueprint $table) {
            $table->enum('order_type', ['retail', 'supply'])->default('retail')->after('invoice_number');
        });
        Schema::rename('supply_orders', 'sales_orders');
    }
};
