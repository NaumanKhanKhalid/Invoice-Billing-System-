@extends('layouts.app')
@section('title','Salary Slip')
@section('content')
@php
  $acName  = \App\Models\Setting::getValue('company_name', tenant()->shop_name ?? config('app.name'));
  $acPhone = \App\Models\Setting::getValue('company_phone');
  $acAddr  = \App\Models\Setting::getValue('company_address');
  $logoPath = \App\Models\Setting::getValue('logo_path');
  $slipNo  = 'SAL-' . str_pad((string) $payment->id, 4, '0', STR_PAD_LEFT);
@endphp
<div>
  {{-- Toolbar --}}
  <div class="flex items-center justify-between gap-3 mb-5 print:hidden">
    <div class="flex items-center gap-3 min-w-0">
      <a href="{{ route('staff.show', $staff) }}" class="w-9 h-9 rounded-lg border border-slate-200 bg-white flex items-center justify-center text-slate-500 hover:text-slate-700 shadow-sm shrink-0">
        <i data-lucide="arrow-left" class="w-4 h-4"></i>
      </a>
      <div class="min-w-0">
        <h1 class="text-lg font-bold text-slate-900 leading-tight">Salary Slip</h1>
        <p class="text-xs text-slate-400 font-mono">{{ $slipNo }}</p>
      </div>
    </div>
    <div class="flex items-center gap-2 shrink-0">
      <button type="button" onclick="downloadSlipPdf()" class="inline-flex items-center gap-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 px-3 py-2 rounded-lg text-sm font-medium">
        <i data-lucide="file-text" class="w-4 h-4"></i><span class="hidden sm:inline">PDF</span>
      </button>
      <button type="button" onclick="saveSlipImage()" class="inline-flex items-center gap-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 px-3 py-2 rounded-lg text-sm font-medium">
        <i data-lucide="image" class="w-4 h-4"></i><span class="hidden sm:inline">Image</span>
      </button>
      @if(feature_enabled('receipt_print'))
      <button onclick="window.print()" class="inline-flex items-center gap-2 bg-slate-800 hover:bg-slate-900 text-white px-4 py-2 rounded-lg text-sm font-medium">
        <i data-lucide="printer" class="w-4 h-4"></i>Print
      </button>
      @endif
    </div>
  </div>

  {{-- Document canvas --}}
  <div class="rounded-2xl px-4 sm:px-8 py-8 print:p-0 print:bg-transparent" style="background:linear-gradient(180deg,#eef2f7 0%,#e2e8f0 100%)">
    <div style="filter:drop-shadow(0 20px 35px rgba(15,23,42,0.15))">
      <div id="slip" class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden print:shadow-none print:border-0 mx-auto" style="max-width:40rem">

        {{-- Letterhead --}}
        <div class="px-8 pt-8 pb-6 flex items-start justify-between gap-6">
          <div class="flex items-start gap-3 min-w-0">
            @if($logoPath)
            <img src="{{ tenant_asset($logoPath) }}" alt="Logo" style="max-height:3.5rem;width:auto;border-radius:0.5rem;">
            @else
            <div class="w-12 h-12 rounded-xl bg-green-600 text-white flex items-center justify-center text-xl font-extrabold shrink-0">{{ mb_substr($acName,0,1) }}</div>
            @endif
            <div class="min-w-0">
              <h2 class="text-base font-extrabold text-slate-900 leading-tight">{{ $acName }}</h2>
              @if($acAddr)<p class="text-xs text-slate-500 leading-snug">{{ $acAddr }}</p>@endif
              @if($acPhone)<p class="text-xs text-slate-500 leading-snug">{{ $acPhone }}</p>@endif
            </div>
          </div>
          <div class="text-right shrink-0">
            <h1 class="text-3xl font-black tracking-tight text-green-700 leading-none">SALARY</h1>
            <p class="text-xs text-slate-400 mt-2">Slip No: <span class="font-bold text-slate-700 font-mono">{{ $slipNo }}</span></p>
            <p class="text-xs text-slate-400">Date: <span class="font-semibold text-slate-700">{{ \Carbon\Carbon::parse($payment->payment_date)->format('d M Y') }}</span></p>
          </div>
        </div>

        {{-- Paid To + details --}}
        <div class="px-8 grid grid-cols-2 gap-6 pb-6">
          <div>
            <p class="text-[11px] font-bold text-green-700 uppercase tracking-wider mb-1.5">Paid To</p>
            <p class="font-bold text-slate-900 text-sm">{{ $staff->name }}</p>
            <p class="text-xs text-slate-500 capitalize">{{ $staff->role }}</p>
            @if($staff->phone)<p class="text-xs text-slate-500">{{ $staff->phone }}</p>@endif
          </div>
          <div class="text-right text-xs space-y-1">
            <div class="flex justify-end gap-2"><span class="text-slate-400">Salary Month:</span><span class="font-semibold text-slate-700">{{ \Carbon\Carbon::createFromDate($payment->year, $payment->month, 1)->format('F Y') }}</span></div>
            <div class="flex justify-end gap-2"><span class="text-slate-400">Payment Date:</span><span class="font-semibold text-slate-700">{{ \Carbon\Carbon::parse($payment->payment_date)->format('d M Y') }}</span></div>
          </div>
        </div>

        {{-- Earnings table (full-width, with Total row) --}}
        <div class="px-8">
          <table class="w-full text-sm">
            <thead>
              <tr style="background:#16a34a;color:#fff">
                <th class="px-4 py-2.5 text-left text-xs font-bold rounded-l-lg">Description</th>
                <th class="px-4 py-2.5 text-right text-xs font-bold rounded-r-lg">Amount</th>
              </tr>
            </thead>
            <tbody>
              <tr class="border-b border-slate-100">
                <td class="px-4 py-3">
                  <p class="font-semibold text-slate-800">Salary — {{ \Carbon\Carbon::createFromDate($payment->year, $payment->month, 1)->format('F Y') }}</p>
                  <p class="text-xs text-slate-400 capitalize">{{ $staff->role }}</p>
                </td>
                <td class="px-4 py-3 text-right font-semibold text-slate-900 tabular-nums">PKR {{ number_format($payment->amount) }}</td>
              </tr>
            </tbody>
            <tfoot>
              <tr>
                <td class="px-4 py-3 font-bold text-green-800" style="background:#f0fdf4">Total Paid</td>
                <td class="px-4 py-3 text-right font-extrabold text-lg text-green-700 tabular-nums" style="background:#f0fdf4">PKR {{ number_format($payment->amount) }}</td>
              </tr>
            </tfoot>
          </table>
        </div>

        @if($payment->note)
        <div class="px-8 pt-4">
          <div class="bg-slate-50 rounded-lg p-3 text-xs text-slate-600"><span class="font-semibold">Note: </span>{{ $payment->note }}</div>
        </div>
        @endif

        {{-- Signatures --}}
        <div class="px-8 pt-10 pb-6 flex items-end justify-between gap-8">
          <div class="text-center">
            <div class="w-40 border-t border-slate-300"></div>
            <p class="text-[11px] text-slate-500 mt-1">Employer</p>
          </div>
          <div class="text-center">
            <div class="w-40 border-t border-slate-300"></div>
            <p class="text-[11px] text-slate-500 mt-1">Employee</p>
          </div>
        </div>

        <div class="bg-slate-50 border-t border-slate-100 px-8 py-3 text-center">
          <p class="text-[11px] text-slate-400">This is a record of salary payment. Keep this slip for your records.</p>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
