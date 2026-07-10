@extends('layouts.app')
@section('title','New Job Card')
@section('content')
<div class="max-w-2xl mx-auto space-y-6">

  <div class="flex items-center gap-3">
    <a href="{{ route('repairs.index') }}" class="text-slate-400 hover:text-slate-600">
      <i data-lucide="arrow-left" class="w-5 h-5"></i>
    </a>
    <div>
      <h1 class="text-2xl font-bold text-slate-900">New Job Card</h1>
      <p class="text-sm text-slate-500 mt-0.5">Record a new repair job</p>
    </div>
  </div>

  @if($errors->any())
  <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
    <ul class="list-disc list-inside space-y-0.5">
      @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
    </ul>
  </div>
  @endif

  <form method="POST" action="{{ route('repairs.store') }}" class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 space-y-5">
    @csrf

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Customer Name *</label>
        <input type="text" name="customer_name" value="{{ old('customer_name') }}" required autofocus
               class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
               placeholder="e.g. Ahmed Khan">
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Phone</label>
        <input type="text" name="customer_phone" value="{{ old('customer_phone') }}"
               class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
               placeholder="03xx-xxxxxxx">
      </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Device / Item *</label>
        <input type="text" name="device" value="{{ old('device') }}" required
               class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
               placeholder="e.g. Samsung A51, Honda CD-70">
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Serial / IMEI</label>
        <input type="text" name="serial_imei" value="{{ old('serial_imei') }}"
               class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
               placeholder="Optional">
      </div>
    </div>

    <div>
      <label class="block text-sm font-medium text-slate-700 mb-1">Fault / Problem *</label>
      <textarea name="fault" rows="3" required
                class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="What is wrong with it? e.g. Screen broken, engine noise...">{{ old('fault') }}</textarea>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Estimated Cost (PKR)</label>
        <input type="number" name="estimated_cost" value="{{ old('estimated_cost') }}" min="0" step="0.01"
               class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
               placeholder="0">
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Advance Paid (PKR)</label>
        <input type="number" name="advance_paid" value="{{ old('advance_paid', 0) }}" min="0" step="0.01"
               class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>
    </div>

    <div>
      <label class="block text-sm font-medium text-slate-700 mb-1">Notes</label>
      <textarea name="notes" rows="2"
                class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Any extra details (accessories received, condition, etc.)">{{ old('notes') }}</textarea>
    </div>

    <div class="flex items-center justify-end gap-3 pt-2 border-t border-slate-100">
      <a href="{{ route('repairs.index') }}" class="px-4 py-2 border border-slate-200 text-slate-600 rounded-lg text-sm font-medium hover:bg-slate-50">Cancel</a>
      <button type="submit" class="flex items-center gap-2 bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-green-700">
        <i data-lucide="wrench" class="w-4 h-4"></i>Create Job Card
      </button>
    </div>
  </form>
</div>
@endsection
