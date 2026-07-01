<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // On SQLite the chicken_type_id columns were never created (see earlier
        // migrations' driver guards), so there is nothing to drop here.
        if (DB::connection()->getDriverName() === 'sqlite') {
            Schema::dropIfExists('chicken_types');
            return;
        }

        $supportsDropForeign = true;

        // purchase_orders FK was created on this table directly
        Schema::table('purchase_orders', function (Blueprint $table) use ($supportsDropForeign) {
            if ($supportsDropForeign) $table->dropForeign(['chicken_type_id']);
            $table->dropColumn('chicken_type_id');
        });

        // supply_orders was RENAMED from sales_orders, so FK name uses old table name
        Schema::table('supply_orders', function (Blueprint $table) use ($supportsDropForeign) {
            if ($supportsDropForeign) $table->dropForeign('sales_orders_chicken_type_id_foreign');
            $table->dropColumn('chicken_type_id');
        });

        Schema::table('daily_rates', function (Blueprint $table) use ($supportsDropForeign) {
            if ($supportsDropForeign) $table->dropForeign(['chicken_type_id']);
            $table->dropColumn('chicken_type_id');
        });

        Schema::table('daily_records', function (Blueprint $table) use ($supportsDropForeign) {
            if ($supportsDropForeign) $table->dropForeign(['chicken_type_id']);
            $table->dropUnique('daily_records_date_chicken_type_id_unique');
            $table->dropColumn('chicken_type_id');
            $table->unique('date');
        });

        Schema::dropIfExists('chicken_types');
    }

    public function down(): void
    {
        Schema::create('chicken_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->foreignId('chicken_type_id')->nullable()->constrained('chicken_types');
        });
        Schema::table('supply_orders', function (Blueprint $table) {
            $table->foreignId('chicken_type_id')->nullable()->constrained('chicken_types');
        });
        Schema::table('daily_rates', function (Blueprint $table) {
            $table->foreignId('chicken_type_id')->nullable()->constrained('chicken_types');
        });
        Schema::table('daily_records', function (Blueprint $table) {
            $table->dropUnique(['date']);
            $table->foreignId('chicken_type_id')->nullable()->constrained('chicken_types');
            $table->unique(['date', 'chicken_type_id']);
        });
    }
};
