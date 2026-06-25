<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SetupController extends Controller
{
    public function index()
    {
        return view('setup.index');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'company_name'    => 'required|string|max:100',
            'company_phone'   => 'nullable|string|max:20',
            'company_address' => 'nullable|string|max:255',
            'currency'        => 'required|in:PKR,USD,EUR',
            'timezone'        => 'required|string',
        ]);

        foreach ($data as $key => $value) {
            if ($value !== null) {
                Setting::setValue($key, $value);
            }
        }

        Setting::setValue('setup_completed', '1');

        return redirect()->route('dashboard')->with('success', 'Setup complete! Welcome to your shop dashboard.');
    }
}
