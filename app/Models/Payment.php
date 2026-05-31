<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'invoice_id',
        'amount',
        'payment_date',
        'method',
        'reference_number',
        'notes',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'float',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function getMethodLabelAttribute(): string
    {
        return match($this->method) {
            'cash' => 'Cash',
            'bank_transfer' => 'Bank Transfer',
            'easypaisa' => 'EasyPaisa',
            'jazzcash' => 'JazzCash',
            'cheque' => 'Cheque',
            'other' => 'Other',
            default => ucfirst($this->method),
        };
    }
}
