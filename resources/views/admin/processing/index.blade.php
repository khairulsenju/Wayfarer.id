@extends('layouts.admin')

@section('title', 'Pemrosesan')

@section('content')
    <h1 class="mb-4 text-2xl font-bold">Pemrosesan Pesanan</h1>

    @php
        $tabs = [
            '' => 'Semua Antrian',
            'processing' => 'Perlu Diproses',
            'shipped' => 'Sedang Dikirim',
        ];
    @endphp

    <div class="mb-4 flex flex-wrap gap-2">
        @foreach ($tabs as $value => $label)
            <a href="{{ $value === '' ? route('admin.processing.index') : route('admin.processing.index', ['status' => $value]) }}"
               class="rounded-full border px-4 py-1 text-sm {{ ($status ?? '') === $value ? 'border-green-600 bg-green-600 text-white' : 'bg-white hover:bg-gray-50' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    <div class="overflow-x-auto rounded-lg border bg-white">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-100 text-left">
                <tr>
                    <th class="px-3 py-2">Kode</th>
                    <th class="px-3 py-2">Dibayar</th>
                    <th class="px-3 py-2">Penerima</th>
                    <th class="px-3 py-2">Item</th>
                    <th class="px-3 py-2">Status</th>
                    <th class="px-3 py-2">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($orders as $order)
                    <tr class="border-t align-top">
                        <td class="px-3 py-2 font-mono">
                            <a href="{{ route('admin.orders.show', $order) }}" class="text-green-700 hover:underline">{{ $order->order_code }}</a>
                        </td>
                        <td class="px-3 py-2">{{ $order->paid_at ? $order->paid_at->format('d/m/Y H:i') : '-' }}</td>
                        <td class="px-3 py-2">
                            {{ $order->recipient_name }}
                            <div class="text-xs text-gray-500">{{ $order->phone }}</div>
                            <div class="max-w-xs whitespace-pre-line text-xs text-gray-500">{{ $order->address }}</div>
                        </td>
                        <td class="px-3 py-2">
                            <ul class="space-y-1">
                                @foreach ($order->items as $item)
                                    <li>
                                        {{ $item->qty }}x {{ $item->product->name }}
                                        <x-badge :status="$item->type">{{ $item->type === 'rent' ? 'Sewa' : 'Beli' }}</x-badge>
                                        @if ($item->type === 'rent')
                                            <div class="text-xs text-gray-500">{{ $item->rent_start->format('d/m/Y') }} - {{ $item->rent_end->format('d/m/Y') }}</div>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </td>
                        <td class="px-3 py-2"><x-badge :status="$order->order_status">{{ $order->orderStatusLabel() }}</x-badge></td>
                        <td class="px-3 py-2">
                            <div class="flex flex-wrap gap-2">
                                @foreach ($transitions[$order->id] ?? [] as $next)
                                    <form method="POST" action="{{ route('admin.processing.update', $order) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="{{ $next }}">
                                        <button type="submit" class="rounded bg-green-600 px-3 py-1 text-xs text-white hover:bg-green-700">
                                            {{ $statusLabels[$next] ?? $next }}
                                        </button>
                                    </form>
                                @endforeach
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-3 py-6 text-center text-gray-500">Tidak ada pesanan dalam antrian.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $orders->links() }}
    </div>
@endsection