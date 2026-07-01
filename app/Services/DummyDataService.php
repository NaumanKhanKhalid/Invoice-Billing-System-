<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\CreditSale;
use App\Models\DailyRate;
use App\Models\Expense;
use App\Models\Product;
use App\Models\ProductPurchase;
use App\Models\ProductPurchaseItem;
use App\Models\PosSale;
use App\Models\PosSaleItem;
use App\Models\PurchaseOrder;
use App\Models\Staff;
use App\Models\Supplier;
use App\Models\SupplyOrder;
use App\Models\UdharCustomer;
use App\Models\CoachingCourse;
use App\Models\CoachingBatch;
use App\Models\CoachingStudent;
use App\Models\CoachingFeeCollection;

class DummyDataService
{
    const MARKER_KEY = 'demo_data_seeded';

    // Valid enum values (must match DB migrations exactly)
    // expenses.category:      staff_salary, delivery, shop_rent, electricity, fuel, maintenance, other
    // customers.type:         retail, hotel, restaurant, company, reseller
    // staff.role:             manager, butcher, delivery_boy, cashier
    // pos_sales.payment_method: cash, jazzcash, easypaisa, bank, credit
    // purchase/supply.payment_status: unpaid, partial, paid

    public static function seed(string $shopType): void
    {
        static::delete();

        match ($shopType) {
            'chicken'  => static::seedChicken(),
            'hardware' => static::seedHardware(),
            'mobile'   => static::seedMobile(),
            'bike'     => static::seedBike(),
            'general'  => static::seedGeneral(),
            'medical'  => static::seedMedical(),
            'coaching' => static::seedCoaching(),
            default    => static::seedGeneral(),
        };

        \App\Models\Setting::setValue(static::MARKER_KEY, '1');
    }

    public static function delete(): void
    {
        \App\Models\PurchaseReturnItem::query()->delete();
        \App\Models\PurchaseReturn::query()->delete();
        \App\Models\PosSaleReturnItem::query()->delete();
        \App\Models\PosSaleReturn::query()->delete();
        PosSaleItem::query()->delete();
        PosSale::query()->delete();
        ProductPurchaseItem::query()->delete();
        ProductPurchase::query()->delete();
        Product::query()->delete();
        CreditSale::query()->delete();
        UdharCustomer::query()->delete();
        SupplyOrder::query()->delete();
        PurchaseOrder::query()->delete();
        Customer::query()->delete();
        Supplier::query()->delete();
        Expense::query()->delete();
        Staff::query()->delete();
        DailyRate::query()->delete();
        CoachingFeeCollection::query()->delete();
        CoachingStudent::query()->delete();
        CoachingBatch::query()->delete();
        CoachingCourse::query()->delete();

        \App\Models\Setting::setValue(static::MARKER_KEY, '0');
    }

    public static function isSeeded(): bool
    {
        return \App\Models\Setting::getValue(static::MARKER_KEY, '0') === '1';
    }

    // ─────────────────────────────────────────────────────────────
    // CHICKEN SHOP
    // ─────────────────────────────────────────────────────────────
    private static function seedChicken(): void
    {
        $suppliers = collect([
            ['name' => 'Arshad Murgi Farm',   'phone' => '0300-1234567', 'address' => 'Raiwind Road, Lahore', 'credit_days' => 7,  'balance' => 15000],
            ['name' => 'Khalid Poultry',       'phone' => '0333-9876543', 'address' => 'Sheikhupura',          'credit_days' => 10, 'balance' => 8000],
            ['name' => 'Raja Chicken Supply',  'phone' => '0321-4567890', 'address' => 'Gujranwala',           'credit_days' => 5,  'balance' => 0],
        ])->map(fn($s) => Supplier::create($s + ['is_active' => true]));

        // type must be: retail, hotel, restaurant, company, reseller
        $customers = collect([
            ['name' => 'Hotel Al-Jannat',  'phone' => '0311-1112222', 'type' => 'hotel',      'credit_limit' => 50000, 'credit_days' => 15],
            ['name' => 'Karahi Palace',    'phone' => '0322-3334444', 'type' => 'restaurant', 'credit_limit' => 30000, 'credit_days' => 10],
            ['name' => 'Biryani Corner',   'phone' => '0344-5556666', 'type' => 'retail',     'credit_limit' => 10000, 'credit_days' => 7],
            ['name' => 'Restaurant Star',  'phone' => '0355-7778888', 'type' => 'restaurant', 'credit_limit' => 40000, 'credit_days' => 15],
        ])->map(fn($c) => Customer::create($c + ['current_balance' => 0, 'is_active' => true, 'is_blacklisted' => false]));

        // Daily Rates (last 7 days)
        foreach (range(6, 0) as $daysAgo) {
            DailyRate::create([
                'date'               => now()->subDays($daysAgo)->toDateString(),
                'live_rate_per_kg'   => rand(280, 320),
                'retail_rate_per_kg' => rand(370, 420),
                'supply_rate_per_kg' => rand(340, 380),
                'notes'              => 'Demo rate',
            ]);
        }

        // Purchase Orders (last 10 days)
        foreach (range(9, 0) as $daysAgo) {
            $liveKg = rand(80, 200);
            $rate   = rand(285, 315);
            $total  = $liveKg * $rate;
            $paid   = round($total * (rand(50, 100) / 100));
            PurchaseOrder::create([
                'supplier_id'        => $suppliers->random()->id,
                'date'               => now()->subDays($daysAgo)->toDateString(),
                'invoice_number'     => 'PO-DEMO-' . str_pad($daysAgo + 1, 3, '0', STR_PAD_LEFT),
                'live_weight_kg'     => $liveKg,
                'dead_on_arrival_kg' => rand(1, 5),
                'rate_per_kg_live'   => $rate,
                'total_amount'       => $total,
                'amount_paid'        => $paid,
                'amount_due'         => $total - $paid,
                'due_date'           => now()->subDays($daysAgo)->addDays(7)->toDateString(),
                'payment_status'     => $paid >= $total ? 'paid' : 'partial',
            ]);
        }

        // Supply Orders
        foreach (range(8, 0) as $daysAgo) {
            $kg    = rand(20, 60);
            $rate  = rand(350, 400);
            $total = $kg * $rate;
            $paid  = $daysAgo > 2 ? $total : round($total * 0.5);
            SupplyOrder::create([
                'customer_id'       => $customers->random()->id,
                'date'              => now()->subDays($daysAgo)->toDateString(),
                'delivery_date'     => now()->subDays($daysAgo)->addDay()->toDateString(),
                'invoice_number'    => 'SO-DEMO-' . str_pad($daysAgo + 1, 3, '0', STR_PAD_LEFT),
                'dressed_weight_kg' => $kg,
                'rate_per_kg'       => $rate,
                'total_amount'      => $total,
                'amount_paid'       => $paid,
                'amount_due'        => $total - $paid,
                'due_date'          => now()->subDays($daysAgo)->addDays(10)->toDateString(),
                'payment_status'    => $paid >= $total ? 'paid' : 'partial',
                'is_delivered'      => $daysAgo > 0,
            ]);
        }

        static::seedCommon();
    }

