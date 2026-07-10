@extends('layouts.app')
@section('title','Repair Jobs')
@section('content')
<div class="space-y-6">

  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-2xl font-bold text-slate-900">Repair Jobs</h1>
      <p class="text-sm text-slate-500 mt-0.5">Job cards for repairing work — track from intake to delivery</p>
    </div>
    <a href="{{ route('repairs.create') }}"
       class="flex items-center gap-2 bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-green-700">
      <i data-lucide="wrench" class="w-4 h-4"></i>New Job Card
    </a>
  </div>

  @if(session('success'))
  <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg text-sm">{{ session('success') }}</div>
  @endif
  @if($errors->any())
  <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">{{ $errors->first() }}</div>
  @endif

  {{-- Status Tabs --}}
  <div class="flex flex-wrap gap-2">
    @foreach([
      'active'      => ['Active', 'bg-slate-800 text-white'],
      'pending'     => ['Pending', 'bg-slate-600 text-white'],
      'in_progress' => ['In Progress', 'bg-blue-600 text-white'],
      'ready'       => ['Ready', 'bg-green-600 text-white'],
      'delivered'   => ['Delivered', 'bg-slate-600 text-white'],
      'cancelled'   => ['Cancelled', 'bg-red-600 text-white'],
    ] as $key => [$label, $activeClass])
    <a href="{{ route('repairs.index', array_filter(['status' => $key, 'search' => request('search')])) }}"
       class="px-3 py-1.5 rounded-lg text-sm font-medium transition-colors {{ $tab === $key ? $activeClass : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}">
      {{ $label }}
      <span class="ml-1 text-xs {{ $tab === $key ? 'opacity-80' : 'text-slate-400' }}">{{ $counts[$key] }}</span>
    </a>
    @endforeach
  </div>

  {{-- Search --}}
  <form method="GET" class="flex flex-wrap gap-3">
    <input type="hidden" name="status" value="{{ $tab }}">
    <input name="search" value="{{ request('search') }}"
           placeholder="Search customer, phone, device or job #..."
           class="border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 w-72">
    <button type="submit" class="bg-slate-700 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-slate-800">Search</button>
    @if(request()->filled('search'))
    <a href="{{ route('repairs.index', ['status' => $tab]) }}" class="text-sm text-slate-500 hover:underline self-center">Clear</a>
    @endif
  </form>

  <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="bg-slate-50 border-b border-slate-200">
          <tr>
            <th class="text-left px-4 py-3 font-semibold text-slate-600">Job #</th>
            <th class="text-left px-4 py-3 font-semibold text-slate-600">Customer</th>
            <th class="text-left px-4 py-3 font-semibold text-slate-600">Device</th>
            <th class="text-left px-4 py-3 font-semibold text-slate-600">Fault</th>
            <th class="text-right px-4 py-3 font-semibold text-slate-600">Estimate / Advance</th>
            <th class="text-center px-4 py-3 font-semibold text-slate-600">Status</th>
            <th class="text-right px-4 py-3 font-semibold text-slate-600">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          @forelse($jobs as $job)
          <tr class="hover:bg-slate-50">
            <td class="px-4 py-3">
              <a href="{{ route('repairs.show', $job) }}" class="font-semibold text-slate-900 hover:text-blue-600">{{ $job->job_number }}</a>
              <p class="text-xs text-slate-400">{{ $job->created_at->format('d M Y') }}</p>
            </td>
            <td class="px-4 py-3">
              <p class="font-medium text-slate-900">{{ $job->customer_name }}</p>
              @if($job->customer_phone)<p class="text-xs text-slate-400">{{ $job->customer_phone }}</p>@endif
            </td>
            <td class="px-4 py-3 text-slate-600">
              {{ $job->device }}
              @if($job->serial_imei)<p class="text-xs text-slate-400">{{ $job->serial_imei }}</p>@endif
            </td>
            <td class="px-4 py-3 text-slate-500 max-w-[16rem]">
              <span class="line-clamp-2">{{ \Illuminate\Support\Str::limit($job->fault, 70) }}</span>
            </td>
            <td class="px-4 py-3 text-right">
              <p class="font-medium text-slate-900">{{ $job->estimated_cost !== null ? 'PKR ' . number_format($job->estimated_cost) : '—' }}</p>
              @if($job->advance_paid > 0)
              <p class="text-xs text-green-600">Adv: PKR {{ number_format($job->advance_paid) }}</p>
              @endif
            </td>
            <td class="px-4 py-3 text-center">
              @if($job->status === 'pending')
              <span class="text-xs bg-slate-100 text-slate-600 px-2 py-0.5 rounded-full font-medium">Pending</span>
              @elseif($job->status === 'in_progress')
              <span class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full font-medium">In Progress</span>
              @elseif($job->status === 'ready')
              <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full font-medium inline-flex items-center gap-1">
                <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></span>Ready
              </span>
              @elseif($job->status === 'delivered')
              <span class="text-xs bg-slate-100 text-slate-500 px-2 py-0.5 rounded-full font-medium">✓ Delivered</span>
              @else
              <span class="text-xs bg-red-100 text-red-700 px-2 py-0.5 rounded-full font-medium">Cancelled</span>
              @endif
            </td>
            <td class="px-4 py-3 text-right">
              <div class="flex items-center justify-end gap-2">
                @if($job->status === 'pending')
                <form method="POST" action="{{ route('repairs.status', $job) }}">
                  @csrf @method('PATCH')
                  <input type="hidden" name="status" value="in_progress">
                  <button class="text-xs bg-blue-50 text-blue-700 border border-blue-200 px-2 py-1 rounded-lg hover:bg-blue-100 font-medium">Start</button>
                </form>
                @elseif($job->status === 'in_progress')
                <form method="POST" action="{{ route('repairs.status', $job) }}">
                  @csrf @method('PATCH')
                  <input type="hidden" name="status" value="ready">
                  <button class="text-xs bg-green-50 text-green-700 border border-green-200 px-2 py-1 rounded-lg hover:bg-green-100 font-medium">Mark Ready</button>
                </form>
                @endif
                <a href="{{ route('repairs.show', $job) }}" class="text-blue-600 hover:underline text-xs">View</a>
                <a href="{{ route('repairs.edit', $job) }}" class="text-slate-500 hover:underline text-xs">Edit</a>
              </div>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="7" class="px-4 py-12 text-center text-slate-400">
              <i data-lucide="wrench" class="w-8 h-8 mx-auto mb-2 text-slate-300"></i>
              No repair jobs found.
              <p class="text-sm mt-1">Create a job card when a customer brings something in for repair.</p>
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    @if($jobs->hasPages())
    <div class="px-4 py-3 border-t border-slate-100">{{ $jobs->links() }}</div>
    @endif
  </div>
</div>
@endsection
