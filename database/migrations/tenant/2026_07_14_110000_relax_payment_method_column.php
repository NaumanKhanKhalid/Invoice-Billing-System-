<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Some tenant DBs still carry the original enum CHECK constraint on
     * pos_sales.payment_method (cash/jazzcash/easypaisa/bank/credit), which
     * rejects newer values like 'online' and 'split'. Rebuild the column as a
     * plain string so the controller validation is the single source of truth.
     */
    public function up(): void
    {
        if (in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'])) {
            DB::statement("ALTER TABLE pos_sales MODIFY COLUMN payment_method VARCHAR(20) NOT NULL DEFAULT 'cash'");
        } else {
            // SQLite (Laravel 11 rebuilds the table, dropping the old CHECK)
            Schema::table('pos_sales', function (Blueprint $table) {
                $table->string('payment_method', 20)->default('cash')->change();
            });
        }
    }

    public function down(): void
    {
        // no-op — we don't want to reinstate the restrictive constraint
    }
};
