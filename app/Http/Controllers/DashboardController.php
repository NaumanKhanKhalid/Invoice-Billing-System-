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
        $userId = auth()->id();

        $totalClients = Client::where('user_id', $userId)->count();
        $totalInvoices = Invoice::where('user_id', $userId)->count();
        $totalRevenue = Invoice::where('user_id', $userId)->where('status', 'paid')->sum('total');
        $pendingAmount = Invoice::where('user_id', $userId)
            ->whereIn('status', ['sent', 'overdue'])
            ->with('payments')
            ->get()
            ->sum('amount_due');

        $overdueInvoices = Invoice::with('client')
            ->where('user_id', $userId)
            ->where('status', 'overdue')
            ->orderBy('due_date')
            ->limit(5)
            ->get();

        $recentInvoices = Invoice::with('client')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Monthly revenue for last 6 months
        $monthlyRevenue = [];
        $monthlyLabels = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthlyLabels[] = $month->format('M Y');
            $revenue = Invoice::where('user_id', $userId)
                ->where('status', 'paid')
                ->whereYear('issue_date', $month->year)
                ->whereMonth('issue_date', $month->month)
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