    // ─────────────────────────────────────────────────────────────
    // HARDWARE SHOP
    // ─────────────────────────────────────────────────────────────
    private static function seedHardware(): void
    {
        $suppliers = collect([
            ['name' => 'Ali Hardware Wholesale', 'phone' => '0300-1111222', 'address' => 'Anarkali, Lahore',    'credit_days' => 30, 'balance' => 25000],
            ['name' => 'Shah Steel & Iron',       'phone' => '0333-2223333', 'address' => 'Shahdara, Lahore',    'credit_days' => 15, 'balance' => 12000],
            ['name' => 'Pak Plumbing Supplies',   'phone' => '0321-3334444', 'address' => 'Badami Bagh, Lahore', 'credit_days' => 20, 'balance' => 0],
        ])->map(fn($s) => Supplier::create($s + ['is_active' => true]));

        $products = [
            ['name' => 'Cement Screw 2 inch',  'sku' => 'HW-001', 'category' => 'Fasteners',  'unit' => 'Dozen', 'cost_price' => 25,   'sale_price' => 40,   'stock_qty' => 500, 'low_stock_alert' => 50],
            ['name' => 'GI Pipe 1/2 inch',     'sku' => 'HW-002', 'category' => 'Pipes',      'unit' => 'Meter', 'cost_price' => 180,  'sale_price' => 250,  'stock_qty' => 120, 'low_stock_alert' => 20],
            ['name' => 'Wall Putty 20kg',       'sku' => 'HW-003', 'category' => 'Paints',     'unit' => 'Bag',   'cost_price' => 750,  'sale_price' => 950,  'stock_qty' => 40,  'low_stock_alert' => 10],
            ['name' => 'Circuit Breaker 30A',   'sku' => 'HW-004', 'category' => 'Electrical', 'unit' => 'Pcs',   'cost_price' => 450,  'sale_price' => 650,  'stock_qty' => 25,  'low_stock_alert' => 5],
            ['name' => 'PVC Elbow 3/4',         'sku' => 'HW-005', 'category' => 'Pipes',      'unit' => 'Pcs',   'cost_price' => 15,   'sale_price' => 25,   'stock_qty' => 300, 'low_stock_alert' => 50],
            ['name' => 'Hammer 300g',           'sku' => 'HW-006', 'category' => 'Tools',      'unit' => 'Pcs',   'cost_price' => 280,  'sale_price' => 420,  'stock_qty' => 15,  'low_stock_alert' => 3],
            ['name' => 'Wire 1mm 100m Roll',    'sku' => 'HW-007', 'category' => 'Electrical', 'unit' => 'Roll',  'cost_price' => 1800, 'sale_price' => 2400, 'stock_qty' => 20,  'low_stock_alert' => 5],
            ['name' => 'Drill Bit Set',         'sku' => 'HW-008', 'category' => 'Tools',      'unit' => 'Set',   'cost_price' => 350,  'sale_price' => 550,  'stock_qty' => 12,  'low_stock_alert' => 3],
        ];

        $productModels = collect($products)->map(fn($p) => Product::create($p + ['is_active' => true, 'description' => '']));
        static::seedProductPurchases($suppliers, $productModels);
        static::seedPosSales($productModels);
        static::seedCommon();
    }

    // ─────────────────────────────────────────────────────────────
    // MOBILE SHOP
    // ─────────────────────────────────────────────────────────────
    private static function seedMobile(): void
    {
        $suppliers = collect([
            ['name' => 'Hafeez Mobile Wholesale', 'phone' => '0300-5556666', 'address' => 'Hall Road, Lahore',   'credit_days' => 15, 'balance' => 50000],
            ['name' => 'Galaxy Distributors',      'phone' => '0333-6667777', 'address' => 'Abid Market, Lahore', 'credit_days' => 7,  'balance' => 20000],
            ['name' => 'Tech Import Co.',          'phone' => '0321-7778888', 'address' => 'Karachi',             'credit_days' => 30, 'balance' => 0],
        ])->map(fn($s) => Supplier::create($s + ['is_active' => true]));

        $products = [
            ['name' => 'Samsung A15 6+128GB',      'sku' => 'MOB-001', 'category' => 'Smartphones', 'unit' => 'Pcs', 'cost_price' => 42000,  'sale_price' => 48000,  'stock_qty' => 8,   'low_stock_alert' => 2],
            ['name' => 'Infinix Hot 40',            'sku' => 'MOB-002', 'category' => 'Smartphones', 'unit' => 'Pcs', 'cost_price' => 28000,  'sale_price' => 33000,  'stock_qty' => 12,  'low_stock_alert' => 3],
            ['name' => 'iPhone 13 128GB',           'sku' => 'MOB-003', 'category' => 'Smartphones', 'unit' => 'Pcs', 'cost_price' => 155000, 'sale_price' => 170000, 'stock_qty' => 3,   'low_stock_alert' => 1],
            ['name' => 'Type-C Cable 1m',           'sku' => 'ACC-001', 'category' => 'Accessories', 'unit' => 'Pcs', 'cost_price' => 120,    'sale_price' => 250,    'stock_qty' => 100, 'low_stock_alert' => 20],
            ['name' => 'Tempered Glass Universal',  'sku' => 'ACC-002', 'category' => 'Accessories', 'unit' => 'Pcs', 'cost_price' => 80,     'sale_price' => 200,    'stock_qty' => 200, 'low_stock_alert' => 30],
            ['name' => '20W Fast Charger',          'sku' => 'ACC-003', 'category' => 'Accessories', 'unit' => 'Pcs', 'cost_price' => 650,    'sale_price' => 1100,   'stock_qty' => 30,  'low_stock_alert' => 5],
            ['name' => 'Power Bank 20000mAh',       'sku' => 'ACC-004', 'category' => 'Accessories', 'unit' => 'Pcs', 'cost_price' => 2800,   'sale_price' => 4200,   'stock_qty' => 10,  'low_stock_alert' => 2],
            ['name' => 'Silicon Cover Samsung A15', 'sku' => 'COV-001', 'category' => 'Covers',      'unit' => 'Pcs', 'cost_price' => 150,    'sale_price' => 350,    'stock_qty' => 50,  'low_stock_alert' => 10],
        ];

        $productModels = collect($products)->map(fn($p) => Product::create($p + ['is_active' => true, 'description' => '']));
        static::seedProductPurchases($suppliers, $productModels);
        static::seedPosSales($productModels);
        static::seedCommon();
    }

