<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DailyRate extends Model
{
    use HasFactory;

    protected $fillable = ['chicken_type_id', 'rate_per_kg', 'rate_per_kg_dressed', 'date', 'notes'];

    protected function casts(): array
    {
        return ['rate_per_kg' => 'float', 'rate_per_kg_dressed' => 'float', 'date' => 'date'];
    }

    public function chickenType()
    {
        return $this->belongsTo(ChickenType::class);
    }
}
