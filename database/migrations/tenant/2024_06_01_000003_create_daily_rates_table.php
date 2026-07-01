<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $sqlite = \Illuminate\Support\Facades\DB::connection()->getDriverName() === 'sqlite';
        Schema::create('daily_rates', function (Blueprint $table) use ($sqlite) {
            $table->id();
            if (!$sqlite) $table->foreignId('chicken_type_id')->constrained('chicken_types')->cascadeOnDelete();
            $table->decimal('rate_per_kg', 8, 2);
            $table->decimal('rate_per_kg_dressed', 8, 2);
            $table->date('date');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_rates');
    }
};