    // ─────────────────────────────────────────────────────────────
    // BIKE SHOP
    // ─────────────────────────────────────────────────────────────
    private static function seedBike(): void
    {
        $suppliers = collect([
            ['name' => 'Honda Parts Distributor', 'phone' => '0300-8889999', 'address' => 'McLeod Road, Lahore', 'credit_days' => 30, 'balance' => 35000],
            ['name' => 'Yamaha Spare Parts',       'phone' => '0333-9990000', 'address' => 'Brandreth Road',      'credit_days' => 15, 'balance' => 18000],
            ['name' => 'China Parts Import',       'phone' => '0321-0001111', 'address' => 'Bilal Gunj, Lahore',  'credit_days' => 7,  'balance' => 0],
        ])->map(fn($s) => Supplier::create($s + ['is_active' => true]));

        $products = [
            ['name' => 'Honda 125 Chain',          'sku' => 'BK-001', 'category' => 'Drive',      'unit' => 'Pcs', 'cost_price' => 850,  'sale_price' => 1200, 'stock_qty' => 20, 'low_stock_alert' => 5],
            ['name' => 'Motorcycle Tyre 2.75-17',  'sku' => 'BK-002', 'category' => 'Tyres',      'unit' => 'Pcs', 'cost_price' => 2200, 'sale_price' => 3000, 'stock_qty' => 15, 'low_stock_alert' => 4],
            ['name' => 'Engine Oil 1L 20W50',      'sku' => 'BK-003', 'category' => 'Lubricants', 'unit' => 'Ltr', 'cost_price' => 900,  'sale_price' => 1300, 'stock_qty' => 40, 'low_stock_alert' => 10],
            ['name' => 'Brake Shoe Front',         'sku' => 'BK-004', 'category' => 'Brakes',     'unit' => 'Set', 'cost_price' => 280,  'sale_price' => 450,  'stock_qty' => 25, 'low_stock_alert' => 5],
            ['name' => 'Spark Plug NGK',           'sku' => 'BK-005', 'category' => 'Engine',     'unit' => 'Pcs', 'cost_price' => 180,  'sale_price' => 320,  'stock_qty' => 50, 'low_stock_alert' => 10],
            ['name' => 'Headlight Bulb 12V',       'sku' => 'BK-006', 'category' => 'Electrical', 'unit' => 'Pcs', 'cost_price' => 120,  'sale_price' => 220,  'stock_qty' => 30, 'low_stock_alert' => 5],
            ['name' => 'Air Filter Honda 125',     'sku' => 'BK-007', 'category' => 'Engine',     'unit' => 'Pcs', 'cost_price' => 350,  'sale_price' => 550,  'stock_qty' => 18, 'low_stock_alert' => 4],
            ['name' => 'Clutch Plate Set',         'sku' => 'BK-008', 'category' => 'Drive',      'unit' => 'Set', 'cost_price' => 650,  'sale_price' => 950,  'stock_qty' => 10, 'low_stock_alert' => 3],
        ];

        $productModels = collect($products)->map(fn($p) => Product::create($p + ['is_active' => true, 'description' => '']));
        static::seedProductPurchases($suppliers, $productModels);
        static::seedPosSales($productModels);
        static::seedCommon();
    }

    // ─────────────────────────────────────────────────────────────
    // GENERAL SHOP
    // ─────────────────────────────────────────────────────────────
    private static function seedGeneral(): void
    {
        $suppliers = collect([
            ['name' => 'Unilever Distributor',   'phone' => '0300-2223333', 'address' => 'Johar Town, Lahore', 'credit_days' => 21, 'balance' => 45000],
            ['name' => 'National Foods Agent',   'phone' => '0333-3334444', 'address' => 'Gulberg, Lahore',    'credit_days' => 15, 'balance' => 22000],
            ['name' => 'Local Wholesale Market', 'phone' => '0321-4445555', 'address' => 'Bhati Gate, Lahore', 'credit_days' => 7,  'balance' => 0],
        ])->map(fn($s) => Supplier::create($s + ['is_active' => true]));

        $products = [
            ['name' => 'Surf Excel 1kg',          'sku' => 'GEN-001', 'category' => 'Detergents',   'unit' => 'Pcs', 'cost_price' => 380,  'sale_price' => 440,  'stock_qty' => 60,  'low_stock_alert' => 10],
            ['name' => 'Tapal Danedar 900g',      'sku' => 'GEN-002', 'category' => 'Beverages',    'unit' => 'Pcs', 'cost_price' => 920,  'sale_price' => 1100, 'stock_qty' => 30,  'low_stock_alert' => 8],
            ['name' => 'Basmati Rice 5kg',        'sku' => 'GEN-003', 'category' => 'Grocery',      'unit' => 'Pcs', 'cost_price' => 950,  'sale_price' => 1200, 'stock_qty' => 50,  'low_stock_alert' => 10],
            ['name' => 'Nestle MilkPak 1L',       'sku' => 'GEN-004', 'category' => 'Dairy',        'unit' => 'Pcs', 'cost_price' => 185,  'sale_price' => 220,  'stock_qty' => 80,  'low_stock_alert' => 20],
            ['name' => 'Colgate 150ml',           'sku' => 'GEN-005', 'category' => 'PersonalCare', 'unit' => 'Pcs', 'cost_price' => 165,  'sale_price' => 215,  'stock_qty' => 40,  'low_stock_alert' => 10],
            ['name' => 'Lays Chips Large',        'sku' => 'GEN-006', 'category' => 'Snacks',       'unit' => 'Pcs', 'cost_price' => 60,   'sale_price' => 80,   'stock_qty' => 120, 'low_stock_alert' => 20],
            ['name' => 'Cooking Oil 5L',          'sku' => 'GEN-007', 'category' => 'Grocery',      'unit' => 'Pcs', 'cost_price' => 2600, 'sale_price' => 3000, 'stock_qty' => 25,  'low_stock_alert' => 5],
            ['name' => 'Head Shoulders Shampoo',  'sku' => 'GEN-008', 'category' => 'PersonalCare', 'unit' => 'Pcs', 'cost_price' => 520,  'sale_price' => 680,  'stock_qty' => 20,  'low_stock_alert' => 5],
        ];

        $productModels = collect($products)->map(fn($p) => Product::create($p + ['is_active' => true, 'description' => '']));
        static::seedProductPurchases($suppliers, $productModels);
        static::seedPosSales($productModels);
        static::seedCommon();
    }

