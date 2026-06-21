@extends('layouts.app')
@section('title','Expenses')
@section('content')
<div class="space-y-5">
  <div class="flex items-center justify-between">
    <div><h1 class="text-2xl font-bold text-slate-900">Expenses</h1><p class="text-sm text-slate-500 mt-0.5">Daily business expenses</p></div>
    <a href="{{ route('expenses.create') }}" class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors"><i data-lucide="plus" class="w-4 h-4"></i>Add Expense</a>
  </div>

  <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4 text-center">
      <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Today</p>
      <p class="text-xl font-bold text-slate-900 mt-1">{{ formatCurrency($stats['today']) }}</p>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4 text-center">
      <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">This Month</p>
      <p class="text-xl font-bold text-orange-600 mt-1">{{ formatCurrency($stats['this_month']) }}</p>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4 text-center">
      <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">All Time</p>
      <p class="text-xl font-bold text-slate-700 mt-1">{{ formatCurrency($stats['total']) }}</p>
    </div>
  </div>

  <form method="GET" class="flex flex-wrap gap-3">
    <select name="category" class="px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none bg-white">
      <option value="">All Categories</option>
      @foreach($categories as $cat)<option value="{{ $cat }}" {{ request('category')==$cat?'selected':'' }}>{{ $cat }}</option>@endforeach
    </select>
    <input type="date" name="from_date" value="{{ request('from_date') }}" class="px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
    <input type="date" name="to_date" value="{{ request('to_date') }}" class="px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
    <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white px-4 py-2 rounded-lg text-sm font-medium">Filter</button>
    @if(request()->hasAny(['category','from_date','to_date']))<a href="{{ route('expenses.index') }}" class="bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 px-4 py-2 rounded-lg text-sm">Clear</a>@endif
  </form>

  <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <table class="w-full">
      <thead class="bg-slate-50"><tr>
        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Date</th>
        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Category</th>
        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Description</th>
        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Paid To</th>
        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Amount</th>
        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Actions</th>
      </tr></thead>
      <tbody class="divide-y divide-slate-100">
        @forelse($expenses as $e)
        <tr class="hover:bg-slate-50">
          <td class="px-4 py-3 text-sm text-slate-600">{{ \Carbon\Carbon::parse($e->date)->format('d M Y') }}</td>
          <td class="px-4 py-3"><span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-600">{{ $e->category }}</span></td>
          <td class="px-4 py-3 text-sm text-slate-700">{{ $e->description }}</td>
          <td class="px-4 py-3 text-sm text-slate-500">{{ $e->paid_to ?? '-' }}</td>
          <td class="px-4 py-3 text-sm text-right font-medium text-slate-900">{{ formatCurrency($e->amount) }}</td>
          <td class="px-4 py-3">
            <div class="flex items-center justify-end gap-2">
              <a href="{{ route('expenses.edit',$e) }}" class="text-slate-400 hover:text-blue-600"><i data-lucide="pencil" class="w-4 h-4"></i></a>
              <form method="POST" action="{{ route('expenses.destroy',$e) }}" class="inline" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button type="submit" class="text-slate-400 hover:text-red-600"><i data-lucide="trash-2" class="w-4 h-4"></i></button></form>
            </div>
          </td>
        </tr>
        @empty
        <tr><td colspan="6" class="px-4 py-12 text-center">
          <i data-lucide="wallet" class="w-10 h-10 text-slate-300 mx-auto mb-3"></i>
          <p class="text-slate-500 font-medium">No expenses yet</p>
          <a href="{{ route('expenses.create') }}" class="text-green-600 text-sm mt-1 inline-block hover:underline">Add first expense</a>
        </td></tr>
        @endforelse
      </tbody>
    </table>
    @if($expenses->hasPages())<div class="px-4 py-3 border-t border-slate-100">{{ $expenses->links() }}</div>@endif
  </div>
</div>
@endsection
