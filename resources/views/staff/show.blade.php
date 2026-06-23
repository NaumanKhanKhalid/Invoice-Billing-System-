@extends('layouts.app')
@section('title', $staff->name)
@section('content')
<div class="space-y-6">
  <div class="flex items-center justify-between">
    <div class="flex items-center gap-3">
      <a href="{{ route('staff.index') }}" class="text-slate-400 hover:text-slate-600"><i data-lucide="arrow-left" class="w-5 h-5"></i></a>
      <div>
        <h1 class="text-2xl font-bold text-slate-900">{{ $staff->name }}</h1>
        <p class="text-sm text-slate-500">{{ ucfirst($staff->role) }}</p>
      </div>
    </div>
    <a href="{{ route('staff.edit',$staff) }}" class="inline-flex items-center gap-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 px-3 py-2 rounded-lg text-sm font-medium"><i data-lucide="pencil" class="w-4 h-4"></i>Edit</a>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-5">
      <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
        <h2 class="font-semibold text-slate-900 mb-4">Staff Details</h2>
        <div class="grid grid-cols-2 gap-4 text-sm">
          <div><p class="text-slate-500">Phone</p><p class="font-medium text-slate-900 mt-0.5">{{ $staff->phone ?? '-' }}</p></div>
          <div><p class="text-slate-500">Role</p><span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-700 mt-0.5">{{ ucfirst($staff->role) }}</span></div>
          <div><p class="text-slate-500">Monthly Salary</p><p class="font-bold text-green-700 mt-0.5 text-base">{{ formatCurrency($staff->salary) }}</p></div>
          <div><p class="text-slate-500">Joining Date</p><p class="font-medium text-slate-900 mt-0.5">{{ $staff->joining_date->format('d M Y') }}</p></div>
          <div><p class="text-slate-500">Status</p><span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $staff->is_active?'bg-green-100 text-green-700':'bg-slate-100 text-slate-500' }} mt-0.5">{{ $staff->is_active?'Active':'Inactive' }}</span></div>
          <div><p class="text-slate-500">Total Paid</p><p class="font-medium text-slate-900 mt-0.5">{{ formatCurrency($totalPaid) }}</p></div>
        </div>
      </div>

      <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100"><h2 class="font-semibold text-slate-900">Salary History</h2></div>
        <table class="w-full">
          <thead class="bg-slate-50"><tr>
            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Month/Year</th>
            <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Amount</th>
            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Date Paid</th>
            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Note</th>
          </tr></thead>
          <tbody class="divide-y divide-slate-100">
            @forelse($staff->salaryPayments->sortByDesc('payment_date') as $p)
            <tr class="hover:bg-slate-50">
              <td class="px-4 py-3 text-sm text-slate-700">{{ \Carbon\Carbon::createFromDate($p->year,$p->month,1)->format('M Y') }}</td>
              <td class="px-4 py-3 text-sm text-right font-medium text-green-600">{{ formatCurrency($p->amount) }}</td>
              <td class="px-4 py-3 text-sm text-slate-600">{{ \Carbon\Carbon::parse($p->payment_date)->format('d M Y') }}</td>
              <td class="px-4 py-3 text-sm text-slate-500">{{ $p->note ?? '-' }}</td>
            </tr>
            @empty
            <tr><td colspan="4" class="px-4 py-6 text-center text-slate-400 text-sm">No salary payments yet.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    <div>
      <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
        <h2 class="font-semibold text-slate-900 mb-4">Pay Salary</h2>
        <form method="POST" action="{{ route('staff.salary',$staff) }}" class="space-y-4">
          @csrf
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">Month <span class="text-red-500">*</span></label>
              <select name="month" required class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none bg-white">
                @for($m=1;$m<=12;$m++)<option value="{{ $m }}" {{ now()->month==$m?'selected':'' }}>{{ \Carbon\Carbon::createFromDate(null,$m,1)->format('M') }}</option>@endfor
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">Year <span class="text-red-500">*</span></label>
              <input type="number" name="year" value="{{ now()->year }}" min="2020" required class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Amount <span class="text-red-500">*</span></label>
            <div class="relative"><span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">PKR</span>
            <input type="number" name="amount" value="{{ $staff->salary }}" step="0.01" min="0.01" required class="w-full pl-12 pr-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none"></div>
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Date Paid <span class="text-red-500">*</span></label>
            <input type="date" name="payment_date" value="{{ today()->toDateString() }}" required class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Note</label>
            <input type="text" name="note" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
          </div>
          <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white py-2 rounded-lg text-sm font-medium transition-colors">Record Payment</button>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
