<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class Quotation extends Model
{
    use LogsActivity;

    protected $fillable = [
        'quote_number', 'date', 'valid_until',
        'customer_name', 'customer_phone', 'customer_email',
        'subtotal', 'discount', 'total', 'status', 'notes',
    ];

    protected $casts = ['date' => 'date', 'valid_until' => 'date'];

    public function items() { return $this->hasMany(QuotationItem::class); }

    public static function nextNumber(): string
    {
        // lockForUpdate is only effective inside a DB::transaction — callers
        // must wrap generate+insert in one to avoid duplicate numbers
        $last = static::latest('id')->lockForUpdate()->value('quote_number');
        if (!$last) return 'QT-0001';
        $n = (int) substr($last, 3);
        return 'QT-' . str_pad($n + 1, 4, '0', STR_PAD_LEFT);
    }
}
