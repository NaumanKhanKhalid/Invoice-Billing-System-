<?php

if (!function_exists('wa_number')) {
    /**
     * Normalise a Pakistani phone number to WhatsApp (wa.me) international
     * format: digits only, no leading 0, with the 92 country code.
     * Examples: 0300-1234567 → 923001234567 ; +92 300 1234567 → 923001234567
     */
    function wa_number(?string $phone): string
    {
        $digits = preg_replace('/[^0-9]/', '', $phone ?? '');
        if ($digits === '') return '';

        if (str_starts_with($digits, '0')) {
            $digits = '92' . substr($digits, 1);      // 03xx… → 923xx…
        } elseif (str_starts_with($digits, '92')) {
            // already international
        } elseif (str_starts_with($digits, '3') && strlen($digits) === 10) {
            $digits = '92' . $digits;                 // 3xx… → 923xx…
        }

        return $digits;
    }
}

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

        if (!plan_allows($key)) {
            return $cache[$key] = false;
        }

        $value = \App\Models\Setting::getValue("feature_$key");
        if ($value === null) {
            return $cache[$key] = (bool) $registry['default'];
        }

        return $cache[$key] = $value === '1';
    }
}

if (!function_exists('plan_allows')) {
    /**
     * Check whether the tenant's subscription plan tier includes a feature.
     * Plan ranks: basic(1) < pro(2) < business(3). Outside tenancy → allowed.
     */
    function plan_allows(string $featureKey): bool
    {
        $minPlan = config("features.$featureKey.min_plan", 'basic');
        if (!tenancy()->initialized) {
            return true;
        }

        $ranks = ['basic' => 1, 'pro' => 2, 'business' => 3];
        $tenantRank = $ranks[tenant()->plan ?? 'basic'] ?? 1;

        return $tenantRank >= ($ranks[$minPlan] ?? 1);
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

if (!function_exists('shop_favicon')) {
    /**
     * Favicon for the current context. Uses the tenant's uploaded logo if set,
     * otherwise a shop-type emoji on the brand-green tile (central = cart).
     */
    function shop_favicon(): string
    {
        $isTenant = function_exists('tenancy') && tenancy()->initialized;

        if ($isTenant) {
            $logo = \App\Models\Setting::getValue('logo_path');
            if ($logo) return tenant_asset($logo);
        }

        $icons = [
            'chicken' => '🍗', 'bike' => '🏍️', 'hardware' => '🔧', 'mobile' => '📱',
            'general' => '🏪', 'medical' => '💊', 'coaching' => '🎓',
        ];
        $type  = $isTenant ? (tenant()->shop_type ?? '') : '';
        $emoji = $isTenant ? ($icons[$type] ?? '🏪') : '🛒';

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64">'
             . '<rect width="64" height="64" rx="14" fill="#16a34a"/>'
             . '<text x="32" y="46" font-size="34" text-anchor="middle">' . $emoji . '</text></svg>';

        return 'data:image/svg+xml,' . rawurlencode($svg);
    }
}

if (!function_exists('payment_label')) {
    /** Friendly label for a payment method value (credit → Udhar). */
    function payment_label(?string $method): string
    {
        return [
            'cash'      => 'Cash',
            'online'    => 'Online',
            'credit'    => 'Udhar',
            'split'     => 'Split',
            'jazzcash'  => 'JazzCash',
            'easypaisa' => 'Easypaisa',
            'bank'      => 'Bank',
        ][$method] ?? ucfirst((string) $method);
    }
}
