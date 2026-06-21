<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DailyRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'date', 'chicken_type_id',
        'opening_stock_live_kg', 'opening_stock_dressed_kg',
        'total_purchased_live_kg', 'purchase_cost',
        'total_supply_dressed_kg', 'total_supply_revenue',
        'counter_cash',
        'closing_stock_live_kg', 'closing_stock_dressed_kg', 'closing_stock_value',
        'dead_kg', 'spoilage_kg', 'waste_notes',
        'total_revenue', 'total_cost', 'gross_profit', 'total_expenses', 'net_profit',
        'is_closed', 'closed_at', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'date'                      => 'date',
            'closed_at'                 => 'datetime',
            'is_closed'                 => 'boolean',
            'opening_stock_live_kg'     => 'float',
            'opening_stock_dressed_kg'  => 'float',
            'total_purchased_live_kg'   => 'float',
            'purchase_cost'             => 'float',
            'total_supply_dressed_kg'   => 'float',
            'total_supply_revenue'      => 'float',
            'counter_cash'              => 'float',
            'closing_stock_live_kg'     => 'float',
            'closing_stock_dressed_kg'  => 'float',
            'closing_stock_value'       => 'float',
            'dead_kg'                   => 'float',
            'spoilage_kg'               => 'float',
            'total_revenue'             => 'float',
            'total_cost'                => 'float',
            'gross_profit'              => 'float',
            'total_expenses'            => 'float',
            'net_profit'                => 'float',
        ];
    }

    public function chickenType()
    {
        return $this->belongsTo(ChickenType::class);
    }
}
