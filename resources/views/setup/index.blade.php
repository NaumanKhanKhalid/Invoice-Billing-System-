<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Setup — {{ tenant()->shop_name }}</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
  <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
  @php $m = json_decode(file_get_contents(public_path('build/manifest.json')), true); @endphp
  <link rel="stylesheet" href="/build/{{ $m['resources/css/app.css']['file'] }}">
  <script type="module" src="/build/{{ $m['resources/js/app.js']['file'] }}" defer></script>
  <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="min-h-screen bg-gradient-to-br from-green-50 to-slate-100 flex items-center justify-center p-4">
<div class="w-full max-w-lg">

  {{-- Header --}}
  <div class="text-center mb-8">
    <div class="w-16 h-16 bg-green-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg">
      <i data-lucide="store" class="w-8 h-8 text-white"></i>
    </div>
    <h1 class="text-2xl font-bold text-slate-900">Welcome to ShopSaas!</h1>
    <p class="text-slate-500 mt-1">Quick setup — 1 minute baqi hai 🎉</p>
  </div>

  {{-- Steps indicator --}}
  <div class="flex items-center justify-center gap-2 mb-6">
    <div class="flex items-center gap-2">
      <div class="w-7 h-7 rounded-full bg-green-600 text-white flex items-center justify-center text-xs font-bold">1</div>
      <span class="text-xs text-green-600 font-medium">Shop Info</span>
    </div>
    <div class="w-8 h-0.5 bg-slate-200"></div>
    <div class="flex items-center gap-2">
      <div class="w-7 h-7 rounded-full bg-slate-200 text-slate-500 flex items-center justify-center text-xs font-bold">2</div>
      <span class="text-xs text-slate-400">Done!</span>
    </div>
  </div>

  <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-8">
    @if($errors->any())
    <div class="mb-4 text-sm text-red-600 bg-red-50 border border-red-200 rounded-lg px-3 py-2">
      {{ $errors->first() }}
    </div>
    @endif

    <form method="POST" action="{{ route('setup.store') }}" class="space-y-4">
      @csrf

      <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1">Dukan ka Naam (Company Name) *</label>
        <input type="text" name="company_name" value="{{ old('company_name', tenant()->shop_name) }}" required
               class="w-full px-3 py-2.5 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none @error('company_name') border-red-400 @enderror"
               placeholder="e.g. Al-Madina Chicken Shop">
        <p class="text-xs text-slate-400 mt-1">Yeh naam invoices aur reports pe show hoga</p>
      </div>

      <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1">Contact Number</label>
        <input type="text" name="company_phone" value="{{ old('company_phone', tenant()->owner_phone) }}"
               class="w-full px-3 py-2.5 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none"
               placeholder="03XX-XXXXXXX">
      </div>

      <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1">Address</label>
        <input type="text" name="company_address" value="{{ old('company_address') }}"
               class="w-full px-3 py-2.5 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none"
               placeholder="Shop #, Street, City">
      </div>

      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="block text-xs font-semibold text-slate-600 mb-1">Currency</label>
          <div class="relative">
            <select name="currency" class="appearance-none w-full px-3 py-2.5 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none bg-white pr-8">
              <option value="PKR" selected>PKR — Pakistani Rupee</option>
              <option value="USD">USD — US Dollar</option>
              <option value="EUR">EUR — Euro</option>
            </select>
            <i data-lucide="chevron-down" class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
          </div>
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-600 mb-1">Timezone</label>
          <div class="relative">
            <select name="timezone" class="appearance-none w-full px-3 py-2.5 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none bg-white pr-8">
              <option value="Asia/Karachi" selected>Asia/Karachi (PKT)</option>
              <option value="UTC">UTC</option>
              <option value="Asia/Dubai">Asia/Dubai</option>
            </select>
            <i data-lucide="chevron-down" class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
          </div>
        </div>
      </div>

      <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white py-3 rounded-xl text-sm font-bold transition-colors mt-2">
        Setup Mukammal Karo →
      </button>
    </form>
  </div>

  <p class="text-center text-xs text-slate-400 mt-4">Yeh settings baad mein bhi change ki ja sakti hain — Settings page se.</p>
</div>
<script>lucide.createIcons();</script>
</body>
</html>
