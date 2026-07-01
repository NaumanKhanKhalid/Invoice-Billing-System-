# ShopSaas — Invoice & Billing System
## Complete A-to-Z Project Documentation

> Multi-tenant SaaS billing platform for Pakistani small businesses (Karachi-focused).
> Ye document har module, har feature, har shop type aur har logic ko cover karta hai — taake missing cheezein identify ho sakein aur project ko behtar banaya ja sake.

---

## 1. Tech Stack & Architecture

| Layer | Technology |
|---|---|
| Backend | Laravel 11.54, PHP 8.2+ |
| Multi-tenancy | stancl/tenancy v3.8 (subdomain-based, DB-per-tenant) |
| Database | MySQL — central DB `invoice_billing`, tenant DBs `tenant-{name}` |
| Frontend | Blade templates + Tailwind CSS + Alpine.js |
| Icons | Lucide (CDN, `lucide.createIcons({icons: lucide.icons})`) |
| Charts | Chart.js 4 (CDN) |
| Build | Vite (assets manifest-based load hoti hain, `@vite` nahi — cross-subdomain issue ki wajah se) |
| PDF | PdfService (invoices/receipts) |

### Multi-tenancy ka flow
- **Central domain** (e.g. `shopsaas.test`) → landing page + super-admin panel.
- **Tenant subdomain** (e.g. `ali-hardware.shopsaas.test`) → us shop ka apna system, apna database.
- Naya tenant banate hi uska database create hota hai aur `database/migrations/tenant/` ki saari migrations us DB par chal jaati hain.
- `tenant()` helper se current shop ki info milti hai (shop_name, shop_type, plan, etc.).

### Important architectural decisions (ye yaad rakhna zaroori hai)
1. **Assets loading**: `@vite()` absolute URL banata tha jo subdomain par fail hota tha. Fix: `public/build/manifest.json` parse karke root-relative `/build/...` path use hota hai (har layout mein).
2. **Impersonation tokens**: Cache use nahi ho sakta (tenancy cache ko tag karti hai, file driver tags support nahi karta). Isliye `impersonation_tokens` table **central DB** mein hai; `ImpersonationToken` model ka `getConnectionName()` hamesha central connection return karta hai.
3. **Migrations 2 jagah hain**: `database/migrations/` (central: tenants, domains, subscription_payments, impersonation_tokens) aur `database/migrations/tenant/` (har shop ka data).

---

## 2. Shop Types (7 types)

| Type | Emoji | Kaun use karega | Primary modules |
|---|---|---|---|
| `chicken` | 🍗 | Chicken/poultry shop | Daily Rates, Purchases, Supply Orders, Hotels/Companies, Day End |
| `bike` | 🏍️ | Bike parts shop | Products, POS, Open Tabs, Quotations |
| `hardware` | 🔧 | Hardware store | Products, POS, Open Tabs (mechanic tabs), Quotations |
| `mobile` | 📱 | Mobile shop | Products, POS, Quotations |
| `general` | 🏪 | General store | Products, POS |
| `medical` | 💊 | Medical store / pharmacy | Products, POS (dawaiyon ka data seeded) |
| `coaching` | 🎓 | Coaching center | Courses, Batches, Students, Fee Collection |

### Layout mein type detection (`layouts/app.blade.php`)
```php
$isChicken  = $shopType === 'chicken';
$isProduct  = in_array($shopType, ['hardware','mobile','bike','general','medical']);
$isCoaching = $shopType === 'coaching';
```
Sidebar, FAB buttons aur dashboard in flags ke hisaab se change hote hain.

---

## 3. Central Admin Panel (Super Admin)

**Routes**: `/admin/*` (middleware: `auth` + `super.admin`)

