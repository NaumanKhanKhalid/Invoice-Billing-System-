@extends('layouts.app')
@section('title','Admin — Tenants')
@section('content')
<div class="space-y-6">
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-2xl font-bold text-slate-900">{{ __('admin.tenant_management') }}</h1>
      <p class="text-sm text-slate-500 mt-0.5">{{ __('admin.all_shops') }}</p>
    </div>
    <a href="{{ route('admin.tenants.create') }}" class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
      <i data-lucide="plus" class="w-4 h-4"></i>New Tenant
    </a>
  </div>

  {{-- Stats --}}
  <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 text-center">
      <p class="text-xs text-slate-400 font-semibold uppercase tracking-wider mb-1">{{ __('admin.total_shops') }}</p>
      <p class="text-3xl font-bold text-slate-900">{{ $stats['total'] }}</p>
    </div>
    <div class="bg-white rounded-xl border border-green-200 shadow-sm p-5 text-center">
      <p class="text-xs text-green-600 font-semibold uppercase tracking-wider mb-1">{{ __('common.active') }}</p>
      <p class="text-3xl font-bold text-green-600">{{ $stats['active'] }}</p>
    </div>
    <div class="bg-white rounded-xl border border-red-200 shadow-sm p-5 text-center">
      <p class="text-xs text-red-500 font-semibold uppercase tracking-wider mb-1">{{ __('admin.expired') }}</p>
      <p class="text-3xl font-bold text-red-600">{{ $stats['expired'] }}</p>
    </div>
    <div class="bg-white rounded-xl border border-blue-200 shadow-sm p-5 text-center">
      <p class="text-xs text-blue-500 font-semibold uppercase tracking-wider mb-1">{{ __('common.this_month') }}</p>
      <p class="text-2xl font-bold text-blue-600">{{ number_format($stats['revenue_month']) }}</p>
      <p class="text-xs text-slate-400 mt-0.5">Total: PKR {{ number_format($stats['revenue_total']) }}</p>
    </div>
  </div>

  {{-- Expiring soon alert --}}
  @php $expiringSoon = $tenants->filter(fn($t) => $t->plan_expires_at && $t->plan_expires_at > now() && $t->plan_expires_at <= now()->addDays(7)) @endphp
  @if($expiringSoon->count())
  <div class="bg-amber-50 border border-amber-200 rounded-xl p-4">
    <p class="text-sm font-semibold text-amber-800 mb-3">⚠️ {{ $expiringSoon->count() }} tenant(s) expiring within 7 days</p>
    <div class="space-y-2">
      @foreach($expiringSoon as $t)
      <div class="flex items-center justify-between bg-white rounded-lg border border-amber-100 px-3 py-2">
        <div>
          <span class="text-sm font-medium text-slate-900">{{ $t->shop_name }}</span>
          <span class="text-xs text-slate-400 ml-2">{{ $t->owner_name }}</span>
        </div>
        <div class="flex items-center gap-3">
          <span class="text-xs text-amber-700 font-semibold">{{ $t->plan_expires_at->format('d M Y') }}</span>
          @if($t->owner_phone)
          <a href="https://wa.me/{{ wa_number($t->owner_phone) }}?text={{ urlencode('Assalam o Alaikum ' . $t->owner_name . ' bhai! Aapka ShopSaas plan ' . $t->plan_expires_at->format('d M Y') . ' ko expire ho raha hai. Renew karne ke liye rabta karen.') }}"
             target="_blank"
             class="inline-flex items-center gap-1 px-2 py-1 bg-green-100 hover:bg-green-200 text-green-700 rounded text-xs font-medium">
            WhatsApp
          </a>
          @endif
          <a href="{{ route('admin.tenants.show', $t) }}" class="text-xs text-slate-500 hover:text-green-600">Renew →</a>
        </div>
      </div>
      @endforeach
    </div>
  </div>
  @endif

  {{-- Filters --}}
  <div class="bg-white rounded-xl border border-slate-200 shadow-sm px-4 py-3">
    <form method="GET" class="flex flex-wrap items-center gap-3">
      <div class="flex rounded-lg border border-slate-200 overflow-hidden">
        @foreach(['all'=>'All','active'=>'Active','expired'=>'Expired'] as $k=>$lbl)
        <a href="{{ route('admin.tenants.index', array_merge(request()->except('status'), $k==='all' ? [] : ['status'=>$k])) }}"
           class="px-4 py-2 text-sm font-medium border-l first:border-l-0 border-slate-200 transition-colors {{ (request('status','all')===$k || (!request('status') && $k==='all')) ? 'bg-slate-800 text-white' : 'text-slate-600 hover:bg-slate-50' }}">{{ $lbl }}</a>
        @endforeach
      </div>
      <select name="plan" onchange="this.form.submit()" class="px-3 py-2 text-sm border border-slate-200 rounded-lg bg-white text-slate-700 outline-none focus:ring-2 focus:ring-green-300">
        <option value="">{{ __('admin.all_plans') }}</option>
        @foreach(['free','pro','business'] as $p)<option value="{{ $p }}" {{ request('plan')==$p?'selected':'' }}>{{ ucfirst($p) }}</option>@endforeach
      </select>
      <select name="shop_type" onchange="this.form.submit()" class="px-3 py-2 text-sm border border-slate-200 rounded-lg bg-white text-slate-700 outline-none focus:ring-2 focus:ring-green-300">
        <option value="">{{ __('admin.all_types') }}</option>
        @foreach(['chicken','hardware','mobile','bike','general','medical','coaching'] as $t)<option value="{{ $t }}" {{ request('shop_type')==$t?'selected':'' }}>{{ ucfirst($t) }}</option>@endforeach
      </select>
      @if(request('status'))<input type="hidden" name="status" value="{{ request('status') }}">@endif
      <div class="relative flex-1 min-w-[180px]">
        <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none"></i>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('admin.search_tenant') }}"
               class="w-full pl-9 pr-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
      </div>
      <button type="submit" class="flex items-center gap-2 bg-slate-800 hover:bg-slate-900 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
        <i data-lucide="search" class="w-4 h-4"></i>Search
      </button>
      @if(request()->hasAny(['search','plan','shop_type','status']))
      <a href="{{ route('admin.tenants.index') }}" class="flex items-center gap-1.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-600 px-4 py-2 rounded-lg text-sm transition-colors">
        <i data-lucide="x" class="w-4 h-4"></i>Clear
      </a>
      @endif
      <span class="ml-auto text-sm text-slate-500"><span class="font-semibold text-slate-700">{{ $tenants->count() }}</span> shown</span>
    </form>
  </div>

  {{-- Table --}}
  <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <table class="w-full">
      <thead class="bg-slate-50">
        <tr>
          <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">{{ __('admin.shop') }}</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">{{ __('pages.type') }}</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">{{ __('admin.owner') }}</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">{{ __('admin.plan') }}</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">{{ __('admin.expires') }}</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">{{ __('common.status') }}</th>
          <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">{{ __('common.actions') }}</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        @forelse($tenants as $tenant)
        @php
          $expired = $tenant->plan_expires_at && $tenant->plan_expires_at < now();
          $daysLeft = $tenant->plan_expires_at ? now()->diffInDays($tenant->plan_expires_at, false) : null;
        @endphp
        <tr class="hover:bg-slate-50 {{ $expired ? 'bg-red-50' : '' }}">
          <td class="px-4 py-3">
            <a href="{{ route('admin.tenants.show', $tenant) }}" class="font-semibold text-slate-900 hover:text-green-600">{{ $tenant->shop_name }}</a>
            <p class="text-xs text-slate-400">{{ $tenant->id }}</p>
          </td>
          <td class="px-4 py-3">
            @php $typeClass = [
              'chicken'=>'bg-green-100 text-green-700','bike'=>'bg-blue-100 text-blue-700',
              'hardware'=>'bg-orange-100 text-orange-700','mobile'=>'bg-purple-100 text-purple-700',
              'general'=>'bg-slate-100 text-slate-700','medical'=>'bg-teal-100 text-teal-700',
              'coaching'=>'bg-indigo-100 text-indigo-700',
            ][$tenant->shop_type] ?? 'bg-slate-100 text-slate-700'; @endphp
            <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $typeClass }}">{{ ucfirst($tenant->shop_type) }}</span>
          </td>
          <td class="px-4 py-3">
            <p class="text-sm font-medium text-slate-900">{{ $tenant->owner_name }}</p>
            <p class="text-xs text-slate-400">{{ $tenant->owner_email }}</p>
          </td>
          <td class="px-4 py-3">
            <span class="inline-flex px-2 py-0.5 rounded text-xs font-semibold uppercase
              {{ $tenant->plan === 'business' ? 'bg-purple-100 text-purple-700' : ($tenant->plan === 'pro' ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-600') }}">
              {{ $tenant->plan }}
            </span>
          </td>
          <td class="px-4 py-3 text-sm {{ $expired ? 'text-red-600 font-semibold' : ($daysLeft <= 7 ? 'text-amber-600 font-semibold' : 'text-slate-600') }}">
            @if($tenant->plan_expires_at)
              {{ $tenant->plan_expires_at->format('d M Y') }}
              @if(!$expired && $daysLeft <= 30)<br><span class="text-xs">{{ $daysLeft }} days left</span>@endif
              @if($expired)<br><span class="text-xs">{{ __('admin.expired') }}</span>@endif
            @else —
            @endif
          </td>
          <td class="px-4 py-3">
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium
              {{ $tenant->is_active && !$expired ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
              <span class="w-1.5 h-1.5 rounded-full {{ $tenant->is_active && !$expired ? 'bg-green-500' : 'bg-red-500' }}"></span>
              {{ $tenant->is_active && !$expired ? 'Active' : 'Inactive' }}
            </span>
          </td>
          <td class="px-4 py-3 text-right">
            <div class="flex items-center justify-end gap-1.5">
              <form method="POST" action="{{ route('admin.tenants.impersonate', $tenant) }}" target="_blank">
                @csrf
                <button type="submit"
                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-purple-50 hover:bg-purple-100 text-purple-600 hover:text-purple-700 text-xs font-semibold transition-colors"
                        title="Login as this tenant">
                  <i data-lucide="log-in" class="w-3.5 h-3.5"></i>Login As
                </button>
              </form>
              <a href="{{ route('admin.tenants.show', $tenant) }}" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-green-100 text-slate-500 hover:text-green-700 text-xs font-medium transition-colors">
                <i data-lucide="eye" class="w-3.5 h-3.5"></i>View
              </a>
              <a href="{{ route('admin.tenants.edit', $tenant) }}" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-blue-100 text-slate-500 hover:text-blue-700 text-xs font-medium transition-colors">
                <i data-lucide="pencil" class="w-3.5 h-3.5"></i>Edit
              </a>
              <form method="POST" action="{{ route('admin.tenants.toggle-active', $tenant) }}"
                    data-confirm-title="{{ $tenant->is_active ? 'Suspend shop?' : 'Activate shop?' }}"
                    data-confirm-message="{{ $tenant->shop_name }} ko {{ $tenant->is_active ? 'suspend' : 'activate' }} karna hai?"
                    data-confirm-text="Haan">
                @csrf
                @if($tenant->is_active)
                <button type="submit" title="Suspend" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-red-100 text-slate-500 hover:text-red-700 text-xs font-medium transition-colors">
                  <i data-lucide="pause" class="w-3.5 h-3.5"></i>Suspend
                </button>
                @else
                <button type="submit" title="Activate" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-green-100 hover:bg-green-200 text-green-700 text-xs font-medium transition-colors">
                  <i data-lucide="play" class="w-3.5 h-3.5"></i>Activate
                </button>
                @endif
              </form>
            </div>
          </td>
        </tr>
        @empty
        <tr><td colspan="7" class="px-4 py-12 text-center">
          <i data-lucide="store" class="w-10 h-10 text-slate-300 mx-auto mb-3"></i>
          <p class="text-slate-400">{{ __('admin.no_tenants') }}</p>
          <a href="{{ route('admin.tenants.create') }}" class="text-green-600 text-sm hover:underline mt-1 inline-block">{{ __('admin.create_first_tenant') }}</a>
        </td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
