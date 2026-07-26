<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'variant_group')) {
                $table->string('variant_group')->nullable()->index()->after('category');
            }
            if (!Schema::hasColumn('products', 'variant_name')) {
                $table->string('variant_name')->nullable()->after('variant_group');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            foreach (['variant_group', 'variant_name'] as $c) {
                if (Schema::hasColumn('products', $c)) $table->dropColumn($c);
            }
        });
    }
};
