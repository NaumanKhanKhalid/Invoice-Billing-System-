@extends('layouts.app')
@section('title', $customer->name . ' — Ledger')
@section('content')
<div class="space-y-5">

  <div class="flex items-center gap-3">
    <a href="{{ route('customers.show', $customer) }}" class="w-9 h-9 rounded-lg border border-slate-200 bg-white flex items-center justify-center text-slate-500 hover:text-slate-700 shadow-sm">
      <i data-lucide="arrow-left" class="w-4 h-4"></i>
    </a>
    <div>
      <h1 class="text-xl font-bold text-slate-900">{{ $customer->name }} — Ledger</h1>
      <p class="text-sm text-slate-500">{{ $customer->phone }}</p>
    </div>
    <div class="ml-auto">
      <button onclick="window.print()" class="inline-flex items-center gap-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 px-3 py-2 rounded-lg text-sm font-medium transition-colors">
        <i data-lucide="printer" class="w-4 h-4"></i> Print
      </button>
    </div>
  </div>

  {{-- Summary Cards --}}
  <div class="grid grid-cols-3 gap-4">
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4 text-center">
      <p class="text-xs text-slate-500 uppercase tracking-wider font-medium">Total Invoiced</p>
      <p class="text-xl font-bold text-red-600 mt-1">PKR {{ number_format($totalDebit) }}</p>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4 text-center">
      <p class="text-xs text-slate-500 uppercase tracking-wider font-medium">Total Received</p>
      <p class="text-xl font-bold text-green-600 mt-1">PKR {{ number_format($totalCredit) }}</p>
    </div>
    <div class="bg-{{ $closingBalance > 0 ? 'red' : 'slate' }}-50 rounded-xl border border-{{ $closingBalance > 0 ? 'red' : 'slate' }}-200 shadow-sm p-4 text-center">
      <p class="text-xs text-{{ $closingBalance > 0 ? 'red' : 'slate' }}-500 uppercase tracking-wider font-medium">Balance Due</p>
      <p class="text-xl font-bold text-{{ $closingBalance > 0 ? 'red-600' : 'slate-500' }} mt-1">PKR {{ number_format($closingBalance) }}</p>
    </div>
  </div>

  {{-- Ledger Table --}}
  <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-100">
      <h2 class="font-semibold text-slate-900">Transaction History</h2>
    </div>
    <div class="overflow-x-auto">
      <table class="w-full">
        <thead class="bg-slate-50">
          <tr>
            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Date</th>
            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Description</th>
            <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Debit (Invoice)</th>
            <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Credit (Payment)</th>
            <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Balance</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          @forelse($ledger as $row)
          <tr class="hover:bg-slate-50">
            <td class="px-4 py-3 text-sm text-slate-500 whitespace-nowrap">{{ \Carbon\Carbon::parse($row['date'])->format('d M Y') }}</td>
            <td class="px-4 py-3 text-sm text-slate-700">
              @if($row['ref'])
                <a href="{{ $row['ref'] }}" class="hover:text-green-600">{{ $row['description'] }}</a>
              @else
                {{ $row['description'] }}
              @endif
            </td>
            <td class="px-4 py-3 text-sm text-right font-medium {{ $row['debit'] > 0 ? 'text-red-600' : 'text-slate-300' }}">
              {{ $row['debit'] > 0 ? 'PKR '.number_format($row['debit']) : '—' }}
            </td>
            <td class="px-4 py-3 text-sm text-right font-medium {{ $row['credit'] > 0 ? 'text-green-600' : 'text-slate-300' }}">
              {{ $row['credit'] > 0 ? 'PKR '.number_format($row['credit']) : '—' }}
            </td>
            <td class="px-4 py-3 text-sm text-right font-bold {{ $row['balance'] > 0 ? 'text-red-600' : ($row['balance'] < 0 ? 'text-green-600' : 'text-slate-400') }}">
              PKR {{ number_format(abs($row['balance'])) }}
              @if($row['balance'] < 0)<span class="text-xs font-normal">(Overpaid)</span>@endif
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="5" class="px-4 py-12 text-center text-slate-400 text-sm">
              <i data-lucide="book-open" class="w-10 h-10 text-slate-200 mx-auto mb-3"></i>
              No transactions found.
            </td>
          </tr>
          @endforelse
        </tbody>
        @if(count($ledger))
        <tfoot class="bg-slate-50 border-t-2 border-slate-200">
          <tr>
            <td colspan="2" class="px-4 py-3 text-sm font-bold text-slate-900">Closing Balance</td>
            <td class="px-4 py-3 text-sm text-right font-bold text-red-600">PKR {{ number_format($totalDebit) }}</td>
            <td class="px-4 py-3 text-sm text-right font-bold text-green-600">PKR {{ number_format($totalCredit) }}</td>
            <td class="px-4 py-3 text-sm text-right font-bold {{ $closingBalance > 0 ? 'text-red-600' : 'text-slate-500' }}">PKR {{ number_format($closingBalance) }}</td>
          </tr>
        </tfoot>
        @endif
      </table>
    </div>
  </div>
</div>
@endsection
