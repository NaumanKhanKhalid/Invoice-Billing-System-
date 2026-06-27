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
use Carbon\Carbon;
use Illuminate\Support\Str;

class DummyDataService
{
    // Marker stored in settings to track demo data
    const MARKER_KEY = 'demo_data_seeded';

    public static function seed(string $shopType): void
    {
        match ($shopType) {
            'chicken'  => static::seedChicken(),
            'hardware' => static::seedHardware(),
            'mobile'   => static::seedMobile(),
            'bike'     => static::seedBike(),
            'general'  => static::seedGeneral(),
            default    => static::seedGeneral(),
        };

        \App\Models\Setting::setValue(static::MARKER_KEY, '1');
    }

    public static function delete(): void
    {
        // Delete in FK-safe order
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

        \App\Models\Setting::setValue(static::MARKER_KEY, '0');
    }

    public static function isSeeded(): bool
    {
        return \App\Models\Setting::getValue(static::MARKER_KEY, '0') === '1';
    }

    // ─────────────────────────────────────────────────────────────
    // CHICKEN SHOP 🍗
    // ─────────────────────────────────────────────────────────────
    private static function seedChicken(): void
    {
        // Suppliers
        $suppliers = collect([
            ['name' => 'Arshad Murgi Farm',     'phone' => '0300-1234567', 'address' => 'Raiwind Road, Lahore',    'credit_days' => 7,  'balance' => 15000],
            ['name' => 'Khalid Poultry',         'phone' => '0333-9876543', 'address' => 'Sheikhupura',            'credit_days' => 10, 'balance' => 8000],
            ['name' => 'Raja Chicken Supply',    'phone' => '0321-4567890', 'address' => 'Gujranwala',             'credit_days' => 5,  'balance' => 0],
        ])->map(fn($s) => Supplier::create($s + ['is_active' => true]));

        // Customers
        $customers = collect([
            ['name' => 'Hotel Al-Jannat',        'phone' => '0311-1112222', 'type' => 'wholesale', 'credit_limit' => 50000, 'credit_days' => 15],
            ['name' => 'Karahi Palace',          'phone' => '0322-3334444', 'type' => 'wholesale', 'credit_limit' => 30000, 'credit_days' => 10],
            ['name' => 'Biryani Corner',         'phone' => '0344-5556666', 'type' => 'retail',    'credit_limit' => 10000, 'credit_days' => 7],
            ['name' => 'Restaurant Star',        'phone' => '0355-7778888', 'type' => 'wholesale', 'credit_limit' => 40000, 'credit_days' => 15],
        ])->map(fn($c) => Customer::create($c + ['current_balance' => 0, 'is_active' => true, 'is_blacklisted' => false]));

        // Daily Rates (last 7 days)
        foreach (range(6, 0) as $daysAgo) {
            DailyRate::create([
                'date'                => now()->subDays($daysAgo)->toDateString(),
                'live_rate_per_kg'    => rand(280, 320),
                'retail_rate_per_kg'  => rand(370, 420),
                'supply_rate_per_kg'  => rand(340, 380),
                'notes'               => 'Demo rate',
            ]);
        }

        // Purchase Orders (last 10 days)
        foreach (range(9, 0) as $daysAgo) {
            $liveKg   = rand(80, 200);
            $rate     = rand(285, 315);
            $total    = $liveKg * $rate;
            $paid     = $total * (rand(50, 100) / 100);
            PurchaseOrder::create([
                'supplier_id'        => $suppliers->random()->id,
                'date'               => now()->subDays($daysAgo)->toDateString(),
                'invoice_number'     => 'PO-DEMO-' . str_pad($daysAgo + 1, 3, '0', STR_PAD_LEFT),
                'live_weight_kg'     => $liveKg,
                'dead_on_arrival_kg' => rand(1, 5),
                'rate_per_kg_live'   => $rate,
                'total_amount'       => $total,
                'amount_paid'        => round($paid),
                'amount_due'         => round($total - $paid),
                'due_date'           => now()->subDays($daysAgo)->addDays(7)->toDateString(),
                'payment_status'     => $paid >= $total ? 'paid' : 'partial',
            ]);
        }

        // Supply Orders
        foreach (range(8, 0) as $daysAgo) {
            $kg    = rand(20, 60);
            $rate  = rand(350, 400);
            $total = $kg * $rate;
            $paid  = $daysAgo > 2 ? $total : $total * 0.5;
            SupplyOrder::create([
                'customer_id'       => $customers->random()->id,
                'date'              => now()->subDays($daysAgo)->toDateString(),
                'delivery_date'     => now()->subDays($daysAgo)->addDay()->toDateString(),
                'invoice_number'    => 'SO-DEMO-' . str_pad($daysAgo + 1, 3, '0', STR_PAD_LEFT),
                'dressed_weight_kg' => $kg,
                'rate_per_kg'       => $rate,
                'total_amount'      => $total,
                'amount_paid'       => round($paid),
                'amount_due'        => round($total - $paid),
                'due_date'          => now()->subDays($daysAgo)->addDays(10)->toDateString(),
                'payment_status'    => $paid >= $total ? 'paid' : 'partial',
                'is_delivered'      => $daysAgo > 0,
            ]);
        }

        static::seedCommon();
    }

