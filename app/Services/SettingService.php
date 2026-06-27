<?php

namespace App\Services;

use App\Models\Setting;

class SettingService
{
    public function get(string $key, $default = null)
    {
        return Setting::getValue($key, $default);
    }

    public function set(string $key, $value): void
    {
        Setting::setValue($key, $value);
    }

    public function all(): array
    {
        return Setting::all()->pluck('setting_value', 'setting_key')->toArray();
    }
}
