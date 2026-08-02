<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoachingFeePayment extends Model
{
    protected $fillable = ['fee_collection_id', 'amount', 'paid_on', 'method', 'note'];

    protected $casts = [
        'amount'  => 'float',
        'paid_on' => 'date',
    ];

    public function fee(): BelongsTo
    {
        return $this->belongsTo(CoachingFeeCollection::class, 'fee_collection_id');
    }
}
