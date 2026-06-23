@extends('layouts.app')
@section('title','Supply Orders')
@section('content')
<div class="space-y-5">
  <div class="flex items-center justify-between">
    <div><h1 class="text-2xl font-bold text-slate-900">Supply Orders</h1><p class="text-sm text-slate-500 mt-0.5">Hotels & Companies</p></div>
    <a href="{{ route('supply.create') }}" class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors"><i data-lucide="plus" class="w-4 h-4"></i>New Order</a>
  </div>

  <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4 text-center">
      <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Today's Supply</p>
      <p class="text-xl font-bold text-slate-900 mt-1">{{ formatCurrency($stats['total_today']) }}</p>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4 text-center">
      <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Outstanding</p>
      <p class="text-xl font-bold text-orange-600 mt-1">{{ formatCurrency($stats['total_due']) }}</p>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4 text-center">
      <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Overdue</p>
      <p class="text-xl font-bold text-red-600 mt-1">{{ $stats['overdue_count'] }} orders</p>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4 text-center">
      <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">This Month (kg)</p>
      <p class="text-xl font-bold text-green-600 mt-1">{{ formatKg($stats['this_month_kg']) }}</p>
    </div>
  </div>

  <form method="GET" class="flex flex-wrap gap-3">
    <select name="customer_id" class="px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none bg-white">
      <option value="">All Customers</option>
      @foreach($customers as $c)<option value="{{ $c->id }}" {{ request('customer_id')==$c->id?'selected':'' }}>{{ $c->name }}</option>@endforeach
    </select>
    <select name="status" class="px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none bg-white">
      <option value="">All Status</option>
      <option value="unpaid" {{ request('status')=='unpaid'?'selected':'' }}>Unpaid</option>
      <option value="partial" {{ request('status')=='partial'?'selected':'' }}>Partial</option>
      <option value="paid" {{ request('status')=='paid'?'selected':'' }}>Paid</option>
    </select>
    <input type="date" name="from_date" value="{{ request('from_date') }}" class="px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
    <input type="date" name="to_date" value="{{ request('to_date') }}" class="px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
    <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white px-4 py-2 rounded-lg text-sm font-medium">Filter</button>
    @if(request()->hasAny(['customer_id','status','from_date','to_date']))<a href="{{ route('supply.index') }}" class="bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 px-4 py-2 rounded-lg text-sm">Clear</a>@endif
  </form>

  <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <table class="w-full">
      <thead class="bg-slate-50"><tr>
        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Order Date</th>
        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Delivery Date</th>
        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Invoice #</th>
        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Customer</th>
        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Kg</th>
        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Amount</th>
        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Due</th>
        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Payment</th>
        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Delivery</th>
        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Actions</th>
      </tr></thead>
      <tbody class="divide-y divide-slate-100">
        @forelse($orders as $order)
        @php
          $isOverdue = $order->payment_status!=='paid' && $order->due_date && \Carbon\Carbon::parse($order->due_date)->isPast();
          $deliveryDate = $order->delivery_date ? \Carbon\Carbon::parse($order->delivery_date) : null;
          $deliveryOverdue = $deliveryDate && !$order->is_delivered && $deliveryDate->isPast();
          $deliveryToday = $deliveryDate && !$order->is_delivered && $deliveryDate->isToday();
          $rowClass = $isOverdue ? 'bg-red-50' : ($deliveryOverdue ? 'bg-orange-50' : '');
        @endphp
        <tr class="hover:bg-slate-50 transition-colors {{ $rowClass }}">
          <td class="px-4 py-3 text-sm text-slate-600">{{ \Carbon\Carbon::parse($order->date)->format('d M Y') }}</td>
          <td class="px-4 py-3 text-sm {{ $deliveryOverdue ? 'text-red-600 font-semibold' : ($deliveryToday ? 'text-amber-600 font-semibold' : 'text-slate-500') }}">
            @if($deliveryDate)
              {{ $deliveryDate->format('d M Y') }}
              @if($deliveryToday)<p class="text-[10px] text-amber-500">Aaj</p>@endif
              @if($deliveryOverdue)<p class="text-[10px] text-red-400">{{ $deliveryDate->diffInDays(today()) }} din late</p>@endif
            @else
              <span class="text-slate-300">—</span>
            @endif
          </td>
          <td class="px-4 py-3 text-sm font-medium"><a href="{{ route('supply.show',$order) }}" class="text-slate-900 hover:text-green-600">{{ $order->invoice_number }}</a></td>
          <td class="px-4 py-3 text-sm text-slate-700">{{ $order->customer?->name ?? '—' }}</td>
          <td class="px-4 py-3 text-sm text-right">{{ formatKg($order->dressed_weight_kg) }}</td>
          <td class="px-4 py-3 text-sm text-right font-medium text-slate-900">{{ formatCurrency($order->total_amount) }}</td>
          <td class="px-4 py-3 text-sm text-right font-medium {{ $isOverdue?'text-red-600':($order->amount_due>0?'text-orange-600':'text-slate-400') }}">{{ formatCurrency($order->amount_due) }}</td>
          <td class="px-4 py-3">
            @if($isOverdue)<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-700">Overdue</span>
            @elseif($order->payment_status==='paid')<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-700">Paid</span>
            @elseif($order->payment_status==='partial')<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-700">Partial</span>
            @else<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-700">Unpaid</span>@endif
          </td>
          <td class="px-4 py-3">
            @if($order->is_delivered)
              <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-700">
                <i data-lucide="check" class="w-3 h-3"></i>Delivered
              </span>
            @else
              <form method="POST" action="{{ route('supply.deliver',$order) }}" class="inline"
                    data-confirm-title="Mark Delivered?" data-confirm-message="Ye order deliver ho gaya hai?" data-confirm-text="Haan, Delivered" data-confirm-danger="false">
                @csrf @method('PATCH')
                <button type="submit"
                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium bg-amber-100 hover:bg-amber-200 text-amber-700 transition-colors cursor-pointer">
                  <i data-lucide="truck" class="w-3 h-3"></i>Pending
                </button>
              </form>
            @endif
          </td>
          <td class="px-4 py-3">
            <div class="flex items-center justify-end gap-2">
              <a href="{{ route('supply.show',$order) }}" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-green-100 text-slate-500 hover:text-green-700 text-xs font-medium transition-colors"><i data-lucide="eye" class="w-3.5 h-3.5"></i>View</a>
              <button onclick="printInvoice('{{ route('supply.invoice',$order) }}')" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-indigo-100 text-slate-500 hover:text-indigo-700 text-xs font-medium transition-colors cursor-pointer"><i data-lucide="printer" class="w-3.5 h-3.5"></i>Print</button>
              @if($order->payment_status!=='paid')<a href="{{ route('supply.edit',$order) }}" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-blue-100 text-slate-500 hover:text-blue-700 text-xs font-medium transition-colors"><i data-lucide="pencil" class="w-3.5 h-3.5"></i>Edit</a>@endif
              @if($order->payment_status==='unpaid')<form method="POST" action="{{ route('supply.destroy',$order) }}" class="inline" data-confirm-title="Delete Order?" data-confirm-message="Is order ko delete karna chahte hain?" data-confirm-text="Haan, Delete Karo" >@csrf @method('DELETE')<button type="submit" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-red-100 text-slate-500 hover:text-red-700 text-xs font-medium transition-colors"><i data-lucide="trash-2" class="w-3.5 h-3.5"></i>Delete</button></form>@endif
            </div>
          </td>
        </tr>
        @empty
        <tr><td colspan="10" class="px-4 py-12 text-center">
          <i data-lucide="receipt" class="w-10 h-10 text-slate-300 mx-auto mb-3"></i>
          <p class="text-slate-500 font-medium">No supply orders yet</p>
          <a href="{{ route('supply.create') }}" class="text-green-600 text-sm mt-1 inline-block hover:underline">Record first order</a>
        </td></tr>
        @endforelse
      </tbody>
    </table>
    @if($orders->hasPages())<div class="px-4 py-3 border-t border-slate-100">{{ $orders->links() }}</div>@endif
  </div>
</div>
@endsection
