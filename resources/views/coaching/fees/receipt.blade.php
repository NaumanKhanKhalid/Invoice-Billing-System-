@extends('layouts.app')
@section('title','Fee Receipt')
@section('content')
<div class="max-w-3xl mx-auto">

  <div class="flex items-center justify-between mb-4 print:hidden">
    <a href="{{ route('coaching.fees.index') }}"
       class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-slate-700 transition-colors">
      <i data-lucide="arrow-left" class="w-4 h-4"></i>Back
    </a>
    <div class="flex items-center gap-2">
      @if($fee->student->phone && feature_enabled('whatsapp_share'))
      @php
        // Public link parent taps to open the real receipt in the browser
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
        <i data-lucide="message-circle" class="w-4 h-4"></i>WhatsApp
      </a>
      @endif
      <button type="button" onclick="saveReceiptImage()"
              class="inline-flex items-center gap-2 bg-slate-100 hover:bg-slate-200 text-slate-700 px-3 py-2 rounded-lg text-sm font-medium transition-colors">
        <i data-lucide="image" class="w-4 h-4"></i>Save Image
      </button>
      @if(feature_enabled('receipt_print'))
      <button onclick="window.print()" class="inline-flex items-center gap-2 bg-slate-800 hover:bg-slate-900 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
        <i data-lucide="printer" class="w-4 h-4"></i>Print
      </button>
      @endif
    </div>
  </div>

  @include('coaching.fees._card')

</div>

<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
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
</script>
@endsection
