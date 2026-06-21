@extends('layouts.app')
@section('title','Daily Rates')
@section('content')
<div class="space-y-6">
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-2xl font-bold text-slate-900">Daily Rates</h1>
      <p class="text-sm text-slate-500 mt-0.5">Aaj ka live, retail aur supply rate set karo</p>
    </div>
    <span class="text-sm text-slate-500 bg-white border border-slate-200 px-3 py-1.5 rounded-lg">{{ now()->format('d M Y') }}</span>
  </div>

  @if(!$todayComplete)
  <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 flex items-center gap-3">
    <i data-lucide="alert-triangle" class="w-5 h-5 text-amber-600 flex-shrink-0"></i>
    <p class="text-sm text-amber-800 font-medium">Aaj ka rate abhi set nahi hua. Neeche enter karo.</p>
  </div>
  @else
  <div class="bg-green-50 border border-green-200 rounded-xl p-4 flex items-center gap-3">
    <i data-lucide="check-circle-2" class="w-5 h-5 text-green-600 flex-shrink-0"></i>
    <p class="text-sm text-green-800 font-medium">Aaj ka rate set ho gaya hai.</p>
  </div>
  @endif

  {{-- Set Today's Rates Form --}}
  <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
    <h2 class="font-semibold text-slate-900 mb-1">Aaj Ka Rate Set Karo</h2>
    <p class="text-xs text-slate-400 mb-5">Live rate (supplier se) · Retail rate (counter pe) · Supply rate (hotels/companies ko)</p>
    <form method="POST" action="{{ route('daily-rates.store') }}" class="space-y-4">
      @csrf
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Live Rate (PKR/kg) <span class="text-red-500">*</span></label>
          <div class="relative">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">PKR</span>
            <input type="number" name="live_rate_per_kg" value="{{ $todayRate?->live_rate_per_kg }}" step="0.01" min="0" placeholder="0.00" required class="w-full pl-12 pr-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 focus:border-green-400 outline-none">
          </div>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Retail Rate (PKR/kg)</label>
          <div class="relative">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">PKR</span>
            <input type="number" name="retail_rate_per_kg" value="{{ $todayRate?->retail_rate_per_kg }}" step="0.01" min="0" placeholder="0.00" class="w-full pl-12 pr-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 focus:border-green-400 outline-none">
          </div>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Supply Rate (PKR/kg)</label>
          <div class="relative">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">PKR</span>
            <input type="number" name="supply_rate_per_kg" value="{{ $todayRate?->supply_rate_per_kg }}" step="0.01" min="0" placeholder="0.00" class="w-full pl-12 pr-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 focus:border-green-400 outline-none">
          </div>
        </div>
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Notes</label>
        <input type="text" name="notes" value="{{ $todayRate?->notes }}" placeholder="Optional..." class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 focus:border-green-400 outline-none">
      </div>
      <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg text-sm font-medium transition-colors">
        <i data-lucide="save" class="w-4 h-4 inline mr-1"></i>Save Rates
      </button>
    </form>
  </div>

  {{-- Rate History --}}
  <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-100">
      <h2 class="font-semibold text-slate-900">Rate History</h2>
    </div>
    <table class="w-full">
      <thead class="bg-slate-50">
        <tr>
          <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Date</th>
          <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Live (PKR/kg)</th>
          <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Retail (PKR/kg)</th>
          <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Supply (PKR/kg)</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Notes</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        @forelse($rates as $rate)
        <tr class="hover:bg-slate-50">
          <td class="px-4 py-3 text-sm text-slate-600">
            {{ \Carbon\Carbon::parse($rate->date)->format('d M Y') }}
            @if($rate->date->isToday())<span class="badge badge-green ml-1 text-xs">Today</span>@endif
          </td>
          <td class="px-4 py-3 text-sm text-right font-medium text-slate-900">PKR {{ number_format($rate->live_rate_per_kg,2) }}</td>
          <td class="px-4 py-3 text-sm text-right text-slate-600">{{ $rate->retail_rate_per_kg ? 'PKR '.number_format($rate->retail_rate_per_kg,2) : '-' }}</td>
          <td class="px-4 py-3 text-sm text-right text-slate-600">{{ $rate->supply_rate_per_kg ? 'PKR '.number_format($rate->supply_rate_per_kg,2) : '-' }}</td>
          <td class="px-4 py-3 text-sm text-slate-500">{{ $rate->notes ?? '-' }}</td>
        </tr>
        @empty
        <tr><td colspan="5" class="px-4 py-8 text-center text-slate-400 text-sm">Abhi koi rate history nahi hai.</td></tr>
        @endforelse
      </tbody>
    </table>
    @if($rates->hasPages())
      <div class="px-4 py-3 border-t border-slate-100">{{ $rates->links() }}</div>
    @endif
  </div>
</div>
@endsection
