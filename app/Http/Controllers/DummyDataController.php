<?php

namespace App\Http\Controllers;

use App\Services\DummyDataService;
use Illuminate\Http\RedirectResponse;

class DummyDataController extends Controller
{
    public function seed(): RedirectResponse
    {
        if (DummyDataService::isSeeded()) {
            return back()->with('error', 'Demo data is already loaded. Delete it first.');
        }

        $shopType = tenant()->shop_type ?? 'general';

        try {
            DummyDataService::seed($shopType);
            return back()->with('success', 'Demo data loaded successfully! Explore the system with sample data.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Failed to load demo data: ' . $e->getMessage());
        }
    }

    public function delete(): RedirectResponse
    {
        if (!DummyDataService::isSeeded()) {
            return back()->with('error', 'No demo data found.');
        }

        try {
            DummyDataService::delete();
            return back()->with('success', 'Demo data deleted successfully.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Failed to delete demo data: ' . $e->getMessage());
        }
    }
}
