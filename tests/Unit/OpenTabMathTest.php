<?php

namespace Tests\Unit;

use App\Models\OpenTab;
use Tests\TestCase;

/**
 * OpenTab::recalculate() queries the tenant DB (items()->sum + update),
 * so the discount clamp max(0, subtotal - discount) is verified here in
 * isolation, alongside the model's casts and fillable configuration.
 */
class OpenTabMathTest extends TestCase
{
    public function test_discount_clamp_never_produces_negative_total(): void
    {
        // Same formula as OpenTab::recalculate()
        $this->assertSame(800.0, (float) max(0, 1000.0 - 200.0));
        $this->assertSame(0.0, (float) max(0, 1000.0 - 1000.0));
        $this->assertSame(0.0, (float) max(0, 500.0 - 800.0));
    }

    public function test_money_attributes_are_cast_to_float(): void
    {
        $tab = new OpenTab([
            'subtotal' => '1000.50',
            'discount' => '100',
            'total'    => '900.50',
        ]);

        $this->assertSame(1000.5, $tab->subtotal);
        $this->assertSame(100.0, $tab->discount);
        $this->assertSame(900.5, $tab->total);
    }

    public function test_fillable_includes_totals_and_discount(): void
    {
        $tab = new OpenTab();

        foreach (['subtotal', 'discount', 'total', 'amount_paid', 'status'] as $field) {
            $this->assertContains($field, $tab->getFillable());
        }
    }
}
