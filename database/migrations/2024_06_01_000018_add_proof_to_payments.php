<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_payments', function (Blueprint $table) {
            $table->string('proof_path')->nullable()->after('note');
        });
        Schema::table('supply_payments', function (Blueprint $table) {
            $table->string('proof_path')->nullable()->after('note');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_payments', function (Blueprint $table) {
            $table->dropColumn('proof_path');
        });
        Schema::table('supply_payments', function (Blueprint $table) {
            $table->dropColumn('proof_path');
        });
    }
};
