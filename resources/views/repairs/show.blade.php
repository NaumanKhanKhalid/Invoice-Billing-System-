@extends('layouts.app')
@section('title', $job->job_number)
@section('content')
<div class="max-w-3xl mx-auto space-y-6">

  <div class="flex items-center justify-between print:hidden">
    <div class="flex items-center gap-3">
      <a href="{{ route('repairs.index') }}" class="text-slate-400 hover:text-slate-600">
        <i data-lucide="arrow-left" class="w-5 h-5"></i>
      </a>
      <div>
        <h1 class="text-2xl font-bold text-slate-900">{{ $job->job_number }}</h1>
        <p class="text-sm text-slate-500 mt-0.5">Created {{ $job->created_at->format('d M Y, h:i A') }}</p>
      </div>
    </div>
    <div class="flex items-center gap-2">
      @if(feature_enabled('whatsapp_share') && $job->status === 'ready' && $job->customer_phone)
      @php
        $waPhone = preg_replace('/\D/', '', $job->customer_phone);
        $waPhone = str_starts_with($waPhone, '0') ? '92' . substr($waPhone, 1) : $waPhone;
        $waText = "Assalam o Alaikum {$job->customer_name} — aapka {$job->device} theek ho gaya hai, aa kar le jayen. Total: PKR " . number_format($job->final_cost ?? $job->estimated_cost ?? 0);
      @endphp
      <a href="https://wa.me/{{ $waPhone }}?text={{ urlencode($waText) }}" target="_blank"
         class="flex items-center gap-2 bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-green-700">
        <i data-lucide="message-circle" class="w-4 h-4"></i>WhatsApp Customer
      </a>
      @endif
      @if(feature_enabled('receipt_print'))
      <button onclick="window.print()" class="flex items-center gap-2 bg-slate-700 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-slate-800">
        <i data-lucide="printer" class="w-4 h-4"></i>Print Job Card
      </button>
      @endif
      <a href="{{ route('repairs.edit', $job) }}"
         class="flex items-center gap-2 border border-slate-200 text-slate-600 px-4 py-2 rounded-lg text-sm font-medium hover:bg-slate-50">
        <i data-lucide="pencil" class="w-4 h-4"></i>Edit
      </a>
    </div>
  </div>

  @if(session('success'))
  <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg text-sm print:hidden">{{ session('success') }}</div>
  @endif
  @if($errors->any())
  <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm print:hidden">{{ $errors->first() }}</div>
  @endif

  {{-- Status + Actions --}}
  <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 print:hidden">
    <div class="flex flex-wrap items-center justify-between gap-4">
      <div class="flex items-center gap-3">
        <span class="text-sm text-slate-500">Status:</span>
        @if($job->status === 'pending')
        <span class="text-sm bg-slate-100 text-slate-600 px-3 py-1 rounded-full font-semibold">Pending</span>
        @elseif($job->status === 'in_progress')
        <span class="text-sm bg-blue-100 text-blue-700 px-3 py-1 rounded-full font-semibold">In Progress</span>
        @elseif($job->status === 'ready')
        <span class="text-sm bg-green-100 text-green-700 px-3 py-1 rounded-full font-semibold inline-flex items-center gap-1.5">
          <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>Ready for Pickup
        </span>
        @elseif($job->status === 'delivered')
        <span class="text-sm bg-slate-100 text-slate-500 px-3 py-1 rounded-full font-semibold">✓ Delivered</span>
        @else
        <span class="text-sm bg-red-100 text-red-700 px-3 py-1 rounded-full font-semibold">Cancelled</span>
        @endif
      </div>

      <div class="flex flex-wrap items-center gap-2">
        @if($job->status === 'pending')
        <form method="POST" action="{{ route('repairs.status', $job) }}">
          @csrf @method('PATCH')
          <input type="hidden" name="status" value="in_progress">
          <button class="flex items-center gap-1.5 bg-blue-600 text-white px-3 py-1.5 rounded-lg text-sm font-medium hover:bg-blue-700">
            <i data-lucide="play" class="w-3.5 h-3.5"></i>Start Work
          </button>
        </form>
        @endif
        @if(in_array($job->status, ['pending', 'in_progress']))
        <form method="POST" action="{{ route('repairs.status', $job) }}">
          @csrf @method('PATCH')
          <input type="hidden" name="status" value="ready">
          <button class="flex items-center gap-1.5 bg-green-600 text-white px-3 py-1.5 rounded-lg text-sm font-medium hover:bg-green-700">
            <i data-lucide="check" class="w-3.5 h-3.5"></i>Mark Ready
          </button>
        </form>
        @endif
        @if(!in_array($job->status, ['delivered', 'cancelled']))
        <form method="POST" action="{{ route('repairs.status', $job) }}"
              onsubmit="return confirm('Cancel job {{ $job->job_number }}?')">
          @csrf @method('PATCH')
          <input type="hidden" name="status" value="cancelled">
          <button class="flex items-center gap-1.5 border border-red-200 text-red-600 px-3 py-1.5 rounded-lg text-sm font-medium hover:bg-red-50">
            <i data-lucide="x" class="w-3.5 h-3.5"></i>Cancel Job
          </button>
        </form>
        @endif
      </div>
    </div>

    {{-- Deliver form: collect final cost + remaining --}}
    @if(in_array($job->status, ['ready', 'in_progress']))
    <div x-data="{ finalCost: {{ $job->final_cost ?? $job->estimated_cost ?? 0 }} }" class="mt-4 pt-4 border-t border-slate-100">
      <form method="POST" action="{{ route('repairs.status', $job) }}" class="flex flex-wrap items-end gap-3">
        @csrf @method('PATCH')
        <input type="hidden" name="status" value="delivered">
        <div>
          <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Final Cost (PKR) *</label>
          <input type="number" name="final_cost" x-model="finalCost" required min="0" step="0.01"
                 class="border border-slate-300 rounded-lg px-3 py-2 text-sm w-40 focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div class="text-sm text-slate-500 pb-2">
          Advance paid: <span class="font-medium text-slate-700">PKR {{ number_format($job->advance_paid) }}</span>
          <span class="mx-2 text-slate-300">|</span>
          Remaining to collect:
          <span class="font-bold text-slate-900" x-text="'PKR ' + Math.max(0, (parseFloat(finalCost) || 0) - {{ $job->advance_paid }}).toLocaleString()"></span>
        </div>
        <button class="flex items-center gap-1.5 bg-slate-800 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-slate-900">
          <i data-lucide="package-check" class="w-4 h-4"></i>Mark Delivered
        </button>
      </form>
    </div>
    @endif
  </div>

  {{-- Printable Job Card --}}
  <div id="receipt" class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden print:shadow-none print:border-0">
    <div class="bg-slate-800 text-white px-6 py-5 text-center">
      @if($logoPath = \App\Models\Setting::getValue('logo_path'))
      <img src="{{ asset('storage/'.$logoPath) }}" alt="Logo" class="max-h-16 mx-auto mb-2">
      @endif
      <h1 class="text-xl font-bold">Repair Job Card</h1>
      <p class="text-slate-300 text-sm mt-1">{{ \App\Models\Setting::getValue('company_name', tenant()->shop_name ?? config('app.name')) }}</p>
    </div>

    <div class="px-6 py-5 space-y-5">
      <div class="flex justify-between text-sm">
        <div>
          <p class="text-xs text-slate-400 font-semibold uppercase tracking-wide">Job No</p>
          <p class="font-bold text-slate-900 text-lg">{{ $job->job_number }}</p>
        </div>
        <div class="text-right">
          <p class="text-xs text-slate-400 font-semibold uppercase tracking-wide">Date</p>
          <p class="font-medium text-slate-700">{{ $job->created_at->format('d M Y') }}</p>
        </div>
      </div>

      <hr class="border-slate-100">

      <div class="space-y-2 text-sm">
        <div class="flex justify-between">
          <span class="text-slate-500">Customer</span>
          <span class="font-semibold text-slate-900">{{ $job->customer_name }}</span>
        </div>
        @if($job->customer_phone)
        <div class="flex justify-between">
          <span class="text-slate-500">Phone</span>
          <span class="text-slate-700">{{ $job->customer_phone }}</span>
        </div>
        @endif
        <div class="flex justify-between">
          <span class="text-slate-500">Device / Item</span>
          <span class="font-medium text-slate-900">{{ $job->device }}</span>
        </div>
        @if($job->serial_imei)
        <div class="flex justify-between">
          <span class="text-slate-500">Serial / IMEI</span>
          <span class="text-slate-700">{{ $job->serial_imei }}</span>
        </div>
        @endif
      </div>

      <div class="bg-slate-50 rounded-lg p-3 text-sm text-slate-600">
        <span class="font-medium text-slate-700">Fault: </span>{{ $job->fault }}
      </div>

      <hr class="border-slate-100">

      <div class="space-y-2 text-sm">
        <div class="flex justify-between">
          <span class="text-slate-500">Estimated Cost</span>
          <span class="text-slate-700">{{ $job->estimated_cost !== null ? 'PKR ' . number_format($job->estimated_cost) : '—' }}</span>
        </div>
        <div class="flex justify-between">
          <span class="text-slate-500">Advance Paid</span>
          <span class="text-green-700 font-medium">PKR {{ number_format($job->advance_paid) }}</span>
        </div>
        @if($job->final_cost !== null)
        <div class="flex justify-between font-bold text-base border-t border-slate-200 pt-2 mt-2">
          <span class="text-slate-900">Final Cost</span>
          <span class="text-slate-900">PKR {{ number_format($job->final_cost) }}</span>
        </div>
        <div class="flex justify-between {{ $job->final_cost - $job->advance_paid > 0 ? 'text-red-600' : 'text-green-700' }}">
          <span>{{ $job->status === 'delivered' ? 'Collected on Delivery' : 'Remaining' }}</span>
          <span>PKR {{ number_format(max(0, $job->final_cost - $job->advance_paid)) }}</span>
        </div>
        @endif
      </div>

      @if($job->notes)
      <div class="bg-slate-50 rounded-lg p-3 text-sm text-slate-600">
        <span class="font-medium">Note: </span>{{ $job->notes }}
      </div>
      @endif
    </div>

    <div class="bg-slate-50 border-t border-slate-100 px-6 py-3 text-center">
      <p class="text-xs text-slate-400">Please bring this job card when collecting your item.</p>
    </div>
  </div>

  {{-- Timeline --}}
  <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 print:hidden">
    <h2 class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-4">Timeline</h2>
    <div class="space-y-3 text-sm">
      <div class="flex items-center gap-3">
        <span class="w-2.5 h-2.5 rounded-full bg-slate-400"></span>
        <span class="text-slate-700">Job received</span>
        <span class="text-xs text-slate-400 ml-auto">{{ $job->created_at->format('d M Y, h:i A') }}</span>
      </div>
      @if($job->ready_at)
      <div class="flex items-center gap-3">
        <span class="w-2.5 h-2.5 rounded-full bg-green-500"></span>
        <span class="text-slate-700">Marked ready for pickup</span>
        <span class="text-xs text-slate-400 ml-auto">{{ $job->ready_at->format('d M Y, h:i A') }}</span>
      </div>
      @endif
      @if($job->delivered_at)
      <div class="flex items-center gap-3">
        <span class="w-2.5 h-2.5 rounded-full bg-slate-800"></span>
        <span class="text-slate-700">Delivered to customer</span>
        <span class="text-xs text-slate-400 ml-auto">{{ $job->delivered_at->format('d M Y, h:i A') }}</span>
      </div>
      @endif
      @if($job->status === 'cancelled')
      <div class="flex items-center gap-3">
        <span class="w-2.5 h-2.5 rounded-full bg-red-500"></span>
        <span class="text-slate-700">Job cancelled</span>
        <span class="text-xs text-slate-400 ml-auto">{{ $job->updated_at->format('d M Y, h:i A') }}</span>
      </div>
      @endif
    </div>
  </div>

