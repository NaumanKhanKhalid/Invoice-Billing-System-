@extends('layouts.app')
@section('title', 'Settings')

@section('content')
<div class="space-y-5">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Settings</h1>
        <p class="text-sm text-slate-500 mt-1">Company information manage karo</p>
    </div>

    @if(session('success'))
    <div class="flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl text-sm">
        <i data-lucide="check-circle" class="w-4 h-4 shrink-0"></i>{{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="flex items-center gap-3 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl text-sm">
        <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>{{ session('error') }}
    </div>
    @endif

    {{-- Google Drive Backup --}}
    @php
        $driveConnected = (bool) \App\Models\Setting::getValue('google_drive_token');
        $lastBackup     = \App\Models\Setting::getValue('last_backup_at');
    @endphp
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
        <h2 class="font-semibold text-slate-900 mb-4 flex items-center gap-2">
            <i data-lucide="cloud" class="w-4 h-4 text-green-600"></i>
            Google Drive Backup
        </h2>
        @if($driveConnected)
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <p class="text-sm text-green-700 font-medium flex items-center gap-2">
                    <i data-lucide="check-circle" class="w-4 h-4"></i>Google Drive Connected
                </p>
                @if($lastBackup)
                <p class="text-xs text-slate-400 mt-1">Last backup: {{ \Carbon\Carbon::parse($lastBackup)->format('d M Y, h:i A') }}</p>
                @else
                <p class="text-xs text-slate-400 mt-1">No backup taken yet</p>
                @endif
            </div>
            <div class="flex gap-2">
                <form method="POST" action="{{ route('backup.google') }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                        <i data-lucide="upload-cloud" class="w-4 h-4"></i>Backup Now
                    </button>
                </form>
                <a href="{{ route('settings.backups') }}" class="inline-flex items-center gap-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                    <i data-lucide="list" class="w-4 h-4"></i>View Backups
                </a>
                <form method="POST" action="{{ route('google.disconnect') }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 bg-white border border-red-200 hover:bg-red-50 text-red-600 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                        <i data-lucide="unlink" class="w-4 h-4"></i>Disconnect
                    </button>
                </form>
            </div>
        </div>
        @else
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <p class="text-sm text-slate-600">Database ko Google Drive mein automatically backup karo.</p>
                <p class="text-xs text-slate-400 mt-1">Connect karne ke baad "Backup Now" se manual backup lo.</p>
            </div>
            <a href="{{ route('google.connect') }}" class="inline-flex items-center gap-2 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                <svg class="w-4 h-4" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
                Connect Google Drive
            </a>
        </div>
        @endif
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
