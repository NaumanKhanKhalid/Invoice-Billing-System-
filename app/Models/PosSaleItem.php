<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PosSaleItem extends Model
{
    protected $fillable = ['pos_sale_id', 'product_id', 'product_name', 'qty', 'unit_price', 'total'];

    public function product() { return $this->belongsTo(Product::class); }
    public function sale()    { return $this->belongsTo(PosSale::class, 'pos_sale_id'); }
}
