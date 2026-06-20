<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SalePayment extends Model
{
    use HasFactory;

    protected $fillable = ['sales_order_id', 'amount', 'payment_date', 'method', 'note'];

    protected function casts(): array
    {
        return ['amount' => 'float', 'payment_date' => 'date'];
    }

    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class);
    }
}
