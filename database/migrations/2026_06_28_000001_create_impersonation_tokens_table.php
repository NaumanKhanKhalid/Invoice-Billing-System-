<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('impersonation_tokens', function (Blueprint $table) {
            $table->string('token', 36)->primary();
            $table->string('tenant_id');
            $table->unsignedBigInteger('admin_id');
            $table->string('admin_name');
            $table->string('admin_panel_url');
            $table->timestamp('expires_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('impersonation_tokens');
    }
};
