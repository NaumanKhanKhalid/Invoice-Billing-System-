<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $fromDate = $request->input('from_date', now()->startOfYear()->toDateString());
        $toDate = $request->input('to_date', now()->toDateString());

        // Revenue by month for last 12 months
        $monthlyData = [];
        $monthlyLabels = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthlyLabels[] = $month->format('M Y');
            $revenue = Invoice::where('status', 'paid')
                ->whereYear('issue_date', $month->year)
                ->whereMonth('issue_date', $month->month)
                ->sum('total');
            $monthlyData[] = round($revenue, 2);
        }

        // Top 5 clients by billed amount in date range
        $topClients = Client::withSum(['invoices' => function ($q) use ($fromDate, $toDate) {
            $q->whereBetween('issue_date', [$fromDate, $toDate]);
        }], 'total')
            ->orderByDesc('invoices_sum_total')
            ->limit(5)
            ->get();

        // Invoice status summary
        $statusSummary = Invoice::whereBetween('issue_date', [$fromDate, $toDate])
            ->selectRaw('status, COUNT(*) as count, SUM(total) as total')
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        // Revenue in date range
        $totalRevenue = Invoice::where('status', 'paid')
            ->whereBetween('issue_date', [$fromDate, $toDate])
            ->sum('total');

        $totalPending = Invoice::whereIn('status', ['sent', 'overdue'])
            ->whereBetween('issue_date', [$fromDate, $toDate])
            ->sum('total');

        return view('reports.index', compact(
            'monthlyData',
            'monthlyLabels',
            'topClients',
            'statusSummary',
            'totalRevenue',
            'totalPending',
            'fromDate',
            'toDate'
        ));
    }
}