</div>

<style>
@media print {
  @page { size: 80mm auto; margin: 2mm; }
  aside, nav, header, footer, .print\:hidden, .no-print { display: none !important; }
  body, #main-content { background: white !important; padding: 0 !important; margin: 0 !important; }
  body * { visibility: hidden; }
  #receipt, #receipt * { visibility: visible; }
  #receipt {
    position: absolute; top: 0; left: 0;
    width: 76mm !important; max-width: 76mm !important;
    margin: 0 !important; padding: 0 !important;
    font-family: 'Courier New', Courier, monospace !important;
    font-size: 11px !important; line-height: 1.3 !important;
    color: #000 !important; background: #fff !important;
    border: none !important; border-radius: 0 !important; box-shadow: none !important;
    overflow: visible !important;
  }
  #receipt * {
    color: #000 !important; background: transparent !important;
    box-shadow: none !important; border-radius: 0 !important;
    font-size: 11px !important; line-height: 1.3 !important;
  }
  #receipt h1 { font-size: 13px !important; font-weight: bold !important; }
  #receipt [class*="px-6"] { padding-left: 2mm !important; padding-right: 2mm !important; }
  #receipt [class*="py-"] { padding-top: 1mm !important; padding-bottom: 1mm !important; }
  #receipt [class*="border"] { border-color: #000 !important; }
  #receipt .rounded-full { border: 1px solid #000 !important; }
  #receipt img { max-height: 15mm !important; width: auto !important; margin: 0 auto 1mm !important; }
}
</style>
@endsection
