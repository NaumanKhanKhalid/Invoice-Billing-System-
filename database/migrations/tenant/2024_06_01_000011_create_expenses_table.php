<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->enum('category', ['staff_salary', 'delivery', 'shop_rent', 'electricity', 'fuel', 'maintenance', 'other']);
            $table->string('description');
            $table->decimal('amount', 10, 2);
            $table->string('paid_to')->nullable();
            $table->string('receipt_number')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
