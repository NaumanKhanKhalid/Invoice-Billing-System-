@props(['status'])

@php
$map = [
    'draft' => ['class' => 'badge badge-gray', 'icon' => 'file', 'label' => 'Draft'],
    'sent' => ['class' => 'badge badge-blue', 'icon' => 'send', 'label' => 'Sent'],
    'paid' => ['class' => 'badge badge-green', 'icon' => 'check-circle-2', 'label' => 'Paid'],
    'overdue' => ['class' => 'badge badge-red', 'icon' => 'alert-circle', 'label' => 'Overdue'],
    'cancelled' => ['class' => 'badge badge-slate', 'icon' => 'x-circle', 'label' => 'Cancelled'],
    'active' => ['class' => 'badge badge-green', 'icon' => 'circle-check', 'label' => 'Active'],
    'inactive' => ['class' => 'badge badge-red', 'icon' => 'circle-x', 'label' => 'Inactive'],
    'pending' => ['class' => 'badge badge-yellow', 'icon' => 'clock', 'label' => 'Pending'],
];
$config = $map[$status] ?? ['class' => 'badge badge-gray', 'icon' => 'circle', 'label' => ucfirst($status)];
@endphp

<span class="{{ $config['class'] }}">
    <i data-lucide="{{ $config['icon'] }}" style="width:12px;height:12px;"></i>
    {{ $config['label'] }}
</span>
