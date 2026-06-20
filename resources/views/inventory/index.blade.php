@extends('layouts.app')
@section('title','Inventory')
@section('content')
<div class="space-y-5">
  <div class="flex items-center justify-between">
    <div><h1 class="text-2xl font-bold text-slate-900">Daily Inventory</h1><p class="text-sm text-slate-500 mt-0.5">Stock tracking by chicken type</p></div>
    <a href="{{ route('inventory.create') }}" class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors"><i data-lucide="plus" class="w-4 h-4"></i>Add Entry</a>
  </div>

  <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4 text-center">
      <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Today's Stock</p>
      <p class="text-xl font-bold text-slate-900 mt-1">{{ number_format($stats['today_stock_kg'],1) }} kg</p>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4 text-center">
      <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Active Types Today</p>
      <p class="text-xl font-bold text-green-600 mt-1">{{ $stats['total_types'] }}</p>
    </div>
    <div class="bg-white rounded-xl border {{ $stats['low_stock']>0?'border-yellow-300 bg-yellow-50':'border-slate-200' }} shadow-sm p-4 text-center">
      <p class="text-xs {{ $stats['low_stock']>0?'text-yellow-600':'text-slate-500' }} font-medium uppercase tracking-wider">Low Stock Alerts</p>
      <p class="text-xl font-bold {{ $stats['low_stock']>0?'text-yellow-700':'text-slate-400' }} mt-1">{{ $stats['low_stock'] }}</p>
    </div>
  </div>

  <form method="GET" class="flex flex-wrap gap-3">
    <select name="chicken_type_id" class="px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none bg-white">
      <option value="">All Types</option>
      @foreach($chickenTypes as $t)<option value="{{ $t->id }}" {{ request('chicken_type_id')==$t->id?'selected':'' }}>{{ $t->name }}</option>@endforeach
    </select>
    <input type="date" name="from_date" value="{{ request('from_date') }}" class="px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
    <input type="date" name="to_date" value="{{ request('to_date') }}" class="px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
    <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white px-4 py-2 rounded-lg text-sm font-medium">Filter</button>
    @if(request()->hasAny(['chicken_type_id','from_date','to_date']))<a href="{{ route('inventory.index') }}" class="bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 px-4 py-2 rounded-lg text-sm">Clear</a>@endif
  </form>

  <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <table class="w-full">
      <thead class="bg-slate-50"><tr>
        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Date</th>
        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Type</th>
        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Opening</th>
        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Purchased</th>
        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Sold Retail</th>
        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Sold Supply</th>
        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Dead/Spoil</th>
        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Closing</th>
        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Actions</th>
      </tr></thead>
      <tbody class="divide-y divide-slate-100">
        @forelse($records as $r)
        @php $lowStock = $r->closing_stock_kg < 50; @endphp
        <tr class="hover:bg-slate-50 transition-colors {{ $lowStock?'bg-yellow-50':'' }}">
          <td class="px-4 py-3 text-sm text-slate-600">{{ \Carbon\Carbon::parse($r->date)->format('d M Y') }}</td>
          <td class="px-4 py-3 text-sm font-medium text-slate-900">{{ $r->chickenType?->name ?? '-' }}</td>
          <td class="px-4 py-3 text-sm text-right text-slate-600">{{ number_format($r->opening_stock_kg,1) }}</td>
          <td class="px-4 py-3 text-sm text-right text-green-600">{{ number_format($r->total_purchased_kg,1) }}</td>
          <td class="px-4 py-3 text-sm text-right text-slate-600">{{ number_format($r->total_sold_retail_kg,1) }}</td>
          <td class="px-4 py-3 text-sm text-right text-slate-600">{{ number_format($r->total_sold_supply_kg,1) }}</td>
          <td class="px-4 py-3 text-sm text-right text-red-500">{{ number_format($r->dead_kg + $r->spoilage_kg, 1) }}</td>
          <td class="px-4 py-3 text-sm text-right font-bold {{ $lowStock?'text-yellow-700':'text-slate-900' }}">
            {{ number_format($r->closing_stock_kg,1) }}
            @if($lowStock)<span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-700 ml-1">Low</span>@endif
          </td>
          <td class="px-4 py-3 text-right">
            <a href="{{ route('inventory.show',$r) }}" class="text-slate-400 hover:text-green-600"><i data-lucide="eye" class="w-4 h-4"></i></a>
          </td>
        </tr>
        @empty
        <tr><td colspan="9" class="px-4 py-12 text-center">
          <i data-lucide="package" class="w-10 h-10 text-slate-300 mx-auto mb-3"></i>
          <p class="text-slate-500 font-medium">No inventory records yet</p>
          <a href="{{ route('inventory.create') }}" class="text-green-600 text-sm mt-1 inline-block hover:underline">Add first entry</a>
        </td></tr>
        @endforelse
      </tbody>
    </table>
    @if($records->hasPages())<div class="px-4 py-3 border-t border-slate-100">{{ $records->links() }}</div>@endif
  </div>
</div>
@endsection
