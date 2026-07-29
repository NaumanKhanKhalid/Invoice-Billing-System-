<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('invoices:mark-overdue')->daily();
Schedule::command('backup:google')->dailyAt('00:00');
Schedule::command('tenants:notify-expiring --days=7')->weeklyOn(1, '09:00');

// Auto-suspend expired tenants + build 7/3/1-day expiry reminder digest (daily 09:00).
// Grace: 1 din — expiry ke agle din suspend, taake same-day renew ka mauqa mile.
Schedule::command('subscriptions:process --grace=1')->dailyAt('09:00');
