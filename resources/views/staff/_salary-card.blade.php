{{-- Professional salary invoice — A4 document style --}}
@php
  $acName  = \App\Models\Setting::getValue('company_name', tenant()->shop_name ?? config('app.name'));
  $acPhone = \App\Models\Setting::getValue('company_phone');
  $acAddr  = \App\Models\Setting::getValue('company_address');
  $acEmail = \App\Models\Setting::getValue('company_email');
  $acNtn   = \App\Models\Setting::getValue('ntn_number');
  $logoPath = \App\Models\Setting::getValue('logo_path');
  $slipNo  = 'SAL-' . str_pad((string) $payment->id, 4, '0', STR_PAD_LEFT);

  $basic   = (float) ($staff->salary ?? 0);
  $paid    = (float) $payment->amount;
  $diff    = $basic - $paid;                 // >0 => deduction, <0 => bonus
  $bonus   = $diff < 0 ? abs($diff) : 0;
  $deduct  = $diff > 0 ? $diff : 0;
  $grossEarnings = $basic + $bonus;          // Basic + Bonus

  // Build line items like the fee invoice
  $items = [];
  $items[] = ['desc' => 'Basic Salary', 'sub' => \Carbon\Carbon::createFromDate($payment->year, $payment->month, 1)->format('F Y'), 'amount' => $basic];
  if ($bonus > 0) {
    $items[] = ['desc' => 'Bonus / Extra', 'sub' => 'Additional earning', 'amount' => $bonus];
  }
@endphp
<div id="slip" class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden print:shadow-none print:border-0 mx-auto" style="max-width:44rem">

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
      <h1 class="text-3xl font-black tracking-tight text-green-700 leading-none">SALARY</h1>
      <div class="mt-3 text-xs space-y-0.5">
        <p class="text-slate-400">Slip No: <span class="font-bold text-slate-700 font-mono">{{ $slipNo }}</span></p>
        <p class="text-slate-400">Pay Date: <span class="font-semibold text-slate-700">{{ \Carbon\Carbon::parse($payment->payment_date)->format('d M Y') }}</span></p>
      </div>
    </div>
  </div>

  {{-- Paid To / Details --}}
  <div class="px-8 grid grid-cols-2 gap-6 pb-6">
    <div>
      <p class="text-[11px] font-bold text-green-700 uppercase tracking-wider mb-1.5">Paid To</p>
      <p class="font-bold text-slate-900 text-sm">{{ $staff->name }}</p>
      <p class="text-xs text-slate-500 capitalize">{{ $staff->role ?: '—' }}</p>
      @if($staff->phone)<p class="text-xs text-slate-500">{{ $staff->phone }}</p>@endif
    </div>
    <div class="text-right text-xs space-y-1">
      <div class="flex justify-end gap-2"><span class="text-slate-400">Pay Period:</span><span class="font-semibold text-slate-700">{{ \Carbon\Carbon::createFromDate($payment->year, $payment->month, 1)->format('F Y') }}</span></div>
      <div class="flex justify-end gap-2"><span class="text-slate-400">Payment Date:</span><span class="font-semibold text-slate-700">{{ \Carbon\Carbon::parse($payment->payment_date)->format('d M Y') }}</span></div>
      <div class="flex justify-end items-center gap-2">
        <span class="text-slate-400">Status:</span>
        <span style="color:#16a34a;font-size:11px;font-weight:800;letter-spacing:0.04em;text-transform:uppercase;white-space:nowrap;">Paid</span>
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
          <th class="px-3 py-2.5 text-right text-xs font-bold rounded-r-lg">Amount</th>
        </tr>
      </thead>
      <tbody>
        @foreach($items as $i => $it)
        <tr class="border-b border-slate-100">
          <td class="px-3 py-3 text-slate-500">{{ $i + 1 }}</td>
          <td class="px-3 py-3">
            <p class="font-semibold text-slate-800">{{ $it['desc'] }}</p>
            <p class="text-xs text-slate-400">{{ $it['sub'] }}</p>
          </td>
          <td class="px-3 py-3 text-right font-semibold text-slate-900 tabular-nums">{{ number_format($it['amount']) }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  {{-- Totals --}}
  <div class="px-8 pt-4 flex justify-end">
    <div class="w-full sm:w-72 text-sm">
      <div class="flex justify-between py-1.5"><span class="text-slate-500">Gross Earnings</span><span class="text-slate-700 tabular-nums">PKR {{ number_format($grossEarnings) }}</span></div>
      @if($deduct > 0)
      <div class="flex justify-between py-1.5 text-slate-600 border-t border-slate-100 mt-1"><span>Deduction / Advance</span><span class="tabular-nums">− PKR {{ number_format($deduct) }}</span></div>
      @endif
      <div class="flex justify-between items-center mt-1.5 px-3 py-2.5 rounded-lg text-white" style="background:#16a34a">
        <span class="font-bold text-sm">Net Pay</span>
        <span class="font-extrabold text-lg tabular-nums">PKR {{ number_format($paid) }}</span>
      </div>
    </div>
  </div>

  @if($payment->note)
  <div class="px-8 pt-4">
    <div class="bg-slate-50 rounded-lg p-3 text-xs text-slate-600"><span class="font-semibold">Note: </span>{{ $payment->note }}</div>
  </div>
  @endif

  {{-- Signatures --}}
  <div class="px-8 py-8 flex items-end justify-between gap-8">
    <div class="text-center">
      <div class="w-40 border-t border-slate-300"></div>
      <p class="text-[11px] text-slate-500 mt-1">Employer</p>
    </div>
    <div class="text-center">
      <div class="w-40 border-t border-slate-300"></div>
      <p class="text-[11px] text-slate-500 mt-1">Employee / Signature</p>
    </div>
  </div>

  <div class="bg-slate-50 border-t border-slate-100 px-8 py-3 text-center">
    <p class="text-[11px] text-slate-400">This is a record of salary payment. Keep this slip for your records.</p>
  </div>
</div>

<style>
@media print {
  @page { size: A4 portrait; margin: 12mm; }
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
