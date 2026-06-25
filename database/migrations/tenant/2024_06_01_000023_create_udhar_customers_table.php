<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('udhar_customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('whatsapp_number')->nullable();
            $table->string('address')->nullable();
            $table->text('notes')->nullable();
            $table->decimal('total_given', 12, 2)->default(0);
            $table->decimal('total_received', 12, 2)->default(0);
            $table->decimal('current_balance', 12, 2)->default(0);
            $table->timestamps();
        });

        Schema::table('credit_sales', function (Blueprint $table) {
            $table->foreignId('udhar_customer_id')->nullable()->after('id')->constrained('udhar_customers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('credit_sales', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\UdharCustomer::class);
            $table->dropColumn('udhar_customer_id');
        });
        Schema::dropIfExists('udhar_customers');
    }
};
