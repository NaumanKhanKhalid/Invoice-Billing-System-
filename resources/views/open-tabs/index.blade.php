@extends('layouts.app')
@section('title','Open Tabs')
@section('content')
<div class="space-y-6">

  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-2xl font-bold text-slate-900">Open Tabs</h1>
      <p class="text-sm text-slate-500 mt-0.5">Running bills — add items anytime, close when done</p>
    </div>
    <button onclick="document.getElementById('newTabModal').classList.remove('hidden')"
            class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-colors">
      <i data-lucide="plus" class="w-4 h-4"></i>New Tab
    </button>
  </div>

  {{-- Open Tabs --}}
  @if($openTabs->isNotEmpty())
  <div>
    <h2 class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-3 flex items-center gap-2">
      <span class="w-2 h-2 rounded-full bg-green-500"></span>Open ({{ $openTabs->count() }})
    </h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
      @foreach($openTabs as $tab)
      <a href="{{ route('open-tabs.show', $tab) }}"
         class="bg-white rounded-xl border border-green-200 shadow-sm p-5 hover:border-green-400 hover:shadow-md transition-all group">
        <div class="flex items-start justify-between mb-3">
          <div>
            <p class="font-bold text-slate-900 group-hover:text-green-700 transition-colors">{{ $tab->customer_name }}</p>
            @if($tab->customer_phone)
            <p class="text-xs text-slate-400 mt-0.5">{{ $tab->customer_phone }}</p>
            @endif
          </div>
          <span class="text-xs font-semibold text-green-600 bg-green-50 px-2 py-0.5 rounded-full border border-green-200">
            {{ $tab->tab_number }}
          </span>
        </div>
        <div class="flex items-end justify-between">
          <div>
            <p class="text-xs text-slate-400">{{ $tab->items_count }} item{{ $tab->items_count != 1 ? 's' : '' }}</p>
            <p class="text-xs text-slate-400 mt-0.5">{{ $tab->created_at->format('d M, h:i A') }}</p>
          </div>
          <div class="text-right">
            <p class="text-xl font-bold text-slate-900">PKR {{ number_format($tab->total) }}</p>
            <p class="text-xs text-green-600 font-medium group-hover:underline">Open →</p>
          </div>
        </div>
      </a>
      @endforeach
    </div>
  </div>
  @else
  <div class="bg-white rounded-xl border border-dashed border-slate-300 p-12 text-center">
    <i data-lucide="receipt" class="w-12 h-12 text-slate-300 mx-auto mb-3"></i>
    <p class="text-slate-500 font-medium">No open tabs right now</p>
    <p class="text-sm text-slate-400 mt-1">Start a new tab when a customer begins their work</p>
    <button onclick="document.getElementById('newTabModal').classList.remove('hidden')"
            class="mt-4 inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
      <i data-lucide="plus" class="w-4 h-4"></i>Open First Tab
    </button>
  </div>
  @endif

  {{-- Closed Tabs --}}
  @if($closedTabs->isNotEmpty())
  <div>
    <h2 class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-3 flex items-center gap-2">
      <span class="w-2 h-2 rounded-full bg-slate-400"></span>Recently Closed
    </h2>
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
      <table class="w-full">
        <thead class="bg-slate-50 border-b border-slate-100">
          <tr>
            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Tab #</th>
            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Customer</th>
            <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Items</th>
            <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Total</th>
            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Closed</th>
            <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          @foreach($closedTabs as $tab)
          <tr class="hover:bg-slate-50">
            <td class="px-4 py-3 text-xs font-semibold text-slate-500">{{ $tab->tab_number }}</td>
            <td class="px-4 py-3">
              <p class="text-sm font-medium text-slate-900">{{ $tab->customer_name }}</p>
              @if($tab->customer_phone)<p class="text-xs text-slate-400">{{ $tab->customer_phone }}</p>@endif
            </td>
            <td class="px-4 py-3 text-sm text-right text-slate-500">{{ $tab->items_count }}</td>
            <td class="px-4 py-3 text-sm text-right font-bold text-slate-900">PKR {{ number_format($tab->total) }}</td>
            <td class="px-4 py-3 text-xs text-slate-400">{{ $tab->closed_at?->format('d M, h:i A') }}</td>
            <td class="px-4 py-3 text-right">
              <a href="{{ route('open-tabs.receipt', $tab) }}"
                 class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-medium transition-colors">
                <i data-lucide="printer" class="w-3.5 h-3.5"></i>Receipt
              </a>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
  @endif
</div>

{{-- New Tab Modal --}}
<div id="newTabModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4"
     style="background:rgba(15,23,42,0.5);backdrop-filter:blur(4px)">
  <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm">
    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
      <h3 class="font-bold text-slate-900">Open New Tab</h3>
      <button onclick="document.getElementById('newTabModal').classList.add('hidden')"
              class="text-slate-400 hover:text-slate-600">
        <i data-lucide="x" class="w-5 h-5"></i>
      </button>
    </div>
    <form method="POST" action="{{ route('open-tabs.store') }}" class="p-6 space-y-4">
      @csrf
      <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wide">Customer / Mechanic Name *</label>
        <input type="text" name="customer_name" required autofocus
               class="w-full px-3 py-2.5 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-green-300 outline-none"
               placeholder="e.g. Khalid Mechanic">
      </div>
      <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wide">Phone (optional)</label>
        <input type="text" name="customer_phone"
               class="w-full px-3 py-2.5 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-green-300 outline-none"
               placeholder="03xx-xxxxxxx">
      </div>
      <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wide">Note (optional)</label>
        <input type="text" name="notes"
               class="w-full px-3 py-2.5 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-green-300 outline-none"
               placeholder="e.g. Honda 125 repair">
      </div>
      <div class="flex gap-3 pt-2">
        <button type="button" onclick="document.getElementById('newTabModal').classList.add('hidden')"
                class="flex-1 px-4 py-2.5 border border-slate-200 text-slate-600 rounded-lg text-sm font-medium hover:bg-slate-50 transition-colors">
          Cancel
        </button>
        <button type="submit"
                class="flex-1 bg-green-600 hover:bg-green-700 text-white px-4 py-2.5 rounded-lg text-sm font-semibold transition-colors">
          Open Tab →
        </button>
      </div>
    </form>
  </div>
</div>
@endsection
