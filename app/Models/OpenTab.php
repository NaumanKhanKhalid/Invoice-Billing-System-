<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OpenTab extends Model
{
    protected $fillable = [
        'tab_number', 'customer_name', 'customer_phone', 'notes',
        'subtotal', 'discount', 'total', 'amount_paid', 'payment_method',
        'status', 'closed_at',
    ];

    protected $casts = [
        'closed_at' => 'datetime',
        'subtotal'  => 'float',
        'discount'  => 'float',
        'total'     => 'float',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(OpenTabItem::class);
    }

    public function recalculate(): void
    {
        $subtotal = $this->items()->sum('total');
        $total    = max(0, $subtotal - $this->discount);
        $this->update(['subtotal' => $subtotal, 'total' => $total]);
    }

    public static function nextNumber(): string
    {
        $last = static::withTrashed()->orderByDesc('id')->value('tab_number');
        $num  = $last ? ((int) substr($last, 4)) + 1 : 1;
        return 'TAB-' . str_pad($num, 4, '0', STR_PAD_LEFT);
    }
}
