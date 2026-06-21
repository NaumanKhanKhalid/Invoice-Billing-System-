<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DailyRate extends Model
{
    use HasFactory;

    protected $fillable = ['chicken_type_id', 'live_rate_per_kg', 'retail_rate_per_kg', 'supply_rate_per_kg', 'date', 'notes'];

    protected function casts(): array
    {
        return ['live_rate_per_kg' => 'float', 'retail_rate_per_kg' => 'float', 'supply_rate_per_kg' => 'float', 'date' => 'date'];
    }

    public function chickenType()
    {
        return $this->belongsTo(ChickenType::class);
    }
}
