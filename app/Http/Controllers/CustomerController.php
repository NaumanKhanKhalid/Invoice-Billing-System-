<?php
namespace App\Http\Controllers;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::withCount('salesOrders')
            ->withSum('salesOrders','total_amount')
            ->withSum('salesOrders','amount_paid');
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('name','like',"%$s%")->orWhere('phone','like',"%$s%"));
        }
        if ($request->filled('type'))   { $query->where('type', $request->type); }
        if ($request->filled('status')) { $query->where('is_active', $request->status === 'active'); }
        $customers = $query->orderBy('name')->paginate(15)->withQueryString();
        $stats = [
            'total'       => Customer::count(),
            'active'      => Customer::where('is_active',true)->count(),
            'billed'      => \DB::table('sales_orders')->sum('total_amount'),
            'outstanding' => Customer::sum('current_balance'),
        ];
        return view('customers.index', compact('customers','stats'));
    }

    public function create()
    {
        return view('customers.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'             => 'required|string|max:255',
            'phone'            => 'required|string|max:20',
            'address'          => 'nullable|string',
            'type'             => 'required|in:retail,hotel,restaurant,company,reseller',
            'credit_days'      => 'integer|min:0',
            'credit_limit'     => 'numeric|min:0',
            'whatsapp_number'  => 'nullable|string|max:20',
        ]);
        $data['is_active']      = $request->boolean('is_active', true);
        $data['current_balance'] = 0;
        $data['credit_days']    = $data['credit_days'] ?? match($data['type']) {
            'retail'   => 0,
            'reseller' => 7,
            default    => 30,
        };
        Customer::create($data);
        return redirect()->route('customers.index')->with('success', 'Customer added successfully.');
    }

    public function show(Customer $customer)
    {
        $orders      = $customer->salesOrders()->with('chickenType')->orderByDesc('date')->paginate(10);
        $totalBilled = $customer->salesOrders()->sum('total_amount');
        $totalPaid   = $customer->salesOrders()->sum('amount_paid');
        $outstanding = $totalBilled - $totalPaid;
        return view('customers.show', compact('customer','orders','totalBilled','totalPaid','outstanding'));
    }

    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $data = $request->validate([
            'name'            => 'required|string|max:255',
            'phone'           => 'required|string|max:20',
            'address'         => 'nullable|string',
            'type'            => 'required|in:retail,hotel,restaurant,company,reseller',
            'credit_days'     => 'integer|min:0',
            'credit_limit'    => 'numeric|min:0',
            'whatsapp_number' => 'nullable|string|max:20',
        ]);
        $data['is_active'] = $request->boolean('is_active');
        $customer->update($data);
        return redirect()->route('customers.index')->with('success', 'Customer updated.');
    }

    public function destroy(Customer $customer)
    {
        if ($customer->salesOrders()->count() > 0) {
            return back()->with('error', 'Cannot delete customer with order history.');
        }
        $customer->delete();
        return redirect()->route('customers.index')->with('success', 'Customer deleted.');
    }

    public function toggleStatus(Customer $customer)
    {
        $customer->update(['is_active' => !$customer->is_active]);
        return back()->with('success', 'Customer status updated.');
    }

    public function toggleBlacklist(Request $request, Customer $customer)
    {
        if ($customer->is_blacklisted) {
            $customer->update(['is_blacklisted' => false, 'blacklist_reason' => null]);
            return back()->with('success', 'Customer removed from blacklist.');
        }
        $customer->update(['is_blacklisted' => true, 'blacklist_reason' => $request->reason]);
        return back()->with('success', 'Customer blacklisted.');
    }
}
