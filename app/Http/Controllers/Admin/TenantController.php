<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TenantController extends Controller
{
    public function index()
    {
        $tenants = Tenant::latest()->get();
        $stats = [
            'total'    => $tenants->count(),
            'active'   => $tenants->where('is_active', true)->count(),
            'expired'  => $tenants->filter(fn($t) => $t->plan_expires_at && $t->plan_expires_at < now())->count(),
            'revenue'  => 0, // future billing integration
        ];
        return view('admin.tenants.index', compact('tenants', 'stats'));
    }

    public function create()
    {
        return view('admin.tenants.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'shop_name'    => 'required|string|max:100',
            'shop_type'    => 'required|in:chicken,bike,hardware,mobile,general',
            'owner_name'   => 'required|string|max:100',
            'owner_email'  => 'required|email|unique:tenants,owner_email',
            'owner_phone'  => 'nullable|string|max:20',
            'plan'         => 'required|in:basic,pro,business',
            'plan_months'  => 'required|integer|min:1|max:12',
            'subdomain'    => 'required|string|alpha_dash|max:50|unique:domains,domain',
            'notes'        => 'nullable|string',
        ]);

        $tenantId = Str::slug($data['subdomain']);

        $tenant = Tenant::create([
            'id'              => $tenantId,
            'shop_name'       => $data['shop_name'],
            'shop_type'       => $data['shop_type'],
            'owner_name'      => $data['owner_name'],
            'owner_email'     => $data['owner_email'],
            'owner_phone'     => $data['owner_phone'] ?? null,
            'plan'            => $data['plan'],
            'plan_expires_at' => now()->addMonths((int)$data['plan_months']),
            'is_active'       => true,
            'notes'           => $data['notes'] ?? null,
        ]);

        $subdomain = $data['subdomain'] . '.' . config('app.central_domain', 'localhost');
        $tenant->domains()->create(['domain' => $subdomain]);

        // Create the owner user inside the tenant's database
        tenancy()->initialize($tenant);
        \App\Models\User::create([
            'name'     => $data['owner_name'],
            'email'    => $data['owner_email'],
            'password' => bcrypt('password123'), // temp password
            'role'     => 'owner',
        ]);
        tenancy()->end();

        return redirect()->route('admin.tenants.show', $tenant)
            ->with('success', "Tenant '{$tenant->shop_name}' created. Login: {$data['owner_email']} / password123");
    }

    public function show(Tenant $tenant)
    {
        return view('admin.tenants.show', compact('tenant'));
    }

    public function edit(Tenant $tenant)
    {
        return view('admin.tenants.edit', compact('tenant'));
    }

    public function update(Request $request, Tenant $tenant)
    {
        $data = $request->validate([
            'shop_name'      => 'required|string|max:100',
            'shop_type'      => 'required|in:chicken,bike,hardware,mobile,general',
            'owner_name'     => 'required|string|max:100',
            'owner_phone'    => 'nullable|string|max:20',
            'plan'           => 'required|in:basic,pro,business',
            'plan_expires_at'=> 'required|date',
            'is_active'      => 'boolean',
            'notes'          => 'nullable|string',
        ]);

        $tenant->update($data + ['is_active' => $request->boolean('is_active')]);

        return redirect()->route('admin.tenants.show', $tenant)->with('success', 'Tenant updated.');
    }

    public function destroy(Tenant $tenant)
    {
        $tenant->delete();
        return redirect()->route('admin.tenants.index')->with('success', 'Tenant and database deleted.');
    }

    public function renewPlan(Request $request, Tenant $tenant)
    {
        $data = $request->validate(['months' => 'required|integer|min:1|max:24']);

        $from = $tenant->plan_expires_at && $tenant->plan_expires_at > now()
            ? $tenant->plan_expires_at
            : now();

        $tenant->update(['plan_expires_at' => $from->addMonths($data['months']), 'is_active' => true]);

        return back()->with('success', "Plan renewed by {$data['months']} month(s).");
    }
}
