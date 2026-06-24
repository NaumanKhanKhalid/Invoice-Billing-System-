@extends('layouts.app')
@section('title','Udhar Report')
@section('content')
<div class="space-y-6">

  <div class="flex items-center justify-between">
    <div class="flex items-center gap-3">
      <a href="{{ route('udhar.index') }}" class="w-9 h-9 rounded-lg border border-slate-200 bg-white flex items-center justify-center text-slate-500 hover:text-slate-700 transition-colors shadow-sm">
        <i data-lucide="arrow-left" class="w-4 h-4"></i>
      </a>
      <div>
        <h1 class="text-xl font-bold text-slate-900">Udhar Monthly Report</h1>
        <p class="text-sm text-slate-500">{{ $months[$month] }} {{ $year }}</p>
      </div>
    </div>
  </div>

  {{-- Month/Year filter --}}
  <div class="bg-white rounded-xl border border-slate-200 shadow-sm px-4 py-3">
    <form method="GET" class="flex flex-wrap gap-4 items-center">
      <div class="flex items-center gap-2">
        <i data-lucide="calendar" class="w-4 h-4 text-slate-400 flex-shrink-0"></i>
        <div class="flex rounded-lg border border-slate-200 overflow-hidden">
          @foreach($months as $num => $name)
          <button type="submit" name="month" value="{{ $num }}"
                  onclick="document.querySelector('[name=year_hidden]').value=document.querySelector('select[name=year]').value"
                  class="px-3 py-1.5 text-xs font-medium border-r border-slate-200 last:border-r-0 transition-colors
                         {{ $num == $month ? 'bg-green-600 text-white' : 'text-slate-600 hover:bg-slate-50' }}">
            {{ substr($name, 0, 3) }}
          </button>
          @endforeach
        </div>
      </div>

      <div class="flex items-center gap-2 ml-auto">
        <select name="year" onchange="this.form.submit()"
                class="px-3 py-1.5 text-sm font-semibold border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none bg-white text-slate-700">
          @foreach($years as $y)
          <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
          @endforeach
        </select>
        <input type="hidden" name="month" value="{{ $month }}">
      </div>
    </form>
  </div>

  {{-- Summary cards --}}
  <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
    <div class="bg-white rounded-xl border border-red-200 shadow-sm p-5 text-center">
      <p class="text-xs text-red-500 font-semibold uppercase tracking-wider mb-1">Total Udhar Diya</p>
      <p class="text-2xl font-bold text-red-600">{{ formatCurrency($totalGiven) }}</p>
      <p class="text-xs text-slate-400 mt-1">New credit extended this month</p>
    </div>
    <div class="bg-white rounded-xl border border-green-200 shadow-sm p-5 text-center">
      <p class="text-xs text-green-600 font-semibold uppercase tracking-wider mb-1">Total Wapas Aaya</p>
      <p class="text-2xl font-bold text-green-600">{{ formatCurrency($paymentsThisMonth) }}</p>
      <p class="text-xs text-slate-400 mt-1">Payments received this month</p>
    </div>
    <div class="bg-{{ $netDue > 0 ? 'amber' : 'slate' }}-50 rounded-xl border border-{{ $netDue > 0 ? 'amber' : 'slate' }}-200 shadow-sm p-5 text-center">
      <p class="text-xs text-{{ $netDue > 0 ? 'amber' : 'slate' }}-600 font-semibold uppercase tracking-wider mb-1">Net Baqi</p>
      <p class="text-2xl font-bold text-{{ $netDue > 0 ? 'amber-600' : 'slate-400' }}">{{ formatCurrency($netDue) }}</p>
      <p class="text-xs text-slate-400 mt-1">Still outstanding from this month's sales</p>
    </div>
  </div>

  {{-- Customer breakdown --}}
  <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-100">
      <h2 class="font-semibold text-slate-900">Customer Breakdown — {{ $months[$month] }} {{ $year }}</h2>
    </div>
    @if($customerBreakdown->count())
    <table class="w-full">
      <thead class="bg-slate-50">
        <tr>
          <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Customer</th>
          <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Entries</th>
          <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Total Given</th>
          <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Received</th>
          <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Balance</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        @foreach($customerBreakdown as $row)
        <tr class="hover:bg-slate-50">
          <td class="px-4 py-3 text-sm font-medium text-slate-900">{{ $row['name'] }}</td>
          <td class="px-4 py-3 text-sm text-center text-slate-500">{{ $row['count'] }}</td>
          <td class="px-4 py-3 text-sm text-right text-slate-700">{{ formatCurrency($row['total_given']) }}</td>
          <td class="px-4 py-3 text-sm text-right text-green-600">{{ formatCurrency($row['total_received']) }}</td>
          <td class="px-4 py-3 text-sm text-right font-bold {{ $row['balance'] > 0 ? 'text-red-600' : 'text-slate-400' }}">
            {{ formatCurrency($row['balance']) }}
          </td>
        </tr>
        @endforeach
      </tbody>
      <tfoot class="bg-slate-50 border-t-2 border-slate-200">
        <tr>
          <td class="px-4 py-3 text-sm font-bold text-slate-900">Total</td>
          <td class="px-4 py-3 text-sm text-center font-semibold text-slate-600">{{ $customerBreakdown->sum('count') }}</td>
          <td class="px-4 py-3 text-sm text-right font-bold text-slate-800">{{ formatCurrency($totalGiven) }}</td>
          <td class="px-4 py-3 text-sm text-right font-bold text-green-600">{{ formatCurrency($totalReceived) }}</td>
          <td class="px-4 py-3 text-sm text-right font-bold text-red-600">{{ formatCurrency($netDue) }}</td>
        </tr>
      </tfoot>
    </table>
    @else
    <div class="px-4 py-12 text-center">
      <i data-lucide="file-x" class="w-10 h-10 text-slate-300 mx-auto mb-3"></i>
      <p class="text-slate-400 text-sm">No udhar records for {{ $months[$month] }} {{ $year }}</p>
    </div>
    @endif
  </div>

</div>
@endsection
