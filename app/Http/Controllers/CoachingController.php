<?php

namespace App\Http\Controllers;

use App\Models\CoachingBatch;
use App\Models\CoachingFeeCollection;
use App\Models\CoachingStudent;
use Carbon\Carbon;

class CoachingController extends Controller
{
    public function dashboard()
    {
        $totalStudents  = CoachingStudent::count();
        $activeStudents = CoachingStudent::where('status', 'active')->count();
        $totalBatches   = CoachingBatch::where('is_active', true)->count();

        $thisMonth      = Carbon::now()->startOfMonth()->toDateString();
        $monthLabel     = Carbon::now()->format('F Y');

        $monthFees      = CoachingFeeCollection::where('month', $thisMonth);
        $collected      = (clone $monthFees)->sum('amount_paid');
        $expected       = (clone $monthFees)->sum('amount_due');
        $pending        = (clone $monthFees)->where('status', '!=', 'paid')->sum('balance_due');
        $defaulters     = CoachingStudent::where('status', 'active')
            ->whereDoesntHave('fees', fn($q) => $q->where('month', $thisMonth)->where('status', 'paid'))
            ->with('batch.course')
            ->limit(10)
            ->get();

        $recentPayments = CoachingFeeCollection::with('student')
            ->where('status', '!=', 'pending')
            ->orderByDesc('payment_date')
            ->limit(8)
            ->get();

        return view('coaching.dashboard', compact(
            'totalStudents', 'activeStudents', 'totalBatches',
            'monthLabel', 'collected', 'expected', 'pending',
            'defaulters', 'recentPayments'
        ));
    }
}
