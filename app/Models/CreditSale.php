<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreditSale extends Model
{
    protected $fillable = [
        'udhar_customer_id', 'customer_name', 'phone', 'amount', 'amount_paid', 'amount_due',
        'sale_date', 'due_date', 'description', 'status', 'notes'
    ];

    protected function casts(): array
    {
        return [
            'sale_date' => 'date',
            'due_date' => 'date',
            'amount' => 'float',
            'amount_paid' => 'float',
            'amount_due' => 'float',
        ];
    }

    public function payments()
    {
        return $this->hasMany(CreditPayment::class);
    }

    public function udharCustomer()
    {
        return $this->belongsTo(UdharCustomer::class);
    }
}
