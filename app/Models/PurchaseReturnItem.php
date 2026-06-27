<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseReturnItem extends Model
{
    protected $fillable = [
        'purchase_return_id', 'product_id', 'product_name', 'qty', 'unit_price', 'total',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
