<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ChickenType extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function dailyRates()
    {
        return $this->hasMany(DailyRate::class);
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function supplyOrders()
    {
        return $this->hasMany(SupplyOrder::class);
    }

    public function dailyRecords()
    {
        return $this->hasMany(DailyRecord::class);
    }
}
