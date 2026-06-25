<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;

class NotifyExpiringTenants extends Command
{
    protected $signature   = 'tenants:notify-expiring {--days=7 : Notify tenants expiring within N days}';
    protected $description = 'Show list of tenants expiring soon (for manual WhatsApp follow-up)';

    public function handle(): int
    {
        $days    = (int) $this->option('days');
        $tenants = Tenant::whereNotNull('plan_expires_at')
            ->where('plan_expires_at', '>=', now())
            ->where('plan_expires_at', '<=', now()->addDays($days))
            ->orderBy('plan_expires_at')
            ->get();

        if ($tenants->isEmpty()) {
            $this->info("No tenants expiring within {$days} days.");
            return 0;
        }

        $this->warn("⚠️  {$tenants->count()} tenant(s) expiring within {$days} days:\n");

        $rows = $tenants->map(fn($t) => [
            $t->shop_name,
            $t->owner_name,
            $t->owner_phone ?? '—',
            $t->plan,
            $t->plan_expires_at->format('d M Y'),
            $t->plan_expires_at->diffForHumans(),
        ])->toArray();

        $this->table(['Shop', 'Owner', 'Phone', 'Plan', 'Expires', 'In'], $rows);

        $this->newLine();
        $this->info('WhatsApp Message Template:');
        $this->line('─────────────────────────────────');
        foreach ($tenants as $t) {
            $msg = "Assalam o Alaikum {$t->owner_name} bhai! "
                . "Aapka ShopSaas {$t->plan} plan {$t->plan_expires_at->format('d M Y')} ko expire ho raha hai. "
                . "Renew karne ke liye rabta karen. Shukriya!";
            $phone = $t->owner_phone ?? 'no phone';
            $this->line("📱 {$phone} ({$t->shop_name}): {$msg}");
            $this->newLine();
        }

        return 0;
    }
}
