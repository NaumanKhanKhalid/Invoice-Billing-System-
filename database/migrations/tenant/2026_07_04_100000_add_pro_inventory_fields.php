<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Hardware: mechanic/wholesale rate alag, walk-in retail alag
            $table->decimal('wholesale_price', 12, 2)->nullable()->after('sale_price');
            // Mobile: IMEI/serial tracking per unit
            $table->boolean('track_serial')->default(false)->after('barcode');
            // Unit conversion: maal roll/dozen mein aata hai, piece/meter mein bikta hai
            $table->string('purchase_unit', 30)->nullable()->after('unit');
            $table->decimal('conversion_factor', 10, 3)->nullable()->after('purchase_unit'); // 1 purchase_unit = X base units
        });

        // IMEI / serial numbers (mobile shops)
        Schema::create('product_serials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('serial', 64);
            $table->enum('status', ['in_stock', 'sold', 'returned'])->default('in_stock');
            $table->unsignedBigInteger('pos_sale_item_id')->nullable();
            $table->timestamps();
            $table->unique(['product_id', 'serial']);
            $table->index('serial');
        });

        // Batches with expiry (medical stores)
        Schema::create('product_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('batch_no', 50)->nullable();
            $table->date('expiry_date');
            $table->decimal('qty', 12, 3)->default(0);
            $table->unsignedBigInteger('product_purchase_item_id')->nullable();
            $table->timestamps();
            $table->index(['product_id', 'expiry_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_batches');
        Schema::dropIfExists('product_serials');
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['wholesale_price', 'track_serial', 'purchase_unit', 'conversion_factor']);
        });
    }
};
