@extends('layouts.app')
@section('title', 'Settings')

@section('content')
<div class="space-y-5">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">{{ __('pages.settings') }}</h1>
        <p class="text-sm text-slate-500 mt-1">{{ __('pages.settings_sub') }}</p>
    </div>

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
                <p class="text-sm text-slate-600">Automatically back up your database to Google Drive.</p>
                <p class="text-xs text-slate-400 mt-1">After connecting, use "Backup Now" to take a manual backup.</p>
            </div>
            @if(\App\Http\Controllers\GoogleDriveController::isConfigured())
            <a href="{{ route('google.connect') }}" class="inline-flex items-center gap-2 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                <svg class="w-4 h-4" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
                Connect Google Drive
            </a>
            @else
            <span class="inline-flex items-center gap-2 bg-slate-50 border border-slate-200 text-slate-400 px-4 py-2 rounded-lg text-sm font-medium cursor-not-allowed"
                  title="Admin ko .env me GOOGLE_CLIENT_ID / GOOGLE_CLIENT_SECRET set karne honge">
                <i data-lucide="cloud-off" class="w-4 h-4"></i>
                Google Drive not configured
            </span>
            @endif
        </div>
        @endif
    </div>

    {{-- Demo / Test Data --}}
    @php $demoSeeded = \App\Services\DummyDataService::isSeeded(); @endphp
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
        <h2 class="font-semibold text-slate-900 mb-1 flex items-center gap-2">
            <i data-lucide="database" class="w-4 h-4 text-green-600"></i>
            Demo / Test Data
        </h2>
        <p class="text-sm text-slate-500 mb-4">Load sample data to explore the system, or delete it when you're ready to go live.</p>
        <div class="flex items-center gap-3 flex-wrap">
            @if(!$demoSeeded)
            <form method="POST" action="{{ route('demo.seed') }}"
                  data-confirm-title="Sample data load karein?"
                  data-confirm-message="Aapke shop type ke hisab se customers, suppliers, invoices waghera add ho jayenge.">
                @csrf
                <button type="submit"
                        class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                    <i data-lucide="play-circle" class="w-4 h-4"></i>
                    Load Demo Data
                </button>
            </form>
            <span class="text-xs text-slate-400">No demo data loaded yet.</span>
            @else
            <span class="inline-flex items-center gap-1.5 text-sm text-green-700 font-medium bg-green-50 border border-green-200 px-3 py-1.5 rounded-lg">
                <i data-lucide="check-circle" class="w-4 h-4"></i>Demo data loaded
            </span>
            <form method="POST" action="{{ route('demo.delete') }}">
                @csrf
                <button type="submit"
                        onclick="return confirm('Delete all demo data? This cannot be undone.')"
                        class="inline-flex items-center gap-2 bg-white border border-red-200 hover:bg-red-50 text-red-600 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                    Delete Demo Data
                </button>
            </form>
            @endif
        </div>
    </div>

    {{-- Data Export --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
        <h2 class="font-semibold text-slate-900 mb-1 flex items-center gap-2">
            <i data-lucide="download" class="w-4 h-4 text-green-600"></i>
            Export My Data
        </h2>
        <p class="text-sm text-slate-500 mb-4">Apna poora shop data (sales, udhar, products, expenses — sab kuch) CSV files ki ZIP mein download karein.</p>
        <a href="{{ route('data.export') }}"
           class="inline-flex items-center gap-2 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
            <i data-lucide="archive" class="w-4 h-4"></i>Download ZIP
        </a>
    </div>

    <!-- Feature Toggles -->
    <form method="POST" action="{{ route('settings.update') }}" class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
        @csrf
        <input type="hidden" name="features_form" value="1">
        <div class="flex items-center justify-between mb-1">
            <h2 class="font-semibold text-slate-900 flex items-center gap-2">
                <i data-lucide="toggle-right" class="w-4 h-4 text-green-600"></i>
                Features On / Off
            </h2>
            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">{{ __('pages.save_features') }}</button>
        </div>
        <p class="text-xs text-slate-400 mb-4">Jo feature aapki dukaan ko nahi chahiye usko off kar dein — sidebar aur buttons se ghayab ho jayega.</p>
        @php $shopTypeForFeatures = tenant()->shop_type ?? 'general'; @endphp
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            @foreach(config('features') as $fKey => $f)
                @continue($f['shop_types'] !== null && !in_array($shopTypeForFeatures, $f['shop_types']))
                @php $planLocked = !plan_allows($fKey); @endphp
                @if($planLocked)
                <div class="flex items-start gap-3 p-3 rounded-lg border border-slate-200 bg-slate-50 opacity-70">
                    <i data-lucide="lock" class="mt-0.5 w-4 h-4 text-slate-400 flex-shrink-0"></i>
                    <span class="flex-1 min-w-0">
                        <span class="flex items-center gap-2 text-sm font-medium text-slate-500">
                            <i data-lucide="{{ $f['icon'] }}" class="w-3.5 h-3.5 text-slate-400"></i>{{ $f['label'] }}
                            <span class="text-[10px] font-bold uppercase bg-amber-100 text-amber-700 px-1.5 py-0.5 rounded-full">{{ ucfirst($f['min_plan'] ?? 'pro') }} plan</span>
                        </span>
                        <span class="block text-xs text-slate-400 mt-0.5">Ye feature {{ ucfirst($f['min_plan'] ?? 'pro') }} plan mein milta hai — upgrade ke liye admin se raabta karein.</span>
                    </span>
                </div>
                @else
                <label class="flex items-start gap-3 p-3 rounded-lg border border-slate-200 hover:border-green-300 hover:bg-green-50/40 cursor-pointer transition-colors">
                    <input type="checkbox" name="features[]" value="{{ $fKey }}"
                           @checked(feature_enabled($fKey))
                           class="mt-0.5 w-4 h-4 rounded border-slate-300 text-green-600 focus:ring-green-500">
                    <span class="flex-1 min-w-0">
                        <span class="flex items-center gap-2 text-sm font-medium text-slate-800">
                            <i data-lucide="{{ $f['icon'] }}" class="w-3.5 h-3.5 text-slate-400"></i>{{ $f['label'] }}
                        </span>
                        <span class="block text-xs text-slate-400 mt-0.5">{{ $f['description'] }}</span>
                    </span>
                </label>
                @endif
            @endforeach
        </div>
    </form>

    <form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data" class="space-y-5">
        @csrf
        <input type="hidden" name="company_form" value="1">

        <!-- Company Info -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
            <h2 class="font-semibold text-slate-900 mb-4 flex items-center gap-2">
                <i data-lucide="building-2" class="w-4 h-4 text-green-600"></i>
                Company Information
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('pages.company_name') }}</label>
                    <input type="text" name="company_name" value="{{ $settings['company_name'] ?? '' }}"
                           class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none"
                           placeholder="InvoicePro Ltd.">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('pages.company_email') }}</label>
                    <input type="email" name="company_email" value="{{ $settings['company_email'] ?? '' }}"
                           class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none"
                           placeholder="contact@company.pk">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('common.phone') }}</label>
                    <input type="text" name="company_phone" value="{{ $settings['company_phone'] ?? '' }}"
                           class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none"
                           placeholder="+92 21 1234567">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('pages.logo') }}</label>
                    <input type="file" name="logo" accept="image/*"
                           class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
                    @if(!empty($settings['logo_path']))
                    <div class="mt-2 flex items-center gap-3">
                      <img src="{{ tenant_asset($settings['logo_path']) }}" alt="Logo"
                           class="h-12 w-12 object-contain rounded-lg border border-slate-200 bg-white p-1">
                      <span class="text-xs text-slate-400">{{ __('pages.current_logo') }}</span>
                    </div>
                    @endif
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('pages.address') }}</label>
                    <textarea name="company_address" rows="2"
                              class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none resize-none"
                              placeholder="Company address...">{{ $settings['company_address'] ?? '' }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('pages.currency') }}</label>
                    <div class="relative">
                      <select name="currency" class="appearance-none w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none bg-white pr-8">
                        @foreach(['PKR'=>'PKR — Pakistani Rupee','USD'=>'USD — US Dollar','EUR'=>'EUR — Euro'] as $val=>$label)
                        <option value="{{ $val }}" @selected(($settings['currency'] ?? 'PKR')===$val)>{{ $label }}</option>
                        @endforeach
                      </select>
                      <i data-lucide="chevron-down" class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('pages.ntn') }}</label>
                    <input type="text" name="ntn_number" value="{{ $settings['ntn_number'] ?? '' }}"
                           class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none"
                           placeholder="1234567-8">
                    <p class="text-xs text-slate-400 mt-1">{{ __('pages.fbr_note') }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('pages.sales_tax') }}</label>
                    <input type="number" name="sales_tax_percent" value="{{ $settings['sales_tax_percent'] ?? '' }}" min="0" max="100" step="0.01"
                           class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none"
                           placeholder="18">
                    <p class="text-xs text-slate-400 mt-1">{{ __('pages.fbr_note') }}</p>
                </div>
                <div class="sm:col-span-2">
                    <label class="flex items-start gap-3 p-3 rounded-lg border border-slate-200 hover:border-green-300 hover:bg-green-50/40 cursor-pointer transition-colors">
                        <input type="checkbox" name="hide_branding" value="1"
                               @checked(($settings['hide_branding'] ?? '0') === '1')
                               class="mt-0.5 w-4 h-4 rounded border-slate-300 text-green-600 focus:ring-green-500">
                        <span class="flex-1 min-w-0">
                            <span class="text-sm font-medium text-slate-800">Apni branding (receipt se 'Powered by ShopSaas' hatao)</span>
                            <span class="block text-xs text-slate-400 mt-0.5">On karne par receipts sirf aapki shop ki branding dikhayengi.</span>
                        </span>
                    </label>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Timezone</label>
                    <div class="relative">
                      <select name="timezone" class="appearance-none w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none bg-white pr-8">
                        <option value="Asia/Karachi" @selected(($settings['timezone'] ?? 'Asia/Karachi')==='Asia/Karachi')>Asia/Karachi (PKT)</option>
                        <option value="UTC" @selected(($settings['timezone'] ?? '')==='UTC')>UTC</option>
                        <option value="Asia/Dubai" @selected(($settings['timezone'] ?? '')==='Asia/Dubai')>Asia/Dubai</option>
                      </select>
                      <i data-lucide="chevron-down" class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
                    </div>
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
