<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'phone', 'address', 'type', 'credit_days', 'credit_limit', 'current_balance', 'whatsapp_number', 'is_active', 'is_blacklisted', 'blacklist_reason'];

    protected function casts(): array
    {
        return ['credit_limit' => 'float', 'current_balance' => 'float', 'is_active' => 'boolean', 'is_blacklisted' => 'boolean'];
    }

    public function salesOrders()
    {
        return $this->hasMany(SalesOrder::class);
    }

    public function getTotalPurchasedAttribute()
    {
        return $this->salesOrders()->sum('total_amount');
    }

    public function getTotalPaidAttribute()
    {
        return $this->salesOrders()->sum('amount_paid');
    }
}
