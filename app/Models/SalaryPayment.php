<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SalaryPayment extends Model
{
    use LogsActivity, HasFactory;

    protected $fillable = ['staff_id', 'month', 'year', 'amount', 'payment_date', 'note'];

    protected function casts(): array
    {
        return ['amount' => 'float', 'payment_date' => 'date'];
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }
}
