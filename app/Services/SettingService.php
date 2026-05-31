<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingService
{
    public function get(string $key, $default = null)
    {
        return Cache::rememberForever('setting_' . $key, function () use ($key, $default) {
            return Setting::getValue($key, $default);
        });
    }

    public function set(string $key, $value): void
    {
        Setting::setValue($key, $value);
        Cache::forget('setting_' . $key);
        Cache::forget('all_settings');
    }

    public function all(): array
    {
        return Cache::rememberForever('all_settings', function () {
            return Setting::all()->pluck('setting_value', 'setting_key')->toArray();
        });
    }
}
