@props(['type' => 'success', 'message'])

@php
$map = [
    'success' => ['bg' => 'bg-emerald-50', 'border' => 'border-emerald-200', 'text' => 'text-emerald-800', 'icon' => 'check-circle-2'],
    'error' => ['bg' => 'bg-red-50', 'border' => 'border-red-200', 'text' => 'text-red-800', 'icon' => 'alert-circle'],
    'warning' => ['bg' => 'bg-yellow-50', 'border' => 'border-yellow-200', 'text' => 'text-yellow-800', 'icon' => 'alert-triangle'],
    'info' => ['bg' => 'bg-blue-50', 'border' => 'border-blue-200', 'text' => 'text-blue-800', 'icon' => 'info'],
];
$c = $map[$type] ?? $map['info'];
@endphp

<div class="flex items-center gap-2 {{ $c['bg'] }} border {{ $c['border'] }} {{ $c['text'] }} px-4 py-3 rounded-xl text-sm">
    <i data-lucide="{{ $c['icon'] }}" class="w-4 h-4 flex-shrink-0"></i>
    {{ $message }}
</div>
