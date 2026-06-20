<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = ['supplier_id', 'date', 'invoice_number', 'live_weight_kg', 'dressed_weight_kg', 'waste_weight_kg', 'yield_percentage', 'dead_on_arrival_kg', 'chicken_type_id', 'rate_per_kg_live', 'total_amount', 'amount_paid', 'amount_due', 'due_date', 'payment_status', 'notes'];

    protected function casts(): array
    {
        return [
            'live_weight_kg' => 'float',
            'dressed_weight_kg' => 'float',
            'waste_weight_kg' => 'float',
            'yield_percentage' => 'float',
            'dead_on_arrival_kg' => 'float',
            'rate_per_kg_live' => 'float',
            'total_amount' => 'float',
            'amount_paid' => 'float',
            'amount_due' => 'float',
            'date' => 'date',
            'due_date' => 'date',
        ];
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function chickenType()
    {
        return $this->belongsTo(ChickenType::class);
    }

    public function purchasePayments()
    {
        return $this->hasMany(PurchasePayment::class);
    }

    public function getComputedAmountDueAttribute()
    {
        return $this->total_amount - $this->amount_paid;
    }

    public function getPaymentStatusColorAttribute()
    {
        return match ($this->payment_status) {
            'paid' => 'green',
            'partial' => 'yellow',
            'unpaid' => 'red',
            default => 'gray',
        };
    }
}
