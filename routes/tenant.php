<?php

declare(strict_types=1);

use App\Http\Controllers\CreditSaleController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DailyRateController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DayEndController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\SupplyController;
use App\Http\Controllers\UdharCustomerController;
use App\Http\Controllers\GoogleDriveController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductPurchaseController;
use App\Http\Controllers\DummyDataController;
use App\Http\Controllers\PurchaseReturnController;
use App\Http\Controllers\SaleReturnController;
use App\Http\Controllers\LedgerController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\DaySummaryController;
use App\Http\Controllers\TenantImpersonateController;
use App\Http\Controllers\SetupController;
use App\Http\Controllers\TenantUserController;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {

    Route::get('/', function () {
        return redirect()->route('dashboard');
    });

    // Impersonation — no auth required (token IS the auth)
    Route::get('/impersonate/{token}', [TenantImpersonateController::class, 'start'])->name('impersonate.start');
    Route::post('/impersonate/stop', [TenantImpersonateController::class, 'stop'])->name('impersonate.stop');

    // Google OAuth callback
    Route::get('/google/callback', [GoogleDriveController::class, 'callback'])->name('google.callback');

    // Onboarding setup (auth required but no subscription check)
    Route::middleware(['auth'])->group(function () {
        Route::get('/setup', [SetupController::class, 'index'])->name('setup.index');
        Route::post('/setup', [SetupController::class, 'store'])->name('setup.store');
    });

    Route::middleware(['auth', 'tenant.subscription', 'tenant.onboarding'])->group(function () {
        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Profile
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

        // Reports
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

        // Settings
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');
        Route::get('/google/connect', [GoogleDriveController::class, 'connect'])->name('google.connect');
        Route::post('/google/disconnect', [GoogleDriveController::class, 'disconnect'])->name('google.disconnect');
        Route::post('/backup/google', [GoogleDriveController::class, 'backup'])->name('backup.google');
        Route::get('/backup/list', [GoogleDriveController::class, 'listBackups'])->name('settings.backups');
        Route::post('/backup/restore/{fileId}', [GoogleDriveController::class, 'restore'])->name('backup.restore');
        Route::delete('/backup/delete/{fileId}', [GoogleDriveController::class, 'deleteBackup'])->name('backup.delete');

        // Suppliers
        Route::resource('suppliers', SupplierController::class);
        Route::post('/suppliers/{supplier}/toggle-status', [SupplierController::class, 'toggleStatus'])->name('suppliers.toggle-status');

        // Customers
        Route::resource('customers', CustomerController::class);
        Route::post('/customers/{customer}/toggle-status', [CustomerController::class, 'toggleStatus'])->name('customers.toggle-status');
        Route::post('/customers/{customer}/toggle-blacklist', [CustomerController::class, 'toggleBlacklist'])->name('customers.toggle-blacklist');

        // Daily Rates
        Route::get('/daily-rates', [DailyRateController::class, 'index'])->name('daily-rates.index');
        Route::post('/daily-rates', [DailyRateController::class, 'store'])->name('daily-rates.store');
        Route::get('/daily-rates/today', [DailyRateController::class, 'today'])->name('daily-rates.today');
        Route::delete('/daily-rates/{dailyRate}', [DailyRateController::class, 'destroy'])->name('daily-rates.destroy');

        // Purchases
        Route::resource('purchases', PurchaseController::class);
        Route::post('/purchases/{purchase}/payment', [PurchaseController::class, 'storePayment'])->name('purchases.payment');
        Route::delete('/purchases/{purchase}/payment/{payment}', [PurchaseController::class, 'destroyPayment'])->name('purchases.payment.destroy');

        // Supply Orders
        Route::get('/supply', [SupplyController::class, 'index'])->name('supply.index');
        Route::get('/supply/create', [SupplyController::class, 'create'])->name('supply.create');
        Route::post('/supply', [SupplyController::class, 'store'])->name('supply.store');
        Route::get('/supply/{supply}', [SupplyController::class, 'show'])->name('supply.show');
        Route::get('/supply/{supply}/invoice', [SupplyController::class, 'invoice'])->name('supply.invoice');
        Route::get('/supply/{supply}/edit', [SupplyController::class, 'edit'])->name('supply.edit');
        Route::put('/supply/{supply}', [SupplyController::class, 'update'])->name('supply.update');
        Route::delete('/supply/{supply}', [SupplyController::class, 'destroy'])->name('supply.destroy');
        Route::post('/supply/{supply}/payment', [SupplyController::class, 'storePayment'])->name('supply.payment');
        Route::patch('/supply/{supply}/deliver', [SupplyController::class, 'markDelivered'])->name('supply.deliver');

        // Day End
        // General shop day-end summary
        Route::get('/day-summary', [DaySummaryController::class, 'index'])->name('day-summary.index');
        Route::get('/day-summary/create', [DaySummaryController::class, 'create'])->name('day-summary.create');
        Route::post('/day-summary', [DaySummaryController::class, 'store'])->name('day-summary.store');
        Route::get('/day-summary/{daySummary}', [DaySummaryController::class, 'show'])->name('day-summary.show');
        Route::post('/day-summary/{daySummary}/close', [DaySummaryController::class, 'close'])->name('day-summary.close');
        Route::delete('/day-summary/{daySummary}', [DaySummaryController::class, 'destroy'])->name('day-summary.destroy');

        Route::get('/day-end', [DayEndController::class, 'index'])->name('day-end.index');
        Route::get('/day-end/create', [DayEndController::class, 'create'])->name('day-end.create');
        Route::post('/day-end', [DayEndController::class, 'store'])->name('day-end.store');
        Route::get('/day-end/{dayEnd}', [DayEndController::class, 'show'])->name('day-end.show');
        Route::get('/day-end/{dayEnd}/edit', [DayEndController::class, 'edit'])->name('day-end.edit');
        Route::put('/day-end/{dayEnd}', [DayEndController::class, 'update'])->name('day-end.update');
        Route::delete('/day-end/{dayEnd}', [DayEndController::class, 'destroy'])->name('day-end.destroy');
        Route::post('/day-end/{dayEnd}/close', [DayEndController::class, 'close'])->name('day-end.close');

        // Expenses
        Route::resource('expenses', ExpenseController::class)->except(['show']);

        // Staff
        Route::resource('staff', StaffController::class);
        Route::post('/staff/{staff}/toggle-status', [StaffController::class, 'toggleStatus'])->name('staff.toggle-status');
        Route::post('/staff/{staff}/salary', [StaffController::class, 'storeSalary'])->name('staff.salary');

        // Udhar Book
        Route::get('/udhar/report', [CreditSaleController::class, 'report'])->name('udhar.report');
        Route::get('/udhar', [CreditSaleController::class, 'index'])->name('udhar.index');
        Route::get('/udhar/create', [CreditSaleController::class, 'create'])->name('udhar.create');
        Route::post('/udhar', [CreditSaleController::class, 'store'])->name('udhar.store');
        Route::get('/udhar/{creditSale}', [CreditSaleController::class, 'show'])->name('udhar.show');
        Route::get('/udhar/{creditSale}/edit', [CreditSaleController::class, 'edit'])->name('udhar.edit');
        Route::put('/udhar/{creditSale}', [CreditSaleController::class, 'update'])->name('udhar.update');
        Route::post('/udhar/{creditSale}/payment', [CreditSaleController::class, 'storePayment'])->name('udhar.payment');
        Route::delete('/udhar/{creditSale}/payment/{payment}', [CreditSaleController::class, 'destroyPayment'])->name('udhar.payment.destroy');
        Route::delete('/udhar/{creditSale}', [CreditSaleController::class, 'destroy'])->name('udhar.destroy');

        // Udhar Customers
        Route::resource('udhar-customers', UdharCustomerController::class);

        // Products & Inventory
        Route::resource('products', ProductController::class);
        Route::post('/products/{product}/stock', [ProductController::class, 'adjustStock'])->name('products.stock');

        // Product Purchases (bike/hardware/mobile shops)
        Route::get('/product-purchases', [ProductPurchaseController::class, 'index'])->name('product-purchases.index');
        Route::get('/product-purchases/create', [ProductPurchaseController::class, 'create'])->name('product-purchases.create');
        Route::post('/product-purchases', [ProductPurchaseController::class, 'store'])->name('product-purchases.store');
        Route::get('/product-purchases/{productPurchase}', [ProductPurchaseController::class, 'show'])->name('product-purchases.show');
        Route::delete('/product-purchases/{productPurchase}', [ProductPurchaseController::class, 'destroy'])->name('product-purchases.destroy');
        Route::post('/product-purchases/{productPurchase}/payment', [ProductPurchaseController::class, 'storePayment'])->name('product-purchases.payment');

        // Quotations
        Route::get('/quotations', [QuotationController::class, 'index'])->name('quotations.index');
        Route::get('/quotations/create', [QuotationController::class, 'create'])->name('quotations.create');
        Route::post('/quotations', [QuotationController::class, 'store'])->name('quotations.store');
        Route::get('/quotations/{quotation}', [QuotationController::class, 'show'])->name('quotations.show');
        Route::patch('/quotations/{quotation}/status', [QuotationController::class, 'updateStatus'])->name('quotations.status');
        Route::delete('/quotations/{quotation}', [QuotationController::class, 'destroy'])->name('quotations.destroy');

        // Ledger
        Route::get('/ledger/supplier/{supplier}', [LedgerController::class, 'supplier'])->name('ledger.supplier');
        Route::get('/ledger/customer/{customer}', [LedgerController::class, 'customer'])->name('ledger.customer');
        Route::get('/ledger/udhar-customer/{udharCustomer}', [LedgerController::class, 'udharCustomer'])->name('ledger.udhar-customer');

        // Purchase Returns
        Route::get('/purchase-returns', [PurchaseReturnController::class, 'index'])->name('purchase-returns.index');
        Route::get('/purchase-returns/{purchaseReturn}', [PurchaseReturnController::class, 'show'])->name('purchase-returns.show');
        Route::get('/product-purchases/{purchase}/return', [PurchaseReturnController::class, 'create'])->name('purchase-returns.create');
        Route::post('/product-purchases/{purchase}/return', [PurchaseReturnController::class, 'store'])->name('purchase-returns.store');

        // POS
        Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
        Route::get('/pos/sale', [PosController::class, 'create'])->name('pos.create');
        Route::post('/pos/sale', [PosController::class, 'store'])->name('pos.store');
        Route::get('/pos/sale/{posSale}/receipt', [PosController::class, 'receipt'])->name('pos.receipt');
        Route::get('/pos/sale/{posSale}', [PosController::class, 'show'])->name('pos.show');

        // Sale Returns
        Route::get('/sale-returns', [SaleReturnController::class, 'index'])->name('sale-returns.index');
        Route::get('/sale-returns/{saleReturn}', [SaleReturnController::class, 'show'])->name('sale-returns.show');
        Route::get('/pos/sale/{sale}/return', [SaleReturnController::class, 'create'])->name('sale-returns.create');
        Route::post('/pos/sale/{sale}/return', [SaleReturnController::class, 'store'])->name('sale-returns.store');

        // Demo Data
        Route::post('/settings/seed-demo', [DummyDataController::class, 'seed'])->name('demo.seed');
        Route::post('/settings/delete-demo', [DummyDataController::class, 'delete'])->name('demo.delete');

        // Team / Users
        Route::get('/users', [TenantUserController::class, 'index'])->name('tenant.users.index');
        Route::get('/users/create', [TenantUserController::class, 'create'])->name('tenant.users.create');
        Route::post('/users', [TenantUserController::class, 'store'])->name('tenant.users.store');
        Route::delete('/users/{user}', [TenantUserController::class, 'destroy'])->name('tenant.users.destroy');
        Route::post('/users/{user}/reset-password', [TenantUserController::class, 'resetPassword'])->name('tenant.users.reset-password');
    });

    require __DIR__.'/auth.php';
});
