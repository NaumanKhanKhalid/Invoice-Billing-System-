# ShopSaas — Multi-Tenant Shop Billing Platform

A complete SaaS billing system for selling to multiple shop clients on subscription. Each tenant gets their own isolated database. Built with Laravel + `stancl/tenancy`.

## Supported Shop Types
- 🍗 Chicken Shop
- 🔧 Hardware Shop
- 📱 Mobile / Accessories Shop
- 🏍️ Bike Spare Parts Shop
- 🏪 General Shop

## Features

### Central Admin Panel (`/admin`)
- Tenant management (create, edit, delete)
- Subscription plan management (Basic / Pro / Business)
- Payment recording & history per tenant
- Revenue dashboard (monthly/total actual payments)
- Expiring tenant alerts: `php artisan tenants:notify-expiring --days=7`

### Per-Tenant Shop System
- **Dashboard** — Revenue, purchases, expense charts
- **Purchases** — Supplier purchases with payment tracking
- **Supply Orders** — Customer orders + PDF invoice generation
- **Udhar Book** — Credit sales with payment tracking
- **Expenses** — Category-wise expense tracking
- **Staff & Salaries** — Employee management
- **Day End** — Daily closing records
- **Team Members** — Multi-user with plan-based limits
- **Settings** — Company info, Google Drive backup
- **First-Login Wizard** — Guided setup on new tenant login

### Subscription Plans
| Plan     | Price/mo | Users     | Staff | Backup |
|----------|----------|-----------|-------|--------|
| Basic    | PKR 1500 | 1         | ✗     | ✗      |
| Pro      | PKR 3000 | 3         | ✓     | ✓      |
| Business | PKR 5000 | Unlimited | ✓     | ✓      |

## Tech Stack
- Laravel 13, PHP 8.4
- `stancl/tenancy` v3.8 — separate DB per tenant
- SQLite (central) + SQLite per tenant (switchable to MySQL)
- Blade, Tailwind CSS, Alpine.js, Lucide Icons, Chart.js
- barryvdh/laravel-dompdf

## Installation

```bash
git clone <repo>
cd Invoice-Billing-System-
composer install
cp .env.example .env
php artisan key:generate

# Configure .env:
# CENTRAL_DOMAIN=yourdomain.com
# SUPER_ADMIN_EMAIL=admin@yourdomain.com
# SUPER_ADMIN_PASSWORD=yourpassword

php artisan migrate                    # central DB
php artisan db:seed --class=SuperAdminSeeder

# Create first tenant:
php artisan tenant:create

# Or via admin panel: http://yourdomain.com/admin
```

## Subdomain Routing
Each tenant is served at `{subdomain}.yourdomain.com`. Configure wildcard DNS:
```
*.yourdomain.com → your server IP
```

## Artisan Commands
```bash
php artisan admin:create               # create super admin
php artisan tenant:create              # create tenant interactively
php artisan tenants:notify-expiring    # show expiring clients (for WhatsApp follow-up)
php artisan tenants:run "migrate"      # run migration on all tenant DBs
```

## Scheduled Tasks (add to crontab)
```
* * * * * php artisan schedule:run
```
- Daily: mark overdue invoices, Google Drive backup
- Weekly (Mon 9am): expiring tenant report
