<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DailyInventory extends Model
{
    use HasFactory;

    protected $fillable = ['date', 'chicken_type_id', 'opening_stock_kg', 'total_purchased_kg', 'total_sold_retail_kg', 'total_sold_supply_kg', 'dead_kg', 'spoilage_kg', 'cold_storage_kg', 'closing_stock_kg', 'notes'];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'opening_stock_kg' => 'float',
            'total_purchased_kg' => 'float',
            'total_sold_retail_kg' => 'float',
            'total_sold_supply_kg' => 'float',
            'dead_kg' => 'float',
            'spoilage_kg' => 'float',
            'cold_storage_kg' => 'float',
            'closing_stock_kg' => 'float',
        ];
    }

    public function chickenType()
    {
        return $this->belongsTo(ChickenType::class);
    }
}
