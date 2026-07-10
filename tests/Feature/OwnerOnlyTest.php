<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithTenancy;
use Tests\TestCase;

class OwnerOnlyTest extends TestCase
{
    use RefreshDatabase;
    use InteractsWithTenancy;

    public function test_cashier_cannot_access_settings(): void
    {
        $this->createTenant();

        $cashier = $this->inTenant(fn () => User::create([
            'name'     => 'Cashier',
            'email'    => 'cashier@example.com',
            'password' => bcrypt('password123'),
            'role'     => 'cashier',
        ]));

        $this->actingAs($cashier)
            ->get($this->tenantUrl('/settings'))
            ->assertForbidden();
    }

    public function test_owner_can_access_settings(): void
    {
        $this->createTenant();

        $this->actingAs($this->owner)
            ->get($this->tenantUrl('/settings'))
            ->assertOk();
    }
}