    // ─────────────────────────────────────────────────────────────
    // HARDWARE SHOP 🔧
    // ─────────────────────────────────────────────────────────────
    private static function seedHardware(): void
    {
        $suppliers = collect([
            ['name' => 'Ali Hardware Wholesale',   'phone' => '0300-1111222', 'address' => 'Anarkali, Lahore',     'credit_days' => 30, 'balance' => 25000],
            ['name' => 'Shah Steel & Iron',        'phone' => '0333-2223333', 'address' => 'Shahdara, Lahore',     'credit_days' => 15, 'balance' => 12000],
            ['name' => 'Pak Plumbing Supplies',    'phone' => '0321-3334444', 'address' => 'Badami Bagh, Lahore',  'credit_days' => 20, 'balance' => 0],
        ])->map(fn($s) => Supplier::create($s + ['is_active' => true]));

        $products = [
            ['name' => 'Cement Screw 2 inch',   'sku' => 'HW-001', 'category' => 'Fasteners',  'unit' => 'Dozen',  'cost_price' => 25,   'sale_price' => 40,   'stock_qty' => 500, 'low_stock_alert' => 50],
            ['name' => 'GI Pipe 1/2 inch',      'sku' => 'HW-002', 'category' => 'Pipes',      'unit' => 'Meter',  'cost_price' => 180,  'sale_price' => 250,  'stock_qty' => 120, 'low_stock_alert' => 20],
            ['name' => 'Wall Putty 20kg',        'sku' => 'HW-003', 'category' => 'Paints',     'unit' => 'Bag',    'cost_price' => 750,  'sale_price' => 950,  'stock_qty' => 40,  'low_stock_alert' => 10],
            ['name' => 'Circuit Breaker 30A',    'sku' => 'HW-004', 'category' => 'Electrical', 'unit' => 'Pcs',   'cost_price' => 450,  'sale_price' => 650,  'stock_qty' => 25,  'low_stock_alert' => 5],
            ['name' => 'PVC Elbow 3/4',          'sku' => 'HW-005', 'category' => 'Pipes',      'unit' => 'Pcs',   'cost_price' => 15,   'sale_price' => 25,   'stock_qty' => 300, 'low_stock_alert' => 50],
            ['name' => 'Hammer 300g',            'sku' => 'HW-006', 'category' => 'Tools',      'unit' => 'Pcs',   'cost_price' => 280,  'sale_price' => 420,  'stock_qty' => 15,  'low_stock_alert' => 3],
            ['name' => 'Wire 1mm (100m roll)',   'sku' => 'HW-007', 'category' => 'Electrical', 'unit' => 'Roll',  'cost_price' => 1800, 'sale_price' => 2400, 'stock_qty' => 20,  'low_stock_alert' => 5],
            ['name' => 'Drill Bit Set',          'sku' => 'HW-008', 'category' => 'Tools',      'unit' => 'Set',   'cost_price' => 350,  'sale_price' => 550,  'stock_qty' => 12,  'low_stock_alert' => 3],
        ];

        $productModels = collect($products)->map(fn($p) => Product::create($p + ['is_active' => true, 'description' => '']));

        static::seedProductPurchases($suppliers, $productModels);
        static::seedPosSales($productModels);
        static::seedCommon();
    }

