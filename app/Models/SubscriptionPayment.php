<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPayment extends Model
{
    protected $fillable = [
        'tenant_id', 'amount', 'plan', 'months', 'method',
        'paid_at', 'period_from', 'period_to', 'reference', 'notes',
    ];

    protected $casts = [
        'paid_at'     => 'date',
        'period_from' => 'date',
        'period_to'   => 'date',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
