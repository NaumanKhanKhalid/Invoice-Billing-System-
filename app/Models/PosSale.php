<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class PosSale extends Model
{
    use LogsActivity;

    protected $fillable = [
        'sale_number', 'date', 'customer_name', 'customer_phone',
        'subtotal', 'discount', 'total', 'amount_paid', 'change_due',
        'cash_amount', 'online_amount', 'payment_method', 'notes',
    ];

    protected $casts = ['date' => 'date'];

    public function items() { return $this->hasMany(PosSaleItem::class); }
}
