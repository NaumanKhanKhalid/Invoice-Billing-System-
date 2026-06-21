@extends('layouts.app')
@section('title','Purchases')
@section('content')
<div class="space-y-6">
  <div class="flex items-center justify-between">
    <div><h1 class="text-2xl font-bold text-slate-900">Purchase Orders</h1><p class="text-sm text-slate-500 mt-0.5">Chicken purchases from suppliers</p></div>
    <a href="{{ route('purchases.create') }}" class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors"><i data-lucide="plus" class="w-4 h-4"></i>New Purchase</a>
  </div>

  {{-- Stats --}}
  <div class="grid grid-cols-2 sm:grid-cols-5 gap-4">
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4"><p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Total Orders</p><p class="text-2xl font-bold text-slate-900 mt-1">{{ $stats['total_orders'] }}</p></div>
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4"><p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Total Amount</p><p class="text-xl font-bold text-slate-900 mt-1">PKR {{ number_format($stats['total_amount'],0) }}</p></div>
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4"><p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Total Paid</p><p class="text-xl font-bold text-green-600 mt-1">PKR {{ number_format($stats['total_paid'],0) }}</p></div>
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4"><p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Outstanding</p><p class="text-xl font-bold text-red-600 mt-1">PKR {{ number_format($stats['total_due'],0) }}</p></div>
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4"><p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Overdue</p><p class="text-2xl font-bold text-orange-600 mt-1">{{ $stats['overdue_count'] }}</p></div>
  </div>

  {{-- Filters --}}
  <form method="GET" class="flex flex-wrap gap-3">
    <select name="supplier_id" class="px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none bg-white"><option value="">All Suppliers</option>@foreach($suppliers as $s)<option value="{{ $s->id }}" {{ request('supplier_id')==$s->id?'selected':'' }}>{{ $s->name }}</option>@endforeach</select>
    <select name="status" class="px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none bg-white"><option value="">All Status</option><option value="unpaid" {{ request('status')=='unpaid'?'selected':'' }}>Unpaid</option><option value="partial" {{ request('status')=='partial'?'selected':'' }}>Partial</option><option value="paid" {{ request('status')=='paid'?'selected':'' }}>Paid</option></select>
    <input type="date" name="from_date" value="{{ request('from_date') }}" class="px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
    <input type="date" name="to_date" value="{{ request('to_date') }}" class="px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
    <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white px-4 py-2 rounded-lg text-sm font-medium">Filter</button>
    @if(request()->hasAny(['supplier_id','status','from_date','to_date']))<a href="{{ route('purchases.index') }}" class="bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 px-4 py-2 rounded-lg text-sm">Clear</a>@endif
  </form>

  {{-- Table --}}
  <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <table class="w-full">
      <thead class="bg-slate-50"><tr>
        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Date</th>
        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Invoice #</th>
        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Supplier</th>
        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Live Kg</th>
        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Amount</th>
        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Due</th>
        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Status</th>
        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Actions</th>
      </tr></thead>
      <tbody class="divide-y divide-slate-100">
        @forelse($orders as $order)
        @php $isOverdue = $order->payment_status !== 'paid' && $order->due_date && \Carbon\Carbon::parse($order->due_date)->isPast(); @endphp
        <tr class="hover:bg-slate-50 transition-colors {{ $isOverdue ? 'bg-red-50' : '' }}">
          <td class="px-4 py-3 text-sm text-slate-600">{{ \Carbon\Carbon::parse($order->date)->format('d M Y') }}</td>
          <td class="px-4 py-3 text-sm font-medium"><a href="{{ route('purchases.show',$order) }}" class="text-slate-900 hover:text-green-600">{{ $order->invoice_number }}</a></td>
          <td class="px-4 py-3 text-sm text-slate-700">{{ $order->supplier->name }}</td>
          <td class="px-4 py-3 text-sm text-right text-slate-700">{{ number_format($order->live_weight_kg,1) }}</td>
          <td class="px-4 py-3 text-sm text-right font-medium text-slate-900">PKR {{ number_format($order->total_amount,0) }}</td>
          <td class="px-4 py-3 text-sm text-right font-medium {{ $isOverdue?'text-red-600':($order->amount_due>0?'text-orange-600':'text-slate-400') }}">PKR {{ number_format($order->amount_due,0) }}</td>
          <td class="px-4 py-3">
            @if($isOverdue)<span class="badge badge-red">Overdue</span>
            @elseif($order->payment_status==='paid')<span class="badge badge-green">Paid</span>
            @elseif($order->payment_status==='partial')<span class="badge badge-yellow">Partial</span>
            @else<span class="badge badge-red">Unpaid</span>@endif
          </td>
          <td class="px-4 py-3">
            <div class="flex items-center justify-end gap-2">
              <a href="{{ route('purchases.show',$order) }}" class="text-slate-400 hover:text-green-600"><i data-lucide="eye" class="w-4 h-4"></i></a>
              @if($order->payment_status!=='paid')<a href="{{ route('purchases.edit',$order) }}" class="text-slate-400 hover:text-blue-600"><i data-lucide="pencil" class="w-4 h-4"></i></a>@endif
              @if($order->payment_status==='unpaid')
              <form method="POST" action="{{ route('purchases.destroy',$order) }}" class="inline" onsubmit="return confirm('Delete this order?')">@csrf @method('DELETE')<button type="submit" class="text-slate-400 hover:text-red-600"><i data-lucide="trash-2" class="w-4 h-4"></i></button></form>
              @endif
            </div>
          </td>
        </tr>
        @empty
        <tr><td colspan="9" class="px-4 py-12 text-center"><i data-lucide="shopping-cart" class="w-10 h-10 text-slate-300 mx-auto mb-3"></i><p class="text-slate-500 font-medium">No purchase orders yet</p><a href="{{ route('purchases.create') }}" class="text-green-600 text-sm mt-1 inline-block hover:underline">Record first purchase</a></td></tr>
        @endforelse
      </tbody>
    </table>
    @if($orders->hasPages())<div class="px-4 py-3 border-t border-slate-100">{{ $orders->links() }}</div>@endif
  </div>
</div>
@endsection
