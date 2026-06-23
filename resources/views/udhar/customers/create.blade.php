@extends('layouts.app')
@section('title','New Udhar Customer')
@section('content')
<div class="space-y-6">

  <div class="flex items-center gap-3">
    <a href="{{ route('udhar-customers.index') }}" class="w-9 h-9 rounded-lg border border-slate-200 bg-white flex items-center justify-center text-slate-500 hover:text-slate-700 transition-colors shadow-sm">
      <i data-lucide="arrow-left" class="w-4 h-4"></i>
    </a>
    <div>
      <h1 class="text-xl font-bold text-slate-900">New Udhar Customer</h1>
      <p class="text-sm text-slate-500">Add a registered credit customer</p>
    </div>
  </div>

  @if($errors->any())
  <div class="flex items-start gap-3 bg-red-50 border border-red-200 rounded-xl px-4 py-3">
    <i data-lucide="alert-circle" class="w-4 h-4 text-red-500 mt-0.5 shrink-0"></i>
    <ul class="text-sm text-red-700 space-y-0.5">
      @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
    </ul>
  </div>
  @endif

  <form method="POST" action="{{ route('udhar-customers.store') }}">
    @csrf
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 space-y-4">

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="sm:col-span-2">
          <label class="block text-sm font-medium text-slate-700 mb-1">Name <span class="text-red-500">*</span></label>
          <input type="text" name="name" value="{{ old('name') }}" required
                 class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none"
                 placeholder="e.g. Ahmed Bhai">
          @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Phone</label>
          <input type="text" name="phone" value="{{ old('phone') }}"
                 class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none"
                 placeholder="03001234567">
          @error('phone')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">WhatsApp Number</label>
          <input type="text" name="whatsapp_number" value="{{ old('whatsapp_number') }}"
                 class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none"
                 placeholder="Same as phone if blank">
          @error('whatsapp_number')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="sm:col-span-2">
          <label class="block text-sm font-medium text-slate-700 mb-1">Address</label>
          <input type="text" name="address" value="{{ old('address') }}"
                 class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none"
                 placeholder="Optional">
          @error('address')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="sm:col-span-2">
          <label class="block text-sm font-medium text-slate-700 mb-1">Notes</label>
          <textarea name="notes" rows="3"
                    class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none"
                    placeholder="e.g. Regular customer, mornings only, trusts on handshake...">{{ old('notes') }}</textarea>
          @error('notes')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
      </div>

      <div class="flex gap-3 pt-2">
        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2.5 rounded-lg text-sm font-semibold transition-colors">
          Save Customer
        </button>
        <button type="submit" name="redirect_to_udhar" value="1"
                class="bg-slate-800 hover:bg-slate-900 text-white px-6 py-2.5 rounded-lg text-sm font-semibold transition-colors">
          Save & Add Udhar Entry
        </button>
        <a href="{{ route('udhar-customers.index') }}" class="bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 px-6 py-2.5 rounded-lg text-sm font-medium transition-colors">
          Cancel
        </a>
      </div>

    </div>
  </form>
</div>
@endsection
