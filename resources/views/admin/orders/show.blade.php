@extends('layouts.admin')

@section('title', 'Pesanan ' . $order->order_code)

@section('content')
    <a href="{{ route('admin.orders.index') }}" class="mb-4 inline-block text-sm text-green-700 hover:underline">&larr; Kembali ke pesanan</a>

    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="font-mono text-2xl font-bold">{{ $order->order_code }}</h1>
            <div class="text-sm text-gray-500">Dibuat {{ $order->created_at->format('d/m/Y H:i') }}</div>
        </div>

        @if ($order->isPending())
            <form method="POST" action="{{ route('admin.orders.sync', $order) }}">
                @csrf
                <button type="submit" class="rounded border bg-white px-4 py-2 text-sm hover:bg-gray-50">Sinkronkan Status Midtrans</button>
            </form>
        @endif
    </div>

    <div class="mb-6 grid gap-4 md:grid-cols-3">
        <div class="rounded-lg border bg-white p-4 text-sm">
            <div class="mb-2 font-semibold">Pelanggan</div>
            <div>{{ $order->user->name }}</div>
            <div>{{ $order->user->email }}</div>
            <div>{{ $order->user->phone }}</div>
        </div>

        <div class="rounded-lg border bg-white p-4 text-sm">
            <div class="mb-2 font-semibold">Pengiriman</div>
            <div>{{ $order->recipient_name }}</div>
            <div>{{ $order->phone }}</div>
            <div class="whitespace-pre-line">{{ $order->address }}</div>
        </div>

        <div class="rounded-lg border bg-white p-4 text-sm">
            <div class="mb-2 font-semibold">Status</div>
            <div class="mb-1">Pembayaran (Midtrans, hanya baca): <x-badge :status="$order->payment_status">{{ $order->paymentStatusLabel() }}</x-badge></div>
            <div class="mb-1">Pesanan: <x-badge :status="$order->order_status">{{ $order->orderStatusLabel() }}</x-badge></div>
            @if ($order->paid_at)
                <div class="text-gray-600">Dibayar {{ $order->paid_at->format('d/m/Y H:i') }}</div>
            @endif
            @if ($order->payment)
                <div class="text-gray-600">Status Midtrans: {{ $order->payment->transaction_status }}</div>
                <div class="text-gray-600">Metode: {{ $order->payment->payment_type ?? '-' }}</div>
            @endif
        </div>
    </div>

    @if (count($transitions) > 0)
        <div class="mb-6 rounded-lg border bg-white p-4">
            <div class="mb-2 font-semibold">Ubah Status Pesanan</div>

            <div class="flex flex-wrap gap-2">
                @foreach ($transitions as $next)
                    @if ($next === 'returned')
                        <form method="POST" action="{{ route('admin.returns.store', $order) }}"
                              onsubmit="return confirm('Catat pengembalian barang sewa untuk pesanan ini?')">
                            @csrf
                            <button type="submit" class="rounded bg-teal-600 px-4 py-2 text-sm text-white hover:bg-teal-700">
                                Proses Pengembalian Sewa
                            </button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('admin.processing.update', $order) }}">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="{{ $next }}">
                            <button type="submit" class="rounded bg-green-600 px-4 py-2 text-sm text-white hover:bg-green-700">
                                Ubah ke "{{ $statusLabels[$next] ?? $next }}"
                            </button>
                        </form>
                    @endif
                @endforeach
            </div>
        </div>
    @endif

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
                        <td class="px-3 py-2"><x-badge :status="$item->type">{{ $item->type === 'rent' ? 'Sewa' : 'Beli' }}</x-badge></td>
                        <td class="px-3 py-2">
                            @if ($item->type === 'rent')
                                {{ $item->rent_start->format('d/m/Y') }} - {{ $item->rent_end->format('d/m/Y') }}
                                <div class="text-xs text-gray-500">{{ $item->rent_days }} hari</div>
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-3 py-2 text-right">{{ $item->qty }}</td>
                        <td class="px-3 py-2 text-right">@rupiah($item->unit_price)</td>
                        <td class="px-3 py-2 text-right">{{ $item->deposit > 0 ? \App\Services\PricingService::rupiah($item->deposit) : '-' }}</td>
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

    <div class="mt-6 rounded-lg border bg-white">
        <div class="border-b px-4 py-3 font-semibold">Riwayat Notifikasi Pembayaran</div>

        @if ($order->payments->isEmpty())
            <div class="p-4 text-sm text-gray-500">Belum ada data pembayaran.</div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-left">
                        <tr>
                            <th class="px-3 py-2">Waktu</th>
                            <th class="px-3 py-2">Midtrans Order ID</th>
                            <th class="px-3 py-2">Status</th>
                            <th class="px-3 py-2">Metode</th>
                            <th class="px-3 py-2 text-right">Nominal</th>
                            <th class="px-3 py-2">Signature</th>
                            <th class="px-3 py-2">Respons</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($order->payments as $payment)
                            <tr class="border-t align-top">
                                <td class="px-3 py-2">{{ $payment->updated_at->format('d/m/Y H:i:s') }}</td>
                                <td class="px-3 py-2 font-mono text-xs">{{ $payment->midtrans_order_id }}</td>
                                <td class="px-3 py-2">{{ $payment->transaction_status }}</td>
                                <td class="px-3 py-2">{{ $payment->payment_type ?? '-' }}</td>
                                <td class="px-3 py-2 text-right">@rupiah($payment->gross_amount)</td>
                                <td class="px-3 py-2">
                                    @if ($payment->signature_valid)
                                        <span class="text-green-700">Valid</span>
                                    @else
                                        <span class="text-red-600">Tidak valid / belum ada</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2">
                                    @if ($payment->raw_response)
                                        <details>
                                            <summary class="cursor-pointer text-green-700">Lihat</summary>
                                            <pre class="mt-1 max-w-xs overflow-x-auto rounded bg-gray-50 p-2 text-xs">{{ json_encode($payment->raw_response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                        </details>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection