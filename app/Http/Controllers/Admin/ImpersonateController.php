<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ImpersonationToken;
use App\Models\Tenant;
use Illuminate\Support\Str;

class ImpersonateController extends Controller
{
    public function start(Tenant $tenant)
    {
        // Clean up expired tokens
        ImpersonationToken::where('expires_at', '<', now())->delete();

        $token = Str::uuid()->toString();

        ImpersonationToken::create([
            'token'           => $token,
            'tenant_id'       => $tenant->id,
            'admin_id'        => auth()->id(),
            'admin_name'      => auth()->user()->name,
            'admin_panel_url' => url('/admin/tenants'),
            'expires_at'      => now()->addMinutes(2),
        ]);

        $domain = $tenant->domains()->first();
        if (!$domain) {
            return back()->with('error', 'Tenant has no domain configured.');
        }

        $scheme  = request()->secure() ? 'https' : 'http';
        $port    = request()->getPort();
        $portStr = (($scheme === 'http' && $port == 80) || ($scheme === 'https' && $port == 443)) ? '' : ':' . $port;

        return redirect("{$scheme}://{$domain->domain}{$portStr}/impersonate/{$token}");
    }
}
