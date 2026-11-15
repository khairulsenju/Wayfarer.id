@extends('layouts.customer')

@section('title', 'Pesanan Saya')

@section('content')
    <h1 class="mb-4 text-2xl font-bold">Pesanan Saya</h1>

    @if ($orders->isEmpty())
        <div class="rounded-lg border bg-white p-8 text-center text-gray-500">
            Belum ada pesanan.
            <div class="mt-3">
                <a href="{{ route('catalog.index') }}" class="text-green-700 hover:underline">Mulai belanja</a>
            </div>
        </div>
    @else
        <div class="overflow-x-auto rounded-lg border bg-white">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-100 text-left">
                    <tr>
                        <th class="px-3 py-2">Kode</th>
                        <th class="px-3 py-2">Tanggal</th>
                        <th class="px-3 py-2">Item</th>
                        <th class="px-3 py-2 text-right">Total</th>
                        <th class="px-3 py-2">Pembayaran</th>
                        <th class="px-3 py-2">Status</th>
                        <th class="px-3 py-2"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($orders as $order)
                        <tr class="border-t">
                            <td class="px-3 py-2 font-mono">{{ $order->order_code }}</td>
                            <td class="px-3 py-2">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-3 py-2">{{ $order->items_count }}</td>
                            <td class="px-3 py-2 text-right">@rupiah($order->grand_total)</td>
                            <td class="px-3 py-2">
                                <x-badge :status="$order->payment_status">{{ $order->paymentStatusLabel() }}</x-badge>
                            </td>
                            <td class="px-3 py-2">
                                <x-badge :status="$order->order_status">{{ $order->orderStatusLabel() }}</x-badge>
                            </td>
                            <td class="px-3 py-2 text-right">
                                <a href="{{ route('orders.show', $order) }}" class="text-green-700 hover:underline">Detail</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $orders->links() }}
        </div>
    @endif
@endsection