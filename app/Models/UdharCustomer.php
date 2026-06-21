<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UdharCustomer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'phone', 'whatsapp_number', 'address', 'notes',
        'total_given', 'total_received', 'current_balance',
    ];

    protected function casts(): array
    {
        return [
            'total_given'     => 'float',
            'total_received'  => 'float',
            'current_balance' => 'float',
        ];
    }

    public function creditSales()
    {
        return $this->hasMany(CreditSale::class);
    }
}
