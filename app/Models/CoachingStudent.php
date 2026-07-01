<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CoachingStudent extends Model
{
    protected $fillable = [
        'batch_id', 'name', 'phone', 'guardian_name', 'guardian_phone',
        'address', 'enrollment_date', 'custom_fee', 'discount_percent',
        'status', 'notes',
    ];

    protected $casts = [
        'enrollment_date'  => 'date',
        'custom_fee'       => 'float',
        'discount_percent' => 'integer',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(CoachingBatch::class, 'batch_id');
    }

    public function fees(): HasMany
    {
        return $this->hasMany(CoachingFeeCollection::class, 'student_id');
    }

    /** Monthly fee after discount */
    public function effectiveFee(): float
    {
        $base     = $this->custom_fee ?? $this->batch->course->monthly_fee;
        $discount = $base * ($this->discount_percent / 100);
        return round($base - $discount, 2);
    }
}