    // ─────────────────────────────────────────────────────────────
    // MOBILE SHOP 📱
    // ─────────────────────────────────────────────────────────────
    private static function seedMobile(): void
    {
        $suppliers = collect([
            ['name' => 'Hafeez Mobile Wholesale',  'phone' => '0300-5556666', 'address' => 'Hall Road, Lahore',    'credit_days' => 15, 'balance' => 50000],
            ['name' => 'Galaxy Distributors',      'phone' => '0333-6667777', 'address' => 'Abid Market, Lahore',  'credit_days' => 7,  'balance' => 20000],
            ['name' => 'Tech Import Co.',          'phone' => '0321-7778888', 'address' => 'Karachi',              'credit_days' => 30, 'balance' => 0],
        ])->map(fn($s) => Supplier::create($s + ['is_active' => true]));

        $products = [
            ['name' => 'Samsung A15 (6+128GB)',    'sku' => 'MOB-001', 'category' => 'Smartphones',   'unit' => 'Pcs', 'cost_price' => 42000, 'sale_price' => 48000, 'stock_qty' => 8,  'low_stock_alert' => 2],
            ['name' => 'Infinix Hot 40',           'sku' => 'MOB-002', 'category' => 'Smartphones',   'unit' => 'Pcs', 'cost_price' => 28000, 'sale_price' => 33000, 'stock_qty' => 12, 'low_stock_alert' => 3],
            ['name' => 'iPhone 13 (128GB)',        'sku' => 'MOB-003', 'category' => 'Smartphones',   'unit' => 'Pcs', 'cost_price' => 155000,'sale_price' => 170000,'stock_qty' => 3,  'low_stock_alert' => 1],
            ['name' => 'Type-C Cable 1m',          'sku' => 'ACC-001', 'category' => 'Accessories',   'unit' => 'Pcs', 'cost_price' => 120,   'sale_price' => 250,   'stock_qty' => 100,'low_stock_alert' => 20],
            ['name' => 'Tempered Glass (Universal)','sku' => 'ACC-002','category' => 'Accessories',   'unit' => 'Pcs', 'cost_price' => 80,    'sale_price' => 200,   'stock_qty' => 200,'low_stock_alert' => 30],
            ['name' => '20W Fast Charger',         'sku' => 'ACC-003', 'category' => 'Accessories',   'unit' => 'Pcs', 'cost_price' => 650,   'sale_price' => 1100,  'stock_qty' => 30, 'low_stock_alert' => 5],
            ['name' => 'Power Bank 20000mAh',      'sku' => 'ACC-004', 'category' => 'Accessories',   'unit' => 'Pcs', 'cost_price' => 2800,  'sale_price' => 4200,  'stock_qty' => 10, 'low_stock_alert' => 2],
            ['name' => 'Silicon Cover (Samsung A15)','sku' => 'COV-001','category' => 'Covers',       'unit' => 'Pcs', 'cost_price' => 150,   'sale_price' => 350,   'stock_qty' => 50, 'low_stock_alert' => 10],
        ];

        $productModels = collect($products)->map(fn($p) => Product::create($p + ['is_active' => true, 'description' => '']));

        static::seedProductPurchases($suppliers, $productModels);
        static::seedPosSales($productModels);
        static::seedCommon();
    }

    // ─────────────────────────────────────────────────────────────
    // BIKE SHOP 🏍️
    // ─────────────────────────────────────────────────────────────
    private static function seedBike(): void
    {
        $suppliers = collect([
            ['name' => 'Honda Parts Distributor',  'phone' => '0300-8889999', 'address' => 'McLeod Road, Lahore',  'credit_days' => 30, 'balance' => 35000],
            ['name' => 'Yamaha Spare Parts',       'phone' => '0333-9990000', 'address' => 'Brandreth Road',       'credit_days' => 15, 'balance' => 18000],
            ['name' => 'China Parts Import',       'phone' => '0321-0001111', 'address' => 'Bilal Gunj, Lahore',   'credit_days' => 7,  'balance' => 0],
        ])->map(fn($s) => Supplier::create($s + ['is_active' => true]));

        $products = [
            ['name' => 'Honda 125 Chain',         'sku' => 'BK-001', 'category' => 'Drive',     'unit' => 'Pcs', 'cost_price' => 850,  'sale_price' => 1200,  'stock_qty' => 20, 'low_stock_alert' => 5],
            ['name' => 'Motorcycle Tyre 2.75-17', 'sku' => 'BK-002', 'category' => 'Tyres',     'unit' => 'Pcs', 'cost_price' => 2200, 'sale_price' => 3000,  'stock_qty' => 15, 'low_stock_alert' => 4],
            ['name' => 'Engine Oil 1L (20W50)',   'sku' => 'BK-003', 'category' => 'Lubricants', 'unit' => 'Ltr', 'cost_price' => 900,  'sale_price' => 1300,  'stock_qty' => 40, 'low_stock_alert' => 10],
            ['name' => 'Brake Shoe (Front)',       'sku' => 'BK-004', 'category' => 'Brakes',    'unit' => 'Set', 'cost_price' => 280,  'sale_price' => 450,   'stock_qty' => 25, 'low_stock_alert' => 5],
            ['name' => 'Spark Plug (NGK)',         'sku' => 'BK-005', 'category' => 'Engine',    'unit' => 'Pcs', 'cost_price' => 180,  'sale_price' => 320,   'stock_qty' => 50, 'low_stock_alert' => 10],
            ['name' => 'Headlight Bulb 12V',       'sku' => 'BK-006', 'category' => 'Electrical','unit' => 'Pcs', 'cost_price' => 120,  'sale_price' => 220,   'stock_qty' => 30, 'low_stock_alert' => 5],
            ['name' => 'Air Filter Honda 125',     'sku' => 'BK-007', 'category' => 'Engine',    'unit' => 'Pcs', 'cost_price' => 350,  'sale_price' => 550,   'stock_qty' => 18, 'low_stock_alert' => 4],
            ['name' => 'Clutch Plate Set',         'sku' => 'BK-008', 'category' => 'Drive',     'unit' => 'Set', 'cost_price' => 650,  'sale_price' => 950,   'stock_qty' => 10, 'low_stock_alert' => 3],
        ];

        $productModels = collect($products)->map(fn($p) => Product::create($p + ['is_active' => true, 'description' => '']));

        static::seedProductPurchases($suppliers, $productModels);
        static::seedPosSales($productModels);
        static::seedCommon();
    }

