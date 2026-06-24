<?php

namespace App\Http\Controllers;

use App\Models\SalaryPayment;
use App\Models\Staff;
use Illuminate\Http\Request;

class StaffController extends Controller
{
    public function index()
    {
        $staff = Staff::withCount('salaryPayments')
            ->with(['salaryPayments' => fn($q) => $q->latest('payment_date')->limit(1)])
            ->orderBy('name')->get();
        $totalSalary = Staff::where('is_active', true)->sum('salary');
        return view('staff.index', compact('staff', 'totalSalary'));
    }

    public function create()
    {
        return view('staff.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'         => 'required|string|max:100',
            'phone'        => 'nullable|string|max:20',
            'role'         => 'required|in:manager,butcher,delivery,cleaner,cashier,other',
            'salary'       => 'required|numeric|min:0',
            'joining_date' => 'required|date',
        ]);

        Staff::create(array_merge($data, ['is_active' => true]));

        return redirect()->route('staff.index')->with('success', $data['name'] . ' added to staff.');
    }

    public function show(Staff $staff)
    {
        $staff->load('salaryPayments');
        $totalPaid = $staff->salaryPayments->sum('amount');
        return view('staff.show', compact('staff', 'totalPaid'));
    }

    public function edit(Staff $staff)
    {
        return view('staff.edit', compact('staff'));
    }

    public function update(Request $request, Staff $staff)
    {
        $data = $request->validate([
            'name'         => 'required|string|max:100',
            'phone'        => 'nullable|string|max:20',
            'role'         => 'required|in:manager,butcher,delivery,cleaner,cashier,other',
            'salary'       => 'required|numeric|min:0',
            'joining_date' => 'required|date',
        ]);

        $staff->update($data);

        return redirect()->route('staff.show', $staff)->with('success', 'Staff updated.');
    }

    public function toggleStatus(Staff $staff)
    {
        $staff->update(['is_active' => !$staff->is_active]);
        $status = $staff->is_active ? 'activated' : 'deactivated';
        return back()->with('success', $staff->name . ' ' . $status . '.');
    }

    public function storeSalary(Request $request, Staff $staff)
    {
        $data = $request->validate([
            'month'        => 'required|integer|between:1,12',
            'year'         => 'required|integer|min:2020',
            'amount'       => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'note'         => 'nullable|string|max:255',
        ]);

        SalaryPayment::create(array_merge($data, ['staff_id' => $staff->id]));

        return redirect()->route('staff.show', $staff)->with('success', 'Salary of PKR ' . number_format($data['amount'], 0) . ' recorded.');
    }
}
