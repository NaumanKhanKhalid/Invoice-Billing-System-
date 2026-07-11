@extends('layouts.app')
@section('title', $student->name)
@section('content')
<div class="space-y-6">

  <div class="flex items-center justify-between">
    <div class="flex items-center gap-3">
      <a href="{{ route('coaching.students.index') }}" class="text-slate-400 hover:text-slate-600">
        <i data-lucide="arrow-left" class="w-5 h-5"></i>
      </a>
      <div>
        <h1 class="text-2xl font-bold text-slate-900">{{ $student->name }}</h1>
        <p class="text-sm text-slate-500 mt-0.5">{{ $student->batch->course->name }} · {{ $student->batch->name }}</p>
      </div>
    </div>
    <div class="flex items-center gap-2">
      <a href="{{ route('coaching.students.edit', $student) }}"
         class="flex items-center gap-2 border border-slate-300 text-slate-700 px-4 py-2 rounded-lg text-sm font-medium hover:bg-slate-50">
        <i data-lucide="edit-2" class="w-4 h-4"></i>Edit
      </a>
      <form action="{{ route('coaching.students.destroy', $student) }}" method="POST"
            onsubmit="return confirm('Remove this student?')">
        @csrf @method('DELETE')
        <button class="flex items-center gap-2 border border-red-200 text-red-600 px-4 py-2 rounded-lg text-sm font-medium hover:bg-red-50">
          <i data-lucide="trash-2" class="w-4 h-4"></i>Remove
        </button>
      </form>
    </div>
  </div>

  @if(session('success'))
  <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg text-sm">{{ session('success') }}</div>
  @endif

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Info Card --}}
    <div class="lg:col-span-1 space-y-4">
      <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 space-y-4">
        <div class="flex items-center justify-between">
          <h2 class="font-semibold text-slate-800">Student Info</h2>
          @if($student->status === 'active')
          <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full font-medium">Active</span>
          @elseif($student->status === 'completed')
          <span class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full font-medium">Completed</span>
          @else
          <span class="text-xs bg-slate-100 text-slate-500 px-2 py-0.5 rounded-full font-medium">Dropped</span>
          @endif
        </div>
        <dl class="space-y-3 text-sm">
          <div>
            <dt class="text-xs text-slate-400 uppercase font-semibold tracking-wide">Phone</dt>
            <dd class="text-slate-800 mt-0.5">
              {{ $student->phone ?? '—' }}
              @if($student->phone)
              <a href="https://wa.me/{{ wa_number($student->phone) }}"
                 target="_blank" class="text-xs text-green-600 hover:underline ml-2">WhatsApp</a>
              @endif
            </dd>
          </div>
          @if($student->guardian_name)
          <div>
            <dt class="text-xs text-slate-400 uppercase font-semibold tracking-wide">Guardian</dt>
            <dd class="text-slate-800 mt-0.5">{{ $student->guardian_name }}</dd>
          </div>
          @endif
          @if($student->guardian_phone)
          <div>
            <dt class="text-xs text-slate-400 uppercase font-semibold tracking-wide">Guardian Phone</dt>
            <dd class="text-slate-800 mt-0.5">{{ $student->guardian_phone }}</dd>
          </div>
          @endif
          @if($student->address)
          <div>
            <dt class="text-xs text-slate-400 uppercase font-semibold tracking-wide">Address</dt>
            <dd class="text-slate-800 mt-0.5">{{ $student->address }}</dd>
          </div>
          @endif
          <div>
            <dt class="text-xs text-slate-400 uppercase font-semibold tracking-wide">Enrolled</dt>
            <dd class="text-slate-800 mt-0.5">{{ \Carbon\Carbon::parse($student->enrollment_date)->format('d M Y') }}</dd>
          </div>
          <div>
            <dt class="text-xs text-slate-400 uppercase font-semibold tracking-wide">Monthly Fee</dt>
            <dd class="text-slate-800 mt-0.5 font-semibold">
              PKR {{ number_format($student->effectiveFee()) }}
              @if($student->discount_percent > 0)
              <span class="text-xs text-green-600 font-normal">({{ $student->discount_percent }}% off)</span>
              @endif
            </dd>
          </div>
          @if($student->notes)
          <div>
            <dt class="text-xs text-slate-400 uppercase font-semibold tracking-wide">Notes</dt>
            <dd class="text-slate-800 mt-0.5">{{ $student->notes }}</dd>
          </div>
          @endif
        </dl>
      </div>

      {{-- Fee Stats --}}
      @php
        $totalPaid   = $student->fees->sum('amount_paid');
        $totalPending = $student->fees->where('status','!=','paid')->sum('balance_due');
      @endphp
      <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
        <h2 class="font-semibold text-slate-800 mb-3">Fee Summary</h2>
        <div class="grid grid-cols-2 gap-3">
          <div class="bg-green-50 rounded-lg p-3 text-center">
            <p class="text-xs text-green-600 font-semibold uppercase mb-1">Total Paid</p>
            <p class="text-lg font-bold text-green-700">PKR {{ number_format($totalPaid) }}</p>
          </div>
          <div class="bg-red-50 rounded-lg p-3 text-center">
            <p class="text-xs text-red-500 font-semibold uppercase mb-1">Pending</p>
            <p class="text-lg font-bold text-red-600">PKR {{ number_format($totalPending) }}</p>
          </div>
        </div>
      </div>
    </div>

    {{-- Fee History --}}
    <div class="lg:col-span-2">
      <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="bg-slate-50 border-b border-slate-200 px-5 py-3">
          <h2 class="font-semibold text-slate-800 flex items-center gap-2">
            <i data-lucide="receipt" class="w-4 h-4 text-slate-400"></i>Fee History
          </h2>
        </div>
        @if($student->fees->isNotEmpty())
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-100">
              <tr>
                <th class="text-left px-4 py-3 font-semibold text-slate-600">Month</th>
                <th class="text-right px-4 py-3 font-semibold text-slate-600">Due</th>
                <th class="text-right px-4 py-3 font-semibold text-slate-600">Paid</th>
                <th class="text-right px-4 py-3 font-semibold text-slate-600">Balance</th>
                <th class="text-center px-4 py-3 font-semibold text-slate-600">Status</th>
                <th class="text-right px-4 py-3 font-semibold text-slate-600">Receipt</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              @foreach($student->fees->sortByDesc('month') as $fee)
              <tr class="hover:bg-slate-50">
                <td class="px-4 py-3 font-medium text-slate-900">
                  {{ \Carbon\Carbon::parse($fee->month)->format('M Y') }}
                  @if($fee->payment_date)
                  <p class="text-xs text-slate-400">Paid: {{ \Carbon\Carbon::parse($fee->payment_date)->format('d M') }}</p>
                  @endif
                </td>
                <td class="px-4 py-3 text-right text-slate-600">{{ number_format($fee->amount_due) }}</td>
                <td class="px-4 py-3 text-right font-medium text-green-600">{{ number_format($fee->amount_paid) }}</td>
                <td class="px-4 py-3 text-right text-red-600">{{ number_format($fee->balance_due) }}</td>
                <td class="px-4 py-3 text-center">
                  @if($fee->status === 'paid')
                  <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full">Paid</span>
                  @elseif($fee->status === 'partial')
                  <span class="text-xs bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded-full">Partial</span>
                  @else
                  <span class="text-xs bg-red-100 text-red-600 px-2 py-0.5 rounded-full">Pending</span>
                  @endif
                </td>
                <td class="px-4 py-3 text-right">
                  @if($fee->receipt_number)
                  <a href="{{ route('coaching.fees.receipt', $fee) }}" class="text-xs text-blue-600 hover:underline">{{ $fee->receipt_number }}</a>
                  @else
                  <span class="text-xs text-slate-300">—</span>
                  @endif
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        @else
        <div class="px-5 py-10 text-center text-slate-400 text-sm">No fee records yet.</div>
        @endif
      </div>
    </div>

  </div>
</div>
@endsection
