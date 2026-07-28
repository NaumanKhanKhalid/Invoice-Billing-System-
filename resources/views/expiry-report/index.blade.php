@extends('layouts.app')
@section('title','Expiry Report')
@section('content')
<div class="space-y-6">

  <div class="flex items-center gap-3">
    <a href="{{ route('dashboard') }}" class="w-9 h-9 rounded-lg border border-slate-200 bg-white flex items-center justify-center text-slate-500 hover:text-slate-700 shadow-sm">
      <i data-lucide="arrow-left" class="w-4 h-4"></i>
    </a>
    <div>
      <h1 class="text-xl font-bold text-slate-900">Expiry Report</h1>
      <p class="text-sm text-slate-500">Batches with stock, grouped by expiry</p>
    </div>
  </div>

  @php
  $sections = [
    ['title' => 'Expired',              'batches' => $expired,    'color' => 'red',   'icon' => 'alert-octagon'],
    ['title' => 'Expiring in 30 days',  'batches' => $expiring30, 'color' => 'amber', 'icon' => 'alarm-clock'],
    ['title' => 'Expiring in 90 days',  'batches' => $expiring90, 'color' => 'slate', 'icon' => 'clock'],
  ];
  @endphp

  @foreach($sections as $section)
  <div class="bg-white rounded-xl border border-{{ $section['color'] }}-200 shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-{{ $section['color'] }}-100 bg-{{ $section['color'] }}-50 flex items-center gap-2">
      <i data-lucide="{{ $section['icon'] }}" class="w-4 h-4 text-{{ $section['color'] }}-600"></i>
      <h2 class="font-semibold text-{{ $section['color'] }}-800">{{ $section['title'] }}</h2>
      <span class="ml-auto text-xs font-semibold bg-{{ $section['color'] }}-100 text-{{ $section['color'] }}-700 px-2 py-0.5 rounded-full">{{ $section['batches']->count() }}</span>
    </div>
    @if($section['batches']->isEmpty())
    <p class="text-sm text-slate-400 text-center py-6">Koi batch nahi</p>
    @else
    <table class="w-full">
      <thead class="bg-slate-50">
        <tr>
          <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Product</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Batch</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Expiry</th>
          <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Qty</th>
          <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Days Left</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        @foreach($section['batches'] as $batch)
        @php $daysLeft = (int) today()->diffInDays($batch->expiry_date, false); @endphp
        <tr>
          <td class="px-4 py-3 text-sm font-medium text-slate-900">
            <a href="{{ route('products.show', $batch->product) }}" class="hover:text-green-600">{{ $batch->product->name }}</a>
          </td>
          <td class="px-4 py-3 text-sm text-slate-600">{{ $batch->batch_no ?? '—' }}</td>
          <td class="px-4 py-3 text-sm text-slate-600">{{ $batch->expiry_date->format('d M Y') }}</td>
          <td class="px-4 py-3 text-sm text-right text-slate-600">{{ rtrim(rtrim(number_format($batch->qty, 3), '0'), '.') }} {{ $batch->product->unit }}</td>
          <td class="px-4 py-3 text-right">
            @if($daysLeft < 0)
            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700">{{ abs($daysLeft) }} din pehle expired</span>
            @elseif($daysLeft <= 30)
            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-700">{{ $daysLeft }} din</span>
            @else
            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-600">{{ $daysLeft }} din</span>
            @endif
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
    @endif
  </div>
  @endforeach
</div>
@endsection
