@props(['title', 'value', 'icon', 'color' => 'blue', 'change' => null, 'subtitle' => null])

@php
$colorMap = [
    'blue' => ['bg' => 'bg-blue-50', 'icon' => 'text-blue-600', 'ring' => 'ring-blue-100'],
    'green' => ['bg' => 'bg-emerald-50', 'icon' => 'text-emerald-600', 'ring' => 'ring-emerald-100'],
    'red' => ['bg' => 'bg-red-50', 'icon' => 'text-red-600', 'ring' => 'ring-red-100'],
    'purple' => ['bg' => 'bg-purple-50', 'icon' => 'text-purple-600', 'ring' => 'ring-purple-100'],
    'indigo' => ['bg' => 'bg-indigo-50', 'icon' => 'text-indigo-600', 'ring' => 'ring-indigo-100'],
    'orange' => ['bg' => 'bg-orange-50', 'icon' => 'text-orange-600', 'ring' => 'ring-orange-100'],
];
$c = $colorMap[$color] ?? $colorMap['blue'];
@endphp

<div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
    <div class="flex items-start justify-between">
        <div class="flex-1">
            <p class="text-sm font-medium text-slate-500">{{ $title }}</p>
            <p class="text-2xl font-bold text-slate-900 mt-1">{{ $value }}</p>
            @if($subtitle)
                <p class="text-xs text-slate-400 mt-1">{{ $subtitle }}</p>
            @endif
            @if($change !== null)
                <p class="text-xs mt-2 {{ str_starts_with((string)$change, '-') ? 'text-red-500' : 'text-emerald-500' }} font-medium">
                    {{ str_starts_with((string)$change, '-') ? '↓' : '↑' }} {{ $change }}
                </p>
            @endif
        </div>
        <div class="{{ $c['bg'] }} {{ $c['ring'] }} ring-1 w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0">
            <i data-lucide="{{ $icon }}" class="w-5 h-5 {{ $c['icon'] }}"></i>
        </div>
    </div>
</div>
