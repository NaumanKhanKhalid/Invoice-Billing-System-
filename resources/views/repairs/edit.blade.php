@extends('layouts.app')
@section('title','Edit Job Card')
@section('content')
<div class="space-y-6">

  <div class="flex items-center gap-3">
    <a href="{{ route('repairs.show', $job) }}" class="text-slate-400 hover:text-slate-600">
      <i data-lucide="arrow-left" class="w-5 h-5"></i>
    </a>
    <div>
      <h1 class="text-2xl font-bold text-slate-900">Edit {{ $job->job_number }}</h1>
      <p class="text-sm text-slate-500 mt-0.5">{{ $job->device }} — {{ $job->customer_name }}</p>
    </div>
  </div>

  @if($errors->any())
  <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
    <ul class="list-disc list-inside space-y-0.5">
      @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
    </ul>
  </div>
  @endif

  <form method="POST" action="{{ route('repairs.update', $job) }}" class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 space-y-5">
    @csrf
    @method('PUT')

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Customer Name *</label>
        <input type="text" name="customer_name" value="{{ old('customer_name', $job->customer_name) }}" required
               class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Phone</label>
        <input type="text" name="customer_phone" value="{{ old('customer_phone', $job->customer_phone) }}"
               class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Device / Item *</label>
        <input type="text" name="device" value="{{ old('device', $job->device) }}" required
               class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Serial / IMEI</label>
        <input type="text" name="serial_imei" value="{{ old('serial_imei', $job->serial_imei) }}"
               class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>
    </div>

    <div>
      <label class="block text-sm font-medium text-slate-700 mb-1">Fault / Problem *</label>
      <textarea name="fault" rows="3" required
                class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('fault', $job->fault) }}</textarea>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Estimated Cost (PKR)</label>
        <input type="number" name="estimated_cost" value="{{ old('estimated_cost', $job->estimated_cost) }}" min="0" step="0.01"
               class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Advance Paid (PKR)</label>
        <input type="number" name="advance_paid" value="{{ old('advance_paid', $job->advance_paid) }}" min="0" step="0.01"
               class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Final Cost (PKR)</label>
        <input type="number" name="final_cost" value="{{ old('final_cost', $job->final_cost) }}" min="0" step="0.01"
               class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>
    </div>

    <div>
      <label class="block text-sm font-medium text-slate-700 mb-1">Notes</label>
      <textarea name="notes" rows="2"
                class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('notes', $job->notes) }}</textarea>
    </div>

    <div class="flex items-center justify-between pt-2 border-t border-slate-100">
      <button type="submit" form="deleteJobForm"
              onclick="return confirm('Delete job card {{ $job->job_number }}? This cannot be undone.')"
              class="text-red-600 hover:underline text-sm font-medium">Delete Job Card</button>
      <div class="flex items-center gap-3">
        <a href="{{ route('repairs.show', $job) }}" class="px-4 py-2 border border-slate-200 text-slate-600 rounded-lg text-sm font-medium hover:bg-slate-50">Cancel</a>
        <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-green-700">Save Changes</button>
      </div>
    </div>
  </form>

  <form id="deleteJobForm" method="POST" action="{{ route('repairs.destroy', $job) }}" class="hidden">
    @csrf
    @method('DELETE')
  </form>
</div>
@endsection
