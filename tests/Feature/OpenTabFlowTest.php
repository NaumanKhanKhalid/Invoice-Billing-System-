<?php

namespace Tests\Feature;

use App\Models\OpenTab;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithTenancy;
use Tests\TestCase;

class OpenTabFlowTest extends TestCase
{
    use RefreshDatabase;
    use InteractsWithTenancy;

    public function test_open_tab_full_lifecycle_create_add_items_close(): void
    {
        $this->createTenant(['shop_type' => 'hardware']); // open_tabs enabled by default

        // 1. Open a tab
        $response = $this->actingAs($this->owner)->post($this->tenantUrl('/open-tabs'), [
            'customer_name'  => 'Mechanic Ali',
            'customer_phone' => '03001234567',
        ]);

        $tab = $this->inTenant(fn () => OpenTab::first());
        $this->assertNotNull($tab, 'OpenTab row was not created');
        $this->assertSame('TAB-0001', $tab->tab_number);
        $this->assertSame('open', $tab->status);
        $response->assertRedirect($this->tenantUrl('/open-tabs/' . $tab->id));

        // 2. Add two items (JSON endpoint) and verify recalculated totals
        $this->actingAs($this->owner)
            ->postJson($this->tenantUrl("/open-tabs/{$tab->id}/items"), [
                'product_name' => 'Brake Cable',
                'qty'          => 2,
                'price'        => 150,
            ])
            ->assertOk()
            ->assertJson(['subtotal' => 300, 'total' => 300]);

        $addSecond = $this->actingAs($this->owner)
            ->postJson($this->tenantUrl("/open-tabs/{$tab->id}/items"), [
                'product_name' => 'Chain Oil',
                'qty'          => 1,
                'price'        => 450,
            ]);

        $addSecond->assertOk()->assertJson(['subtotal' => 750, 'total' => 750]);
        $this->assertCount(2, $addSecond->json('items'));

        // 3. Close the tab with a discount
        $this->actingAs($this->owner)
            ->post($this->tenantUrl("/open-tabs/{$tab->id}/close"), [
                'discount'       => 50,
                'amount_paid'    => 700,
                'payment_method' => 'cash',
            ])
            ->assertRedirect($this->tenantUrl("/open-tabs/{$tab->id}/receipt"));

        $this->inTenant(function () use ($tab) {
            $closed = OpenTab::find($tab->id);
            $this->assertSame('closed', $closed->status);
            $this->assertNotNull($closed->closed_at);
            $this->assertSame(750.0, $closed->subtotal);
            $this->assertSame(50.0, $closed->discount);
            $this->assertSame(700.0, $closed->total); // 750 - 50
            $this->assertSame(700.0, (float) $closed->amount_paid);
        });
    }
}
