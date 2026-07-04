<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoachingFeeCollection extends Model
{
    use LogsActivity;

    protected $fillable = [
        'student_id', 'month', 'amount_due', 'discount_amount',
        'amount_paid', 'balance_due', 'payment_date', 'payment_method',
        'receipt_number', 'status', 'notes',
    ];

    protected $casts = [
        'month'        => 'date',
        'payment_date' => 'date',
        'amount_due'   => 'float',
        'amount_paid'  => 'float',
        'balance_due'  => 'float',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(CoachingStudent::class, 'student_id');
    }

    public static function nextReceiptNumber(): string
    {
        // Pending rows have NULL receipt_number, so look at the highest issued
        // receipt (not just the latest row) to avoid duplicate numbers.
        // lockForUpdate is only effective inside a DB::transaction — callers
        // must wrap generate+insert in one to avoid races
        $last = static::whereNotNull('receipt_number')
            ->orderByDesc('receipt_number')
            ->lockForUpdate()
            ->value('receipt_number');
        $num  = $last ? ((int) substr($last, 4)) + 1 : 1;
        return 'RCP-' . str_pad($num, 4, '0', STR_PAD_LEFT);
    }
}
