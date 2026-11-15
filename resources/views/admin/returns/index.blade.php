@extends('layouts.admin')

@section('title', 'Pengembalian Sewa')

@section('content')
    <h1 class="mb-4 text-2xl font-bold">Pengembalian Sewa</h1>

    <form method="GET" action="{{ route('admin.returns.index') }}" class="mb-4 flex flex-wrap items-center gap-3 rounded-lg border bg-white p-3">
        <input type="text" name="q" value="{{ $keyword }}" placeholder="Kode atau nama penerima..." class="rounded border px-3 py-2">

        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" name="overdue" value="1" @checked($onlyOverdue) class="rounded border">
            Hanya yang terlambat
        </label>

        <button type="submit" class="rounded bg-green-600 px-4 py-2 text-white hover:bg-green-700">Filter</button>
        <a href="{{ route('admin.returns.index') }}" class="rounded border px-4 py-2 hover:bg-gray-50">Reset</a>
    </form>

    <div class="overflow-x-auto rounded-lg border bg-white">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-100 text-left">
                <tr>
                    <th class="px-3 py-2">Kode</th>
                    <th class="px-3 py-2">Penyewa</th>
                    <th class="px-3 py-2">Barang Sewaan</th>
                    <th class="px-3 py-2 text-right">Deposit</th>
                    <th class="px-3 py-2">Jatuh Tempo</th>
                    <th class="px-3 py-2"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($orders as $order)
                    @php
                        $dueDate = $order->items->max('rent_end');
                        $overdue = $dueDate && $dueDate->lt($today);
                        $lateDays = $overdue ? (int) $dueDate->diffInDays($today) : 0;
                    @endphp
                    <tr class="border-t align-top {{ $overdue ? 'bg-red-50' : '' }}">
                        <td class="px-3 py-2 font-mono">
                            <a href="{{ route('admin.orders.show', $order) }}" class="text-green-700 hover:underline">{{ $order->order_code }}</a>
                        </td>
                        <td class="px-3 py-2">
                            {{ $order->recipient_name }}
                            <div class="text-xs text-gray-500">{{ $order->phone }}</div>
                        </td>
                        <td class="px-3 py-2">
                            <ul class="space-y-1">
                                @foreach ($order->items as $item)
                                    <li>
                                        {{ $item->qty }}x {{ $item->product->name }}
                                        <div class="text-xs text-gray-500">{{ $item->rent_start->format('d/m/Y') }} - {{ $item->rent_end->format('d/m/Y') }}</div>
                                    </li>
                                @endforeach
                            </ul>
                        </td>
                        <td class="px-3 py-2 text-right">@rupiah($order->deposit_total)</td>
                        <td class="px-3 py-2">
                            {{ $dueDate ? $dueDate->format('d/m/Y') : '-' }}
                            @if ($overdue)
                                <div class="text-xs font-medium text-red-600">Terlambat {{ $lateDays }} hari</div>
                            @endif
                        </td>
                        <td class="px-3 py-2 text-right">
                            <form method="POST" action="{{ route('admin.returns.store', $order) }}"
                                  onsubmit="return confirm('Catat pengembalian untuk pesanan {{ e($order->order_code) }}?')">
                                @csrf
                                <button type="submit" class="rounded bg-teal-600 px-3 py-1 text-xs text-white hover:bg-teal-700">Proses Pengembalian</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-3 py-6 text-center text-gray-500">Tidak ada sewa aktif.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $orders->links() }}
    </div>
@endsection