<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SupplyPayment extends Model
{
    use HasFactory;

    protected $table = 'supply_payments';

    protected $fillable = ['supply_order_id', 'amount', 'payment_date', 'method', 'note'];

    protected function casts(): array
    {
        return ['amount' => 'float', 'payment_date' => 'date'];
    }

    public function supplyOrder()
    {
        return $this->belongsTo(SupplyOrder::class);
    }
}
