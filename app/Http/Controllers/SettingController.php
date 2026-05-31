<?php

namespace App\Http\Controllers;

use App\Services\SettingService;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function __construct(private SettingService $settingService) {}

    public function index()
    {
        $settings = $this->settingService->all();
        return view('settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'company_name' => 'nullable|string|max:255',
            'company_address' => 'nullable|string',
            'company_phone' => 'nullable|string|max:50',
            'company_email' => 'nullable|email|max:255',
            'invoice_prefix' => 'nullable|string|max:20',
            'default_tax_rate' => 'nullable|numeric|min:0|max:100',
            'default_terms' => 'nullable|string',
            'logo' => 'nullable|image|max:2048',
        ]);

        $fields = [
            'company_name', 'company_address', 'company_phone',
            'company_email', 'invoice_prefix', 'default_tax_rate', 'default_terms',
        ];

        foreach ($fields as $field) {
            if ($request->has($field)) {
                $this->settingService->set($field, $request->input($field));
            }
        }

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('logos', 'public');
            $this->settingService->set('logo_path', $path);
        }

        return back()->with('success', 'Settings saved successfully.');
    }
}
