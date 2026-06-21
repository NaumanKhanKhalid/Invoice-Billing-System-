<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop FK + column from every table that still references chicken_types
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropForeign(['chicken_type_id']);
            $table->dropColumn('chicken_type_id');
        });

        Schema::table('supply_orders', function (Blueprint $table) {
            $table->dropForeign(['chicken_type_id']);
            $table->dropColumn('chicken_type_id');
        });

        Schema::table('daily_rates', function (Blueprint $table) {
            $table->dropForeign(['chicken_type_id']);
            $table->dropColumn('chicken_type_id');
        });

        Schema::table('daily_records', function (Blueprint $table) {
            $table->dropForeign(['chicken_type_id']);
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
