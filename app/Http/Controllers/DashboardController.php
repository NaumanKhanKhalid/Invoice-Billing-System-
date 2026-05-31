<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $totalClients = Client::count();
        $totalInvoices = Invoice::count();
        $totalRevenue = Invoice::where('status', 'paid')->sum('total');
        $pendingAmount = Invoice::whereIn('status', ['sent', 'overdue'])->sum('total')
            - Payment::whereHas('invoice', fn($q) => $q->whereIn('status', ['sent', 'overdue']))->sum('amount');

        $overdueInvoices = Invoice::with('client')
            ->where('status', 'overdue')
            ->orderBy('due_date')
            ->limit(5)
            ->get();

        $recentInvoices = Invoice::with('client')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Monthly revenue for last 6 months
        $monthlyRevenue = [];
        $monthlyLabels = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthlyLabels[] = $month->format('M Y');
            $revenue = Invoice::where('status', 'paid')
                ->whereYear('updated_at', $month->year)
                ->whereMonth('updated_at', $month->month)
                ->sum('total');
            $monthlyRevenue[] = round($revenue, 2);
        }

        return view('dashboard.index', compact(
            'totalClients',
            'totalInvoices',
            'totalRevenue',
            'pendingAmount',
            'overdueInvoices',
            'recentInvoices',
            'monthlyRevenue',
            'monthlyLabels'
        ));
    }
}
