<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class TenantImpersonateController extends Controller
{
    public function start(string $token)
    {
        $data = Cache::pull('impersonate:' . $token);

        if (!$data) {
            abort(403, 'Invalid or expired impersonation token.');
        }

        // Find the owner user in this tenant's DB
        $user = User::where('role', 'owner')->first()
             ?? User::first();

        if (!$user) {
            abort(403, 'No user found in this tenant.');
        }

        Auth::login($user, false);

        session([
            'impersonating'   => true,
            'impersonate_as'  => $user->name,
            'admin_name'      => $data['admin_name'],
            'admin_panel_url' => $data['admin_panel_url'],
        ]);

        return redirect()->route('dashboard');
    }

    public function stop(Request $request)
    {
        $adminUrl = session('admin_panel_url', url('/admin/tenants'));

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect($adminUrl);
    }
}
