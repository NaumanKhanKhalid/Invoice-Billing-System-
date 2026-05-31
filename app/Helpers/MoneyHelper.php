<?php

namespace App\Helpers;

class MoneyHelper
{
    public static function format(float $amount, string $currency = 'PKR'): string
    {
        return $currency . ' ' . number_format($amount, 2);
    }
}
