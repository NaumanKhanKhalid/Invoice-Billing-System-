@extends('layouts.app')
@section('title', 'Settings')

@section('content')
<div class="space-y-5">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Settings</h1>
        <p class="text-sm text-slate-500 mt-1">Company information manage karo</p>
    </div>

    <form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data" class="space-y-5">
        @csrf

        <!-- Company Info -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
            <h2 class="font-semibold text-slate-900 mb-4 flex items-center gap-2">
                <i data-lucide="building-2" class="w-4 h-4 text-green-600"></i>
                Company Information
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Company Name</label>
                    <input type="text" name="company_name" value="{{ $settings['company_name'] ?? '' }}"
                           class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none"
                           placeholder="InvoicePro Ltd.">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Company Email</label>
                    <input type="email" name="company_email" value="{{ $settings['company_email'] ?? '' }}"
                           class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none"
                           placeholder="contact@company.pk">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Phone</label>
                    <input type="text" name="company_phone" value="{{ $settings['company_phone'] ?? '' }}"
                           class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none"
                           placeholder="+92 21 1234567">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Logo</label>
                    <input type="file" name="logo" accept="image/*"
                           class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
                    @if(!empty($settings['logo_path']))
                    <p class="text-xs text-slate-400 mt-1">Current logo: {{ $settings['logo_path'] }}</p>
                    @endif
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Address</label>
                    <textarea name="company_address" rows="2"
                              class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none resize-none"
                              placeholder="Company address...">{{ $settings['company_address'] ?? '' }}</textarea>
                </div>
            </div>
        </div>

        <div>
            <button type="submit"
                    class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white font-medium px-6 py-2.5 rounded-lg transition-colors text-sm">
                <i data-lucide="save" class="w-4 h-4"></i>
                Save Settings
            </button>
        </div>
    </form>
</div>
@endsection
