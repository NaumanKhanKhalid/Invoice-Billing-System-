<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductPurchaseItem extends Model
{
    protected $fillable = ['product_purchase_id', 'product_id', 'qty', 'unit_price', 'total'];

    public function product()          { return $this->belongsTo(Product::class); }
    public function productPurchase()  { return $this->belongsTo(ProductPurchase::class); }
}
