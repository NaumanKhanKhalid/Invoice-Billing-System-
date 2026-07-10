<?php

namespace Tests\Feature;

use App\Models\PosSale;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithTenancy;
use Tests\TestCase;

class PosSaleFlowTest extends TestCase
{
    use RefreshDatabase;
    use InteractsWithTenancy;

    private function makeProduct(array $attributes = []): Product
    {
        return $this->inTenant(fn () => Product::create(array_merge([
            'name'       => 'Hammer',
            'sku'        => 'HAM-1',
            'unit'       => 'pcs',
            'cost_price' => 300,
            'sale_price' => 500,
            'stock_qty'  => 10,
            'is_active'  => true,
        ], $attributes)));
    }

    public function test_pos_sale_creates_sale_and_decrements_stock(): void
    {
        $this->createTenant(['shop_type' => 'hardware']);
        $product = $this->makeProduct();

        $response = $this->actingAs($this->owner)->postJson($this->tenantUrl('/pos/sale'), [
            'payment_method' => 'cash',
            'discount'       => 100,
            'amount_paid'    => 1400,
            'items'          => [
                ['product_id' => $product->id, 'qty' => 3, 'unit_price' => 500],
            ],
        ]);

        $response->assertOk()->assertJson(['ok' => true]);

        $this->inTenant(function () use ($product) {
            $sale = PosSale::first();
            $this->assertNotNull($sale, 'PosSale row was not created');
            $this->assertSame(1500.0, (float) $sale->subtotal);
            $this->assertSame(1400.0, (float) $sale->total); // 3*500 - 100 discount
            $this->assertCount(1, $sale->items);
            $this->assertSame(7, (int) $product->fresh()->stock_qty); // 10 - 3
        });
    }

    public function test_stock_guard_blocks_overselling_and_creates_no_sale(): void
    {
        $this->createTenant(['shop_type' => 'hardware']); // plan business, stock_guard default on
        $product = $this->makeProduct(['stock_qty' => 2]);

        // Note: the app only renders JSON errors for api/* routes
        // (shouldRenderJsonWhen in bootstrap/app.php), so a web POST that
        // fails validation redirects back with errors in the session.
        $response = $this->actingAs($this->owner)
            ->from($this->tenantUrl('/pos/sale'))
            ->post($this->tenantUrl('/pos/sale'), [
                'payment_method' => 'cash',
                'amount_paid'    => 2500,
                'items'          => [
                    ['product_id' => $product->id, 'qty' => 5, 'unit_price' => 500],
                ],
            ]);

        $response->assertRedirect($this->tenantUrl('/pos/sale'));
        $response->assertSessionHasErrors('items');

        $this->inTenant(function () use ($product) {
            $this->assertSame(0, PosSale::count());
            $this->assertSame(2, (int) $product->fresh()->stock_qty);
        });
    }
}
