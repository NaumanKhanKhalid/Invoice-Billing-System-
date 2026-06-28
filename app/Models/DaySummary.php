<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DaySummary extends Model
{
    protected $fillable = [
        'date', 'opening_cash', 'pos_sales_count', 'pos_revenue',
        'purchase_cost', 'total_expenses', 'expected_cash',
        'cash_received', 'cash_difference', 'net_profit',
        'notes', 'is_closed', 'closed_at',
    ];

    protected $casts = [
        'date'      => 'date',
        'closed_at' => 'datetime',
        'is_closed' => 'boolean',
    ];
}
