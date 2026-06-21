<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DailyRate extends Model
{
    use HasFactory;

    protected $fillable = ['date', 'live_rate_per_kg', 'retail_rate_per_kg', 'supply_rate_per_kg', 'notes'];

    protected function casts(): array
    {
        return [
            'date'               => 'date',
            'live_rate_per_kg'   => 'float',
            'retail_rate_per_kg' => 'float',
            'supply_rate_per_kg' => 'float',
        ];
    }
}
