@extends('layouts.app')
@section('title','Add Team Member')
@section('content')
<div class="max-w-lg mx-auto space-y-6">
  <div class="flex items-center gap-3">
    <a href="{{ route('tenant.users.index') }}" class="w-9 h-9 rounded-lg border border-slate-200 bg-white flex items-center justify-center text-slate-500 hover:text-slate-700 shadow-sm">
      <i data-lucide="arrow-left" class="w-4 h-4"></i>
    </a>
    <div>
      <h1 class="text-xl font-bold text-slate-900">Add Team Member</h1>
      <p class="text-sm text-slate-500">{{ $count }} of {{ $limit === PHP_INT_MAX ? '∞' : $limit }} slots used</p>
    </div>
  </div>

  <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
    <form method="POST" action="{{ route('tenant.users.store') }}" class="space-y-4">
      @csrf
      <div>
        <label class="block text-xs font-medium text-slate-600 mb-1">Full Name *</label>
        <input type="text" name="name" value="{{ old('name') }}" required
               class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none @error('name') border-red-400 @enderror">
        @error('name')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
      </div>
      <div>
        <label class="block text-xs font-medium text-slate-600 mb-1">Email *</label>
        <input type="email" name="email" value="{{ old('email') }}" required
               class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none @error('email') border-red-400 @enderror">
        @error('email')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
      </div>
      <div>
        <label class="block text-xs font-medium text-slate-600 mb-1">Role *</label>
        <div class="relative">
          <select name="role" class="appearance-none w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none bg-white pr-8">
            <option value="cashier" @selected(old('role')=='cashier')>Cashier — basic access</option>
            <option value="manager" @selected(old('role')=='manager')>Manager — full shop access</option>
            <option value="owner" @selected(old('role')=='owner')>Owner — admin access</option>
          </select>
          <i data-lucide="chevron-down" class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
        </div>
      </div>
      <div>
        <label class="block text-xs font-medium text-slate-600 mb-1">Password *</label>
        <input type="password" name="password" required minlength="6"
               class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none @error('password') border-red-400 @enderror">
        @error('password')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
      </div>
      <div>
        <label class="block text-xs font-medium text-slate-600 mb-1">Confirm Password *</label>
        <input type="password" name="password_confirmation" required
               class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
      </div>
      <div class="flex gap-3 pt-2">
        <a href="{{ route('tenant.users.index') }}" class="flex-1 text-center px-4 py-2 text-sm border border-slate-200 rounded-lg hover:bg-slate-50 text-slate-700">Cancel</a>
        <button type="submit" class="flex-1 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium">Add Member</button>
      </div>
    </form>
  </div>
</div>
@endsection
