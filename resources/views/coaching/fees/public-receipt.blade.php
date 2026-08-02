<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Fee Receipt — {{ $fee->receipt_number }}</title>
    <link rel="icon" href="{{ shop_favicon() }}">
    @php $m = json_decode(file_get_contents(public_path('build/manifest.json')), true); @endphp
    <link rel="stylesheet" href="/build/{{ $m['resources/css/app.css']['file'] }}">
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
    <style>body{background:#f1f5f9;font-family:ui-sans-serif,system-ui,sans-serif;}</style>
</head>
<body class="min-h-screen py-8 px-4">
  <div class="max-w-3xl mx-auto">
    <div class="flex justify-end gap-2 mb-3 print:hidden">
      <button type="button" onclick="downloadReceiptPdf()"
              class="inline-flex items-center gap-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 px-3 py-2 rounded-lg text-sm font-medium shadow-sm">
        <i data-lucide="file-text" class="w-4 h-4"></i>PDF
      </button>
      <button type="button" onclick="saveReceiptImage()"
              class="inline-flex items-center gap-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 px-3 py-2 rounded-lg text-sm font-medium shadow-sm">
        <i data-lucide="download" class="w-4 h-4"></i>Image
      </button>
    </div>

    @include('coaching.fees._card')

    <p class="text-center text-xs text-slate-400 mt-4 print:hidden">Powered by {{ config('app.name', 'ShopSaas') }}</p>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
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
    async function downloadReceiptPdf() {
      const el = document.getElementById('receipt');
      if (!el || typeof html2canvas === 'undefined' || !window.jspdf) { window.print(); return; }
      const canvas = await html2canvas(el, { scale: 2, backgroundColor: '#ffffff', useCORS: true });
      const img = canvas.toDataURL('image/png');
      const { jsPDF } = window.jspdf;
      const pdf = new jsPDF({ orientation: 'portrait', unit: 'pt', format: 'a4' });
      const pw = pdf.internal.pageSize.getWidth(), ph = pdf.internal.pageSize.getHeight();
      const iw = pw - 40, ih = canvas.height * iw / canvas.width;
      pdf.addImage(img, 'PNG', 20, 20, iw, Math.min(ih, ph - 40));
      pdf.save('receipt-{{ $fee->receipt_number }}.pdf');
    }
  </script>
</body>
</html>
