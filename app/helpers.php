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
    function formatCurrency(float|int|null $amount, string $prefix = null): string
    {
        if ($prefix === null) {
            $currency = tenancy()->initialized
                ? (\App\Models\Setting::getValue('currency', 'PKR'))
                : 'PKR';
            $prefix = $currency . ' ';
        }
        if ($amount === null) return $prefix . '0';
        $formatted = rtrim(rtrim(number_format($amount, 2), '0'), '.');
        return $prefix . $formatted;
    }
}
