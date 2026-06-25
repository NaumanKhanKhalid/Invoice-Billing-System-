<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTenantsTable extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('shop_name');
            $table->string('shop_type')->default('general'); // chicken, bike, hardware, mobile
            $table->string('owner_name');
            $table->string('owner_email')->unique();
            $table->string('owner_phone')->nullable();
            $table->string('plan')->default('basic');        // basic, pro, business
            $table->timestamp('plan_expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->json('data')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
}
