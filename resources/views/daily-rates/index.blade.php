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
    <p class="text-sm text-green-800 font-medium">Aaj ka rate set ho gaya hai sab chicken types ke liye.</p>
  </div>
  @endif

  {{-- Set Today's Rates Form --}}
  <div class="bg-white rounded-xl border border-slate-200 shadow-sm">
    <div class="px-5 py-4 border-b border-slate-100">
      <h2 class="font-semibold text-slate-900">Aaj Ka Rate Set Karo</h2>
      <p class="text-xs text-slate-400 mt-0.5">Live rate (supplier se), Retail rate (counter pe), Supply rate (hotels/companies ko)</p>
    </div>
    <form method="POST" action="{{ route('daily-rates.store') }}" class="p-5 space-y-4">
      @csrf
      @if($chickenTypes->isEmpty())
        <p class="text-slate-400 text-sm text-center py-4">Koi chicken type nahi hai. Settings mein add karo.</p>
      @else
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead>
              <tr>
                <th class="pb-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider w-32">Chicken Type</th>
                <th class="pb-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider pl-4">Live Rate (PKR/kg)</th>
                <th class="pb-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider pl-4">Retail Rate (PKR/kg)</th>
                <th class="pb-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider pl-4">Supply Rate (PKR/kg)</th>
                <th class="pb-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider pl-4">Notes</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
              @foreach($chickenTypes as $type)
              @php $existing = $todayRates->get($type->id); @endphp
              <tr>
                <td class="py-3 pr-4">
                  <input type="hidden" name="rates[{{ $type->id }}][date]" value="{{ today()->toDateString() }}">
                  <span class="font-medium text-slate-800">{{ $type->name }}</span>
                  @if($existing)<span class="ml-2 badge badge-green text-xs">Set</span>@endif
                </td>
                <td class="py-3 pl-4">
                  <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">PKR</span>
                    <input type="number" name="rates[{{ $type->id }}][live_rate_per_kg]"
                      value="{{ $existing?->live_rate_per_kg }}"
                      step="0.01" min="0" placeholder="0.00"
                      class="w-36 pl-12 pr-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 focus:border-green-400 outline-none">
                  </div>
                </td>
                <td class="py-3 pl-4">
                  <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">PKR</span>
                    <input type="number" name="rates[{{ $type->id }}][retail_rate_per_kg]"
                      value="{{ $existing?->retail_rate_per_kg }}"
                      step="0.01" min="0" placeholder="0.00"
                      class="w-36 pl-12 pr-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 focus:border-green-400 outline-none">
                  </div>
                </td>
                <td class="py-3 pl-4">
                  <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">PKR</span>
                    <input type="number" name="rates[{{ $type->id }}][supply_rate_per_kg]"
                      value="{{ $existing?->supply_rate_per_kg }}"
                      step="0.01" min="0" placeholder="0.00"
                      class="w-36 pl-12 pr-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 focus:border-green-400 outline-none">
                  </div>
                </td>
                <td class="py-3 pl-4">
                  <input type="text" name="rates[{{ $type->id }}][notes]"
                    value="{{ $existing?->notes }}" placeholder="Optional..."
                    class="w-40 px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 focus:border-green-400 outline-none">
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        <div class="pt-2">
          <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg text-sm font-medium transition-colors">
            <i data-lucide="save" class="w-4 h-4 inline mr-1"></i>Save Rates
          </button>
        </div>
      @endif
    </form>
  </div>

  {{-- Rate History --}}
  <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
      <h2 class="font-semibold text-slate-900">Rate History</h2>
      <form method="GET" class="flex gap-2">
        <select name="chicken_type_id" class="px-3 py-1.5 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none bg-white">
          <option value="">All Types</option>
          @foreach($chickenTypes as $type)
            <option value="{{ $type->id }}" {{ request('chicken_type_id')==$type->id?'selected':'' }}>{{ $type->name }}</option>
          @endforeach
        </select>
        <button type="submit" class="bg-slate-800 text-white px-3 py-1.5 rounded-lg text-sm">Filter</button>
        @if(request('chicken_type_id'))
          <a href="{{ route('daily-rates.index') }}" class="bg-white border border-slate-200 text-slate-700 px-3 py-1.5 rounded-lg text-sm">Clear</a>
        @endif
      </form>
    </div>
    <table class="w-full">
      <thead class="bg-slate-50">
        <tr>
          <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Date</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Chicken Type</th>
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
          <td class="px-4 py-3 text-sm font-medium text-slate-800">{{ $rate->chickenType->name ?? '-' }}</td>
          <td class="px-4 py-3 text-sm text-right font-medium text-slate-900">PKR {{ number_format($rate->live_rate_per_kg,2) }}</td>
          <td class="px-4 py-3 text-sm text-right text-slate-600">{{ $rate->retail_rate_per_kg ? 'PKR '.number_format($rate->retail_rate_per_kg,2) : '-' }}</td>
          <td class="px-4 py-3 text-sm text-right text-slate-600">{{ $rate->supply_rate_per_kg ? 'PKR '.number_format($rate->supply_rate_per_kg,2) : '-' }}</td>
          <td class="px-4 py-3 text-sm text-slate-500">{{ $rate->notes ?? '-' }}</td>
        </tr>
        @empty
        <tr><td colspan="6" class="px-4 py-8 text-center text-slate-400 text-sm">Abhi koi rate history nahi hai.</td></tr>
        @endforelse
      </tbody>
    </table>
    @if($rates->hasPages())
      <div class="px-4 py-3 border-t border-slate-100">{{ $rates->links() }}</div>
    @endif
  </div>
</div>
@endsection
