<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coaching_courses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('monthly_fee', 10, 2);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('coaching_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('coaching_courses')->cascadeOnDelete();
            $table->string('name');
            $table->string('timing')->nullable();          // e.g. "9:00 AM - 11:00 AM"
            $table->string('days')->nullable();            // e.g. "Mon, Wed, Fri"
            $table->string('teacher_name')->nullable();
            $table->unsignedSmallInteger('capacity')->default(20);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('coaching_students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('coaching_batches')->restrictOnDelete();
            $table->string('name');
            $table->string('phone', 20)->nullable();
            $table->string('guardian_name')->nullable();
            $table->string('guardian_phone', 20)->nullable();
            $table->text('address')->nullable();
            $table->date('enrollment_date');
            $table->decimal('custom_fee', 10, 2)->nullable();   // overrides course fee if set
            $table->unsignedTinyInteger('discount_percent')->default(0);
            $table->enum('status', ['active', 'completed', 'dropped'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('coaching_fee_collections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('coaching_students')->cascadeOnDelete();
            $table->date('month');                         // always 1st of month: 2026-07-01
            $table->decimal('amount_due', 10, 2);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('amount_paid', 10, 2)->default(0);
            $table->decimal('balance_due', 10, 2)->default(0);
            $table->date('payment_date')->nullable();
            $table->string('payment_method', 30)->nullable();
            $table->string('receipt_number', 30)->nullable();
            $table->enum('status', ['pending', 'partial', 'paid'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coaching_fee_collections');
        Schema::dropIfExists('coaching_students');
        Schema::dropIfExists('coaching_batches');
        Schema::dropIfExists('coaching_courses');
    }
};
