@extends('layouts.admin')

@section('title', 'Pesanan')

@section('content')
    <h1 class="mb-4 text-2xl font-bold">Pesanan</h1>

    <form method="GET" action="{{ route('admin.orders.index') }}" class="mb-4 grid gap-3 rounded-lg border bg-white p-3 md:grid-cols-4">
        <input type="text" name="q" value="{{ $keyword }}" placeholder="Kode atau nama penerima..." class="rounded border px-3 py-2">

        <select name="payment_status" class="rounded border px-3 py-2">
            <option value="">Semua status bayar</option>
            @foreach ($paymentLabels as $value => $label)
                <option value="{{ $value }}" @selected($paymentStatus === $value)>{{ $label }}</option>
            @endforeach
        </select>

        <select name="order_status" class="rounded border px-3 py-2">
            <option value="">Semua status pesanan</option>
            @foreach ($statusLabels as $value => $label)
                <option value="{{ $value }}" @selected($orderStatus === $value)>{{ $label }}</option>
            @endforeach
        </select>

        <div class="flex gap-2">
            <button type="submit" class="flex-1 rounded bg-green-600 px-4 py-2 text-white hover:bg-green-700">Filter</button>
            <a href="{{ route('admin.orders.index') }}" class="rounded border px-4 py-2 hover:bg-gray-50">Reset</a>
        </div>
    </form>

    <div class="overflow-x-auto rounded-lg border bg-white">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-100 text-left">
                <tr>
                    <th class="px-3 py-2">Kode</th>
                    <th class="px-3 py-2">Tanggal</th>
                    <th class="px-3 py-2">Pelanggan</th>
                    <th class="px-3 py-2 text-right">Item</th>
                    <th class="px-3 py-2 text-right">Total</th>
                    <th class="px-3 py-2">Pembayaran</th>
                    <th class="px-3 py-2">Status</th>
                    <th class="px-3 py-2"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($orders as $order)
                    <tr class="border-t">
                        <td class="px-3 py-2 font-mono">{{ $order->order_code }}</td>
                        <td class="px-3 py-2">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-3 py-2">
                            {{ $order->user->name }}
                            <div class="text-xs text-gray-500">{{ $order->recipient_name }}</div>
                        </td>
                        <td class="px-3 py-2 text-right">{{ $order->items_count }}</td>
                        <td class="px-3 py-2 text-right">@rupiah($order->grand_total)</td>
                        <td class="px-3 py-2"><x-badge :status="$order->payment_status">{{ $order->paymentStatusLabel() }}</x-badge></td>
                        <td class="px-3 py-2"><x-badge :status="$order->order_status">{{ $order->orderStatusLabel() }}</x-badge></td>
                        <td class="px-3 py-2 text-right">
                            <a href="{{ route('admin.orders.show', $order) }}" class="text-green-700 hover:underline">Detail</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-3 py-6 text-center text-gray-500">Pesanan tidak ditemukan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $orders->links() }}
    </div>
@endsection