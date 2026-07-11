<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Fee Receipt — {{ $fee->receipt_number }}</title>
    @php
        $faviconLogo = \App\Models\Setting::getValue('logo_path');
    @endphp
    @if($faviconLogo)
    <link rel="icon" href="{{ tenant_asset($faviconLogo) }}">
    @else
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    @endif
    @php $m = json_decode(file_get_contents(public_path('build/manifest.json')), true); @endphp
    <link rel="stylesheet" href="/build/{{ $m['resources/css/app.css']['file'] }}">
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
    <style>body{background:#f1f5f9;font-family:ui-sans-serif,system-ui,sans-serif;}</style>
</head>
<body class="min-h-screen py-8 px-4">
  <div class="max-w-md mx-auto">
    <div class="flex justify-end mb-3 print:hidden">
      <button type="button" onclick="saveReceiptImage()"
              class="inline-flex items-center gap-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 px-3 py-2 rounded-lg text-sm font-medium shadow-sm">
        <i data-lucide="download" class="w-4 h-4"></i>Save Image
      </button>
    </div>

    @include('coaching.fees._card')

    <p class="text-center text-xs text-slate-400 mt-4 print:hidden">Powered by {{ config('app.name', 'ShopSaas') }}</p>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
  <script>
    if (window.lucide) lucide.createIcons({ icons: lucide.icons });
    async function saveReceiptImage() {
      const el = document.getElementById('receipt');
      if (!el || typeof html2canvas === 'undefined') { window.print(); return; }
      const canvas = await html2canvas(el, { scale: 2, backgroundColor: '#ffffff', useCORS: true });
      const link = document.createElement('a');
      link.download = 'receipt-{{ $fee->receipt_number }}.png';
      link.href = canvas.toDataURL('image/png');
      link.click();
    }
  </script>
</body>
</html>
