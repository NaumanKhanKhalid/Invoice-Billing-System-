<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * pos_sales.payment_method was an enum (cash/jazzcash/easypaisa/bank/credit).
     * JazzCash/Easypaisa/Bank are merged into a single "online" option, so the
     * column is converted to a plain string — flexible and no enum CHECK to fight
     * (the controller validation still restricts values to cash/online/credit).
     */
    public function up(): void
    {
        if (in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'])) {
            DB::statement("ALTER TABLE pos_sales MODIFY COLUMN payment_method VARCHAR(20) NOT NULL DEFAULT 'cash'");
        } else {
            // SQLite: rebuild the column as a plain string, dropping the enum CHECK
            Schema::table('pos_sales', function (Blueprint $table) {
                $table->string('payment_method', 20)->default('cash')->change();
            });
        }
    }

    public function down(): void
    {
        if (in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'])) {
            DB::statement("ALTER TABLE pos_sales MODIFY COLUMN payment_method ENUM('cash','jazzcash','easypaisa','bank','credit') NOT NULL DEFAULT 'cash'");
        }
    }
};
