<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('supply_orders', function (Blueprint $table) {
            $table->boolean('is_delivered')->default(false)->after('delivery_date');
            $table->timestamp('delivered_at')->nullable()->after('is_delivered');
        });
    }

    public function down(): void
    {
        Schema::table('supply_orders', function (Blueprint $table) {
            $table->dropColumn(['is_delivered', 'delivered_at']);
        });
    }
};