    // ─────────────────────────────────────────────────────────────
    // GENERAL SHOP 🏪
    // ─────────────────────────────────────────────────────────────
    private static function seedGeneral(): void
    {
        $suppliers = collect([
            ['name' => 'Unilever Distributor',    'phone' => '0300-2223333', 'address' => 'Johar Town, Lahore',   'credit_days' => 21, 'balance' => 45000],
            ['name' => 'National Foods Agent',    'phone' => '0333-3334444', 'address' => 'Gulberg, Lahore',      'credit_days' => 15, 'balance' => 22000],
            ['name' => 'Local Wholesale Market',  'phone' => '0321-4445555', 'address' => 'Bhati Gate, Lahore',   'credit_days' => 7,  'balance' => 0],
        ])->map(fn($s) => Supplier::create($s + ['is_active' => true]));

        $products = [
            ['name' => 'Surf Excel 1kg',         'sku' => 'GEN-001', 'category' => 'Detergents',  'unit' => 'Pcs', 'cost_price' => 380, 'sale_price' => 440,  'stock_qty' => 60,  'low_stock_alert' => 10],
            ['name' => 'Tapal Danedar 900g',     'sku' => 'GEN-002', 'category' => 'Beverages',   'unit' => 'Pcs', 'cost_price' => 920, 'sale_price' => 1100, 'stock_qty' => 30,  'low_stock_alert' => 8],
            ['name' => 'Basmati Rice 5kg',       'sku' => 'GEN-003', 'category' => 'Grocery',     'unit' => 'Pcs', 'cost_price' => 950, 'sale_price' => 1200, 'stock_qty' => 50,  'low_stock_alert' => 10],
            ['name' => 'Nestle MilkPak 1L',      'sku' => 'GEN-004', 'category' => 'Dairy',       'unit' => 'Pcs', 'cost_price' => 185, 'sale_price' => 220,  'stock_qty' => 80,  'low_stock_alert' => 20],
            ['name' => 'Colgate 150ml',          'sku' => 'GEN-005', 'category' => 'Personal Care','unit' => 'Pcs', 'cost_price' => 165, 'sale_price' => 215,  'stock_qty' => 40,  'low_stock_alert' => 10],
            ['name' => 'Lays Chips (Large)',      'sku' => 'GEN-006', 'category' => 'Snacks',      'unit' => 'Pcs', 'cost_price' => 60,  'sale_price' => 80,   'stock_qty' => 120, 'low_stock_alert' => 20],
            ['name' => 'Cooking Oil 5L',         'sku' => 'GEN-007', 'category' => 'Grocery',     'unit' => 'Pcs', 'cost_price' => 2600,'sale_price' => 3000, 'stock_qty' => 25,  'low_stock_alert' => 5],
            ['name' => 'Shampoo Head & Shoulders','sku' => 'GEN-008', 'category' => 'Personal Care','unit' => 'Pcs', 'cost_price' => 520, 'sale_price' => 680,  'stock_qty' => 20,  'low_stock_alert' => 5],
        ];

        $productModels = collect($products)->map(fn($p) => Product::create($p + ['is_active' => true, 'description' => '']));

        static::seedProductPurchases($suppliers, $productModels);
        static::seedPosSales($productModels);
        static::seedCommon();
    }

