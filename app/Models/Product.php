<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use LogsActivity;

    protected $fillable = [
        'name', 'sku', 'barcode', 'category', 'description', 'image_path', 'unit',
        'cost_price', 'sale_price', 'wholesale_price', 'stock_qty', 'low_stock_alert', 'is_active',
        'track_serial', 'purchase_unit', 'conversion_factor',
    ];

    protected $casts = [
        'cost_price'  => 'decimal:2',
        'sale_price'  => 'decimal:2',
        'is_active'   => 'boolean',
        'track_serial' => 'boolean',
        'wholesale_price' => 'decimal:2',
    ];

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function serials()
    {
        return $this->hasMany(ProductSerial::class);
    }

    public function batches()
    {
        return $this->hasMany(ProductBatch::class);
    }

    public function isLowStock(): bool
    {
        return $this->stock_qty <= $this->low_stock_alert;
    }

    public function addStock(int $qty, float $unitPrice = null, string $reference = null, string $notes = null): void
    {
        $this->increment('stock_qty', $qty);
        $this->stockMovements()->create([
            'type'       => 'in',
            'qty'        => $qty,
            'unit_price' => $unitPrice,
            'reference'  => $reference,
            'notes'      => $notes,
        ]);
    }

    public function removeStock(int $qty, string $reference = null, string $notes = null): void
    {
        $this->decrement('stock_qty', $qty);
        $this->stockMovements()->create([
            'type'      => 'out',
            'qty'       => $qty,
            'reference' => $reference,
            'notes'     => $notes,
        ]);
    }
}
