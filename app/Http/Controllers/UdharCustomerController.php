<?php

namespace App\Http\Controllers;

use App\Models\UdharCustomer;
use Illuminate\Http\Request;

class UdharCustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = UdharCustomer::query();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('phone', 'like', '%' . $request->search . '%');
            });
        }

        $customers = $query->orderBy('name')->paginate(20)->withQueryString();

        $totalBalance = UdharCustomer::sum('current_balance');
        $totalGiven   = UdharCustomer::sum('total_given');
        $totalReceived = UdharCustomer::sum('total_received');

        return view('udhar.customers.index', compact('customers', 'totalBalance', 'totalGiven', 'totalReceived'));
    }

    public function create()
    {
        return view('udhar.customers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'             => 'required|string|max:255',
            'phone'            => 'nullable|string|max:20',
            'whatsapp_number'  => 'nullable|string|max:20',
            'address'          => 'nullable|string|max:500',
            'notes'            => 'nullable|string',
        ]);

        $customer = UdharCustomer::create($validated);

        if ($request->filled('redirect_to_udhar')) {
            return redirect()->route('udhar.create', ['customer_id' => $customer->id])
                ->with('success', 'Customer added. Now create the udhar entry.');
        }

        return redirect()->route('udhar-customers.show', $customer)->with('success', 'Customer added.');
    }

    public function show(UdharCustomer $udharCustomer)
    {
        $sales = $udharCustomer->creditSales()->latest('sale_date')->paginate(15);

        return view('udhar.customers.show', compact('udharCustomer', 'sales'));
    }

    public function edit(UdharCustomer $udharCustomer)
    {
        return view('udhar.customers.edit', compact('udharCustomer'));
    }

    public function update(Request $request, UdharCustomer $udharCustomer)
    {
        $validated = $request->validate([
            'name'             => 'required|string|max:255',
            'phone'            => 'nullable|string|max:20',
            'whatsapp_number'  => 'nullable|string|max:20',
            'address'          => 'nullable|string|max:500',
            'notes'            => 'nullable|string',
        ]);

        $udharCustomer->update($validated);

        return redirect()->route('udhar-customers.show', $udharCustomer)->with('success', 'Customer updated.');
    }
}
