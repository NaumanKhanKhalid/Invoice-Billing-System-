<?php

namespace App\Http\Controllers;

use App\Models\CoachingFeeCollection;
use App\Models\CoachingStudent;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CoachingFeeController extends Controller
{
    public function index(Request $request)
    {
        $month      = $request->input('month', now()->format('Y-m'));
        $monthDate  = Carbon::parse($month . '-01');
        $monthKey   = $monthDate->toDateString(); // YYYY-MM-01

        // Auto-generate fee records for all active students if not yet generated.
        // Lookup uses whereDate so the match works regardless of how the DB
        // driver stored the month (date vs datetime string).
        $activeStudents = CoachingStudent::where('status', 'active')->with('batch.course')->get();

        foreach ($activeStudents as $student) {
            CoachingFeeCollection::whereDate('month', $monthKey)
                ->where('student_id', $student->id)
                ->firstOr(fn() => CoachingFeeCollection::create([
                    'student_id'      => $student->id,
                    'month'           => $monthKey,
                    'amount_due'      => $student->effectiveFee(),
                    'discount_amount' => 0,
                    'amount_paid'     => 0,
                    'balance_due'     => $student->effectiveFee(),
                    'status'          => 'pending',
                ]));
        }

        $fees = CoachingFeeCollection::with('student.batch.course')
            ->whereDate('month', $monthKey)
            ->orderBy('status')
            ->get();

        $summary = [
            'total'     => $fees->count(),
            'paid'      => $fees->where('status', 'paid')->count(),
            'partial'   => $fees->where('status', 'partial')->count(),
            'pending'   => $fees->where('status', 'pending')->count(),
            'collected' => $fees->sum('amount_paid'),
            'expected'  => $fees->sum('amount_due'),
            'balance'   => $fees->sum('balance_due'),
        ];

        return view('coaching.fees.index', compact('fees', 'month', 'monthDate', 'summary'));
    }

    public function collect(Request $request, CoachingFeeCollection $fee)
    {
        $data = $request->validate([
            'amount_paid'      => 'required|numeric|min:0',
            'payment_method'   => 'required|in:cash,jazzcash,easypaisa,bank,other',
            'discount_amount'  => 'nullable|numeric|min:0',
            'notes'            => 'nullable|string|max:300',
        ]);

        $discount    = $data['discount_amount'] ?? $fee->discount_amount;
        $netDue      = max(0, $fee->amount_due - $discount);
        $totalPaid   = $fee->amount_paid + $data['amount_paid'];
        $balance     = max(0, $netDue - $totalPaid);
        $status      = $balance <= 0 ? 'paid' : ($totalPaid > 0 ? 'partial' : 'pending');

        DB::transaction(function () use ($fee, $data, $discount, $totalPaid, $balance, $status) {
            $fee->update([
                'discount_amount' => $discount,
                'amount_paid'     => $totalPaid,
                'balance_due'     => $balance,
                'payment_date'    => today()->toDateString(),
                'payment_method'  => $data['payment_method'],
                'receipt_number'  => $fee->receipt_number ?? CoachingFeeCollection::nextReceiptNumber(),
                'status'          => $status,
                'notes'           => $data['notes'] ?? $fee->notes,
            ]);
        });

        return back()->with('success', 'Payment recorded for ' . $fee->student->name);
    }

    public function receipt(CoachingFeeCollection $fee)
    {
        $fee->load('student.batch.course');
        return view('coaching.fees.receipt', compact('fee'));
    }

    /**
     * Public receipt — opened by parents from the WhatsApp link.
     * No login; access is guarded by the signed-URL middleware.
     */
    public function publicReceipt(CoachingFeeCollection $fee)
    {
        $fee->load('student.batch.course');
        return view('coaching.fees.public-receipt', compact('fee'));
    }
}