### 3.1 Tenant Management (`Admin/TenantController`)
- **Create tenant**: shop_name, shop_type (7 types), owner name/email/phone, subdomain, plan.
- Tenant create hote hi: DB banta hai, migrations chalti hain, owner ka user account tenant DB mein banta hai.
- **Edit / deactivate** tenant. `is_active=false` → shop band.
- **Plans**: `basic`, `pro`, `business` — 1 se 12 months. `plan_expires_at` par expiry.
- **Renew plan** (`renewPlan`) — `subscription_payments` table mein payment record hota hai.
- **Payments history** per tenant.
- Tenants list mein har type ka color badge (chicken=green, bike=blue, hardware=orange, mobile=purple, general=gray, medical=teal, coaching=indigo).

### 3.2 Impersonation ("Login as shop owner")
Flow:
1. Admin tenant row par "Impersonate" click karta hai → `ImpersonateController::start`.
2. Random token generate → central DB `impersonation_tokens` mein save (expiry ke sath).
3. Redirect → `https://{subdomain}/impersonate/{token}`.
4. Tenant side `TenantImpersonateController` token verify karta hai (central DB se), delete karta hai (one-time use), aur owner ko login kar deta hai.

### 3.3 Plans & Revenue page
`/admin/plans` — plans ki overview / revenue view.

---

## 4. Tenant-side Auth & Setup

- Har tenant ka apna `users` table (tenant DB mein).
- **Roles**: users table mein `role` column (owner/staff).
- **Team Members** (`TenantUserController`): owner apne staff ke liye login accounts bana sakta hai.
- **SetupController**: first-time setup wizard (shop settings).
- **ProfileController**: user apna profile/password change kar sakta hai.
- Guest layout mein shop ka emoji + naam login page par dikhta hai.

---

## 5. CHICKEN SHOP Modules (🍗)

### 5.1 Daily Rates (`DailyRateController`)
- Roz ka chicken rate (purchase/sale rate per kg) set hota hai.
- Ye rates supply orders aur billing mein use hote hain.

### 5.2 Purchases (`PurchaseController` → `purchase_orders`)
- Supplier se murghi ki khareedari: qty (kg), rate, total.
- Partial payments (`purchase_payments`) — proof photo attach ho sakta hai.
- Supplier ka balance (udhaar) track hota hai.

### 5.3 Supply Orders (`SupplyController` → `supply_orders`)
- Hotels/companies ko chicken supply karna.
- Delivery date + delivery status.
- Payments (`supply_payments`) — proof photo support.
- Customer (hotel) ka receivable balance track hota hai.

### 5.4 Hotels / Companies (`CustomerController` → `customers`)
- B2B customers jinko regular supply hoti hai.
- Har customer ka ledger available hai (Section 8.6).

### 5.5 Day End / Daily Records (`DayEndController` → `daily_records`)
- Din ke akhir mein poore din ka hisaab: sales, purchases, expenses, profit.
- Create → Edit → **Close** (close hone ke baad record lock).
- Sidebar mein "Daily Records", FAB mein "Close Day".

---

## 6. PRODUCT SHOPS Modules (🔧📱🏍️🏪💊)

### 6.1 Products & Inventory (`ProductController` → `products`)
Fields: name, sku, **barcode**, category, description, unit, cost_price, sale_price, stock_qty, low_stock_alert, is_active.
- **Stock adjust** endpoint (`products.stock`) — manual stock correction.
- **StockMovement** model — har stock change ka record.
- Low-stock alert: `stock_qty <= low_stock_alert` → dashboard par warning count.

### 6.2 POS Counter (`PosController` → `pos_sales`, `pos_sale_items`)
- Walk-in customer ki sale — barcode/naam se product search.
- Discount, payment method, amount paid, change due.
- **Receipt** print view.
- Sales History list (`pos.index`).

### 6.3 Product Purchases (`ProductPurchaseController`)
- Supplier se stock khareedna — items ke sath, stock automatically barh jaata hai.
- Partial payments per purchase.
- Supplier balance update hota hai.

### 6.4 Purchase Returns (`PurchaseReturnController`)
- Kisi purchase ke items wapas karna → stock kam, supplier balance adjust.

### 6.5 Sale Returns (`SaleReturnController`)
- POS sale ke items wapas lena → stock wapas barh jaata hai, refund record.

