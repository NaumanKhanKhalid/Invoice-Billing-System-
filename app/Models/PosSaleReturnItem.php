<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PosSaleReturnItem extends Model
{
    protected $fillable = [
        'pos_sale_return_id', 'product_id', 'product_name', 'qty', 'unit_price', 'total',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
