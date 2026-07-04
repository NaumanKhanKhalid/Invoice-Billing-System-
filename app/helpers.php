<?php

if (!function_exists('feature_enabled')) {
    /**
     * Check whether a toggleable feature is enabled for the current tenant.
     * Reads setting `feature_{key}` and falls back to the registry default.
     * Features not applicable to the tenant's shop type are always disabled.
     */
    function feature_enabled(string $key): bool
    {
        static $cache = [];

        if (array_key_exists($key, $cache)) {
            return $cache[$key];
        }

        $registry = config("features.$key");
        if ($registry === null) {
            return $cache[$key] = true; // unknown key: never block
        }

        if (!tenancy()->initialized) {
            return $cache[$key] = (bool) $registry['default'];
        }

        $shopType = tenant()->shop_type ?? 'general';
        if ($registry['shop_types'] !== null && !in_array($shopType, $registry['shop_types'])) {
            return $cache[$key] = false;
        }

        $value = \App\Models\Setting::getValue("feature_$key");
        if ($value === null) {
            return $cache[$key] = (bool) $registry['default'];
        }

        return $cache[$key] = $value === '1';
    }
}

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