### 6.6 Quotations (`QuotationController`)
- Customer ko **estimate/quote** dena (bina stock kam kiye).
- Status update (pending/accepted/rejected — `quotations.status`).
- **Use case**: bara order aane se pehle rate confirm karna. (Open Tab se different hai — quotation sirf estimate hai, tab actual running bill hai.)

### 6.7 Open Tabs (`OpenTabController`) — Mechanic/Workshop system ⭐
Hardware/bike shops ka khaas flow: mechanic aata hai, din bhar items leta rehta hai, akhir mein ek bill banta hai.
- Tab number auto: `TAB-0001`, `TAB-0002`...
- **Real-time item add/remove** — Alpine.js + fetch JSON API (page reload nahi hota).
- Product search dropdown (name/barcode/SKU) + manual item entry option.
- `recalculate()`: subtotal = items ka sum, total = subtotal − discount.
- **Close Tab**: discount + amount_paid + payment_method → receipt print.
- Status: `open` / `closed`.

### 6.8 Day Summary / Day Closing (`DaySummaryController` → `day_summaries`)
Product shops ka din band karne ka system (chicken ke DayEnd ka product version):
- Auto-fetch: us din ki POS sales, purchases, expenses.
- `expected_cash = opening_cash + pos_revenue − expenses`
- Cash counting ke sath **live reconciliation** (Alpine.js) — surplus/shortage turant dikhta hai.
- `net_profit` calculate hota hai. Close hone par lock (`is_closed`).
- Ek din ka sirf ek summary (date unique).

---

## 7. COACHING CENTER Module (🎓)

Simple rakha gaya hai (no exams/tests) taake baad mein **school system** mein reuse ho sake.

### Tables
1. `coaching_courses` — name, monthly_fee, description, is_active.
2. `coaching_batches` — course_id, name, timing, days, teacher_name, capacity, is_active.
3. `coaching_students` — batch_id, name, phone, guardian info, address, enrollment_date, **custom_fee** (nullable override), **discount_percent**, status (active/completed/dropped), notes.
4. `coaching_fee_collections` — student_id + month (**unique**, month = `YYYY-MM-01`), amount_due, discount_amount, amount_paid, balance_due, status (pending/partial/paid), payment_date, payment_method, receipt_number, notes.

### Key logic
- **`CoachingStudent::effectiveFee()`**: `base = custom_fee ?? course->monthly_fee`, phir `discount_percent` apply. (Har student ki apni fee ho sakti hai.)
- **Fee auto-generation**: Fee Collection page kholte hi (`CoachingFeeController::index`) har active student ke liye us month ka record `firstOrCreate` se ban jaata hai — koi student miss nahi hota.
- **`collect()`**: payment add → balance calculate → status update (paid/partial/pending) → receipt number `RCP-0001` format.
- **Receipt**: printable view (student, batch, course, month, amounts, status badge).

### Screens
- **Dashboard**: KPI cards (total/active students, batches, pending fees), collection progress bar, **defaulters list with WhatsApp reminder link** (Roman Urdu message pre-filled), recent payments, quick actions.
- **Courses & Batches**: ek hi page par dono, add/edit modals ke sath.
- **Students**: list (search + batch + status filters, pagination 30), enroll form, profile page (fee history + fee summary), edit.
- **Fee Collection**: month picker, summary cards (paid/partial/pending counts + amounts), inline collect form (Alpine expand), WhatsApp reminder per student.

### Coaching sidebar (dedicated)
Dashboard, Students, Fee Collection, Courses & Batches, Expenses. System dropdown mein sirf: Staff & Salaries, Team Members, Settings. (Suppliers, Udhar, POS, Reports coaching ke liye **hidden** hain.)
FAB: "Enroll Student" + "Collect Fees".
`/` dashboard coaching shop ke liye `coaching.dashboard` par redirect hota hai.

---

## 8. COMMON Modules (sab shops ke liye)

