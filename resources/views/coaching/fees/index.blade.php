@extends('layouts.app')
@section('title','Fee Collection')
@section('content')
<div class="space-y-6">

  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-2xl font-bold text-slate-900">Fee Collection</h1>
      <p class="text-sm text-slate-500 mt-0.5">{{ $monthDate->format('F Y') }}</p>
    </div>
    <form method="GET" class="flex items-center gap-2">
      <input type="month" name="month" value="{{ $month }}"
             class="border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
      <button type="submit" class="bg-slate-700 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-slate-800">Go</button>
    </form>
  </div>

  @if(session('success'))
  <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg text-sm">{{ session('success') }}</div>
  @endif

  {{-- Summary Cards --}}
  <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4 text-center">
      <p class="text-xs text-slate-400 font-semibold uppercase tracking-wider mb-1">Total</p>
      <p class="text-2xl font-bold text-slate-900">{{ $summary['total'] }}</p>
    </div>
    <div class="bg-white rounded-xl border border-green-200 shadow-sm p-4 text-center">
      <p class="text-xs text-green-600 font-semibold uppercase tracking-wider mb-1">Paid</p>
      <p class="text-2xl font-bold text-green-600">{{ $summary['paid'] }}</p>
    </div>
    <div class="bg-white rounded-xl border border-yellow-200 shadow-sm p-4 text-center">
      <p class="text-xs text-yellow-600 font-semibold uppercase tracking-wider mb-1">Partial</p>
      <p class="text-2xl font-bold text-yellow-600">{{ $summary['partial'] }}</p>
    </div>
    <div class="bg-white rounded-xl border border-red-200 shadow-sm p-4 text-center">
      <p class="text-xs text-red-500 font-semibold uppercase tracking-wider mb-1">Pending</p>
      <p class="text-2xl font-bold text-red-600">{{ $summary['pending'] }}</p>
    </div>
  </div>

  {{-- Amount Summary --}}
  <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
    <div class="grid grid-cols-3 gap-4">
      <div class="text-center p-3 bg-green-50 rounded-xl">
        <p class="text-xs text-green-600 font-semibold uppercase mb-1">Collected</p>
        <p class="text-xl font-bold text-green-700">PKR {{ number_format($summary['collected']) }}</p>
      </div>
      <div class="text-center p-3 bg-slate-50 rounded-xl">
        <p class="text-xs text-slate-500 font-semibold uppercase mb-1">Expected</p>
        <p class="text-xl font-bold text-slate-700">PKR {{ number_format($summary['expected']) }}</p>
      </div>
      <div class="text-center p-3 bg-red-50 rounded-xl">
        <p class="text-xs text-red-500 font-semibold uppercase mb-1">Remaining</p>
        <p class="text-xl font-bold text-red-600">PKR {{ number_format($summary['balance']) }}</p>
      </div>
    </div>
    @if($summary['expected'] > 0)
    <div class="mt-4">
      <div class="flex justify-between text-xs text-slate-500 mb-1">
        <span>Collection Progress</span>
        <span>{{ round(($summary['collected']/$summary['expected'])*100) }}%</span>
      </div>
      <div class="w-full bg-slate-100 rounded-full h-2.5">
        <div class="bg-green-500 h-2.5 rounded-full transition-all" style="width:{{ min(100, ($summary['collected']/$summary['expected'])*100) }}%"></div>
      </div>
    </div>
    @endif
  </div>

  {{-- Fee Table --}}
  <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="bg-slate-50 border-b border-slate-200">
          <tr>
            <th class="text-left px-4 py-3 font-semibold text-slate-600">Student</th>
            <th class="text-left px-4 py-3 font-semibold text-slate-600">Batch</th>
            <th class="text-right px-4 py-3 font-semibold text-slate-600">Due</th>
            <th class="text-right px-4 py-3 font-semibold text-slate-600">Paid</th>
            <th class="text-right px-4 py-3 font-semibold text-slate-600">Balance</th>
            <th class="text-center px-4 py-3 font-semibold text-slate-600">Status</th>
            <th class="text-right px-4 py-3 font-semibold text-slate-600">Action</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          @forelse($fees as $fee)
          <tr class="hover:bg-slate-50" x-data="{open:false}">
            <td class="px-4 py-3">
              <a href="{{ route('coaching.students.show', $fee->student) }}" class="font-medium text-slate-900 hover:text-blue-600">{{ $fee->student->name }}</a>
              @if($fee->payment_date)
              <p class="text-xs text-slate-400">{{ \Carbon\Carbon::parse($fee->payment_date)->format('d M') }} · {{ ucfirst($fee->payment_method ?? '') }}</p>
              @endif
            </td>
            <td class="px-4 py-3 text-slate-600 text-xs">
              {{ $fee->student->batch->name }}<br>
              <span class="text-slate-400">{{ $fee->student->batch->course->name }}</span>
            </td>
            <td class="px-4 py-3 text-right text-slate-600">{{ number_format($fee->amount_due) }}</td>
            <td class="px-4 py-3 text-right font-medium text-green-600">{{ number_format($fee->amount_paid) }}</td>
            <td class="px-4 py-3 text-right text-red-600">{{ number_format($fee->balance_due) }}</td>
            <td class="px-4 py-3 text-center">
              @if($fee->status === 'paid')
              <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full">Paid</span>
              @elseif($fee->status === 'partial')
              <span class="text-xs bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded-full">Partial</span>
              @else
              <span class="text-xs bg-red-100 text-red-600 px-2 py-0.5 rounded-full">Pending</span>
              @endif
            </td>
            <td class="px-4 py-3 text-right">
              <div class="flex items-center justify-end gap-2">
                @if($fee->status !== 'paid')
                <button @click="open=!open" class="inline-flex items-center gap-1 text-xs bg-blue-600 text-white px-3 py-1.5 rounded-lg hover:bg-blue-700 font-medium transition-colors">
                  <i data-lucide="banknote" class="w-3.5 h-3.5"></i>Collect
                </button>
                @else
                <a href="{{ route('coaching.fees.receipt', $fee) }}"
                   class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-medium transition-colors">
                  <i data-lucide="receipt" class="w-3.5 h-3.5"></i>Receipt
                </a>
                @endif
                @if($fee->student->phone && feature_enabled('whatsapp_share'))
                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $fee->student->phone) }}?text={{ urlencode('Assalam o Alaikum ' . $fee->student->name . ' — ' . $monthDate->format('F Y') . ' ki fees abhi tak nahi ayi. Please jald ada karein. Balance: PKR ' . number_format($fee->balance_due)) }}"
                   target="_blank" title="WhatsApp reminder"
                   class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-green-50 hover:bg-green-100 text-green-700 text-xs font-medium transition-colors">
                  <i data-lucide="message-circle" class="w-3.5 h-3.5"></i>WhatsApp
                </a>
                @endif
              </div>
              {{-- Collect Form --}}
              <div x-show="open" x-cloak class="mt-2 bg-slate-50 border border-slate-200 rounded-lg p-3 text-left">
                <form action="{{ route('coaching.fees.collect', $fee) }}" method="POST" class="space-y-2">
                  @csrf
                  <div class="grid grid-cols-2 gap-2">
                    <div>
                      <label class="text-xs text-slate-500">Amount (PKR)</label>
                      <input name="amount_paid" type="number" min="0" value="{{ $fee->balance_due }}"
                             class="w-full border border-slate-300 rounded px-2 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                    </div>
                    <div>
                      <label class="text-xs text-slate-500">Method</label>
                      <select name="payment_method" class="w-full border border-slate-300 rounded px-2 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                        <option value="cash">Cash</option>
                        <option value="jazzcash">JazzCash</option>
                        <option value="easypaisa">Easypaisa</option>
                        <option value="bank">Bank</option>
                        <option value="other">Other</option>
                      </select>
                    </div>
                  </div>
                  <div class="grid grid-cols-2 gap-2">
                    <div>
                      <label class="text-xs text-slate-500">Discount</label>
                      <input name="discount_amount" type="number" min="0" value="{{ $fee->discount_amount }}"
                             class="w-full border border-slate-300 rounded px-2 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                    </div>
                    <div>
                      <label class="text-xs text-slate-500">Notes</label>
                      <input name="notes" class="w-full border border-slate-300 rounded px-2 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500" placeholder="Optional">
                    </div>
                  </div>
                  <div class="flex gap-2 pt-1">
                    <button type="button" @click="open=false" class="flex-1 border border-slate-300 text-slate-600 rounded py-1 text-xs">Cancel</button>
                    <button type="submit" class="flex-1 bg-green-600 text-white rounded py-1 text-xs font-medium hover:bg-green-700">Record Payment</button>
                  </div>
                </form>
              </div>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="7" class="px-4 py-12 text-center text-slate-400">
              No fee records for this month. Active students are auto-listed when you visit this page.
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
