@extends('layouts.app')
@section('title','Admin — Tenants')
@section('content')
<div class="space-y-6">
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-2xl font-bold text-slate-900">Tenant Management</h1>
      <p class="text-sm text-slate-500 mt-0.5">All client shops on this platform</p>
    </div>
    <a href="{{ route('admin.tenants.create') }}" class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
      <i data-lucide="plus" class="w-4 h-4"></i>New Tenant
    </a>
  </div>

  {{-- Stats --}}
  <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 text-center">
      <p class="text-xs text-slate-400 font-semibold uppercase tracking-wider mb-1">Total Shops</p>
      <p class="text-3xl font-bold text-slate-900">{{ $stats['total'] }}</p>
    </div>
    <div class="bg-white rounded-xl border border-green-200 shadow-sm p-5 text-center">
      <p class="text-xs text-green-600 font-semibold uppercase tracking-wider mb-1">Active</p>
      <p class="text-3xl font-bold text-green-600">{{ $stats['active'] }}</p>
    </div>
    <div class="bg-white rounded-xl border border-red-200 shadow-sm p-5 text-center">
      <p class="text-xs text-red-500 font-semibold uppercase tracking-wider mb-1">Expired</p>
      <p class="text-3xl font-bold text-red-600">{{ $stats['expired'] }}</p>
    </div>
    <div class="bg-white rounded-xl border border-blue-200 shadow-sm p-5 text-center">
      <p class="text-xs text-blue-500 font-semibold uppercase tracking-wider mb-1">This Month Revenue</p>
      <p class="text-3xl font-bold text-blue-600">—</p>
    </div>
  </div>

  {{-- Table --}}
  <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <table class="w-full">
      <thead class="bg-slate-50">
        <tr>
          <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Shop</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Type</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Owner</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Plan</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Expires</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Status</th>
          <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Actions</th>
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
            @php $typeColors = ['chicken'=>'green','bike'=>'blue','hardware'=>'orange','mobile'=>'purple','general'=>'gray'] @endphp
            <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-{{ $typeColors[$tenant->shop_type] ?? 'gray' }}-100 text-{{ $typeColors[$tenant->shop_type] ?? 'gray' }}-700">
              {{ ucfirst($tenant->shop_type) }}
            </span>
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
              @if($expired)<br><span class="text-xs">Expired</span>@endif
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
              <a href="{{ route('admin.tenants.show', $tenant) }}" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-green-100 text-slate-500 hover:text-green-700 text-xs font-medium transition-colors">
                <i data-lucide="eye" class="w-3.5 h-3.5"></i>View
              </a>
              <a href="{{ route('admin.tenants.edit', $tenant) }}" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-blue-100 text-slate-500 hover:text-blue-700 text-xs font-medium transition-colors">
                <i data-lucide="pencil" class="w-3.5 h-3.5"></i>Edit
              </a>
            </div>
          </td>
        </tr>
        @empty
        <tr><td colspan="7" class="px-4 py-12 text-center">
          <i data-lucide="store" class="w-10 h-10 text-slate-300 mx-auto mb-3"></i>
          <p class="text-slate-400">No tenants yet</p>
          <a href="{{ route('admin.tenants.create') }}" class="text-green-600 text-sm hover:underline mt-1 inline-block">Create first tenant</a>
        </td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
