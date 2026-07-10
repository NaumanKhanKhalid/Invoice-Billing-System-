<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductSerial extends Model
{
    protected $fillable = ['product_id', 'serial', 'status', 'pos_sale_item_id'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function saleItem()
    {
        return $this->belongsTo(PosSaleItem::class, 'pos_sale_item_id');
    }
}
