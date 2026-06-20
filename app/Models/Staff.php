<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Staff extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'phone', 'role', 'salary', 'joining_date', 'is_active'];

    protected function casts(): array
    {
        return ['salary' => 'float', 'joining_date' => 'date', 'is_active' => 'boolean'];
    }

    public function salaryPayments()
    {
        return $this->hasMany(SalaryPayment::class);
    }
}
