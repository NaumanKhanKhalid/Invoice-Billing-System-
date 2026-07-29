@extends('layouts.app')
@section('title','Expenses')
@section('content')
<div class="space-y-5">
  <div class="flex items-center justify-between">
    <div><h1 class="text-2xl font-bold text-slate-900">{{ __('pages.expenses') }}</h1><p class="text-sm text-slate-500 mt-0.5">{{ __('pages.daily_expenses') }}</p></div>
    <a href="{{ route('expenses.create') }}" class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors"><i data-lucide="plus" class="w-4 h-4"></i>{{ __('pages.add_expense') }}</a>
  </div>

  <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
      <div class="flex items-center justify-between mb-3">
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">{{ __('common.today') }}</p>
        <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center">
          <i data-lucide="calendar" class="w-4 h-4 text-slate-500"></i>
        </div>
      </div>
      <p class="text-xl font-bold text-slate-900">{{ formatCurrency($stats['today']) }}</p>
    </div>
    <div class="bg-white rounded-xl border border-orange-200 shadow-sm p-5">
      <div class="flex items-center justify-between mb-3">
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">{{ __('common.this_month') }}</p>
        <div class="w-8 h-8 rounded-lg bg-orange-100 flex items-center justify-center">
          <i data-lucide="trending-up" class="w-4 h-4 text-orange-500"></i>
        </div>
      </div>
      <p class="text-xl font-bold text-orange-600">{{ formatCurrency($stats['this_month']) }}</p>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
      <div class="flex items-center justify-between mb-3">
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">{{ __('common.all_time') }}</p>
        <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center">
          <i data-lucide="wallet" class="w-4 h-4 text-slate-500"></i>
        </div>
      </div>
      <p class="text-xl font-bold text-slate-700">{{ formatCurrency($stats['total']) }}</p>
    </div>
  </div>

  <div class="bg-white rounded-xl border border-slate-200 shadow-sm px-4 py-3">
    <form method="GET" class="flex flex-wrap gap-3 items-center">
      <div class="relative">
        <i data-lucide="tag" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none"></i>
        <select name="category" class="pl-9 pr-8 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none bg-white text-slate-700 appearance-none cursor-pointer">
          <option value="">{{ __('common.all_categories') }}</option>
          @foreach($categories as $cat)<option value="{{ $cat }}" {{ request('category')==$cat?'selected':'' }}>{{ $cat }}</option>@endforeach
        </select>
        <i data-lucide="chevron-down" class="absolute right-2 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none"></i>
      </div>
      <div class="relative">
        <i data-lucide="calendar" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none"></i>
        <input type="date" name="from_date" value="{{ request('from_date') }}"
               class="pl-9 pr-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none text-slate-700">
      </div>
      <div class="relative">
        <i data-lucide="calendar" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none"></i>
        <input type="date" name="to_date" value="{{ request('to_date') }}"
               class="pl-9 pr-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none text-slate-700">
      </div>
      <button type="submit" class="flex items-center gap-2 bg-slate-800 hover:bg-slate-900 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
        <i data-lucide="search" class="w-4 h-4"></i> Filter
      </button>
      @if(request()->hasAny(['category','from_date','to_date']))
      <a href="{{ route('expenses.index') }}" class="flex items-center gap-1.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-600 px-4 py-2 rounded-lg text-sm transition-colors">
        <i data-lucide="x" class="w-4 h-4"></i> Clear
      </a>
      @endif
      <div class="ml-auto flex items-center gap-4 text-sm text-slate-500">
        <span class="flex items-center gap-1.5">
          <i data-lucide="list" class="w-4 h-4 text-slate-400"></i>
          <span class="font-semibold text-slate-700">{{ $expenses->total() }}</span> entries
        </span>
        <span class="w-px h-5 bg-slate-200"></span>
        <span class="flex items-center gap-1.5">
          <i data-lucide="banknote" class="w-4 h-4 text-slate-400"></i>
          <span class="font-semibold text-slate-700">{{ formatCurrency($filteredTotal) }}</span>
        </span>
      </div>
    </form>
  </div>

  <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden table-responsive">
    <table class="w-full">
      <thead class="bg-slate-50"><tr>
        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">{{ __('common.date') }}</th>
        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">{{ __('common.category') }}</th>
        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">{{ __('common.description') }}</th>
        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">{{ __('common.paid_to') }}</th>
        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">{{ __('common.amount') }}</th>
        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">{{ __('common.actions') }}</th>
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
              <a href="{{ route('expenses.edit',$e) }}" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-blue-100 text-slate-500 hover:text-blue-700 text-xs font-medium transition-colors"><i data-lucide="pencil" class="w-3.5 h-3.5"></i>{{ __('common.edit') }}</a>
              <form method="POST" action="{{ route('expenses.destroy',$e) }}" class="inline" data-confirm-title="Delete Expense?" data-confirm-message="Are you sure you want to delete this expense?" data-confirm-text="Yes, Delete">@csrf @method('DELETE')<button type="submit" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-red-100 text-slate-500 hover:text-red-700 text-xs font-medium transition-colors"><i data-lucide="trash-2" class="w-3.5 h-3.5"></i>{{ __('common.delete') }}</button></form>
            </div>
          </td>
        </tr>
        @empty
        <tr><td colspan="6" class="px-4 py-12 text-center">
          <i data-lucide="wallet" class="w-10 h-10 text-slate-300 mx-auto mb-3"></i>
          <p class="text-slate-500 font-medium">{{ __('pages.no_expenses') }}</p>
          <a href="{{ route('expenses.create') }}" class="text-green-600 text-sm mt-1 inline-block hover:underline">{{ __('pages.add_first_expense') }}</a>
        </td></tr>
        @endforelse
      </tbody>
    </table>
    @if($expenses->hasPages())<div class="px-4 py-3 border-t border-slate-100">{{ $expenses->links() }}</div>@endif
  </div>
</div>
@endsection
