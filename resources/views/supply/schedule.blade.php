@extends('layouts.app')
@section('title','Delivery Schedule')
@section('content')
<div class="max-w-5xl mx-auto space-y-6">

  {{-- Header --}}
  <div class="flex items-center justify-between">
    <div class="flex items-center gap-3">
      <a href="{{ route('supply.index') }}" class="w-9 h-9 flex items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-400 hover:text-slate-700 transition-colors">
        <i data-lucide="arrow-left" class="w-4 h-4"></i>
      </a>
      <div>
        <h1 class="text-xl font-bold text-slate-900">Delivery Schedule</h1>
        <p class="text-xs text-slate-500">{{ $today->format('l, d M Y') }} — pending supply orders</p>
      </div>
    </div>
    <a href="{{ route('supply.create') }}" class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-colors">
      <i data-lucide="plus" class="w-4 h-4"></i> New Order
    </a>
  </div>

  {{-- Summary chips --}}
  <div class="flex flex-wrap gap-3">
    <div class="flex items-center gap-2 bg-red-50 border border-red-200 rounded-xl px-4 py-2.5">
      <span class="w-2.5 h-2.5 rounded-full bg-red-500 flex-shrink-0"></span>
      <span class="text-sm font-semibold text-red-700">{{ $overdue->count() }} Overdue</span>
    </div>
    <div class="flex items-center gap-2 bg-amber-50 border border-amber-200 rounded-xl px-4 py-2.5">
      <span class="w-2.5 h-2.5 rounded-full bg-amber-400 flex-shrink-0"></span>
      <span class="text-sm font-semibold text-amber-700">{{ $todayOrders->count() }} Today</span>
    </div>
    <div class="flex items-center gap-2 bg-blue-50 border border-blue-200 rounded-xl px-4 py-2.5">
      <span class="w-2.5 h-2.5 rounded-full bg-blue-400 flex-shrink-0"></span>
      <span class="text-sm font-semibold text-blue-700">{{ $tomorrowOrders->count() }} Tomorrow</span>
    </div>
    <div class="flex items-center gap-2 bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5">
      <span class="w-2.5 h-2.5 rounded-full bg-slate-400 flex-shrink-0"></span>
      <span class="text-sm font-semibold text-slate-600">{{ $upcoming->count() }} Upcoming</span>
    </div>
  </div>

  {{-- OVERDUE --}}
  @if($overdue->count())
  <div class="bg-white rounded-xl border border-red-200 shadow-sm overflow-hidden">
    <div class="flex items-center gap-3 px-5 py-3 bg-red-50 border-b border-red-100">
      <i data-lucide="alert-circle" class="w-4 h-4 text-red-600 flex-shrink-0"></i>
      <p class="font-semibold text-red-800">Overdue Deliveries</p>
      <span class="ml-auto text-xs bg-red-600 text-white px-2 py-0.5 rounded-full font-semibold">{{ $overdue->count() }}</span>
    </div>
    <div class="divide-y divide-slate-100">
      @foreach($overdue as $order)
      @php $daysLate = \Carbon\Carbon::parse($order->delivery_date)->diffInDays(today()); @endphp
      <div class="flex items-center gap-4 px-5 py-4 hover:bg-red-50/40 transition-colors">
        <div class="w-10 h-10 rounded-xl bg-red-100 flex flex-col items-center justify-center flex-shrink-0">
          <span class="text-xs font-bold text-red-700 leading-none">{{ \Carbon\Carbon::parse($order->delivery_date)->format('d') }}</span>
          <span class="text-[10px] text-red-500 leading-none">{{ \Carbon\Carbon::parse($order->delivery_date)->format('M') }}</span>
        </div>
        <div class="flex-1 min-w-0">
          <p class="font-semibold text-slate-900 text-sm truncate">{{ $order->customer?->name ?? '—' }}</p>
          <p class="text-xs text-slate-500 mt-0.5">{{ $order->invoice_number }} · {{ formatKg($order->dressed_weight_kg) }} kg</p>
        </div>
        <div class="text-right flex-shrink-0">
          <p class="text-sm font-bold text-red-600">{{ formatCurrency($order->amount_due) }}</p>
          <span class="text-[10px] text-red-400 font-medium">{{ $daysLate }} {{ $daysLate == 1 ? 'din' : 'din' }} late</span>
        </div>
        <a href="{{ route('supply.show', $order) }}" class="flex-shrink-0 inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-red-100 hover:bg-red-200 text-red-700 text-xs font-semibold transition-colors">
          <i data-lucide="eye" class="w-3.5 h-3.5"></i> View
        </a>
      </div>
      @endforeach
    </div>
  </div>
  @endif

  {{-- TODAY --}}
  <div class="bg-white rounded-xl border border-amber-200 shadow-sm overflow-hidden">
    <div class="flex items-center gap-3 px-5 py-3 bg-amber-50 border-b border-amber-100">
      <i data-lucide="sun" class="w-4 h-4 text-amber-600 flex-shrink-0"></i>
      <p class="font-semibold text-amber-800">Aaj — {{ $today->format('d M Y') }}</p>
      <span class="ml-auto text-xs bg-amber-500 text-white px-2 py-0.5 rounded-full font-semibold">{{ $todayOrders->count() }}</span>
    </div>
    @if($todayOrders->count())
    <div class="divide-y divide-slate-100">
      @foreach($todayOrders as $order)
      <div class="flex items-center gap-4 px-5 py-4 hover:bg-amber-50/30 transition-colors">
        <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center flex-shrink-0">
          <i data-lucide="truck" class="w-4 h-4 text-amber-600"></i>
        </div>
        <div class="flex-1 min-w-0">
          <p class="font-semibold text-slate-900 text-sm truncate">{{ $order->customer?->name ?? '—' }}</p>
          <p class="text-xs text-slate-500 mt-0.5">{{ $order->invoice_number }} · {{ formatKg($order->dressed_weight_kg) }} kg
            @if($order->delivery_address)· <span class="text-slate-400">{{ Str::limit($order->delivery_address, 30) }}</span>@endif
          </p>
        </div>
        <div class="text-right flex-shrink-0">
          <p class="text-sm font-bold text-slate-800">{{ formatCurrency($order->total_amount) }}</p>
          <span class="badge {{ $order->payment_status === 'partial' ? 'badge-yellow' : 'badge-red' }} text-[10px]">
            {{ ucfirst($order->payment_status) }}
          </span>
        </div>
        <div class="flex gap-1.5 flex-shrink-0">
          @if($order->customer?->phone)
          @php
            $phone = preg_replace('/\D/', '', $order->customer->phone);
            if (str_starts_with($phone, '0')) $phone = '92' . substr($phone, 1);
            $msg = "Dear {$order->customer->name},\nDelivery today: {$order->invoice_number}\nAmount: PKR " . number_format($order->total_amount, 0) . "\n- Anwar Chicken Center";
          @endphp
          <a href="https://wa.me/{{ $phone }}?text={{ urlencode($msg) }}" target="_blank"
             class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-green-100 hover:bg-green-200 text-green-700 text-xs font-semibold transition-colors">
            <i data-lucide="message-circle" class="w-3.5 h-3.5"></i>
          </a>
          @endif
          <a href="{{ route('supply.show', $order) }}" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition-colors">
            <i data-lucide="eye" class="w-3.5 h-3.5"></i>
          </a>
        </div>
      </div>
      @endforeach
    </div>
    @else
    <div class="px-5 py-10 text-center">
      <i data-lucide="check-circle-2" class="w-10 h-10 text-green-300 mx-auto mb-2"></i>
      <p class="text-slate-400 text-sm">Aaj koi delivery nahi hai</p>
    </div>
    @endif
  </div>

  {{-- TOMORROW --}}
  <div class="bg-white rounded-xl border border-blue-200 shadow-sm overflow-hidden">
    <div class="flex items-center gap-3 px-5 py-3 bg-blue-50 border-b border-blue-100">
      <i data-lucide="calendar" class="w-4 h-4 text-blue-600 flex-shrink-0"></i>
      <p class="font-semibold text-blue-800">Kal — {{ $tomorrow->format('d M Y') }}</p>
      <span class="ml-auto text-xs bg-blue-500 text-white px-2 py-0.5 rounded-full font-semibold">{{ $tomorrowOrders->count() }}</span>
    </div>
    @if($tomorrowOrders->count())
    <div class="divide-y divide-slate-100">
      @foreach($tomorrowOrders as $order)
      <div class="flex items-center gap-4 px-5 py-4 hover:bg-blue-50/30 transition-colors">
        <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center flex-shrink-0">
          <i data-lucide="package" class="w-4 h-4 text-blue-600"></i>
        </div>
        <div class="flex-1 min-w-0">
          <p class="font-semibold text-slate-900 text-sm truncate">{{ $order->customer?->name ?? '—' }}</p>
          <p class="text-xs text-slate-500 mt-0.5">{{ $order->invoice_number }} · {{ formatKg($order->dressed_weight_kg) }} kg</p>
        </div>
        <div class="text-right flex-shrink-0">
          <p class="text-sm font-bold text-slate-800">{{ formatCurrency($order->total_amount) }}</p>
        </div>
        <a href="{{ route('supply.show', $order) }}" class="flex-shrink-0 inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition-colors">
          <i data-lucide="eye" class="w-3.5 h-3.5"></i>
        </a>
      </div>
      @endforeach
    </div>
    @else
    <div class="px-5 py-8 text-center">
      <p class="text-slate-400 text-sm">Kal koi delivery schedule nahi</p>
    </div>
    @endif
  </div>

  {{-- UPCOMING --}}
  @if($upcoming->count())
  <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="flex items-center gap-3 px-5 py-3 bg-slate-50 border-b border-slate-100">
      <i data-lucide="calendar-days" class="w-4 h-4 text-slate-500 flex-shrink-0"></i>
      <p class="font-semibold text-slate-700">Aagey — Upcoming</p>
      <span class="ml-auto text-xs bg-slate-400 text-white px-2 py-0.5 rounded-full font-semibold">{{ $upcoming->count() }}</span>
    </div>
    <div class="divide-y divide-slate-100">
      @foreach($upcoming as $order)
      <div class="flex items-center gap-4 px-5 py-4 hover:bg-slate-50/60 transition-colors">
        <div class="w-10 h-10 rounded-xl bg-slate-100 flex flex-col items-center justify-center flex-shrink-0">
          <span class="text-xs font-bold text-slate-600 leading-none">{{ \Carbon\Carbon::parse($order->delivery_date)->format('d') }}</span>
          <span class="text-[10px] text-slate-400 leading-none">{{ \Carbon\Carbon::parse($order->delivery_date)->format('M') }}</span>
        </div>
        <div class="flex-1 min-w-0">
          <p class="font-semibold text-slate-900 text-sm truncate">{{ $order->customer?->name ?? '—' }}</p>
          <p class="text-xs text-slate-500 mt-0.5">{{ $order->invoice_number }} · {{ formatKg($order->dressed_weight_kg) }} kg</p>
        </div>
        <div class="text-right flex-shrink-0">
          <p class="text-sm font-bold text-slate-700">{{ formatCurrency($order->total_amount) }}</p>
          <p class="text-[10px] text-slate-400">{{ \Carbon\Carbon::parse($order->delivery_date)->format('l') }}</p>
        </div>
        <a href="{{ route('supply.show', $order) }}" class="flex-shrink-0 inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition-colors">
          <i data-lucide="eye" class="w-3.5 h-3.5"></i>
        </a>
      </div>
      @endforeach
    </div>
  </div>
  @endif

</div>
@endsection
