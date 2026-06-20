@extends('layouts.app')
@section('title', 'Daily Rates')
@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Daily Rates</h1>
        <p class="text-sm text-slate-500 mt-0.5">Set today's chicken prices</p>
    </div>

    @if(!$todayComplete)
    <div class="flex items-start gap-3 bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded-xl text-sm">
        <i data-lucide="alert-triangle" class="w-4 h-4 flex-shrink-0 mt-0.5"></i>
        <span>Today's rates are not fully set. Please enter rates below before recording purchases or sales.</span>
    </div>
    @endif

    {{-- Set today rates form --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
        <h2 class="font-semibold text-slate-900 mb-4">Set Today's Rates — {{ today()->format('d M Y') }}</h2>
        <form method="POST" action="{{ route('daily-rates.store') }}">
            @csrf
            <div class="space-y-4">
                @foreach($chickenTypes as $type)
                <div class="p-4 bg-slate-50 rounded-lg">
                    <h3 class="font-medium text-slate-700 mb-3">{{ $type->name }}</h3>
                    <input type="hidden" name="rates[{{ $type->id }}][date]" value="{{ today()->toDateString() }}">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-500 mb-1">Live Rate (PKR/kg)</label>
                            <input type="number" name="rates[{{ $type->id }}][rate_per_kg]" value="{{ $todayRates[$type->id]->rate_per_kg ?? '' }}" step="0.01" min="0" placeholder="e.g. 380" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 focus:border-green-400 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 mb-1">Dressed Rate (PKR/kg)</label>
                            <input type="number" name="rates[{{ $type->id }}][rate_per_kg_dressed]" value="{{ $todayRates[$type->id]->rate_per_kg_dressed ?? '' }}" step="0.01" min="0" placeholder="e.g. 550" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 focus:border-green-400 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 mb-1">Notes</label>
                            <input type="text" name="rates[{{ $type->id }}][notes]" value="{{ $todayRates[$type->id]->notes ?? '' }}" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 focus:border-green-400 outline-none">
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            <div class="mt-5">
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg text-sm font-medium transition-colors">Save Today's Rates</button>
            </div>
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
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Type</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Live Rate</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Dressed Rate</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Notes</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($rates as $rate)
                <tr class="hover:bg-slate-50 {{ $rate->date->isToday() ? 'bg-green-50' : '' }}">
                    <td class="px-4 py-3 text-sm text-slate-700">
                        {{ $rate->date->format('d M Y') }}
                        @if($rate->date->isToday())<span class="badge badge-green ml-1">Today</span>@endif
                    </td>
                    <td class="px-4 py-3 text-sm font-medium text-slate-900">{{ $rate->chickenType->name }}</td>
                    <td class="px-4 py-3 text-sm text-right text-slate-700">PKR {{ number_format($rate->rate_per_kg, 2) }}</td>
                    <td class="px-4 py-3 text-sm text-right text-slate-700">PKR {{ number_format($rate->rate_per_kg_dressed, 2) }}</td>
                    <td class="px-4 py-3 text-sm text-slate-500">{{ $rate->notes ?? '-' }}</td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-4 py-8 text-center text-slate-400 text-sm">No rates recorded yet.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($rates->hasPages())
        <div class="px-4 py-3 border-t border-slate-100">{{ $rates->links() }}</div>
        @endif
    </div>
</div>
@endsection
