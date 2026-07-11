<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add 'online' to the pos_sales.payment_method enum (JazzCash/Easypaisa/Bank
     * merged into a single "Online" option). Old values are kept so existing
     * rows stay valid. SQLite stores enums as plain text, so no change needed there.
     */
    public function up(): void
    {
        if (in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'])) {
            DB::statement("ALTER TABLE pos_sales MODIFY COLUMN payment_method ENUM('cash','online','jazzcash','easypaisa','bank','credit') NOT NULL DEFAULT 'cash'");
        }
    }

    public function down(): void
    {
        if (in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'])) {
            DB::statement("ALTER TABLE pos_sales MODIFY COLUMN payment_method ENUM('cash','jazzcash','easypaisa','bank','credit') NOT NULL DEFAULT 'cash'");
        }
    }
};
