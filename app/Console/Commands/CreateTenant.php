<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class CreateTenant extends Command
{
    protected $signature   = 'tenant:create';
    protected $description = 'Interactively create a new tenant with database and owner user';

    public function handle()
    {
        $shopName   = $this->ask('Shop name');
        $shopType   = $this->choice('Shop type', ['chicken', 'bike', 'hardware', 'mobile', 'general'], 0);
        $ownerName  = $this->ask('Owner name');
        $ownerEmail = $this->ask('Owner email');
        $ownerPhone = $this->ask('Owner phone (optional)', null);
        $subdomain  = $this->ask('Subdomain (e.g. ahmed-bikes)', Str::slug($shopName));
        $plan       = $this->choice('Plan', ['basic', 'pro', 'business'], 0);
        $months     = (int) $this->ask('Duration (months)', 12);
        $password   = $this->secret('Owner password') ?: 'password123';

        $tenantId = Str::slug($subdomain);

        $this->info("Creating tenant '{$shopName}'...");

        $tenant = Tenant::create([
            'id'              => $tenantId,
            'shop_name'       => $shopName,
            'shop_type'       => $shopType,
            'owner_name'      => $ownerName,
            'owner_email'     => $ownerEmail,
            'owner_phone'     => $ownerPhone,
            'plan'            => $plan,
            'plan_expires_at' => now()->addMonths($months),
            'is_active'       => true,
        ]);

        $domain = $subdomain . '.' . config('app.central_domain', 'localhost');
        $tenant->domains()->create(['domain' => $domain]);

        tenancy()->initialize($tenant);
        User::create([
            'name'     => $ownerName,
            'email'    => $ownerEmail,
            'password' => bcrypt($password),
            'role'     => 'owner',
        ]);
        tenancy()->end();

        $this->info("✅ Tenant created successfully!");
        $this->table(['Field', 'Value'], [
            ['Shop',     $shopName],
            ['Domain',   $domain],
            ['Login',    $ownerEmail],
            ['Password', $password],
            ['Plan',     $plan . ' (' . $months . ' months)'],
            ['Expires',  now()->addMonths($months)->format('d M Y')],
        ]);
    }
}
