<?php

namespace App\Http\Controllers;

use App\Models\RepairJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RepairJobController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->get('status', 'active');

        $query = RepairJob::query();

        if ($tab === 'active') {
            $query->whereNotIn('status', ['delivered', 'cancelled']);
        } elseif (in_array($tab, RepairJob::STATUSES)) {
            $query->where('status', $tab);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('customer_name', 'like', "%$s%")
                ->orWhere('customer_phone', 'like', "%$s%")
                ->orWhere('device', 'like', "%$s%")
                ->orWhere('job_number', 'like', "%$s%"));
        }

        $jobs = $query->orderByDesc('id')->paginate(30)->withQueryString();

        $statusCounts = RepairJob::selectRaw('status, count(*) as c')
            ->groupBy('status')->pluck('c', 'status');
        $counts = [
            'active'      => ($statusCounts['pending'] ?? 0) + ($statusCounts['in_progress'] ?? 0) + ($statusCounts['ready'] ?? 0),
            'pending'     => $statusCounts['pending'] ?? 0,
            'in_progress' => $statusCounts['in_progress'] ?? 0,
            'ready'       => $statusCounts['ready'] ?? 0,
            'delivered'   => $statusCounts['delivered'] ?? 0,
            'cancelled'   => $statusCounts['cancelled'] ?? 0,
        ];

        return view('repairs.index', compact('jobs', 'counts', 'tab'));
    }

    public function create()
    {
        return view('repairs.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_name'  => 'required|string|max:100',
            'customer_phone' => 'nullable|string|max:20',
            'device'         => 'required|string|max:150',
            'serial_imei'    => 'nullable|string|max:100',
            'fault'          => 'required|string|max:1000',
            'estimated_cost' => 'nullable|numeric|min:0',
            'advance_paid'   => 'nullable|numeric|min:0',
            'notes'          => 'nullable|string|max:1000',
        ]);

        $job = DB::transaction(function () use ($data) {
            return RepairJob::create($data + [
                'job_number'   => RepairJob::nextNumber(),
                'advance_paid' => $data['advance_paid'] ?? 0,
                'status'       => 'pending',
            ]);
        });

        return redirect()->route('repairs.show', $job)
            ->with('success', 'Job card ' . $job->job_number . ' created.');
    }

    public function show(RepairJob $repair)
    {
        return view('repairs.show', ['job' => $repair]);
    }

    public function edit(RepairJob $repair)
    {
        return view('repairs.edit', ['job' => $repair]);
    }

    public function update(Request $request, RepairJob $repair)
    {
        $data = $request->validate([
            'customer_name'  => 'required|string|max:100',
            'customer_phone' => 'nullable|string|max:20',
            'device'         => 'required|string|max:150',
            'serial_imei'    => 'nullable|string|max:100',
            'fault'          => 'required|string|max:1000',
            'estimated_cost' => 'nullable|numeric|min:0',
            'advance_paid'   => 'nullable|numeric|min:0',
            'final_cost'     => 'nullable|numeric|min:0',
            'notes'          => 'nullable|string|max:1000',
        ]);

        $repair->update($data + ['advance_paid' => $data['advance_paid'] ?? 0]);

        return redirect()->route('repairs.show', $repair)->with('success', 'Job card updated.');
    }

    public function updateStatus(Request $request, RepairJob $repair)
    {
        $data = $request->validate([
            'status'     => 'required|in:pending,in_progress,ready,delivered,cancelled',
            'final_cost' => 'required_if:status,delivered|nullable|numeric|min:0',
        ], [
            'final_cost.required_if' => 'Final cost is required when delivering a job.',
        ]);

        $update = ['status' => $data['status']];

        if ($data['status'] === 'ready' && !$repair->ready_at) {
            $update['ready_at'] = now();
        }
        if ($data['status'] === 'delivered') {
            $update['delivered_at'] = now();
            $update['final_cost']   = $data['final_cost'];
        } elseif (isset($data['final_cost']) && $data['final_cost'] !== null) {
            $update['final_cost'] = $data['final_cost'];
        }

        $repair->update($update);

        return back()->with('success', 'Job ' . $repair->job_number . ' marked as ' . str_replace('_', ' ', $data['status']) . '.');
    }

    public function destroy(RepairJob $repair)
    {
        $number = $repair->job_number;
        $repair->delete();

        return redirect()->route('repairs.index')->with('success', 'Job card ' . $number . ' deleted.');
    }
}
