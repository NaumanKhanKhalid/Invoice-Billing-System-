<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductPurchase extends Model
{
    protected $fillable = [
        'supplier_id', 'date', 'invoice_number',
        'total_amount', 'amount_paid', 'amount_due',
        'payment_status', 'due_date', 'notes',
    ];

    protected $casts = ['date' => 'date', 'due_date' => 'date'];

    public function supplier()  { return $this->belongsTo(Supplier::class); }
    public function items()     { return $this->hasMany(ProductPurchaseItem::class); }
}
