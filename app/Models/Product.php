<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name', 'sku', 'category', 'description', 'unit',
        'cost_price', 'sale_price', 'stock_qty', 'low_stock_alert', 'is_active',
    ];

    protected $casts = [
        'cost_price'  => 'decimal:2',
        'sale_price'  => 'decimal:2',
        'is_active'   => 'boolean',
    ];

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
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
