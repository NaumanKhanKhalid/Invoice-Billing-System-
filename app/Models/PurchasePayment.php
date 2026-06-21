<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchasePayment extends Model
{
    protected $fillable = ['purchase_order_id', 'amount', 'payment_date', 'method', 'note', 'proof_path'];

    protected function casts(): array
    {
        return ['amount' => 'float', 'payment_date' => 'date'];
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }
}
