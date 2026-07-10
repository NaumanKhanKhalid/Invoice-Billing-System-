<?php

namespace Tests\Feature;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use Tests\Concerns\InteractsWithTenancy;
use Tests\TestCase;

/**
 * feature_enabled() keeps a static per-process cache, so each test method
 * that expects a different value for the same feature key must run in its
 * own PHP process — otherwise the first request's result leaks into the
 * next test.
 */
class FeatureToggleTest extends TestCase
{
    use RefreshDatabase;
    use InteractsWithTenancy;

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_quotations_disabled_by_setting_returns_403(): void
    {
        $this->createTenant(['shop_type' => 'hardware', 'plan' => 'business']);
        $this->inTenant(fn () => Setting::setValue('feature_quotations', '0'));

        $this->actingAs($this->owner)
            ->get($this->tenantUrl('/quotations'))
            ->assertForbidden();
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_quotations_enabled_by_setting_returns_200(): void
    {
        $this->createTenant(['shop_type' => 'hardware', 'plan' => 'business']);
        $this->inTenant(fn () => Setting::setValue('feature_quotations', '1'));

        $this->actingAs($this->owner)
            ->get($this->tenantUrl('/quotations'))
            ->assertOk();
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_basic_plan_tenant_cannot_access_pro_feature_reports(): void
    {
        // 'reports' requires min_plan 'pro' — a basic tenant is plan-gated
        $this->createTenant(['shop_type' => 'hardware', 'plan' => 'basic']);

        $this->actingAs($this->owner)
            ->get($this->tenantUrl('/reports'))
            ->assertForbidden();
    }
}
