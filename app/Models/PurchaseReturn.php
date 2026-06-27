<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseReturn extends Model
{
    protected $fillable = [
        'return_number', 'product_purchase_id', 'supplier_id', 'date',
        'total', 'adjustment_method', 'reason', 'notes',
    ];

    public function purchase()
    {
        return $this->belongsTo(ProductPurchase::class, 'product_purchase_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseReturnItem::class);
    }
}
