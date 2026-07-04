<?php

namespace Tests\Unit;

use Tests\TestCase;

class FeatureHelperTest extends TestCase
{
    public function test_unknown_key_returns_true(): void
    {
        $this->assertTrue(feature_enabled('test_unknown_key_' . uniqid()));
    }

    public function test_known_key_outside_tenancy_returns_registry_default_true(): void
    {
        $key = 'test_default_true_' . uniqid();
        config()->set("features.$key", ['default' => true, 'shop_types' => null]);

        $this->assertTrue(feature_enabled($key));
    }

    public function test_known_key_outside_tenancy_returns_registry_default_false(): void
    {
        $key = 'test_default_false_' . uniqid();
        config()->set("features.$key", ['default' => false, 'shop_types' => null]);

        $this->assertFalse(feature_enabled($key));
    }
}