<script>
async function saveSlipImage() {
  const el = document.getElementById('slip');
  if (!el || typeof html2canvas === 'undefined') { window.print(); return; }
  const canvas = await html2canvas(el, { scale: 2, backgroundColor: '#ffffff', useCORS: true });
  const link = document.createElement('a');
  link.download = 'salary-{{ $slipNo }}.png';
  link.href = canvas.toDataURL('image/png');
  link.click();
}
async function downloadSlipPdf() {
  const el = document.getElementById('slip');
  if (!el || typeof html2canvas === 'undefined' || !window.jspdf) { window.print(); return; }
  const canvas = await html2canvas(el, { scale: 2, backgroundColor: '#ffffff', useCORS: true });
  const img = canvas.toDataURL('image/png');
  const { jsPDF } = window.jspdf;
  const pdf = new jsPDF({ orientation: 'portrait', unit: 'pt', format: 'a4' });
  const pw = pdf.internal.pageSize.getWidth(), ph = pdf.internal.pageSize.getHeight();
  const iw = pw - 40, ih = canvas.height * iw / canvas.width;
  pdf.addImage(img, 'PNG', 20, 20, iw, Math.min(ih, ph - 40));
  pdf.save('salary-{{ $slipNo }}.pdf');
}
</script>
<style>
@media print {
  @page { size: A5 portrait; margin: 10mm; }
  aside, nav, header, footer, .print\:hidden, .no-print { display: none !important; }
  body, #main-content { background: #fff !important; padding: 0 !important; margin: 0 !important; }
  body * { visibility: hidden; }
  #slip, #slip * { visibility: visible; }
  #slip {
    position: static !important; margin: 0 auto !important;
    width: 100% !important; max-width: 100% !important;
    border: none !important; border-radius: 0 !important; box-shadow: none !important; filter: none !important;
  }
  #slip * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
}
</style>
@endsection
