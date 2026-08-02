@extends('layouts.app')
@section('title','Fee Receipt')
@section('content')
<div>

  {{-- Toolbar --}}
  <div class="flex items-center justify-between gap-3 mb-5 print:hidden">
    <div class="flex items-center gap-3 min-w-0">
      <a href="{{ route('coaching.fees.index') }}"
         class="w-9 h-9 rounded-lg border border-slate-200 bg-white flex items-center justify-center text-slate-500 hover:text-slate-700 shadow-sm shrink-0">
        <i data-lucide="arrow-left" class="w-4 h-4"></i>
      </a>
      <div class="min-w-0">
        <h1 class="text-lg font-bold text-slate-900 leading-tight">Fee Receipt</h1>
        <p class="text-xs text-slate-400 font-mono">{{ $fee->receipt_number }}</p>
      </div>
    </div>
    <div class="flex items-center gap-2 shrink-0">
      @if($fee->student->phone && feature_enabled('whatsapp_share'))
      @php
        $publicUrl = \Illuminate\Support\Facades\URL::signedRoute('coaching.fees.public-receipt', ['fee' => $fee->id]);
        $waText = urlencode("*Fee Receipt — {$fee->receipt_number}*\n"
          . ($fee->student->name) . "\n"
          . \Carbon\Carbon::parse($fee->month)->format('F Y') . " ki fees\n"
          . "Paid: PKR " . number_format($fee->amount_paid)
          . ($fee->balance_due > 0 ? "\nBalance: PKR " . number_format($fee->balance_due) : "")
          . "\n\nReceipt dekhein: " . $publicUrl
          . "\n\nShukriya! 🙏");
        $waPhone = wa_number($fee->student->phone);
      @endphp
      <a href="https://wa.me/{{ $waPhone }}?text={{ $waText }}" target="_blank"
         class="inline-flex items-center gap-2 bg-green-50 hover:bg-green-100 text-green-700 px-3 py-2 rounded-lg text-sm font-medium transition-colors">
        <i data-lucide="message-circle" class="w-4 h-4"></i><span class="hidden sm:inline">WhatsApp</span>
      </a>
      @endif
      <button type="button" onclick="downloadReceiptPdf()"
              class="inline-flex items-center gap-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 px-3 py-2 rounded-lg text-sm font-medium transition-colors">
        <i data-lucide="file-text" class="w-4 h-4"></i><span class="hidden sm:inline">PDF</span>
      </button>
      <button type="button" onclick="saveReceiptImage()"
              class="inline-flex items-center gap-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 px-3 py-2 rounded-lg text-sm font-medium transition-colors">
        <i data-lucide="image" class="w-4 h-4"></i><span class="hidden sm:inline">Image</span>
      </button>
      @if(feature_enabled('receipt_print'))
      <button onclick="window.print()" class="inline-flex items-center gap-2 bg-slate-800 hover:bg-slate-900 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
        <i data-lucide="printer" class="w-4 h-4"></i>Print
      </button>
      @endif
    </div>
  </div>

  {{-- Document canvas — makes the white invoice read like a real document preview --}}
  <div class="rounded-2xl px-4 sm:px-8 py-8 print:p-0 print:bg-transparent"
       style="background:linear-gradient(180deg,#eef2f7 0%,#e2e8f0 100%)">
    <div class="print:shadow-none" style="filter:drop-shadow(0 20px 35px rgba(15,23,42,0.15))">
      @include('coaching.fees._card')
    </div>
  </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
<script>
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
@endsection