    // ─────────────────────────────────────────────────────────────
    // MEDICAL STORE — Karachi
    // ─────────────────────────────────────────────────────────────
    private static function seedMedical(): void
    {
        $suppliers = collect([
            ['name' => 'Sami Pharma Distributors',  'phone' => '0300-2121212', 'address' => 'M.A. Jinnah Road, Karachi',  'credit_days' => 30, 'balance' => 35000],
            ['name' => 'Al-Shifa Medical Wholesale', 'phone' => '0333-3232323', 'address' => 'Saddar, Karachi',             'credit_days' => 15, 'balance' => 18000],
            ['name' => 'Karachi Pharma Agency',      'phone' => '0321-4343434', 'address' => 'Liaquatabad, Karachi',        'credit_days' => 21, 'balance' => 0],
            ['name' => 'Hamdard Distributors',       'phone' => '0311-5454545', 'address' => 'North Nazimabad, Karachi',    'credit_days' => 14, 'balance' => 9500],
        ])->map(fn($s) => Supplier::create($s + ['is_active' => true]));

        $products = [
            // Tablets & Capsules
            ['name' => 'Panadol Tablet 500mg (10s)',    'sku' => 'MED-001', 'category' => 'Tablets',    'unit' => 'Pcs', 'cost_price' => 18,   'sale_price' => 25,   'stock_qty' => 300, 'low_stock_alert' => 50],
            ['name' => 'Brufen 400mg (10s)',             'sku' => 'MED-002', 'category' => 'Tablets',    'unit' => 'Pcs', 'cost_price' => 22,   'sale_price' => 32,   'stock_qty' => 250, 'low_stock_alert' => 40],
            ['name' => 'Amoxil 500mg Capsule (10s)',    'sku' => 'MED-003', 'category' => 'Antibiotics', 'unit' => 'Pcs', 'cost_price' => 55,   'sale_price' => 80,   'stock_qty' => 150, 'low_stock_alert' => 30],
            ['name' => 'Flagyl 400mg Tablet (10s)',     'sku' => 'MED-004', 'category' => 'Antibiotics', 'unit' => 'Pcs', 'cost_price' => 38,   'sale_price' => 55,   'stock_qty' => 120, 'low_stock_alert' => 25],
            ['name' => 'Omeprazole 20mg (14s)',         'sku' => 'MED-005', 'category' => 'Tablets',    'unit' => 'Pcs', 'cost_price' => 48,   'sale_price' => 70,   'stock_qty' => 180, 'low_stock_alert' => 30],
            ['name' => 'Disprin 300mg (10s)',            'sku' => 'MED-006', 'category' => 'Tablets',    'unit' => 'Pcs', 'cost_price' => 12,   'sale_price' => 18,   'stock_qty' => 200, 'low_stock_alert' => 40],
            ['name' => 'Augmentin 625mg (6s)',           'sku' => 'MED-007', 'category' => 'Antibiotics', 'unit' => 'Pcs', 'cost_price' => 185,  'sale_price' => 260,  'stock_qty' => 80,  'low_stock_alert' => 15],
            // Syrups
            ['name' => 'Calpol Syrup 120mg/5ml 90ml',  'sku' => 'MED-008', 'category' => 'Syrups',     'unit' => 'Btl', 'cost_price' => 85,   'sale_price' => 120,  'stock_qty' => 100, 'low_stock_alert' => 20],
            ['name' => 'Benylin Cough Syrup 100ml',    'sku' => 'MED-009', 'category' => 'Syrups',     'unit' => 'Btl', 'cost_price' => 120,  'sale_price' => 165,  'stock_qty' => 70,  'low_stock_alert' => 15],
            ['name' => 'ORS Sachet Oral Rehydration',  'sku' => 'MED-010', 'category' => 'Syrups',     'unit' => 'Pcs', 'cost_price' => 8,    'sale_price' => 15,   'stock_qty' => 500, 'low_stock_alert' => 100],
            // Injections / Drips
            ['name' => 'Normal Saline 1000ml Drip',    'sku' => 'MED-011', 'category' => 'Drips',      'unit' => 'Btl', 'cost_price' => 180,  'sale_price' => 250,  'stock_qty' => 40,  'low_stock_alert' => 10],
            ['name' => 'Vitamin C Injection 500mg',    'sku' => 'MED-012', 'category' => 'Injections',  'unit' => 'Pcs', 'cost_price' => 35,   'sale_price' => 55,   'stock_qty' => 60,  'low_stock_alert' => 15],
            // Bandages & Surgical
            ['name' => 'Crepe Bandage 4 inch',         'sku' => 'MED-013', 'category' => 'Surgical',   'unit' => 'Pcs', 'cost_price' => 45,   'sale_price' => 70,   'stock_qty' => 80,  'low_stock_alert' => 15],
            ['name' => 'Surgical Gloves Medium (100s)','sku' => 'MED-014', 'category' => 'Surgical',   'unit' => 'Box', 'cost_price' => 550,  'sale_price' => 800,  'stock_qty' => 20,  'low_stock_alert' => 5],
            ['name' => 'Cotton Roll 200g',              'sku' => 'MED-015', 'category' => 'Surgical',   'unit' => 'Pcs', 'cost_price' => 65,   'sale_price' => 100,  'stock_qty' => 60,  'low_stock_alert' => 10],
            // BP & Sugar
            ['name' => 'Glucometer Strip (25s)',        'sku' => 'MED-016', 'category' => 'Diagnostics','unit' => 'Box', 'cost_price' => 380,  'sale_price' => 550,  'stock_qty' => 25,  'low_stock_alert' => 5],
            ['name' => 'BP Machine Digital Omron',      'sku' => 'MED-017', 'category' => 'Diagnostics','unit' => 'Pcs', 'cost_price' => 3200, 'sale_price' => 4500, 'stock_qty' => 5,   'low_stock_alert' => 2],
            // Vitamins & Supplements
            ['name' => 'Vitamin D3 1000IU (30s)',       'sku' => 'MED-018', 'category' => 'Vitamins',   'unit' => 'Pcs', 'cost_price' => 180,  'sale_price' => 280,  'stock_qty' => 90,  'low_stock_alert' => 20],
            ['name' => 'Calcium + D3 Tablet (30s)',     'sku' => 'MED-019', 'category' => 'Vitamins',   'unit' => 'Pcs', 'cost_price' => 220,  'sale_price' => 320,  'stock_qty' => 70,  'low_stock_alert' => 15],
            ['name' => 'Hamdard Safi 500ml',            'sku' => 'MED-020', 'category' => 'Herbal',     'unit' => 'Btl', 'cost_price' => 320,  'sale_price' => 450,  'stock_qty' => 30,  'low_stock_alert' => 8],
        ];

        $productModels = collect($products)->map(fn($p) => Product::create($p + ['is_active' => true, 'description' => '']));
        static::seedProductPurchases($suppliers, $productModels);
        static::seedPosSalesMedical($productModels);
        static::seedCommonMedical();
    }

    private static function seedPosSalesMedical($products): void
    {
        $methods   = ['cash', 'cash', 'cash', 'cash', 'jazzcash', 'easypaisa'];
        $customers = ['Walk-in', 'Haji Sahab', 'Baji Tahira', 'Uncle Rasheed', 'Asif bhai', 'Sana Baji'];

        for ($i = 1; $i <= 12; $i++) {
            $selectedProducts = $products->random(min(rand(1, 4), $products->count()));
            $subtotal = 0;
            $items    = [];

            foreach ($selectedProducts as $product) {
                $qty      = rand(1, 5);
                $price    = $product->sale_price;
                $items[]  = ['product' => $product, 'qty' => $qty, 'price' => $price, 'total' => $qty * $price];
                $subtotal += $qty * $price;
            }

            $discount = rand(0, 1) ? rand(10, 100) : 0;
            $total    = $subtotal - $discount;

            $sale = PosSale::create([
                'sale_number'    => 'POS-DEMO-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'date'           => now()->subDays(rand(0, 14)),
                'customer_name'  => collect($customers)->random(),
                'customer_phone' => '',
                'subtotal'       => $subtotal,
                'discount'       => $discount,
                'total'          => $total,
                'amount_paid'    => $total,
                'change_due'     => 0,
                'payment_method' => collect($methods)->random(),
            ]);

            foreach ($items as $item) {
                PosSaleItem::create([
                    'pos_sale_id'  => $sale->id,
                    'product_id'   => $item['product']->id,
                    'product_name' => $item['product']->name,
                    'qty'          => $item['qty'],
                    'unit_price'   => $item['price'],
                    'total'        => $item['total'],
                ]);
            }
        }
    }

    private static function seedCommonMedical(): void
    {
        // Staff
        Staff::create(['name' => 'Dr. Asif Pharmacist', 'phone' => '0312-1234567', 'role' => 'manager',  'salary' => 45000, 'joining_date' => now()->subMonths(8)->toDateString(), 'is_active' => true]);
        Staff::create(['name' => 'Kamran Helper',        'phone' => '0323-2345678', 'role' => 'cashier',  'salary' => 20000, 'joining_date' => now()->subMonths(4)->toDateString(), 'is_active' => true]);
        Staff::create(['name' => 'Wasim Delivery',       'phone' => '0334-3456789', 'role' => 'delivery_boy', 'salary' => 15000, 'joining_date' => now()->subMonths(2)->toDateString(), 'is_active' => true]);

        // Expenses
        $expenseData = [
            ['category' => 'shop_rent',    'description' => 'Shop rent — Saddar Karachi',      'amount' => 25000, 'paid_to' => 'Landlord Haji Amjad'],
            ['category' => 'electricity',  'description' => 'KESC electricity bill',            'amount' => 4200,  'paid_to' => 'K-Electric Office'],
            ['category' => 'staff_salary', 'description' => 'Dr. Asif monthly salary',          'amount' => 45000, 'paid_to' => 'Dr. Asif'],
            ['category' => 'staff_salary', 'description' => 'Kamran salary',                    'amount' => 20000, 'paid_to' => 'Kamran Helper'],
            ['category' => 'delivery',     'description' => 'Home delivery OPD medicines',      'amount' => 600,   'paid_to' => 'Wasim Driver'],
            ['category' => 'maintenance',  'description' => 'AC service & fridge repair',       'amount' => 3000,  'paid_to' => 'Technician Imran'],
            ['category' => 'other',        'description' => 'Paper bags & medicine envelopes',  'amount' => 800,   'paid_to' => 'Saddar Market'],
        ];

        foreach ($expenseData as $i => $exp) {
            Expense::create($exp + [
                'date'           => now()->subDays(rand(0, 15))->toDateString(),
                'receipt_number' => 'EXP-DEMO-' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
            ]);
        }

        // Udhar Customers (credit patients)
        $udharCustomers = collect([
            ['name' => 'Haji Abdul Rehman', 'phone' => '0312-1110001', 'whatsapp_number' => '0312-1110001', 'address' => 'Block 5, Gulshan-e-Iqbal, Karachi'],
            ['name' => 'Sakina Bibi',       'phone' => '0323-2220002', 'whatsapp_number' => null,           'address' => 'Liaquatabad No. 7, Karachi'],
            ['name' => 'Dr. Tariq Clinic',  'phone' => '0334-3330003', 'whatsapp_number' => '0334-3330003', 'address' => 'North Karachi, Sector 11-C'],
        ])->map(fn($u) => UdharCustomer::create($u + ['total_given' => 0, 'total_received' => 0, 'current_balance' => 0, 'notes' => '']));

        foreach ($udharCustomers as $uc) {
            $amount = rand(1500, 8000);
            $paid   = rand(0, $amount);
            CreditSale::create([
                'udhar_customer_id' => $uc->id,
                'customer_name'     => $uc->name,
                'phone'             => $uc->phone,
                'amount'            => $amount,
                'amount_paid'       => $paid,
                'amount_due'        => $amount - $paid,
                'sale_date'         => now()->subDays(rand(1, 20))->toDateString(),
                'due_date'          => now()->addDays(rand(7, 30))->toDateString(),
                'description'       => 'Medicine credit',
                'status'            => $paid >= $amount ? 'paid' : ($paid > 0 ? 'partial' : 'pending'),
                'notes'             => '',
            ]);
        }
    }

    // ─────────────────────────────────────────────────────────────
    // SHARED HELPERS
    // ─────────────────────────────────────────────────────────────
    private static function seedCommon(): void
    {
        // Staff — valid roles: manager, butcher, delivery_boy, cashier
        Staff::create(['name' => 'Ahmed Ali',    'phone' => '0311-1234567', 'role' => 'manager',      'salary' => 35000, 'joining_date' => now()->subMonths(6)->toDateString(), 'is_active' => true]);
        Staff::create(['name' => 'Bilal Hassan', 'phone' => '0322-2345678', 'role' => 'cashier',      'salary' => 22000, 'joining_date' => now()->subMonths(3)->toDateString(), 'is_active' => true]);
        Staff::create(['name' => 'Usman Khan',   'phone' => '0333-3456789', 'role' => 'delivery_boy', 'salary' => 16000, 'joining_date' => now()->subMonths(1)->toDateString(), 'is_active' => true]);

        // Expenses — valid categories: staff_salary, delivery, shop_rent, electricity, fuel, maintenance, other
        $expenseData = [
            ['category' => 'shop_rent',   'description' => 'Monthly shop rent',       'amount' => 18000, 'paid_to' => 'Landlord Malik Sahab'],
            ['category' => 'electricity', 'description' => 'LESCO electricity bill',   'amount' => 3500,  'paid_to' => 'LESCO Office'],
            ['category' => 'delivery',    'description' => 'Delivery rickshaw fare',   'amount' => 800,   'paid_to' => 'Driver Rashid'],
            ['category' => 'staff_salary','description' => 'Ahmed Ali monthly salary', 'amount' => 35000, 'paid_to' => 'Ahmed Ali'],
            ['category' => 'staff_salary','description' => 'Bilal Hassan salary',      'amount' => 22000, 'paid_to' => 'Bilal Hassan'],
            ['category' => 'maintenance', 'description' => 'AC service & gas fill',    'amount' => 2500,  'paid_to' => 'Technician Asif'],
            ['category' => 'other',       'description' => 'Stationery & bags',        'amount' => 650,   'paid_to' => 'Raja Stationery'],
        ];

        foreach ($expenseData as $i => $exp) {
            Expense::create($exp + [
                'date'           => now()->subDays(rand(0, 10))->toDateString(),
                'receipt_number' => 'EXP-DEMO-' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
            ]);
        }

        // Udhar Customers + Credit Sales
        $udharCustomers = collect([
            ['name' => 'Tariq Mehmood', 'phone' => '0312-1111222', 'whatsapp_number' => '0312-1111222', 'address' => 'Mohalla Islam Pura'],
            ['name' => 'Imran Butt',    'phone' => '0323-2223333', 'whatsapp_number' => '0323-2223333', 'address' => 'Street 5, Gulshan Colony'],
            ['name' => 'Nasir Ahmed',   'phone' => '0334-3334444', 'whatsapp_number' => null,           'address' => 'Near Masjid, Block B'],
        ])->map(fn($u) => UdharCustomer::create($u + ['total_given' => 0, 'total_received' => 0, 'current_balance' => 0, 'notes' => '']));

        foreach ($udharCustomers as $uc) {
            $amount = rand(2000, 15000);
            $paid   = rand(0, $amount);
            CreditSale::create([
                'udhar_customer_id' => $uc->id,
                'customer_name'     => $uc->name,
                'phone'             => $uc->phone,
                'amount'            => $amount,
                'amount_paid'       => $paid,
                'amount_due'        => $amount - $paid,
                'sale_date'         => now()->subDays(rand(1, 15))->toDateString(),
                'due_date'          => now()->addDays(rand(5, 30))->toDateString(),
                'description'       => 'Demo credit sale',
                'status'            => $paid >= $amount ? 'paid' : ($paid > 0 ? 'partial' : 'pending'),
                'notes'             => '',
            ]);
        }
    }

    private static function seedProductPurchases($suppliers, $products): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $selectedProducts = $products->random(min(rand(2, 4), $products->count()));
            $total = 0;
            $items = [];

            foreach ($selectedProducts as $product) {
                $qty     = rand(5, 20);
                $price   = $product->cost_price;
                $items[] = ['product' => $product, 'qty' => $qty, 'price' => $price, 'total' => $qty * $price];
                $total  += $qty * $price;
            }

            $paid = $i <= 3 ? $total : round($total * 0.6);

            $purchase = ProductPurchase::create([
                'supplier_id'    => $suppliers->random()->id,
                'date'           => now()->subDays(($i - 1) * 3)->toDateString(),
                'invoice_number' => 'PP-DEMO-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'total_amount'   => $total,
                'amount_paid'    => $paid,
                'amount_due'     => $total - $paid,
                'payment_status' => $paid >= $total ? 'paid' : 'partial',
                'due_date'       => now()->addDays(15)->toDateString(),
            ]);

