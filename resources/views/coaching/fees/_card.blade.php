{{-- Professional fee invoice — A4 document style (coaching) --}}
@php
  $acName  = \App\Models\Setting::getValue('company_name', tenant()->shop_name ?? config('app.name'));
  $acPhone = \App\Models\Setting::getValue('company_phone');
  $acAddr  = \App\Models\Setting::getValue('company_address');
  $acEmail = \App\Models\Setting::getValue('company_email');
  $acNtn   = \App\Models\Setting::getValue('ntn_number');
  $logoPath = \App\Models\Setting::getValue('logo_path');
  $lineTotal = $fee->amount_due;
  $statusMap = [
    'paid'    => ['Fully Paid','#16a34a'],
    'partial' => ['Partial','#d97706'],
    'unpaid'  => ['Unpaid','#dc2626'],
  ];
  [$stLabel,$stColor] = $statusMap[$fee->status] ?? ['—','#64748b'];
@endphp
<div id="receipt" class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden print:shadow-none print:border-0 mx-auto" style="max-width:44rem">

  {{-- Header --}}
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
        @if($acEmail)<p class="text-xs text-slate-500 leading-snug">{{ $acEmail }}</p>@endif
        @if($acNtn)<p class="text-xs text-slate-500 leading-snug">NTN: {{ $acNtn }}</p>@endif
      </div>
    </div>
    <div class="text-right shrink-0">
      <h1 class="text-3xl font-black tracking-tight text-green-700 leading-none">INVOICE</h1>
      <div class="mt-3 text-xs space-y-0.5">
        <p class="text-slate-400">Invoice No: <span class="font-bold text-slate-700 font-mono">{{ $fee->receipt_number }}</span></p>
        <p class="text-slate-400">Invoice Date: <span class="font-semibold text-slate-700">{{ \Carbon\Carbon::parse($fee->payment_date)->format('d M Y') }}</span></p>
      </div>
    </div>
  </div>

  {{-- Bill To / Details --}}
  <div class="px-8 grid grid-cols-2 gap-6 pb-6">
    <div>
      <p class="text-[11px] font-bold text-green-700 uppercase tracking-wider mb-1.5">Bill To</p>
      <p class="font-bold text-slate-900 text-sm">{{ $fee->student->name }}</p>
      @if($fee->student->phone)<p class="text-xs text-slate-500">{{ $fee->student->phone }}</p>@endif
      <p class="text-xs text-slate-500 mt-0.5">{{ $fee->student->batch->course->name }} · {{ $fee->student->batch->name }}</p>
    </div>
    <div class="text-right text-xs space-y-1">
      <div class="flex justify-end gap-2"><span class="text-slate-400">Fee Month:</span><span class="font-semibold text-slate-700">{{ \Carbon\Carbon::parse($fee->month)->format('F Y') }}</span></div>
      <div class="flex justify-end gap-2"><span class="text-slate-400">Payment Mode:</span><span class="font-semibold text-slate-700 capitalize">{{ $fee->payment_method ?? 'cash' }}</span></div>
      <div class="flex justify-end items-center gap-2">
        <span class="text-slate-400">Status:</span>
        <span class="font-bold px-2 py-0.5 rounded-full text-[10px] uppercase tracking-wide text-white" style="background:{{ $stColor }}">{{ $stLabel }}</span>
      </div>
    </div>
  </div>

  {{-- Line items table --}}
  <div class="px-8">
    <table class="w-full text-sm">
      <thead>
        <tr style="background:#16a34a;color:#fff">
          <th class="px-3 py-2.5 text-left text-xs font-bold rounded-l-lg">#</th>
          <th class="px-3 py-2.5 text-left text-xs font-bold">Description</th>
          <th class="px-3 py-2.5 text-center text-xs font-bold">Qty</th>
          <th class="px-3 py-2.5 text-right text-xs font-bold">Unit Price</th>
          <th class="px-3 py-2.5 text-right text-xs font-bold rounded-r-lg">Total</th>
        </tr>
      </thead>
      <tbody>
        <tr class="border-b border-slate-100">
          <td class="px-3 py-3 text-slate-500">1</td>
          <td class="px-3 py-3">
            <p class="font-semibold text-slate-800">Tuition Fee — {{ \Carbon\Carbon::parse($fee->month)->format('F Y') }}</p>
            <p class="text-xs text-slate-400">{{ $fee->student->batch->course->name }} ({{ $fee->student->batch->name }})</p>
          </td>
          <td class="px-3 py-3 text-center text-slate-600">1</td>
          <td class="px-3 py-3 text-right text-slate-700 tabular-nums">{{ number_format($fee->amount_due) }}</td>
          <td class="px-3 py-3 text-right font-semibold text-slate-900 tabular-nums">{{ number_format($lineTotal) }}</td>
        </tr>
      </tbody>
    </table>
  </div>

  {{-- Totals --}}
  <div class="px-8 pt-4 flex justify-end">
    <div class="w-full sm:w-72 text-sm">
      <div class="flex justify-between py-1.5"><span class="text-slate-500">Subtotal</span><span class="text-slate-700 tabular-nums">PKR {{ number_format($fee->amount_due) }}</span></div>
      @if($fee->discount_amount > 0)
      <div class="flex justify-between py-1.5 text-green-600"><span>Discount</span><span class="tabular-nums">− PKR {{ number_format($fee->discount_amount) }}</span></div>
      @endif
      @if($fee->balance_due > 0)
      <div class="flex justify-between py-1.5 border-t border-slate-100 mt-1"><span class="text-slate-500">Amount Paid</span><span class="text-slate-700 tabular-nums">PKR {{ number_format($fee->amount_paid) }}</span></div>
      <div class="flex justify-between items-center mt-1.5 px-3 py-2.5 rounded-lg text-white" style="background:#dc2626">
        <span class="font-bold text-sm">Balance Due</span>
        <span class="font-extrabold text-lg tabular-nums">PKR {{ number_format($fee->balance_due) }}</span>
      </div>
      @else
      <div class="flex justify-between items-center py-2 border-t-2 border-slate-200 mt-1">
        <span class="font-bold text-slate-800">Total Paid</span>
        <span class="font-extrabold text-lg text-green-700 tabular-nums">PKR {{ number_format($fee->amount_paid) }}</span>
      </div>
      @endif
    </div>
  </div>

  {{-- Payment history (installments) --}}
  @php
    $payRows = ($fee->relationLoaded('payments') && $fee->payments->count())
      ? $fee->payments
      : ($fee->amount_paid > 0
          ? collect([(object) ['paid_on' => $fee->payment_date, 'method' => $fee->payment_method ?? 'cash', 'amount' => $fee->amount_paid]])
          : collect());
  @endphp
  @if($payRows->count() > 1)
  <div class="px-8 pt-5">
    <div class="flex items-center gap-2 mb-2">
      <p class="text-xs font-bold text-slate-700 uppercase tracking-wider">Payment History</p>
      <span class="text-[11px] font-semibold text-slate-400">{{ $payRows->count() }} {{ $payRows->count() == 1 ? 'payment' : 'payments' }}</span>
    </div>
    <div class="rounded-xl border border-slate-200 overflow-hidden">
      <table class="w-full text-xs">
        <thead>
          <tr class="bg-slate-100 text-slate-600 border-b border-slate-200">
            <th class="px-3 py-2.5 text-left font-bold w-8">#</th>
            <th class="px-3 py-2.5 text-left font-bold">Date</th>
            <th class="px-3 py-2.5 text-left font-bold">Method</th>
            <th class="px-3 py-2.5 text-right font-bold">Amount</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          @foreach($payRows as $i => $p)
          <tr class="{{ $i % 2 ? 'bg-slate-50/40' : 'bg-white' }}">
            <td class="px-3 py-2.5 text-slate-400 font-semibold">{{ $i + 1 }}</td>
            <td class="px-3 py-2.5 text-slate-700 font-medium whitespace-nowrap">{{ $p->paid_on ? \Carbon\Carbon::parse($p->paid_on)->format('d M Y') : '—' }}</td>
            <td class="px-3 py-2.5 text-slate-600 capitalize">{{ $p->method }}</td>
            <td class="px-3 py-2.5 text-right font-bold text-slate-900 tabular-nums">PKR {{ number_format($p->amount) }}</td>
          </tr>
          @endforeach
        </tbody>
        <tfoot>
          <tr class="bg-green-50 border-t-2 border-green-200">
            <td class="px-3 py-2.5 font-bold text-green-800" colspan="3">Total Paid</td>
            <td class="px-3 py-2.5 text-right font-extrabold text-green-700 tabular-nums">PKR {{ number_format($payRows->sum('amount')) }}</td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>
  @endif

  @if($fee->notes)
  <div class="px-8 pt-4">
    <div class="bg-slate-50 rounded-lg p-3 text-xs text-slate-600"><span class="font-semibold">Note: </span>{{ $fee->notes }}</div>
  </div>
  @endif

  {{-- Signatures --}}
  <div class="px-8 py-8 flex items-end justify-between gap-8">
    <div class="text-center">
      <div class="w-40 border-t border-slate-300"></div>
      <p class="text-[11px] text-slate-500 mt-1">Issued by</p>
    </div>
    <div class="text-center">
      <div class="w-40 border-t border-slate-300"></div>
      <p class="text-[11px] text-slate-500 mt-1">Received / Signature</p>
    </div>
  </div>

  <div class="bg-slate-50 border-t border-slate-100 px-8 py-3 text-center">
    <p class="text-[11px] text-slate-400">Shukriya! Ye receipt apne paas mehfooz rakhein.</p>
  </div>
</div>

<style>
@media print {
  @page { size: A4 portrait; margin: 12mm; }
  aside, nav, header, footer, .print\:hidden, .no-print { display: none !important; }
  body, #main-content { background: #fff !important; padding: 0 !important; margin: 0 !important; }
  /* Hide everything, then reveal only the receipt — in NORMAL flow so a tall
     invoice paginates correctly top-to-bottom (no reversed / duplicated pages). */
  body * { visibility: hidden; }
  #receipt, #receipt * { visibility: visible; }
  #receipt {
    position: static !important; margin: 0 auto !important;
    width: 100% !important; max-width: 100% !important;
    border: none !important; border-radius: 0 !important; box-shadow: none !important;
    filter: none !important;
  }
  #receipt * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
}
</style>
