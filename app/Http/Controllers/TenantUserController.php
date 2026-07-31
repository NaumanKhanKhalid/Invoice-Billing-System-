<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class TenantUserController extends Controller
{
    private function planLimit(): int
    {
        $plan = tenant()->plan ?? 'basic';
        $max  = (int) plan_value($plan, 'max_users', 1);
        return $max < 0 ? PHP_INT_MAX : $max;   // -1 = unlimited
    }

    public function index()
    {
        $users = User::orderBy('role')->orderBy('name')->get();
        $limit = $this->planLimit();
        return view('tenant.users.index', compact('users', 'limit'));
    }

    public function create()
    {
        $limit = $this->planLimit();
        $count = User::count();
        if ($count >= $limit) {
            return redirect()->route('tenant.users.index')
                ->with('error', "Your plan allows max {$limit} user(s). Upgrade to add more.");
        }
        return view('tenant.users.create', compact('limit', 'count'));
    }

    public function store(Request $request)
    {
        $limit = $this->planLimit();
        if (User::count() >= $limit) {
            return back()->with('error', "User limit reached for your plan ({$limit} max).");
        }

        $data = $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:users,email',
            'role'     => 'required|in:owner,manager,cashier',
            'password' => 'required|string|min:6|confirmed',
        ]);

        User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'role'     => $data['role'],
            'password' => Hash::make($data['password']),
        ]);

        return redirect()->route('tenant.users.index')->with('success', "User '{$data['name']}' created.");
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }
        $user->delete();
        return back()->with('success', 'User removed.');
    }

    public function resetPassword(Request $request, User $user)
    {
        $data = $request->validate([
            'password' => 'required|string|min:6|confirmed',
        ]);
        $user->update(['password' => Hash::make($data['password'])]);
        return back()->with('success', "Password updated for {$user->name}.");
    }
}
