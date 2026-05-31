# InvoicePro — Laravel 11 Invoice & Billing System

A complete, production-ready invoice and billing management system built with Laravel 11, featuring a beautiful dark sidebar design, real-time invoice calculations with Alpine.js, PDF generation, and comprehensive reporting.

## Features

- **Dashboard** — Stats cards, revenue chart (Chart.js), overdue alerts, recent invoices
- **Client Management** — Full CRUD, soft deletes, active/inactive toggle, search and filter
- **Products and Services** — Manage billable items with tax rates, live toggle status
- **Invoice Management** — Create/edit invoices with dynamic line items, auto-generated numbers
- **Smart Line Items** — Alpine.js powered: product auto-fill, live totals, discount support
- **Payment Tracking** — Record multiple payments per invoice, modal UI, payment history
- **Status Workflow** — Draft to Sent to Paid/Overdue to Cancelled with auto-status on full payment
- **PDF Generation** — Professional PDF invoices via barryvdh/laravel-dompdf
- **Reports** — Revenue by month, top clients, invoice status summaries with Chart.js
- **Settings** — Company details, invoice prefix, default tax rate, logo upload
- **Artisan Command** — invoices:mark-overdue scheduled to run daily
- **Role-based Access** — Admin and Staff roles

## Tech Stack

- Laravel 11, PHP 8.2+
- SQLite (easily switchable to MySQL)
- Blade Templates, Tailwind CSS v3, Alpine.js 3.x
- Lucide Icons (CDN), Chart.js 4
- barryvdh/laravel-dompdf
- Laravel Breeze

## Installation

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
npm run build
php artisan serve
```

## Demo Credentials

| Role  | Email           | Password |
|-------|-----------------|----------|
| Admin | admin@demo.com  | password |
| Staff | staff@demo.com  | password |

## Artisan Commands

```bash
php artisan invoices:mark-overdue
php artisan schedule:work
```
