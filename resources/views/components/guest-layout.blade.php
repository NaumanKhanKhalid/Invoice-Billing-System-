<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        $isTenant = tenancy()->initialized;
        $shopName  = $isTenant ? (tenant()->shop_name ?? config('app.name')) : config('app.name', 'ShopSaas');
        $shopType  = $isTenant ? (tenant()->shop_type ?? '') : '';
        $typeIcons = ['chicken'=>'🍗','bike'=>'🏍️','hardware'=>'🔧','mobile'=>'📱','general'=>'🏪'];
        $shopIcon  = $typeIcons[$shopType] ?? '🏪';
    @endphp
    <title>{{ $isTenant ? $shopName : 'Admin' }} — ShopSaas</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="min-h-screen bg-slate-50 flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        {{-- Logo / Brand --}}
        <div class="flex items-center justify-center gap-3 mb-8">
            @if($isTenant)
            <div class="w-12 h-12 bg-green-600 rounded-xl flex items-center justify-center text-white text-2xl shadow-md">
                {{ $shopIcon }}
            </div>
            <div class="text-left">
                <p class="text-xl font-bold text-slate-900">{{ $shopName }}</p>
                @if($shopType)<p class="text-xs text-slate-400 capitalize">{{ $shopType }} Shop — Billing System</p>@endif
            </div>
            @else
            <div class="w-10 h-10 bg-green-600 rounded-xl flex items-center justify-center shadow-md">
                <span class="text-white font-bold text-lg">S</span>
            </div>
            <span class="text-2xl font-bold text-slate-900">ShopSaas</span>
            @endif
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8">
            {{ $slot }}
        </div>

        @if($isTenant)
        <p class="text-center text-xs text-slate-400 mt-6">Powered by <span class="font-semibold text-green-600">ShopSaas</span></p>
        @endif
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>