### 8.1 Udhar Book (`CreditSaleController` → `credit_sales`, `credit_payments`) — coaching ke ilawa
- Customer ko udhaar par maal dena — due_date ke sath.
- Partial payments, **proof photo** attach.
- Status: unpaid/partial/paid. Overdue count sidebar badge mein dikhta hai.
- **Udhar Report** (`udhar.report`).
- **Udhar Customers** (`UdharCustomerController`) — udhaar lene walon ki directory.

### 8.2 Expenses (`ExpenseController`)
- Roz ke kharche: description, amount, category, date, payment method.
- Day End/Day Summary aur Reports mein use hote hain.

### 8.3 Staff & Salaries (`StaffController` → `staff`, `salary_payments`)
- Staff: name, role, phone, salary, joining_date, status. Toggle active/inactive.
- Salary payments record (`staff.salary`). *(Salary module ka UI abhi basic hai — improvement candidate.)*

### 8.4 Suppliers (`SupplierController`)
- Supplier directory: phone, address, credit_days, balance (payable).

### 8.5 Reports (`ReportController`)
- `reports.index` — date-range based reports. *(Abhi basic — improvement candidate, user ne "baad mein" kaha tha.)*

### 8.6 Ledger (`LedgerController`)
- Supplier ledger, Customer ledger, Udhar-customer ledger — har party ka complete account statement.

### 8.7 Settings (`SettingController` + `SettingService` → `settings` table)
- Key-value store per tenant: company_name, phone, address, receipt footer, etc.
- `Setting::getValue()/setValue()` helpers.

### 8.8 Demo Data (`DummyDataController` + `DummyDataService`) ⭐
- Settings se ek click par shop-type ke hisaab se **realistic Karachi-based dummy data**:
  - `seedChicken`, `seedHardware`, `seedMobile`, `seedBike`, `seedGeneral`, `seedMedical` (Panadol, Brufen, Amoxil... + pharma suppliers), `seedCoaching` (4 courses, 5 batches, 15 students, 2 months ke fee records — mix paid/partial/pending).
- **Delete demo** — saari demo tables clear (coaching included). Marker setting se pata chalta hai seeded hai ya nahi.

### 8.9 Google Drive Backup (`GoogleDriveController`)
- Backup/export integration ka controller. *(Status verify karna ho to check karein — improvement candidate.)*

### 8.10 PDF / Invoice services
- `InvoiceService`, `PdfService` — PDF generation ke liye.

---

## 9. Database Tables — Quick Reference (tenant DB)

| Table | Module |
|---|---|
| users, settings | Core |
| daily_rates, purchase_orders, purchase_payments, supply_orders, supply_payments, daily_records, customers | Chicken |
| products, stock_movements, product_purchases(+items), pos_sales(+items), pos_sale_returns(+items), purchase_returns(+items), quotations(+items), open_tabs(+items), day_summaries | Product shops |
| credit_sales, credit_payments, udhar_customers | Udhar |
| expenses, staff, salary_payments | Common |
| coaching_courses, coaching_batches, coaching_students, coaching_fee_collections | Coaching |

**Central DB**: tenants, domains, users (super admin), subscription_payments, impersonation_tokens.

---

## 10. UI / Design System 🎨

### 10.1 Layout structure (`layouts/app.blade.php`)
- **Fixed left sidebar** — dark slate (`bg-slate-900` family), white content area.
- Sidebar items: `nav-item` class, active state highlighted, Lucide icons (w-4 h-4).
- Collapsible **System dropdown** (Alpine `x-data open` state).
- **FAB (Floating Action Buttons)** — bottom-right, shop-type ke hisaab se: pill-shaped, colored (green=primary action, blue=secondary, slate=close day).
- Overdue udhar ka **red badge** sidebar mein.

