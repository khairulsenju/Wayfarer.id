@props(['status'])

@php
    $map = [
        'pending' => 'bg-yellow-100 text-yellow-800',
        'paid' => 'bg-green-100 text-green-800',
        'expired' => 'bg-gray-200 text-gray-700',
        'failed' => 'bg-red-100 text-red-800',
        'processing' => 'bg-blue-100 text-blue-800',
        'shipped' => 'bg-indigo-100 text-indigo-800',
        'rented' => 'bg-purple-100 text-purple-800',
        'returned' => 'bg-teal-100 text-teal-800',
        'completed' => 'bg-green-100 text-green-800',
        'cancelled' => 'bg-red-100 text-red-800',
        'sale' => 'bg-blue-100 text-blue-800',
        'rent' => 'bg-orange-100 text-orange-800',
        'both' => 'bg-emerald-100 text-emerald-800',
        'active' => 'bg-green-100 text-green-800',
        'inactive' => 'bg-gray-200 text-gray-700',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-block rounded px-2 py-0.5 text-xs font-medium ' . ($map[$status] ?? 'bg-gray-100 text-gray-700')]) }}>{{ $slot }}</span>