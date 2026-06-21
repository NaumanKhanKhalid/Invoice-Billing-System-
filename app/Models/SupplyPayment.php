<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplyPayment extends Model
{
    protected $fillable = ['supply_order_id', 'amount', 'payment_date', 'method', 'note', 'proof_path'];

    protected function casts(): array
    {
        return ['amount' => 'float', 'payment_date' => 'date'];
    }

    public function supplyOrder()
    {
        return $this->belongsTo(SupplyOrder::class);
    }
}
