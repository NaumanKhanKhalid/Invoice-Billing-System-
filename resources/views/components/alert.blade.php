{{--
    Inline alert banner — use when you need a persistent visible alert inside page content.
    For transient toast notifications, call showToast('message', 'type') in JS instead.

    Usage: <x-alert type="success|error|warning|info" message="..." />
--}}
@props(['type' => 'success', 'message', 'dismissible' => true])

@php
$styles = [
    'success' => ['bg' => 'bg-emerald-50', 'border' => 'border-emerald-200', 'text' => 'text-emerald-800', 'icon' => 'check-circle-2', 'btn' => 'text-emerald-600 hover:text-emerald-800'],
    'error'   => ['bg' => 'bg-red-50',     'border' => 'border-red-200',     'text' => 'text-red-800',     'icon' => 'alert-circle',   'btn' => 'text-red-500 hover:text-red-700'],
    'warning' => ['bg' => 'bg-yellow-50',  'border' => 'border-yellow-200',  'text' => 'text-yellow-800',  'icon' => 'alert-triangle', 'btn' => 'text-yellow-600 hover:text-yellow-800'],
    'info'    => ['bg' => 'bg-blue-50',    'border' => 'border-blue-200',    'text' => 'text-blue-800',    'icon' => 'info',           'btn' => 'text-blue-500 hover:text-blue-700'],
];
$s = $styles[$type] ?? $styles['info'];
@endphp

<div x-data="{ show: true }" x-show="show"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100 translate-y-0"
     x-transition:leave-end="opacity-0 -translate-y-1"
     class="flex items-start gap-3 {{ $s['bg'] }} border {{ $s['border'] }} {{ $s['text'] }} px-4 py-3 rounded-xl text-sm">
    <i data-lucide="{{ $s['icon'] }}" class="w-4 h-4 flex-shrink-0 mt-0.5"></i>
    <span class="flex-1">{{ $message }}</span>
    @if($dismissible)
    <button @click="show = false" class="{{ $s['btn'] }} transition-colors flex-shrink-0 mt-0.5">
        <i data-lucide="x" class="w-4 h-4"></i>
    </button>
    @endif
</div>
