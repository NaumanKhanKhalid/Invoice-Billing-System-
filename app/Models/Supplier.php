<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'phone', 'address', 'credit_days', 'balance', 'notes', 'is_active'];

    protected function casts(): array
    {
        return ['balance' => 'float', 'is_active' => 'boolean'];
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function getTotalPurchasedAttribute()
    {
        return $this->purchaseOrders()->sum('total_amount');
    }

    public function getTotalPaidAttribute()
    {
        return $this->purchaseOrders()->sum('amount_paid');
    }
}