### 10.2 Design tokens / patterns
| Element | Pattern |
|---|---|
| Cards | `bg-white rounded-xl border border-slate-200 shadow-sm` |
| KPI cards | Center-aligned, uppercase tiny label (`text-xs font-semibold uppercase tracking-wider`) + bold number (`text-3xl font-bold`) |
| Status colors | green=paid/active, yellow=partial, red=pending/overdue, blue=info, slate=neutral/inactive |
| Buttons | `rounded-lg px-4 py-2 text-sm font-medium`, primary blue-600/green-600 |
| Badges | `text-xs px-2 py-0.5 rounded-full` colored bg-100/text-700 pairs |
| Tables | `text-sm`, `bg-slate-50` header, `divide-y divide-slate-100`, hover `bg-slate-50` |
| Forms | `border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500` |
| Modals | Fixed overlay `bg-black/50`, white `rounded-xl max-w-md`, JS `classList` toggle ya Alpine |
| Progress bars | `bg-slate-100 rounded-full h-2.5` track + `bg-green-500` fill |
| Currency | Har jagah `PKR {{ number_format(...) }}` |
| Empty states | Centered gray icon + message |

### 10.3 Interactivity
- **Alpine.js**: dropdowns, modals, live cash reconciliation (day summary), inline fee-collect forms, Open Tab manager.
- **Fetch JSON APIs**: Open Tab add/remove items (no page reload).
- **WhatsApp deep links**: `wa.me/{number}?text=...` — fee reminders, Roman Urdu messages.
- **Print views**: receipts with `@media print` CSS (nav hide).

### 10.4 UI improvement candidates (design better karne ke liye)
1. **Mobile responsiveness** — sidebar mobile par kaisi behave karti hai, tables horizontal scroll — audit needed.
2. **Dark mode** nahi hai.
3. **Consistent toasts** — abhi success messages simple green divs hain; toast/auto-dismiss system better hoga.
4. **Loading states** — fetch calls par spinners nahi hain.
5. **Confirmation dialogs** — native `confirm()` use hota hai; styled modal better hoga.
6. **Form validation UX** — errors page-top par hain; inline field errors better honge.
7. Modals JS `classList` aur Alpine dono se ban rahe hain — **ek pattern standardize** karein.

---

## 11. Known Gaps / Pending Work (Claude ko dene ke liye TODO list)

### Functional
1. **Reports Module** — abhi basic hai; sales/profit/stock/party-wise detailed reports with date range + export (user ne defer kiya tha).
2. **Staff Salary UI** — payment history, month-wise due tracking, salary slips.
3. **WhatsApp Share** — quotations/invoices/receipts WhatsApp par bhejna (abhi sirf coaching fee reminders mein hai).
4. **School System** — coaching module ko extend karke: classes/sections, teachers ki salary link, attendance, (baad mein exams).
5. **Coaching**: student attendance, fee months ka carry-forward/arrears view, admission fee (one-time) ka concept nahi hai.
6. **Open Tabs**: sidebar link sirf product shops mein — tab history/reporting nahi hai.
7. **Stock**: stock movement history ka UI, stock valuation report.
8. **Subscription enforcement** — plan expire hone par tenant block hota hai ya nahi, verify/enforce karna.
9. **Google Drive backup** — functional status verify karna.
10. **Notifications** — low stock, plan expiry, fee due ka koi alert system nahi (email/WhatsApp).

### Technical
1. **Tests** — koi automated tests nahi hain.
2. **Authorization** — role-based permissions (owner vs staff) granular nahi hain.
3. **Validation** — kuch controllers mein `numeric` amounts par max limits nahi.
4. **N+1 queries** — kuch dashboards par eager loading audit.
5. **Receipt numbers** — `nextNumber()` race condition (2 sath requests par duplicate ho sakta hai; DB transaction/lock better).
6. **Soft deletes** — records hard-delete hote hain; audit trail ke liye soft deletes consider karein.

---

## 12. How to Run (XAMPP)

```bash
composer install && npm install && npm run build
# .env: DB_CONNECTION=mysql, DB_DATABASE=invoice_billing
php artisan migrate                      # central DB
php artisan serve                        # ya XAMPP vhost
# Admin panel se tenant banao (subdomain hosts file mein add karo)
php artisan tenants:artisan "migrate"    # naye tenant modules ke liye
# Tenant Settings → "Seed Demo Data" se dummy data
```

---

*Generated: 2026-07-01 — branch `claude/charming-fermi-3ze88`*
