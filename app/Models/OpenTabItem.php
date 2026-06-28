<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OpenTabItem extends Model
{
    protected $fillable = [
        'open_tab_id', 'product_id', 'product_name', 'unit', 'qty', 'price', 'total',
    ];

    protected $casts = ['qty' => 'float', 'price' => 'float', 'total' => 'float'];

    public function tab(): BelongsTo
    {
        return $this->belongsTo(OpenTab::class, 'open_tab_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
