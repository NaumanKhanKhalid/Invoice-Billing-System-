@extends('layouts.app')
@section('title','POS Sales History')
@section('content')
<div class="space-y-6">
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-xl font-bold text-slate-900">{{ __('pages.pos_sales') }}</h1>
      <p class="text-sm text-slate-500">{{ $sales->total() }} total sales</p>
    </div>
    <a href="{{ route('pos.create') }}" class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
      <i data-lucide="shopping-bag" class="w-4 h-4"></i>New Sale
    </a>
  </div>

  <div class="grid grid-cols-2 gap-4">
    <div class="bg-white rounded-xl border border-green-200 shadow-sm p-5 text-center">
      <p class="text-xs text-green-600 font-semibold uppercase tracking-wider mb-1">{{ __('pages.todays_sales') }}</p>
      <p class="text-2xl font-bold text-slate-900">{{ $todayCount }}</p>
      <p class="text-sm text-slate-500 mt-0.5">PKR {{ number_format($todayTotal) }}</p>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 text-center">
      <p class="text-xs text-slate-400 font-semibold uppercase tracking-wider mb-1">{{ __('pages.total_sales') }}</p>
      <p class="text-2xl font-bold text-slate-900">{{ $sales->total() }}</p>
    </div>
  </div>

  <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    @if($sales->count())
    <table class="w-full">
      <thead class="bg-slate-50">
        <tr>
          <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">{{ __('pages.sale_no') }}</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">{{ __('common.date') }}</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">{{ __('common.customer') }}</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">{{ __('common.method') }}</th>
          <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">{{ __('common.total') }}</th>
          <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">{{ __('common.action') }}</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        @foreach($sales as $sale)
        <tr class="hover:bg-slate-50">
          <td class="px-4 py-3 text-sm font-medium text-slate-900">{{ $sale->sale_number }}</td>
          <td class="px-4 py-3 text-sm text-slate-600">{{ $sale->date->format('d M Y') }}</td>
          <td class="px-4 py-3 text-sm text-slate-600">{{ $sale->customer_name ?? '—' }}</td>
          <td class="px-4 py-3 text-sm text-slate-500">{{ payment_label($sale->payment_method) }}</td>
          <td class="px-4 py-3 text-sm text-right font-bold text-slate-900">PKR {{ number_format($sale->total) }}</td>
          <td class="px-4 py-3 text-right">
            <div class="flex items-center justify-end gap-2">
              <a href="{{ route('pos.receipt', $sale) }}" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-green-100 text-slate-500 hover:text-green-700 text-xs font-medium">{{ __('common.receipt') }}</a>
              <a href="{{ route('sale-returns.create', $sale) }}" class="px-2.5 py-1 rounded-lg bg-red-50 hover:bg-red-100 text-red-500 hover:text-red-700 text-xs font-medium">{{ __('common.return') }}</a>
            </div>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
    <div class="px-4 py-3 border-t border-slate-100">{{ $sales->links() }}</div>
    @else
    <div class="px-4 py-12 text-center">
      <i data-lucide="shopping-bag" class="w-10 h-10 text-slate-300 mx-auto mb-3"></i>
      <p class="text-slate-400">{{ __('pages.no_sales') }}</p>
      <a href="{{ route('pos.create') }}" class="text-green-600 text-sm hover:underline mt-1 inline-block">{{ __('pages.make_first_sale') }}</a>
    </div>
    @endif
  </div>
</div>
@endsection
