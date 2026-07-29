@extends('layouts.app')
@section('title','Add Team Member')
@section('content')
<div class="space-y-6" x-data="{ role: '{{ old('role', 'cashier') }}' }">
  {{-- Header --}}
  <div class="flex items-center gap-3">
    <a href="{{ route('tenant.users.index') }}" class="w-9 h-9 rounded-lg border border-slate-200 bg-white flex items-center justify-center text-slate-500 hover:text-slate-700 shadow-sm">
      <i data-lucide="arrow-left" class="w-4 h-4"></i>
    </a>
    <div>
      <h1 class="text-xl font-bold text-slate-900">{{ __('pages.add_team_member') }}</h1>
      <p class="text-sm text-slate-500">{{ $count }} of {{ $limit === PHP_INT_MAX ? '∞' : $limit }} slots used</p>
    </div>
  </div>

  <form method="POST" action="{{ route('tenant.users.store') }}" class="space-y-5">
    @csrf

    {{-- Details --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
      <div class="bg-slate-50 border-b border-slate-100 px-5 py-3 flex items-center gap-2">
        <i data-lucide="user" class="w-4 h-4 text-slate-600"></i>
        <span class="font-semibold text-slate-800">{{ __('pages.member_details') }}</span>
      </div>
      <div class="p-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wide">{{ __('pages.full_name') }} *</label>
          <input type="text" name="name" value="{{ old('name') }}" required
                 class="w-full px-3 py-2.5 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none @error('name') border-red-400 @enderror">
          @error('name')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wide">{{ __('pages.email') }} *</label>
          <input type="email" name="email" value="{{ old('email') }}" required
                 class="w-full px-3 py-2.5 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none @error('email') border-red-400 @enderror">
          @error('email')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wide">{{ __('pages.password') }} *</label>
          <input type="password" name="password" required minlength="6"
                 class="w-full px-3 py-2.5 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none @error('password') border-red-400 @enderror">
          @error('password')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wide">{{ __('pages.confirm_password') }} *</label>
          <input type="password" name="password_confirmation" required
                 class="w-full px-3 py-2.5 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
        </div>
      </div>
    </div>

    {{-- Role --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
      <div class="bg-slate-50 border-b border-slate-100 px-5 py-3 flex items-center gap-2">
        <i data-lucide="shield" class="w-4 h-4 text-slate-600"></i>
        <span class="font-semibold text-slate-800">{{ __('pages.role_access') }}</span>
      </div>
      <div class="p-5">
        <input type="hidden" name="role" x-model="role">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
          @php $roles = [
            ['v'=>'cashier','t'=>'Cashier','i'=>'scan-line','d'=>'Sirf sales counter — POS, sales, udhar, quotations.'],
            ['v'=>'manager','t'=>'Manager','i'=>'briefcase','d'=>'Poora shop access — reports, purchases, stock. Settings/team nahi.'],
            ['v'=>'owner','t'=>'Owner','i'=>'crown','d'=>'Full admin — settings, team, sab kuch.'],
          ]; @endphp
          @foreach($roles as $r)
          <button type="button" @click="role = '{{ $r['v'] }}'"
                  :class="role === '{{ $r['v'] }}' ? 'border-green-500 bg-green-50 ring-2 ring-green-200' : 'border-slate-200 bg-white hover:border-green-300'"
                  class="text-left rounded-xl border p-4 transition">
            <div class="flex items-center justify-between mb-1.5">
              <span class="w-8 h-8 rounded-lg flex items-center justify-center"
                    :class="role === '{{ $r['v'] }}' ? 'bg-green-600 text-white' : 'bg-slate-100 text-slate-500'">
                <i data-lucide="{{ $r['i'] }}" class="w-4 h-4"></i>
              </span>
              <i data-lucide="check-circle-2" class="w-5 h-5 text-green-600" x-show="role === '{{ $r['v'] }}'" x-cloak></i>
            </div>
            <p class="font-bold text-slate-900 text-sm">{{ $r['t'] }}</p>
            <p class="text-[11px] text-slate-500 mt-0.5 leading-snug">{{ $r['d'] }}</p>
          </button>
          @endforeach
        </div>
      </div>
    </div>

    {{-- Actions --}}
    <div class="flex items-center justify-end gap-3">
      <a href="{{ route('tenant.users.index') }}" class="px-4 py-2 text-sm text-slate-600 hover:text-slate-900 transition">{{ __('common.cancel') }}</a>
      <button type="submit" class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white px-6 py-2.5 rounded-xl text-sm font-bold transition shadow-sm">
        <i data-lucide="user-plus" class="w-4 h-4"></i> Add Member
      </button>
    </div>
  </form>
</div>
@endsection
