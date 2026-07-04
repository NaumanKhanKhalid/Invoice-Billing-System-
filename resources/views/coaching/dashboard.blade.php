@extends('layouts.app')
@section('title','Coaching Dashboard')
@section('content')
<div class="space-y-6">

  <div>
    <h1 class="text-2xl font-bold text-slate-900">Dashboard</h1>
    <p class="text-sm text-slate-500 mt-0.5">{{ $monthLabel }} overview</p>
  </div>

  {{-- KPI Cards --}}
  <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 text-center">
      <p class="text-xs text-slate-400 font-semibold uppercase tracking-wider mb-1">Total Students</p>
      <p class="text-3xl font-bold text-slate-900">{{ $totalStudents }}</p>
    </div>
    <div class="bg-white rounded-xl border border-green-200 shadow-sm p-5 text-center">
      <p class="text-xs text-green-600 font-semibold uppercase tracking-wider mb-1">Active</p>
      <p class="text-3xl font-bold text-green-600">{{ $activeStudents }}</p>
    </div>
    <div class="bg-white rounded-xl border border-blue-200 shadow-sm p-5 text-center">
      <p class="text-xs text-blue-500 font-semibold uppercase tracking-wider mb-1">Batches Running</p>
      <p class="text-3xl font-bold text-blue-600">{{ $totalBatches }}</p>
    </div>
    <div class="bg-white rounded-xl border border-red-200 shadow-sm p-5 text-center">
      <p class="text-xs text-red-500 font-semibold uppercase tracking-wider mb-1">Fee Pending</p>
      <p class="kpi-value money text-2xl font-bold text-red-600">PKR {{ number_format($pending) }}</p>
    </div>
  </div>

  {{-- Fee Summary --}}
  <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
    <h2 class="font-semibold text-slate-900 mb-4 flex items-center gap-2">
      <i data-lucide="wallet" class="w-4 h-4 text-slate-400"></i>{{ $monthLabel }} Fee Collection
    </h2>
    <div class="grid grid-cols-3 grid-stack-sm gap-4">
      <div class="text-center p-4 bg-green-50 rounded-xl">
        <p class="text-xs text-green-600 font-semibold uppercase mb-1">Collected</p>
        <p class="kpi-value money text-2xl font-bold text-green-700">PKR {{ number_format($collected) }}</p>
      </div>
      <div class="text-center p-4 bg-slate-50 rounded-xl">
        <p class="text-xs text-slate-500 font-semibold uppercase mb-1">Expected</p>
        <p class="kpi-value money text-2xl font-bold text-slate-700">PKR {{ number_format($expected) }}</p>
      </div>
      <div class="text-center p-4 bg-red-50 rounded-xl">
        <p class="text-xs text-red-500 font-semibold uppercase mb-1">Remaining</p>
        <p class="kpi-value money text-2xl font-bold text-red-600">PKR {{ number_format($pending) }}</p>
      </div>
    </div>
    @if($expected > 0)
    <div class="mt-4">
      <div class="flex justify-between text-xs text-slate-500 mb-1">
        <span>Collection Progress</span>
        <span>{{ $expected > 0 ? round(($collected/$expected)*100) : 0 }}%</span>
      </div>
      <div class="w-full bg-slate-100 rounded-full h-2.5">
        <div class="bg-green-500 h-2.5 rounded-full transition-all" style="width:{{ $expected > 0 ? min(100, ($collected/$expected)*100) : 0 }}%"></div>
      </div>
    </div>
    @endif
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    {{-- Defaulters --}}
    <div class="bg-white rounded-xl border border-red-100 shadow-sm overflow-hidden">
      <div class="bg-red-50 border-b border-red-100 px-5 py-3 flex items-center justify-between">
        <h2 class="font-semibold text-red-800 flex items-center gap-2">
          <i data-lucide="alert-circle" class="w-4 h-4"></i>Fee Pending Students
        </h2>
        <a href="{{ route('coaching.fees.index') }}" class="text-xs text-red-600 hover:underline">View All →</a>
      </div>
      @if($defaulters->isNotEmpty())
      <div class="divide-y divide-slate-100">
        @foreach($defaulters as $s)
        <div class="px-5 py-3 flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-slate-900">{{ $s->name }}</p>
            <p class="text-xs text-slate-400">{{ $s->batch->name }} · {{ $s->batch->course->name }}</p>
          </div>
          <div class="text-right">
            <p class="text-sm font-semibold text-red-600">PKR {{ number_format($s->effectiveFee()) }}</p>
            @if($s->phone && feature_enabled('whatsapp_share'))
            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $s->phone) }}?text={{ urlencode('Assalam o Alaikum ' . $s->name . ' — ' . now()->format('F Y') . ' ki fees abhi tak nahi ayi. Please jald ada karein.') }}"
               target="_blank" class="text-xs text-green-600 hover:underline">WhatsApp</a>
            @endif
          </div>
        </div>
        @endforeach
      </div>
      @else
      <div class="px-5 py-8 text-center text-slate-400 text-sm">
        <i data-lucide="check-circle" class="w-8 h-8 text-green-400 mx-auto mb-2"></i>
        All students paid for {{ $monthLabel }}!
      </div>
      @endif
    </div>

    {{-- Recent Payments --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
      <div class="bg-slate-50 border-b border-slate-100 px-5 py-3">
        <h2 class="font-semibold text-slate-800 flex items-center gap-2">
          <i data-lucide="clock" class="w-4 h-4 text-slate-400"></i>Recent Payments
        </h2>
      </div>
      @if($recentPayments->isNotEmpty())
      <div class="divide-y divide-slate-100">
        @foreach($recentPayments as $fee)
        <div class="px-5 py-3 flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-slate-900">{{ $fee->student->name }}</p>
            <p class="text-xs text-slate-400">{{ $fee->payment_date?->format('d M Y') }} · {{ ucfirst($fee->payment_method) }}</p>
          </div>
          <div class="text-right">
            <p class="text-sm font-bold text-green-600">+PKR {{ number_format($fee->amount_paid) }}</p>
            <a href="{{ route('coaching.fees.receipt', $fee) }}" class="text-xs text-slate-400 hover:text-slate-600">Receipt</a>
          </div>
        </div>
        @endforeach
      </div>
      @else
      <div class="px-5 py-8 text-center text-slate-400 text-sm">No payments yet this month</div>
      @endif
    </div>
  </div>

  {{-- Quick Actions --}}
  <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
    <a href="{{ route('coaching.students.create') }}" class="flex items-center gap-3 bg-white border border-slate-200 hover:border-green-300 hover:bg-green-50 rounded-xl p-4 transition-all">
      <div class="w-9 h-9 bg-green-100 rounded-lg flex items-center justify-center flex-shrink-0"><i data-lucide="user-plus" class="w-4 h-4 text-green-600"></i></div>
      <span class="text-sm font-medium text-slate-700">Enroll Student</span>
    </a>
    <a href="{{ route('coaching.fees.index') }}" class="flex items-center gap-3 bg-white border border-slate-200 hover:border-blue-300 hover:bg-blue-50 rounded-xl p-4 transition-all">
      <div class="w-9 h-9 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0"><i data-lucide="banknote" class="w-4 h-4 text-blue-600"></i></div>
      <span class="text-sm font-medium text-slate-700">Collect Fees</span>
    </a>
    <a href="{{ route('coaching.students.index') }}" class="flex items-center gap-3 bg-white border border-slate-200 hover:border-purple-300 hover:bg-purple-50 rounded-xl p-4 transition-all">
      <div class="w-9 h-9 bg-purple-100 rounded-lg flex items-center justify-center flex-shrink-0"><i data-lucide="users" class="w-4 h-4 text-purple-600"></i></div>
      <span class="text-sm font-medium text-slate-700">All Students</span>
    </a>
    <a href="{{ route('coaching.courses.index') }}" class="flex items-center gap-3 bg-white border border-slate-200 hover:border-orange-300 hover:bg-orange-50 rounded-xl p-4 transition-all">
      <div class="w-9 h-9 bg-orange-100 rounded-lg flex items-center justify-center flex-shrink-0"><i data-lucide="book-open" class="w-4 h-4 text-orange-600"></i></div>
      <span class="text-sm font-medium text-slate-700">Courses & Batches</span>
    </a>
  </div>
</div>
@endsection
