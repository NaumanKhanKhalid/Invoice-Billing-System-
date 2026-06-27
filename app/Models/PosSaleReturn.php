<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PosSaleReturn extends Model
{
    protected $fillable = [
        'return_number', 'pos_sale_id', 'date',
        'subtotal', 'total', 'refund_method', 'reason', 'notes',
    ];

    public function sale()
    {
        return $this->belongsTo(PosSale::class, 'pos_sale_id');
    }

    public function items()
    {
        return $this->hasMany(PosSaleReturnItem::class);
    }
}
