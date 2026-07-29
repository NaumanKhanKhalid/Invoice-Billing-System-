<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessSubscriptions extends Command
{
    protected $signature = 'subscriptions:process
        {--grace=0 : Din ki grace period expiry ke baad suspend karne se pehle}
        {--dry-run : Sirf dikhao, koi change na karo}';

    protected $description = 'Expire hone wale tenants ko auto-suspend karo aur 7/3/1 din ki reminder digest banao';

    public function handle(): int
    {
        $grace  = (int) $this->option('grace');
        $dry    = (bool) $this->option('dry-run');
        $cutoff = now()->subDays($grace);

        // ── 1) Auto-suspend expired tenants ─────────────────────────
        $toSuspend = Tenant::where('is_active', true)
            ->whereNotNull('plan_expires_at')
            ->where('plan_expires_at', '<', $cutoff)
            ->get();

        foreach ($toSuspend as $t) {
            if (! $dry) {
                $t->update(['is_active' => false]);
                Log::info('subscription.auto_suspended', [
                    'tenant'  => $t->id,
                    'shop'    => $t->shop_name,
                    'expired' => optional($t->plan_expires_at)->toDateString(),
                ]);
            }
            $this->line(($dry ? '[dry] ' : '') . "Suspended: {$t->shop_name} (expired " . optional($t->plan_expires_at)->format('d M Y') . ')');
        }
        $this->info(($dry ? '[dry] ' : '') . "{$toSuspend->count()} tenant(s) suspended.");

        // ── 2) Reminder digest for 7 / 3 / 1 days ───────────────────
        $this->newLine();
        $this->line('Upcoming expiry reminders:');
        foreach ([7, 3, 1] as $days) {
            $due = Tenant::whereNotNull('plan_expires_at')
                ->whereDate('plan_expires_at', now()->addDays($days)->toDateString())
                ->get();

            foreach ($due as $t) {
                Log::info('subscription.reminder_due', [
                    'tenant'  => $t->id,
                    'shop'    => $t->shop_name,
                    'owner'   => $t->owner_name,
                    'phone'   => $t->owner_phone,
                    'expires' => optional($t->plan_expires_at)->toDateString(),
                    'in_days' => $days,
                ]);
            }

            $this->line("  {$days} din baad: {$due->count()} tenant(s)");
        }

        return self::SUCCESS;
    }
}
