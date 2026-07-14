<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'image_path')) {
                $table->string('image_path')->nullable()->after('description');
            }
        });

        // Split payment: how much of a sale was paid by cash vs online.
        Schema::table('pos_sales', function (Blueprint $table) {
            if (!Schema::hasColumn('pos_sales', 'cash_amount')) {
                $table->decimal('cash_amount', 12, 2)->nullable()->after('amount_paid');
            }
            if (!Schema::hasColumn('pos_sales', 'online_amount')) {
                $table->decimal('online_amount', 12, 2)->nullable()->after('cash_amount');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'image_path')) $table->dropColumn('image_path');
        });
        Schema::table('pos_sales', function (Blueprint $table) {
            foreach (['cash_amount', 'online_amount'] as $c) {
                if (Schema::hasColumn('pos_sales', $c)) $table->dropColumn($c);
            }
        });
    }
};
