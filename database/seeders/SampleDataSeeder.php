<?php

namespace Database\Seeders;

use App\Models\ChickenType;
use App\Models\Customer;
use App\Models\PurchaseOrder;
use App\Models\PurchasePayment;
use App\Models\Supplier;
use App\Models\SupplyOrder;
use Illuminate\Database\Seeder;

class SampleDataSeeder extends Seeder
{
    public function run(): void
    {
        $broiler  = ChickenType::where('name', 'Broiler')->first();
        $supplier = Supplier::first();
        $hotel    = Customer::where('type', 'hotel')->first();

        if (! $broiler || ! $hotel) return;

        // Sample purchase order
        $po = PurchaseOrder::create([
            'supplier_id'        => optional($supplier)->id ?? 1,
            'date'               => today()->toDateString(),
            'invoice_number'     => 'PO-' . date('Y') . '-001',
            'live_weight_kg'     => 200.000,
            'dressed_weight_kg'  => 142.000,
            'waste_weight_kg'    => 58.000,
            'yield_percentage'   => 71.00,
            'dead_on_arrival_kg' => 2.000,
            'chicken_type_id'    => $broiler->id,
            'rate_per_kg_live'   => 380.00,
            'total_amount'       => 76000.00,
            'amount_paid'        => 30000.00,
            'amount_due'         => 46000.00,
            'due_date'           => today()->addDays(15)->toDateString(),
            'payment_status'     => 'partial',
        ]);

        PurchasePayment::create([
            'purchase_order_id' => $po->id,
            'amount'            => 30000.00,
            'payment_date'      => today()->toDateString(),
            'method'            => 'cash',
            'note'              => 'Advance payment',
        ]);

        // Sample supply order — hotel
        $so = SupplyOrder::create([
            'customer_id'       => $hotel->id,
            'date'              => today()->toDateString(),
            'invoice_number'    => 'SO-' . date('Y') . '-001',
            'chicken_type_id'   => $broiler->id,
            'dressed_weight_kg' => 50.000,
            'rate_per_kg'       => 550.00,
            'total_amount'      => 27500.00,
            'amount_paid'       => 0.00,
            'amount_due'        => 27500.00,
            'due_date'          => today()->addDays(30)->toDateString(),
            'payment_status'    => 'unpaid',
        ]);

        $hotel->increment('current_balance', 27500.00);
    }
}
