<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = ['date', 'category', 'description', 'amount', 'paid_to', 'receipt_number'];

    protected function casts(): array
    {
        return ['date' => 'date', 'amount' => 'float'];
    }
}
