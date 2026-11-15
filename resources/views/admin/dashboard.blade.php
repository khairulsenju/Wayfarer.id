@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <h1 class="mb-4 text-2xl font-bold">Dashboard</h1>

    <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-lg border bg-white p-4">
            <div class="text-sm text-gray-500">Pendapatan (belum termasuk deposit)</div>
            <div class="text-xl font-bold text-green-700">@rupiah($stats['revenue'])</div>
        </div>
        <div class="rounded-lg border bg-white p-4">
            <div class="text-sm text-gray-500">Deposit sedang ditahan</div>
            <div class="text-xl font-bold">@rupiah($stats['deposit_held'])</div>
        </div>
        <div class="rounded-lg border bg-white p-4">
            <div class="text-sm text-gray-500">Total Pesanan</div>
            <div class="text-xl font-bold">{{ $stats['orders'] }}</div>
        </div>
        <div class="rounded-lg border bg-white p-4">
            <div class="text-sm text-gray-500">Menunggu Pembayaran</div>
            <div class="text-xl font-bold text-yellow-600">{{ $stats['pending_payment'] }}</div>
        </div>
        <div class="rounded-lg border bg-white p-4">
            <div class="text-sm text-gray-500">Perlu Diproses</div>
            <div class="text-xl font-bold text-blue-600">
                <a href="{{ route('admin.processing.index', ['status' => 'processing']) }}" class="hover:underline">{{ $stats['to_process'] }}</a>
            </div>
        </div>
        <div class="rounded-lg border bg-white p-4">
            <div class="text-sm text-gray-500">Sedang Dikirim</div>
            <div class="text-xl font-bold text-indigo-600">
                <a href="{{ route('admin.processing.index', ['status' => 'shipped']) }}" class="hover:underline">{{ $stats['to_deliver'] }}</a>
            </div>
        </div>
        <div class="rounded-lg border bg-white p-4">
            <div class="text-sm text-gray-500">Sewa Aktif</div>
            <div class="text-xl font-bold text-purple-600">
                <a href="{{ route('admin.returns.index') }}" class="hover:underline">{{ $stats['active_rentals'] }}</a>
            </div>
        </div>
        <div class="rounded-lg border bg-white p-4">
            <div class="text-sm text-gray-500">Sewa Terlambat</div>
            <div class="text-xl font-bold text-red-600">
                <a href="{{ route('admin.returns.index', ['overdue' => 1]) }}" class="hover:underline">{{ $stats['overdue_rentals'] }}</a>
            </div>
        </div>
        <div class="rounded-lg border bg-white p-4">
            <div class="text-sm text-gray-500">Produk</div>
            <div class="text-xl font-bold">{{ $stats['products'] }}</div>
        </div>
        <div class="rounded-lg border bg-white p-4">
            <div class="text-sm text-gray-500">Kategori</div>
            <div class="text-xl font-bold">{{ $stats['categories'] }}</div>
        </div>
        <div class="rounded-lg border bg-white p-4">
            <div class="text-sm text-gray-500">Pelanggan</div>
            <div class="text-xl font-bold">{{ $stats['customers'] }}</div>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="rounded-lg border bg-white">
            <div class="border-b px-4 py-3 font-semibold">Pesanan Terbaru</div>

            @if ($recentOrders->isEmpty())
                <div class="p-4 text-sm text-gray-500">Belum ada pesanan.</div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-left">
                            <tr>
                                <th class="px-3 py-2">Kode</th>
                                <th class="px-3 py-2">Pelanggan</th>
                                <th class="px-3 py-2 text-right">Total</th>
                                <th class="px-3 py-2">Bayar</th>
                                <th class="px-3 py-2">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recentOrders as $order)
                                <tr class="border-t">
                                    <td class="px-3 py-2 font-mono">
                                        <a href="{{ route('admin.orders.show', $order) }}" class="text-green-700 hover:underline">{{ $order->order_code }}</a>
                                    </td>
                                    <td class="px-3 py-2">{{ $order->user->name }}</td>
                                    <td class="px-3 py-2 text-right">@rupiah($order->grand_total)</td>
                                    <td class="px-3 py-2"><x-badge :status="$order->payment_status">{{ $order->paymentStatusLabel() }}</x-badge></td>
                                    <td class="px-3 py-2"><x-badge :status="$order->order_status">{{ $order->orderStatusLabel() }}</x-badge></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <div class="rounded-lg border bg-white">
            <div class="border-b px-4 py-3 font-semibold">Stok Menipis</div>

            @if ($lowStockProducts->isEmpty())
                <div class="p-4 text-sm text-gray-500">Semua stok aman.</div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-left">
                            <tr>
                                <th class="px-3 py-2">Produk</th>
                                <th class="px-3 py-2">Mode</th>
                                <th class="px-3 py-2 text-right">Stok Jual</th>
                                <th class="px-3 py-2 text-right">Stok Sewa</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($lowStockProducts as $product)
                                <tr class="border-t">
                                    <td class="px-3 py-2">
                                        <a href="{{ route('admin.products.edit', $product) }}" class="text-green-700 hover:underline">{{ $product->name }}</a>
                                        <div class="text-xs text-gray-500">{{ $product->category->name }}</div>
                                    </td>
                                    <td class="px-3 py-2"><x-badge :status="$product->mode">{{ $product->modeLabel() }}</x-badge></td>
                                    <td class="px-3 py-2 text-right">{{ $product->canBeSold() ? $product->stock_sale : '-' }}</td>
                                    <td class="px-3 py-2 text-right">{{ $product->canBeRented() ? $product->stock_rent : '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection