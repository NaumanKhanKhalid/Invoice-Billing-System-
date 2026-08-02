{{-- Salary payslip document (Earnings / Deductions / Net Pay) --}}
@php
  $acName  = \App\Models\Setting::getValue('company_name', tenant()->shop_name ?? config('app.name'));
  $acPhone = \App\Models\Setting::getValue('company_phone');
  $acAddr  = \App\Models\Setting::getValue('company_address');
  $logoPath = \App\Models\Setting::getValue('logo_path');
  $slipNo  = 'SAL-' . str_pad((string) $payment->id, 4, '0', STR_PAD_LEFT);

  $basic   = (float) ($staff->salary ?? 0);
  $paid    = (float) $payment->amount;
  $diff    = $basic - $paid;                 // >0 => deduction, <0 => bonus
  $bonus   = $diff < 0 ? abs($diff) : 0;
  $deduct  = $diff > 0 ? $diff : 0;
  $grossEarnings = $basic + $bonus;          // Basic + Bonus
@endphp
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

  {{-- Employee info strip --}}
  <div class="mx-8 mb-6 rounded-xl border border-slate-200 overflow-hidden">
    <div class="grid grid-cols-2 sm:grid-cols-4 divide-x divide-slate-100 text-xs">
      <div class="px-4 py-3">
        <p class="text-slate-400 mb-0.5">Employee</p>
        <p class="font-bold text-slate-800">{{ $staff->name }}</p>
      </div>
      <div class="px-4 py-3">
        <p class="text-slate-400 mb-0.5">Designation</p>
        <p class="font-semibold text-slate-700 capitalize">{{ $staff->role ?: '—' }}</p>
      </div>
      <div class="px-4 py-3 border-t sm:border-t-0 border-slate-100">
        <p class="text-slate-400 mb-0.5">Pay Period</p>
        <p class="font-semibold text-slate-700">{{ \Carbon\Carbon::createFromDate($payment->year, $payment->month, 1)->format('F Y') }}</p>
      </div>
      <div class="px-4 py-3 border-t sm:border-t-0 border-slate-100">
        <p class="text-slate-400 mb-0.5">Pay Date</p>
        <p class="font-semibold text-slate-700">{{ \Carbon\Carbon::parse($payment->payment_date)->format('d M Y') }}</p>
      </div>
    </div>
  </div>

  {{-- Earnings & Deductions --}}
  <div class="px-8 grid grid-cols-1 sm:grid-cols-2 gap-4">
    {{-- Earnings --}}
    <div class="rounded-xl border border-slate-200 overflow-hidden">
      <div class="px-4 py-2.5 text-xs font-bold uppercase tracking-wider text-white" style="background:#16a34a">Earnings</div>
      <table class="w-full text-sm">
        <tbody>
          <tr class="border-b border-slate-100">
            <td class="px-4 py-2.5 text-slate-600">Basic Salary</td>
            <td class="px-4 py-2.5 text-right font-semibold text-slate-800 tabular-nums">{{ number_format($basic) }}</td>
          </tr>
          @if($bonus > 0)
          <tr class="border-b border-slate-100">
            <td class="px-4 py-2.5 text-slate-600">Bonus / Extra</td>
            <td class="px-4 py-2.5 text-right font-semibold text-slate-800 tabular-nums">{{ number_format($bonus) }}</td>
          </tr>
          @endif
        </tbody>
        <tfoot>
          <tr style="background:#f0fdf4">
            <td class="px-4 py-2.5 font-bold text-green-800">Total Earnings</td>
            <td class="px-4 py-2.5 text-right font-extrabold text-green-700 tabular-nums">{{ number_format($grossEarnings) }}</td>
          </tr>
        </tfoot>
      </table>
    </div>
    {{-- Deductions --}}
    <div class="rounded-xl border border-slate-200 overflow-hidden">
      <div class="px-4 py-2.5 text-xs font-bold uppercase tracking-wider text-white" style="background:#64748b">Deductions</div>
      <table class="w-full text-sm">
        <tbody>
          @if($deduct > 0)
          <tr class="border-b border-slate-100">
            <td class="px-4 py-2.5 text-slate-600">Deduction / Advance</td>
            <td class="px-4 py-2.5 text-right font-semibold text-slate-800 tabular-nums">{{ number_format($deduct) }}</td>
          </tr>
          @else
          <tr class="border-b border-slate-100">
            <td class="px-4 py-2.5 text-slate-400 italic" colspan="2">No deductions</td>
          </tr>
          @endif
        </tbody>
        <tfoot>
          <tr style="background:#f8fafc">
            <td class="px-4 py-2.5 font-bold text-slate-700">Total Deductions</td>
            <td class="px-4 py-2.5 text-right font-extrabold text-slate-700 tabular-nums">{{ number_format($deduct) }}</td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>

  {{-- Net Pay --}}
  <div class="px-8 pt-4">
    <div class="flex items-center justify-between px-5 py-3.5 rounded-xl text-white" style="background:#16a34a">
      <div>
        <p class="text-[11px] uppercase tracking-wider font-semibold opacity-90">Net Pay</p>
        <p class="text-[11px] opacity-80">Total Earnings − Total Deductions</p>
      </div>
      <span class="text-2xl font-black tabular-nums">PKR {{ number_format($paid) }}</span>
    </div>
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
