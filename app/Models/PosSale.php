<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PosSale extends Model
{
    protected $fillable = [
        'sale_number', 'date', 'customer_name', 'customer_phone',
        'subtotal', 'discount', 'total', 'amount_paid', 'change_due',
        'payment_method', 'notes',
    ];

    protected $casts = ['date' => 'date'];

    public function items() { return $this->hasMany(PosSaleItem::class); }
}
