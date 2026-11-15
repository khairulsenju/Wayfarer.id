@extends('layouts.customer')

@section('title', 'Pesanan ' . $order->order_code)

@section('content')
    <a href="{{ route('orders.index') }}" class="mb-4 inline-block text-sm text-green-700 hover:underline">&larr; Kembali ke pesanan</a>

    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="font-mono text-2xl font-bold">{{ $order->order_code }}</h1>
            <div class="text-sm text-gray-500">Dibuat {{ $order->created_at->format('d/m/Y H:i') }}</div>
        </div>

        <div class="flex flex-wrap gap-2">
            @if ($canPay)
                <a href="{{ route('checkout.pay', $order) }}" class="rounded bg-green-600 px-4 py-2 text-white hover:bg-green-700">Bayar Sekarang</a>
            @endif

            @if ($order->isPaid())
                <a href="{{ route('orders.invoice', $order) }}" target="_blank" class="rounded border px-4 py-2 hover:bg-gray-100">Cetak Invoice</a>
            @endif
        </div>
    </div>

    <div class="mb-6 grid gap-4 md:grid-cols-3">
        <div class="rounded-lg border bg-white p-4 text-sm">
            <div class="mb-2 font-semibold">Status</div>
            <div class="mb-1">Pembayaran: <x-badge :status="$order->payment_status">{{ $order->paymentStatusLabel() }}</x-badge></div>
            <div class="mb-1">Pesanan: <x-badge :status="$order->order_status">{{ $order->orderStatusLabel() }}</x-badge></div>
            @if ($order->paid_at)
                <div class="text-gray-600">Dibayar {{ $order->paid_at->format('d/m/Y H:i') }}</div>
            @endif
            @if ($order->payment && $order->payment->payment_type)
                <div class="text-gray-600">Metode: {{ $order->payment->payment_type }}</div>
            @endif
        </div>

        <div class="rounded-lg border bg-white p-4 text-sm md:col-span-2">
            <div class="mb-2 font-semibold">Pengiriman</div>
            <div>{{ $order->recipient_name }}</div>
            <div>{{ $order->phone }}</div>
            <div class="whitespace-pre-line">{{ $order->address }}</div>
        </div>
    </div>

    <div class="overflow-x-auto rounded-lg border bg-white">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-100 text-left">
                <tr>
                    <th class="px-3 py-2">Produk</th>
                    <th class="px-3 py-2">Tipe</th>
                    <th class="px-3 py-2">Periode Sewa</th>
                    <th class="px-3 py-2 text-right">Jumlah</th>
                    <th class="px-3 py-2 text-right">Harga</th>
                    <th class="px-3 py-2 text-right">Deposit</th>
                    <th class="px-3 py-2 text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->items as $item)
                    <tr class="border-t">
                        <td class="px-3 py-2">{{ $item->product->name }}</td>
                        <td class="px-3 py-2">
                            <x-badge :status="$item->type">{{ $item->type === 'rent' ? 'Sewa' : 'Beli' }}</x-badge>
                        </td>
                        <td class="px-3 py-2">
                            @if ($item->type === 'rent')
                                {{ $item->rent_start->format('d/m/Y') }} - {{ $item->rent_end->format('d/m/Y') }}
                                <div class="text-xs text-gray-500">{{ $item->rent_days }} hari</div>
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-3 py-2 text-right">{{ $item->qty }}</td>
                        <td class="px-3 py-2 text-right">
                            @rupiah($item->unit_price)
                            @if ($item->type === 'rent')
                                <div class="text-xs text-gray-500">per hari</div>
                            @endif
                        </td>
                        <td class="px-3 py-2 text-right">
                            @if ($item->deposit > 0)
                                @rupiah($item->deposit)
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-3 py-2 text-right">@rupiah($item->subtotal)</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="ml-auto mt-4 max-w-sm rounded-lg border bg-white p-4 text-sm">
        <div class="flex justify-between py-1">
            <span>Subtotal</span>
            <span>@rupiah($order->subtotal)</span>
        </div>
        <div class="flex justify-between py-1">
            <span>Total Deposit</span>
            <span>@rupiah($order->deposit_total)</span>
        </div>
        <div class="flex justify-between border-t py-2 text-lg font-bold">
            <span>Total</span>
            <span>@rupiah($order->grand_total)</span>
        </div>
    </div>

    @if ($order->hasRentItems())
        <p class="mt-4 text-sm text-gray-600">
            Deposit sewa akan dikembalikan setelah barang sewaan diterima kembali dalam kondisi baik.
        </p>
    @endif
@endsection