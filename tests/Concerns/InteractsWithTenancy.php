<?php

namespace Tests\Concerns;

use App\Models\Setting;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * Boots a real tenant (central row + sqlite tenant database + tenant
 * migrations + owner user) for feature tests, and cleans it up after.
 *
 * Requests to tenantUrl('/...') hit InitializeTenancyByDomain and run
 * against the tenant's own database, exactly like production.
 */
trait InteractsWithTenancy
{
    protected ?Tenant $tenant = null;

    protected ?User $owner = null;

    /**
     * Create a tenant + subdomain + owner user. Creating the Tenant model
     * triggers the CreateDatabase + MigrateDatabase job pipeline (sync).
     */
    protected function createTenant(array $attributes = []): Tenant
    {
        $id = 't' . Str::lower(Str::random(10));

        $make = function () use (&$id, $attributes) {
            return Tenant::create(array_merge([
            'id'              => $id,
            'shop_name'       => 'Test Shop',
            'shop_type'       => 'hardware',
            'owner_name'      => 'Test Owner',
            'owner_email'     => $id . '@example.com',
            'plan'            => 'business',
            'plan_expires_at' => now()->addYear(),
            'is_active'       => true,
            ], $attributes));
        };

        try {
            $this->tenant = $make();
        } catch (\Illuminate\Database\QueryException $e) {
            // Transient sqlite "database is locked" during rapid back-to-back
            // tenant creation in the test suite — reset connections and retry
            if (!str_contains($e->getMessage(), 'database is locked')) {
                throw $e;
            }
            \Illuminate\Support\Facades\DB::purge('tenant');
            \Illuminate\Support\Facades\DB::purge(config('database.default'));
            @unlink(database_path('tenant-' . $id));
            usleep(300_000);
            $id = 't' . Str::lower(Str::random(10)); // fresh id + db file
            $this->tenant = $make();
        }

        $this->tenant->domains()->create(['domain' => $id . '.shopsaas.test']);

        $this->owner = $this->inTenant(function () {
            // company_name marks onboarding as complete (TenantOnboarding middleware)
            Setting::setValue('company_name', 'Test Shop');

            return User::create([
                'name'     => 'Test Owner',
                'email'    => 'owner@example.com',
                'password' => bcrypt('password123'),
                'role'     => 'owner',
            ]);
        });

        return $this->tenant;
    }

    /**
     * Create a user in the tenant DB. (The tenant users table has no
     * email_verified_at column, so the default UserFactory cannot be used.)
     */
    protected function makeTenantUser(array $attributes = []): User
    {
        return $this->inTenant(fn () => User::create(array_merge([
            'name'     => 'Tenant User',
            'email'    => 'user' . Str::lower(Str::random(8)) . '@example.com',
            'password' => bcrypt('password'),
            'role'     => 'staff',
        ], $attributes)));
    }

    /** Run a callback inside the tenant context (tenant DB connection). */
    protected function inTenant(callable $callback)
    {
        tenancy()->initialize($this->tenant);

        try {
            return $callback();
        } finally {
            tenancy()->end();
        }
    }

    /** Absolute URL on the tenant's subdomain. */
    protected function tenantUrl(string $path = ''): string
    {
        return 'http://' . $this->tenant->id . '.shopsaas.test' . $path;
    }

    protected function tearDown(): void
    {
        if ($this->tenant) {
            if (tenancy()->initialized) {
                tenancy()->end();
            }

            $dbFile = database_path('tenant-' . $this->tenant->id);

            try {
                $this->tenant->delete(); // triggers DeleteDatabase job
            } catch (\Throwable) {
                // fall through to unlink below
            }

            if (file_exists($dbFile)) {
                @unlink($dbFile);
            }

            $this->tenant = null;
            $this->owner = null;
        }

        parent::tearDown();
    }
}
