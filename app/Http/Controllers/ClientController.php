<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $query = Client::withCount('invoices')->with('invoices');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $clients = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('clients.index', compact('clients'));
    }

    public function create()
    {
        return view('clients.create');
    }

    public function store(StoreClientRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = auth()->id();
        $data['is_active'] = $request->has('is_active');

        Client::create($data);

        return redirect()->route('clients.index')->with('success', 'Client created successfully.');
    }

    public function show(Client $client)
    {
        $client->load(['invoices.payments', 'invoices.items']);
        $invoices = $client->invoices()->with(['payments'])->orderBy('created_at', 'desc')->paginate(10);
        $totalBilled = $client->invoices()->sum('total');
        $totalPaid = $client->invoices()->with('payments')->get()->sum('amount_paid');

        return view('clients.show', compact('client', 'invoices', 'totalBilled', 'totalPaid'));
    }

    public function edit(Client $client)
    {
        return view('clients.edit', compact('client'));
    }

    public function update(UpdateClientRequest $request, Client $client)
    {
        $data = $request->validated();
        $data['is_active'] = $request->has('is_active');
        $client->update($data);

        return redirect()->route('clients.index')->with('success', 'Client updated successfully.');
    }

    public function destroy(Client $client)
    {
        $client->delete();
        return redirect()->route('clients.index')->with('success', 'Client deleted successfully.');
    }

    public function toggleStatus(Client $client)
    {
        $client->update(['is_active' => !$client->is_active]);
        $status = $client->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "Client {$status} successfully.");
    }
}
