<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreditPayment extends Model
{
    protected $fillable = ['credit_sale_id', 'amount', 'payment_date', 'method', 'note', 'proof_photo'];

    protected function casts(): array
    {
        return [
            'payment_date' => 'date',
            'amount' => 'float',
        ];
    }

    public function creditSale()
    {
        return $this->belongsTo(CreditSale::class);
    }
}
