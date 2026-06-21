<?php
namespace App\Http\Controllers;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $query = Supplier::withCount('purchaseOrders')
            ->withSum('purchaseOrders', 'total_amount')
            ->withSum('purchaseOrders', 'amount_paid');
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('name','like',"%$s%")->orWhere('phone','like',"%$s%"));
        }
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }
        $suppliers = $query->orderBy('name')->paginate(15)->withQueryString();
        $stats = [
            'total'       => Supplier::count(),
            'active'      => Supplier::where('is_active', true)->count(),
            'purchased'   => \DB::table('purchase_orders')->sum('total_amount'),
            'outstanding' => Supplier::sum('balance'),
        ];
        return view('suppliers.index', compact('suppliers', 'stats'));
    }

    public function create()
    {
        return view('suppliers.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'phone'       => 'required|string|max:20',
            'address'     => 'nullable|string',
            'credit_days' => 'integer|min:1',
            'notes'       => 'nullable|string',
        ]);
        $data['is_active']   = $request->boolean('is_active', true);
        $data['credit_days'] = $data['credit_days'] ?? 15;
        Supplier::create($data);
        return redirect()->route('suppliers.index')->with('success', 'Supplier added successfully.');
    }

    public function show(Supplier $supplier)
    {
        $totalPurchased = $supplier->purchaseOrders()->sum('total_amount');
        $totalPaid      = $supplier->purchaseOrders()->sum('amount_paid');
        $outstanding    = $totalPurchased - $totalPaid;
        return view('suppliers.show', compact('supplier','orders','totalPurchased','totalPaid','outstanding'));
    }

    public function edit(Supplier $supplier)
    {
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'phone'       => 'required|string|max:20',
            'address'     => 'nullable|string',
            'credit_days' => 'integer|min:1',
            'notes'       => 'nullable|string',
        ]);
        $data['is_active'] = $request->boolean('is_active');
        $supplier->update($data);
        return redirect()->route('suppliers.index')->with('success', 'Supplier updated successfully.');
    }

    public function destroy(Supplier $supplier)
    {
        if ($supplier->purchaseOrders()->count() > 0) {
            return back()->with('error', 'Cannot delete supplier with purchase history.');
        }
        $supplier->delete();
        return redirect()->route('suppliers.index')->with('success', 'Supplier deleted.');
    }

    public function toggleStatus(Supplier $supplier)
    {
        $supplier->update(['is_active' => !$supplier->is_active]);
        return back()->with('success', 'Supplier status updated.');
    }
}
