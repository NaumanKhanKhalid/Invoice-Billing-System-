<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('repair_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('job_number', 20)->unique();
            $table->string('customer_name');
            $table->string('customer_phone', 20)->nullable();
            $table->string('device');
            $table->string('serial_imei', 100)->nullable();
            $table->text('fault');
            $table->decimal('estimated_cost', 12, 2)->nullable();
            $table->decimal('advance_paid', 12, 2)->default(0);
            $table->decimal('final_cost', 12, 2)->nullable();
            $table->enum('status', ['pending', 'in_progress', 'ready', 'delivered', 'cancelled'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamp('ready_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('repair_jobs');
    }
};