            foreach ($items as $item) {
                ProductPurchaseItem::create([
                    'product_purchase_id' => $purchase->id,
                    'product_id'          => $item['product']->id,
                    'qty'                 => $item['qty'],
                    'unit_price'          => $item['price'],
                    'total'               => $item['total'],
                ]);
            }
        }
    }

    private static function seedPosSales($products): void
    {
        // payment_method enum: cash, jazzcash, easypaisa, bank, credit
        $methods = ['cash', 'cash', 'cash', 'jazzcash', 'easypaisa', 'bank'];

        for ($i = 1; $i <= 8; $i++) {
            $selectedProducts = $products->random(min(rand(1, 3), $products->count()));
            $subtotal = 0;
            $items    = [];

            foreach ($selectedProducts as $product) {
                $qty      = rand(1, 3);
                $price    = $product->sale_price;
                $items[]  = ['product' => $product, 'qty' => $qty, 'price' => $price, 'total' => $qty * $price];
                $subtotal += $qty * $price;
            }

            $discount = rand(0, 1) ? rand(50, 200) : 0;
            $total    = $subtotal - $discount;

            $sale = PosSale::create([
                'sale_number'    => 'POS-DEMO-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'date'           => now()->subDays(rand(0, 10)),
                'customer_name'  => collect(['Walk-in', 'Tariq sb', 'Asif bhai', 'Bilal bhai'])->random(),
                'customer_phone' => '',
                'subtotal'       => $subtotal,
                'discount'       => $discount,
                'total'          => $total,
                'amount_paid'    => $total,
                'change_due'     => 0,
                'payment_method' => collect($methods)->random(),
            ]);

            foreach ($items as $item) {
                PosSaleItem::create([
                    'pos_sale_id'  => $sale->id,
                    'product_id'   => $item['product']->id,
                    'product_name' => $item['product']->name,
                    'qty'          => $item['qty'],
                    'unit_price'   => $item['price'],
                    'total'        => $item['total'],
                ]);
            }
        }
    }

    // ─────────────────────────────────────────────────────────────
    private static function seedCoaching(): void
    {
        // Expenses — valid categories: staff_salary, delivery, shop_rent, electricity, fuel, maintenance, other
        $expenseData = [
            ['description' => 'Monthly Rent (Academy)', 'amount' => 15000, 'category' => 'shop_rent',   'paid_to' => 'Malik Sahab (Landlord)'],
            ['description' => 'KESC Bijli Bill',        'amount' => 3200,  'category' => 'electricity', 'paid_to' => 'K-Electric'],
            ['description' => 'Internet (PTCL)',        'amount' => 1500,  'category' => 'other',       'paid_to' => 'PTCL'],
            ['description' => 'Marker & Chalk',         'amount' => 800,   'category' => 'other',       'paid_to' => 'Stationary Shop'],
            ['description' => 'Whiteboard Repair',      'amount' => 1200,  'category' => 'maintenance', 'paid_to' => 'Carpenter'],
        ];
        foreach ($expenseData as $e) {
            Expense::create($e + ['date' => now()->startOfMonth()->toDateString()]);
        }

        // Staff — valid roles: manager, butcher, delivery_boy, cashier
        Staff::create(['name' => 'Ustad Muhammad Arif', 'role' => 'manager', 'phone' => '0300-7171717', 'salary' => 18000, 'joining_date' => now()->subMonths(8)->toDateString(), 'is_active' => true]);
        Staff::create(['name' => 'Asma Baji',           'role' => 'manager', 'phone' => '0321-8282828', 'salary' => 15000, 'joining_date' => now()->subMonths(5)->toDateString(), 'is_active' => true]);
        Staff::create(['name' => 'Bilal Bhai',          'role' => 'cashier', 'phone' => '0333-9393939', 'salary' => 8000,  'joining_date' => now()->subMonths(2)->toDateString(), 'is_active' => true]);

        // Courses
        $matric = CoachingCourse::create(['name' => 'Matriculation (Science)',  'monthly_fee' => 2500, 'description' => 'Physics, Chemistry, Biology, Maths', 'is_active' => true]);
        $fa     = CoachingCourse::create(['name' => 'F.A / F.Sc Preparation',  'monthly_fee' => 3000, 'description' => 'Intermediate prep for Arts & Science', 'is_active' => true]);
        $eng    = CoachingCourse::create(['name' => 'English Speaking Course',  'monthly_fee' => 1800, 'description' => 'Spoken English & Grammar', 'is_active' => true]);
        $comp   = CoachingCourse::create(['name' => 'Computer Basics',          'monthly_fee' => 2000, 'description' => 'MS Office, Internet, Basics', 'is_active' => true]);

        // Batches
        $b1 = CoachingBatch::create(['course_id' => $matric->id, 'name' => 'Morning A',   'timing' => '7:00 AM – 9:00 AM', 'days' => 'Mon-Sat', 'teacher_name' => 'Ustad Muhammad Arif', 'capacity' => 25, 'is_active' => true]);
        $b2 = CoachingBatch::create(['course_id' => $matric->id, 'name' => 'Evening B',   'timing' => '5:00 PM – 7:00 PM', 'days' => 'Mon-Sat', 'teacher_name' => 'Ustad Muhammad Arif', 'capacity' => 25, 'is_active' => true]);
        $b3 = CoachingBatch::create(['course_id' => $fa->id,     'name' => 'Morning',     'timing' => '8:00 AM – 10:00 AM','days' => 'Mon-Fri', 'teacher_name' => 'Asma Baji',           'capacity' => 20, 'is_active' => true]);
        $b4 = CoachingBatch::create(['course_id' => $eng->id,    'name' => 'Spoken Eng',  'timing' => '4:00 PM – 5:30 PM', 'days' => 'Tue,Thu', 'teacher_name' => 'Asma Baji',           'capacity' => 15, 'is_active' => true]);
        $b5 = CoachingBatch::create(['course_id' => $comp->id,   'name' => 'Afternoon',   'timing' => '2:00 PM – 4:00 PM', 'days' => 'Mon,Wed,Fri', 'teacher_name' => 'Bilal Bhai',      'capacity' => 15, 'is_active' => true]);

        // Students
        $students = [
            // Matric Morning A
            ['batch_id' => $b1->id, 'name' => 'Ahmed Raza',       'phone' => '0300-1112222', 'guardian_name' => 'Raza Ahmed',      'guardian_phone' => '0300-1110000', 'enrollment_date' => now()->subMonths(3)->toDateString(), 'status' => 'active'],
            ['batch_id' => $b1->id, 'name' => 'Fatima Noor',      'phone' => '0321-2223333', 'guardian_name' => 'Noor ul Hassan',  'guardian_phone' => '0321-2220000', 'enrollment_date' => now()->subMonths(3)->toDateString(), 'status' => 'active'],
            ['batch_id' => $b1->id, 'name' => 'Usman Tariq',      'phone' => null,           'guardian_name' => 'Tariq Mehmood',   'guardian_phone' => '0333-3334444', 'enrollment_date' => now()->subMonths(2)->toDateString(), 'status' => 'active'],
            ['batch_id' => $b1->id, 'name' => 'Sana Irfan',       'phone' => '0311-4445555', 'guardian_name' => 'Irfan ul Haq',   'guardian_phone' => '0311-4440000', 'enrollment_date' => now()->subMonths(2)->toDateString(), 'status' => 'active', 'discount_percent' => 10],
            ['batch_id' => $b1->id, 'name' => 'Bilal Hassan',     'phone' => '0345-5556666', 'guardian_name' => 'Hassan Ali',      'guardian_phone' => '0345-5550000', 'enrollment_date' => now()->subMonths(4)->toDateString(), 'status' => 'active'],
            // Matric Evening B
            ['batch_id' => $b2->id, 'name' => 'Zainab Khalid',    'phone' => '0312-6667777', 'guardian_name' => 'Khalid Hussain',  'guardian_phone' => '0312-6660000', 'enrollment_date' => now()->subMonths(1)->toDateString(), 'status' => 'active'],
            ['batch_id' => $b2->id, 'name' => 'Hamza Sheikh',     'phone' => null,           'guardian_name' => 'Imran Sheikh',    'guardian_phone' => '0321-7778888', 'enrollment_date' => now()->subMonths(2)->toDateString(), 'status' => 'active'],
            ['batch_id' => $b2->id, 'name' => 'Ayesha Siddiqui',  'phone' => '0300-8889999', 'guardian_name' => 'Siddiqui Sb',    'guardian_phone' => '0300-8880000', 'enrollment_date' => now()->subMonths(3)->toDateString(), 'status' => 'active', 'custom_fee' => 2000],
            ['batch_id' => $b2->id, 'name' => 'Danish Iqbal',     'phone' => '0333-9990000', 'guardian_name' => 'Iqbal Ahmed',    'guardian_phone' => '0333-9990001', 'enrollment_date' => now()->subMonths(1)->toDateString(), 'status' => 'active'],
            // FA
            ['batch_id' => $b3->id, 'name' => 'Hira Baig',        'phone' => '0321-1212121', 'guardian_name' => 'Baig Sahib',     'guardian_phone' => '0321-1210000', 'enrollment_date' => now()->subMonths(2)->toDateString(), 'status' => 'active'],
            ['batch_id' => $b3->id, 'name' => 'Anas Rehman',      'phone' => '0345-2323232', 'guardian_name' => 'Abdul Rehman',   'guardian_phone' => '0345-2320000', 'enrollment_date' => now()->subMonths(3)->toDateString(), 'status' => 'active'],
            // English
            ['batch_id' => $b4->id, 'name' => 'Rabia Malik',      'phone' => '0311-3434343', 'guardian_name' => 'Malik Sb',       'guardian_phone' => '0311-3430000', 'enrollment_date' => now()->subMonths(1)->toDateString(), 'status' => 'active'],
            ['batch_id' => $b4->id, 'name' => 'Tariq Butt',       'phone' => '0300-4545454', 'guardian_name' => 'Butt Sahib',     'guardian_phone' => '0300-4540000', 'enrollment_date' => now()->subMonths(2)->toDateString(), 'status' => 'active'],
            // Computer
            ['batch_id' => $b5->id, 'name' => 'Sara Qureshi',     'phone' => '0333-5656565', 'guardian_name' => 'Qureshi Sb',     'guardian_phone' => '0333-5650000', 'enrollment_date' => now()->subMonths(1)->toDateString(), 'status' => 'active'],
            ['batch_id' => $b5->id, 'name' => 'Mohsin Ansari',    'phone' => '0321-6767676', 'guardian_name' => 'Ansari Sb',      'guardian_phone' => '0321-6760000', 'enrollment_date' => now()->subMonths(2)->toDateString(), 'status' => 'active'],
        ];

        $thisMonth = now()->startOfMonth()->toDateString();
        $lastMonth = now()->subMonth()->startOfMonth()->toDateString();

        foreach ($students as $sd) {
            $student = CoachingStudent::create(array_merge([
                'discount_percent' => 0,
                'custom_fee'       => null,
                'address'          => null,
                'notes'            => null,
            ], $sd));

            $fee = $student->effectiveFee();

            // Last month — all paid
            CoachingFeeCollection::create([
                'student_id'      => $student->id,
                'month'           => $lastMonth,
                'amount_due'      => $fee,
                'discount_amount' => 0,
                'amount_paid'     => $fee,
                'balance_due'     => 0,
                'status'          => 'paid',
                'payment_date'    => now()->subMonth()->endOfMonth()->toDateString(),
                'payment_method'  => 'cash',
                'receipt_number'  => CoachingFeeCollection::nextReceiptNumber(),
            ]);

            // This month — 60% paid, 40% pending
            $paid   = $student->id % 3 === 0 ? $fee : ($student->id % 3 === 1 ? 0 : $fee);
            $status = $paid >= $fee ? 'paid' : ($paid > 0 ? 'partial' : 'pending');
            CoachingFeeCollection::create([
                'student_id'      => $student->id,
                'month'           => $thisMonth,
                'amount_due'      => $fee,
                'discount_amount' => 0,
                'amount_paid'     => $paid,
                'balance_due'     => max(0, $fee - $paid),
                'status'          => $status,
                'payment_date'    => $paid > 0 ? now()->toDateString() : null,
                'payment_method'  => $paid > 0 ? 'cash' : null,
                'receipt_number'  => $paid > 0 ? CoachingFeeCollection::nextReceiptNumber() : null,
            ]);
        }
    }
}
