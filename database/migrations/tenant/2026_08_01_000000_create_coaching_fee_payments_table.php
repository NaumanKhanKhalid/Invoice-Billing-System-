<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coaching_fee_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fee_collection_id')->constrained('coaching_fee_collections')->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->date('paid_on');
            $table->string('method', 30)->default('cash');
            $table->string('note', 300)->nullable();
            $table->timestamps();
        });

        // Backfill: every already-paid fee gets one payment row for its recorded total,
        // so old receipts still show a (single) payment line.
        $rows = \Illuminate\Support\Facades\DB::table('coaching_fee_collections')
            ->where('amount_paid', '>', 0)->get();
        foreach ($rows as $r) {
            \Illuminate\Support\Facades\DB::table('coaching_fee_payments')->insert([
                'fee_collection_id' => $r->id,
                'amount'            => $r->amount_paid,
                'paid_on'           => $r->payment_date ?? now()->toDateString(),
                'method'            => $r->payment_method ?? 'cash',
                'note'              => null,
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('coaching_fee_payments');
    }
};
