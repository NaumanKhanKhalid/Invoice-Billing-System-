@extends('layouts.app')
@section('title','Staff')
@section('content')
<div class="space-y-5">
  <div class="flex items-center justify-between">
    <div><h1 class="text-2xl font-bold text-slate-900">Staff & Salaries</h1><p class="text-sm text-slate-500 mt-0.5">Manage employees</p></div>
    <a href="{{ route('staff.create') }}" class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors"><i data-lucide="plus" class="w-4 h-4"></i>Add Staff</a>
  </div>

  <div class="bg-white rounded-xl border border-green-200 shadow-sm p-5">
    <div class="flex items-center justify-between mb-3">
      <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Monthly Salary Bill</p>
      <div class="w-8 h-8 rounded-lg bg-green-100 flex items-center justify-center">
        <i data-lucide="banknote" class="w-4 h-4 text-green-600"></i>
      </div>
    </div>
    <p class="text-2xl font-bold text-green-700">{{ formatCurrency($totalSalary) }}</p>
    <p class="text-xs text-slate-400 mt-1">Total monthly payroll</p>
  </div>

  <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden table-responsive">
    <table class="w-full">
      <thead class="bg-slate-50"><tr>
        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Name</th>
        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Role</th>
        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Phone</th>
        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Salary</th>
        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Joined</th>
        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Status</th>
        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Last Paid</th>
        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">This Month</th>
        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Actions</th>
      </tr></thead>
      <tbody class="divide-y divide-slate-100">
        @forelse($staff as $s)
        <tr class="hover:bg-slate-50">
          <td class="px-4 py-3 text-sm font-medium text-slate-900">{{ $s->name }}</td>
          <td class="px-4 py-3"><span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-700">{{ ucfirst($s->role) }}</span></td>
          <td class="px-4 py-3 text-sm text-slate-600">{{ $s->phone ?? '-' }}</td>
          <td class="px-4 py-3 text-sm text-right font-medium text-slate-900">{{ formatCurrency($s->salary) }}</td>
          <td class="px-4 py-3 text-sm text-slate-500">{{ $s->joining_date->format('d M Y') }}</td>
          <td class="px-4 py-3">
            @if($s->is_active)<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-700">Active</span>
            @else<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-500">Inactive</span>@endif
          </td>
          <td class="px-4 py-3">
            @php $lastPay = $s->salaryPayments->first(); @endphp
            @if($lastPay)
              <p class="text-xs font-semibold text-slate-700">{{ formatCurrency($lastPay->amount) }}</p>
              <p class="text-xs text-slate-400">{{ $lastPay->payment_date->format('d M Y') }}</p>
            @else
              <span class="text-xs text-slate-300">Not paid yet</span>
            @endif
          </td>
          <td class="px-4 py-3">
            @if($paidThisMonth->contains($s->id))
              <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-700"><i data-lucide="check" class="w-3 h-3"></i>Paid</span>
            @elseif($s->is_active)
              <a href="{{ route('staff.show',$s) }}" class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-600 hover:bg-red-200"><i data-lucide="clock" class="w-3 h-3"></i>Due</a>
            @else
              <span class="text-xs text-slate-300">—</span>
            @endif
          </td>
          <td class="px-4 py-3">
            <div class="flex items-center justify-end gap-2">
              <a href="{{ route('staff.show',$s) }}" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-green-100 text-slate-500 hover:text-green-700 text-xs font-medium transition-colors"><i data-lucide="eye" class="w-3.5 h-3.5"></i>View</a>
              <a href="{{ route('staff.edit',$s) }}" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-blue-100 text-slate-500 hover:text-blue-700 text-xs font-medium transition-colors"><i data-lucide="pencil" class="w-3.5 h-3.5"></i>Edit</a>
              <form method="POST" action="{{ route('staff.toggle-status',$s) }}" class="inline">@csrf
                <button type="submit" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-{{ $s->is_active?'red':'green' }}-100 text-slate-500 hover:text-{{ $s->is_active?'red':'green' }}-700 text-xs font-medium transition-colors" title="{{ $s->is_active?'Deactivate':'Activate' }}">
                  <i data-lucide="{{ $s->is_active?'user-x':'user-check' }}" class="w-3.5 h-3.5"></i>{{ $s->is_active?'Deactivate':'Activate' }}
                </button>
              </form>
            </div>
          </td>
        </tr>
        @empty
        <tr><td colspan="9" class="px-4 py-12 text-center">
          <i data-lucide="users" class="w-10 h-10 text-slate-300 mx-auto mb-3"></i>
          <p class="text-slate-500 font-medium">No staff yet</p>
          <a href="{{ route('staff.create') }}" class="text-green-600 text-sm mt-1 inline-block hover:underline">Add first staff member</a>
        </td></tr>
        @endforelse
      </tbody>
    </table>
    @if($staff->hasPages())
    <div class="px-4 py-3 border-t border-slate-100">{{ $staff->links() }}</div>
    @endif
  </div>
</div>
@endsection
