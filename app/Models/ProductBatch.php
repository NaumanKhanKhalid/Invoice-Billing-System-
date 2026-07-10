<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductBatch extends Model
{
    protected $fillable = ['product_id', 'batch_no', 'expiry_date', 'qty', 'product_purchase_item_id'];

    protected $casts = ['expiry_date' => 'date', 'qty' => 'float'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function isExpired(): bool
    {
        return $this->expiry_date->isPast();
    }

    public function expiresWithin(int $days): bool
    {
        return $this->expiry_date->lte(now()->addDays($days));
    }
}
