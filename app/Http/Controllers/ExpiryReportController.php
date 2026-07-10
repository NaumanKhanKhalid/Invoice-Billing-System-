<?php

namespace App\Http\Controllers;

use App\Models\ProductBatch;

class ExpiryReportController extends Controller
{
    public function index()
    {
        $batches = ProductBatch::with('product')
            ->where('qty', '>', 0)
            ->orderBy('expiry_date')
            ->get();

        $expired     = $batches->filter(fn ($b) => $b->expiry_date->isPast());
        $expiring30  = $batches->filter(fn ($b) => !$b->expiry_date->isPast() && $b->expiry_date->lte(now()->addDays(30)));
        $expiring90  = $batches->filter(fn ($b) => $b->expiry_date->gt(now()->addDays(30)) && $b->expiry_date->lte(now()->addDays(90)));

        return view('expiry-report.index', compact('expired', 'expiring30', 'expiring90'));
    }
}
