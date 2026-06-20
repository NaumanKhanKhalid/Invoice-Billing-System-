<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SalesOrder extends Model
{
    use HasFactory;

    protected $fillable = ['customer_id', 'date', 'invoice_number', 'order_type', 'chicken_type_id', 'dressed_weight_kg', 'rate_per_kg', 'total_amount', 'amount_paid', 'amount_due', 'due_date', 'payment_status', 'delivery_address', 'delivery_notes', 'whatsapp_notified', 'notes'];

    protected function casts(): array
    {
        return [
            'dressed_weight_kg' => 'float',
            'rate_per_kg' => 'float',
            'total_amount' => 'float',
            'amount_paid' => 'float',
            'amount_due' => 'float',
            'date' => 'date',
            'due_date' => 'date',
            'whatsapp_notified' => 'boolean',
        ];
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function chickenType()
    {
        return $this->belongsTo(ChickenType::class);
    }

    public function salePayments()
    {
        return $this->hasMany(SalePayment::class);
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