    // ─────────────────────────────────────────────────────────────
    // SHARED HELPERS
    // ─────────────────────────────────────────────────────────────
    private static function seedCommon(): void
    {
        // Staff
        Staff::create(['name' => 'Ahmed Ali',    'phone' => '0311-1234567', 'role' => 'Manager',  'salary' => 35000, 'joining_date' => now()->subMonths(6)->toDateString(), 'is_active' => true]);
        Staff::create(['name' => 'Bilal Hassan', 'phone' => '0322-2345678', 'role' => 'Salesman', 'salary' => 22000, 'joining_date' => now()->subMonths(3)->toDateString(), 'is_active' => true]);
        Staff::create(['name' => 'Usman Khan',   'phone' => '0333-3456789', 'role' => 'Helper',   'salary' => 16000, 'joining_date' => now()->subMonths(1)->toDateString(), 'is_active' => true]);

        // Expenses (last 10 days)
        $expenseData = [
            ['category' => 'Rent',        'description' => 'Monthly shop rent',     'amount' => 18000, 'paid_to' => 'Landlord Malik Sahab'],
            ['category' => 'Electricity', 'description' => 'LESCO electricity bill','amount' => 3500,  'paid_to' => 'LESCO Office'],
            ['category' => 'Transport',   'description' => 'Delivery rickshaw fare', 'amount' => 800,   'paid_to' => 'Driver Rashid'],
            ['category' => 'Salary',      'description' => 'Ahmed Ali monthly salary','amount' => 35000,'paid_to' => 'Ahmed Ali'],
            ['category' => 'Salary',      'description' => 'Bilal Hassan salary',   'amount' => 22000, 'paid_to' => 'Bilal Hassan'],
            ['category' => 'Repair',      'description' => 'AC service & gas fill', 'amount' => 2500,  'paid_to' => 'Technician Asif'],
            ['category' => 'Miscellaneous','description' => 'Stationery & bags',    'amount' => 650,   'paid_to' => 'Raja Stationery'],
        ];

        foreach ($expenseData as $i => $exp) {
            Expense::create($exp + [
                'date'           => now()->subDays(rand(0, 10))->toDateString(),
                'receipt_number' => 'EXP-DEMO-' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
            ]);
        }

        // Udhar Customers + Credit Sales
        $udharCustomers = collect([
            ['name' => 'Tariq Mehmood',  'phone' => '0312-1111222', 'whatsapp_number' => '0312-1111222', 'address' => 'Mohalla Islam Pura'],
            ['name' => 'Imran Butt',     'phone' => '0323-2223333', 'whatsapp_number' => '0323-2223333', 'address' => 'Street 5, Gulshan Colony'],
            ['name' => 'Nasir Ahmed',    'phone' => '0334-3334444', 'whatsapp_number' => null,           'address' => 'Near Masjid, Block B'],
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
            $selectedProducts = $products->random(rand(2, 4));
            $total = 0;
            $items = [];

            foreach ($selectedProducts as $product) {
                $qty   = rand(5, 20);
                $price = $product->cost_price;
                $items[] = ['product' => $product, 'qty' => $qty, 'price' => $price, 'total' => $qty * $price];
                $total += $qty * $price;
            }

            $paid = $i <= 3 ? $total : $total * 0.6;

            $purchase = ProductPurchase::create([
                'supplier_id'    => $suppliers->random()->id,
                'date'           => now()->subDays(($i - 1) * 3)->toDateString(),
                'invoice_number' => 'PP-DEMO-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'total_amount'   => $total,
                'amount_paid'    => round($paid),
                'amount_due'     => round($total - $paid),
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
        for ($i = 1; $i <= 8; $i++) {
            $selectedProducts = $products->random(rand(1, 3));
            $subtotal = 0;
            $items    = [];

            foreach ($selectedProducts as $product) {
                $qty   = rand(1, 3);
                $price = $product->sale_price;
                $items[] = ['product' => $product, 'qty' => $qty, 'price' => $price, 'total' => $qty * $price];
                $subtotal += $qty * $price;
            }

            $discount  = rand(0, 1) ? rand(50, 200) : 0;
            $total     = $subtotal - $discount;
            $methods   = ['cash', 'jazzcash', 'easypaisa', 'bank', 'credit'];

            $sale = PosSale::create([
                'sale_number'    => 'POS-DEMO-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'date'           => now()->subDays(rand(0, 10))->toDateString(),
                'customer_name'  => collect(['Walk-in Customer', 'Tariq sb', 'Asif bhai', 'Unknown'])->random(),
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
}
