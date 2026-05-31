<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'InvoicePro') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="min-h-screen bg-slate-50 flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <!-- Logo -->
        <div class="flex items-center justify-center gap-3 mb-8">
            <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center">
                <i data-lucide="zap" class="w-5 h-5 text-white"></i>
            </div>
            <span class="text-2xl font-bold text-slate-900">InvoicePro</span>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8">
            {{ $slot }}
        </div>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>
