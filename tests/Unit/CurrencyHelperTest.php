<?php

namespace Tests\Unit;

use Tests\TestCase;

class CurrencyHelperTest extends TestCase
{
    public function test_format_currency_strips_trailing_zeros(): void
    {
        $this->assertSame('PKR 1,500', formatCurrency(1500.00));
        $this->assertSame('PKR 1,500.5', formatCurrency(1500.50));
        $this->assertSame('PKR 1,500.55', formatCurrency(1500.55));
    }

    public function test_format_currency_null_returns_zero(): void
    {
        $this->assertSame('PKR 0', formatCurrency(null));
    }

    public function test_format_currency_custom_prefix(): void
    {
        $this->assertSame('Rs. 250', formatCurrency(250.00, 'Rs. '));
    }

    public function test_format_kg_strips_trailing_zeros(): void
    {
        $this->assertSame('50', formatKg(50.000));
        $this->assertSame('12.5', formatKg(12.500));
        $this->assertSame('0.125', formatKg(0.125));
    }

    public function test_format_kg_null_returns_zero(): void
    {
        $this->assertSame('0', formatKg(null));
    }
}
