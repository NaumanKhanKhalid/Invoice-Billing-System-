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
  <form method="GET" class="flex flex-wrap gap-3 items-end">
    <div>
      <label class="block text-xs font-medium text-slate-500 mb-1">Month</label>
      <select name="month" class="px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none bg-white">
        @foreach($months as $num => $name)
        <option value="{{ $num }}" {{ $num == $month ? 'selected' : '' }}>{{ $name }}</option>
        @endforeach
      </select>
    </div>
    <div>
      <label class="block text-xs font-medium text-slate-500 mb-1">Year</label>
      <select name="year" class="px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none bg-white">
        @foreach($years as $y)
        <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
        @endforeach
      </select>
    </div>
    <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white px-5 py-2 rounded-lg text-sm font-medium">
      Show Report
    </button>
  </form>

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
