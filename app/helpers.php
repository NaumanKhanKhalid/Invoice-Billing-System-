<?php

if (!function_exists('formatKg')) {
    function formatKg(float|int|null $kg): string
    {
        if ($kg === null) return '0';
        // Remove trailing zeros: 50.000 → 50, 12.500 → 12.5
        return rtrim(rtrim(number_format($kg, 3), '0'), '.');
    }
}

if (!function_exists('formatCurrency')) {
    function formatCurrency(float|int|null $amount, string $prefix = 'PKR '): string
    {
        if ($amount === null) return $prefix . '0';
        // Remove trailing zeros: 400.00 → 400, 380.50 → 380.5
        $formatted = rtrim(rtrim(number_format($amount, 2), '0'), '.');
        return $prefix . $formatted;
    }
}
