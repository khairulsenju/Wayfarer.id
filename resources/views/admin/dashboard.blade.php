@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')

@php
    $cards = [
        [
            'label' => 'Pendapatan',
            'value' => \App\Services\PricingService::rupiah($stats['revenue']),
            'icon' => '💰',
            'color' => 'green',
            'description' => 'Belum termasuk deposit',
        ],
        [
            'label' => 'Deposit Ditahan',
            'value' => \App\Services\PricingService::rupiah($stats['deposit_held']),
            'icon' => '🛡️',
            'color' => 'blue',
            'description' => 'Deposit pelanggan',
        ],
        [
            'label' => 'Total Pesanan',
            'value' => $stats['orders'],
            'icon' => '📦',
            'color' => 'purple',
            'description' => 'Seluruh transaksi',
        ],
        [
            'label' => 'Menunggu Pembayaran',
            'value' => $stats['pending_payment'],
            'icon' => '⏳',
            'color' => 'amber',
            'description' => 'Perlu diperhatikan',
        ],
        [
            'label' => 'Perlu Diproses',
            'value' => $stats['to_process'],
            'icon' => '📋',
            'color' => 'blue',
            'description' => 'Pesanan sudah dibayar',
            'href' => route('admin.processing.index', ['status' => 'processing']),
        ],
        [
            'label' => 'Sedang Dikirim',
            'value' => $stats['to_deliver'],
            'icon' => '🚚',
            'color' => 'indigo',
            'description' => 'Dalam pengiriman',
            'href' => route('admin.processing.index', ['status' => 'shipped']),
        ],
        [
            'label' => 'Sewa Aktif',
            'value' => $stats['active_rentals'],
            'icon' => '🏕️',
            'color' => 'purple',
            'description' => 'Barang sedang disewa',
            'href' => route('admin.returns.index'),
        ],
        [
            'label' => 'Sewa Terlambat',
            'value' => $stats['overdue_rentals'],
            'icon' => '⚠️',
            'color' => 'red',
            'description' => 'Perlu tindak lanjut',
            'href' => route('admin.returns.index', ['overdue' => 1]),
        ],
        [
            'label' => 'Produk',
            'value' => $stats['products'],
            'icon' => '🎒',
            'color' => 'green',
            'description' => 'Produk terdaftar',
            'href' => route('admin.products.index'),
        ],
        [
            'label' => 'Kategori',
            'value' => $stats['categories'],
            'icon' => '🗂️',
            'color' => 'blue',
            'description' => 'Kategori produk',
            'href' => route('admin.categories.index'),
        ],
        [
            'label' => 'Pelanggan',
            'value' => $stats['customers'],
            'icon' => '👥',
            'color' => 'amber',
            'description' => 'Akun pelanggan',
        ],
    ];

    $colorClasses = [
        'green' => 'bg-green-50 text-green-700 ring-green-100',
        'blue' => 'bg-blue-50 text-blue-700 ring-blue-100',
        'purple' => 'bg-purple-50 text-purple-700 ring-purple-100',
        'amber' => 'bg-amber-50 text-amber-700 ring-amber-100',
        'indigo' => 'bg-indigo-50 text-indigo-700 ring-indigo-100',
        'red' => 'bg-red-50 text-red-700 ring-red-100',
    ];
@endphp

<div class="w-full min-w-0 max-w-full space-y-6 overflow-x-hidden">

    {{-- HEADER BANNER --}}
    <section class="relative isolate min-h-[230px] overflow-hidden rounded-2xl bg-green-950 shadow-lg">

        <img
            src="{{ asset('images/wayfarer-dashboard.jpg') }}"
            alt="Wayfarer Outdoor Adventure"
            class="absolute inset-0 h-full w-full object-cover object-center"
        >

        <div class="absolute inset-0 bg-gradient-to-r from-green-950 via-green-950/85 to-green-950/20"></div>

        <div class="relative z-10 flex min-h-[230px] flex-col items-start justify-center px-6 py-8 text-white sm:px-8">

            <span class="inline-block rounded-full border border-white/30 bg-white/10 px-3 py-1 text-xs font-semibold">
                ● WAYFARER ADMIN PANEL
            </span>

            <h1 class="mt-4 text-2xl font-bold tracking-tight sm:text-3xl">
                Selamat datang, Admin Wayfarer!
            </h1>

            <p class="mt-2 max-w-lg text-sm leading-relaxed text-white/90">
                Pantau aktivitas toko, kelola pesanan, dan pastikan setiap perjalanan pelanggan bersama Wayfarer berjalan lancar.
            </p>

            <div class="mt-5 flex flex-wrap gap-3">

                <a
                    href="{{ route('admin.orders.index') }}"
                    class="rounded-lg bg-white px-4 py-2.5 text-sm font-semibold text-green-900 transition hover:bg-green-50"
                >
                    Lihat Pesanan →
                </a>

                <a
                    href="{{ route('admin.products.index') }}"
                    class="rounded-lg border border-white/40 bg-white/10 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-white/20"
                >
                    Kelola Produk
                </a>

            </div>

            <p class="mt-5 text-xs text-white/80">
                📅 {{ now()->format('d/m/Y') }} · Selamat bekerja!
            </p>

        </div>
    </section>


    {{-- JUDUL STATISTIK --}}
    <div class="flex flex-wrap items-end justify-between gap-3">

        <div>
            <h2 class="text-xl font-bold text-gray-800">
                Ringkasan Aktivitas
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Informasi dan statistik operasional Wayfarer.
            </p>
        </div>

        <span class="rounded-full bg-green-50 px-3 py-1.5 text-xs font-semibold text-green-700">
            ● Dashboard Aktif
        </span>

    </div>


    {{-- KARTU STATISTIK --}}
    <div class="grid min-w-0 grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

        @foreach ($cards as $card)

            <div class="group min-w-0 rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-1 hover:border-green-200 hover:shadow-md">

                <div class="flex items-start justify-between gap-3">

                    <div class="min-w-0">

                        <p class="text-sm font-medium text-gray-500">
                            {{ $card['label'] }}
                        </p>

                        <div class="mt-3 break-words text-2xl font-bold tracking-tight text-gray-900">

                            @if(isset($card['href']))
                                <a
                                    href="{{ $card['href'] }}"
                                    class="transition hover:text-green-700"
                                >
                                    {{ $card['value'] }}
                                </a>
                            @else
                                {{ $card['value'] }}
                            @endif

                        </div>

                        <p class="mt-2 text-xs text-gray-400">
                            {{ $card['description'] }}
                        </p>

                    </div>

                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl text-xl ring-1 {{ $colorClasses[$card['color']] }}">
                        {{ $card['icon'] }}
                    </div>

                </div>

                @if(isset($card['href']))

                    <a
                        href="{{ $card['href'] }}"
                        class="mt-4 inline-flex items-center gap-1 text-xs font-semibold text-green-700 hover:text-green-900"
                    >
                        Lihat detail →
                    </a>

                @endif

            </div>

        @endforeach

    </div>


    {{-- PESANAN DAN PEMANTAUAN STOK --}}
    <div class="grid min-w-0 gap-6 xl:grid-cols-2">

        {{-- PESANAN TERBARU --}}
        <section class="min-w-0 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 p-5">

                <div>
                    <h2 class="font-bold text-gray-800">
                        Pesanan Terbaru
                    </h2>

                    <p class="mt-1 text-xs text-gray-500">
                        Aktivitas transaksi pelanggan.
                    </p>
                </div>

                <a
                    href="{{ route('admin.orders.index') }}"
                    class="rounded-lg bg-green-50 px-3 py-2 text-xs font-semibold text-green-700 transition hover:bg-green-100"
                >
                    Lihat Semua →
                </a>

            </div>

            @if ($recentOrders->isEmpty())

                <div class="p-10 text-center">

                    <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-full bg-gray-100 text-2xl">
                        📦
                    </div>

                    <p class="font-semibold text-gray-700">
                        Belum ada pesanan
                    </p>

                    <p class="mt-1 text-sm text-gray-400">
                        Pesanan pelanggan akan muncul di sini.
                    </p>

                </div>

            @else

                <div class="max-w-full overflow-x-auto">

                    <table class="min-w-[580px] w-full text-sm">

                        <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
                            <tr>
                                <th class="px-4 py-3 font-semibold">Pesanan</th>
                                <th class="px-4 py-3 font-semibold">Pelanggan</th>
                                <th class="px-4 py-3 text-right font-semibold">Total</th>
                                <th class="px-4 py-3 font-semibold">Status</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100">

                            @foreach ($recentOrders as $order)

                                <tr class="transition hover:bg-green-50/50">

                                    <td class="whitespace-nowrap px-4 py-4">

                                        <a
                                            href="{{ route('admin.orders.show', $order) }}"
                                            class="font-mono text-xs font-bold text-green-700 hover:underline"
                                        >
                                            {{ $order->order_code }}
                                        </a>

                                        <p class="mt-1 text-xs text-gray-400">
                                            {{ $order->created_at->format('d/m/Y H:i') }}
                                        </p>

                                    </td>

                                    <td class="px-4 py-4">
                                        <span class="font-medium text-gray-700">
                                            {{ $order->user->name ?? 'Pelanggan' }}
                                        </span>
                                    </td>

                                    <td class="whitespace-nowrap px-4 py-4 text-right font-semibold text-gray-800">
                                        @rupiah($order->grand_total)
                                    </td>

                                    <td class="whitespace-nowrap px-4 py-4">

                                        <div class="flex flex-col items-start gap-1">

                                            <x-badge :status="$order->payment_status">
                                                {{ $order->paymentStatusLabel() }}
                                            </x-badge>

                                            <x-badge :status="$order->order_status">
                                                {{ $order->orderStatusLabel() }}
                                            </x-badge>

                                        </div>

                                    </td>

                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>

            @endif

        </section>


        {{-- PEMANTAUAN STOK --}}
        <section class="min-w-0 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 p-5">

                <div>
                    <h2 class="font-bold text-gray-800">
                        Pemantauan Stok
                    </h2>

                    <p class="mt-1 text-xs text-gray-500">
                        Produk yang membutuhkan perhatian.
                    </p>
                </div>

                <a
                    href="{{ route('admin.products.index') }}"
                    class="rounded-lg bg-green-50 px-3 py-2 text-xs font-semibold text-green-700 transition hover:bg-green-100"
                >
                    Kelola Produk →
                </a>

            </div>

            @if ($lowStockProducts->isEmpty())

                <div class="p-10 text-center">

                    <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-full bg-green-50 text-2xl">
                        ✅
                    </div>

                    <p class="font-semibold text-gray-700">
                        Stok dalam kondisi aman
                    </p>

                    <p class="mt-1 text-sm text-gray-400">
                        Tidak ada produk dengan stok menipis.
                    </p>

                </div>

            @else

                <div class="max-w-full overflow-x-auto">

                    <table class="min-w-[580px] w-full text-sm">

                        <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
                            <tr>
                                <th class="px-4 py-3 font-semibold">Produk</th>
                                <th class="px-4 py-3 font-semibold">Mode</th>
                                <th class="px-4 py-3 text-right font-semibold">Stok Jual</th>
                                <th class="px-4 py-3 text-right font-semibold">Stok Sewa</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100">

                            @foreach ($lowStockProducts as $product)

                                <tr class="transition hover:bg-green-50/50">

                                    <td class="px-4 py-4">

                                        <a
                                            href="{{ route('admin.products.edit', $product) }}"
                                            class="font-semibold text-gray-800 hover:text-green-700 hover:underline"
                                        >
                                            {{ $product->name }}
                                        </a>

                                        <p class="mt-1 text-xs text-gray-400">
                                            {{ $product->category->name ?? 'Tanpa kategori' }}
                                        </p>

                                    </td>

                                    <td class="whitespace-nowrap px-4 py-4">
                                        <x-badge :status="$product->mode">
                                            {{ $product->modeLabel() }}
                                        </x-badge>
                                    </td>

                                    <td class="px-4 py-4 text-right">

                                        @if($product->canBeSold())

                                            <span class="font-bold {{ $product->stock_sale <= 5 ? 'text-red-600' : 'text-gray-700' }}">
                                                {{ $product->stock_sale }}
                                            </span>

                                        @else

                                            <span class="text-gray-400">-</span>

                                        @endif

                                    </td>

                                    <td class="px-4 py-4 text-right">

                                        @if($product->canBeRented())

                                            <span class="font-bold {{ $product->stock_rent <= 2 ? 'text-red-600' : 'text-gray-700' }}">
                                                {{ $product->stock_rent }}
                                            </span>

                                        @else

                                            <span class="text-gray-400">-</span>

                                        @endif

                                    </td>

                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>

            @endif

        </section>

    </div>


    {{-- FOOTER DASHBOARD --}}
    <div class="rounded-xl border border-green-100 bg-green-50/70 p-4">

        <div class="flex flex-wrap items-center justify-between gap-3">

            <div>
                <p class="text-sm font-bold text-green-900">
                    🌿 Keep Exploring with Wayfarer
                </p>

                <p class="mt-1 text-xs text-green-700">
                    Kelola perlengkapan, transaksi, dan perjalanan pelanggan dalam satu tempat.
                </p>
            </div>

            <span class="text-xs font-medium text-green-700">
                Wayfarer Admin © {{ date('Y') }}
            </span>

        </div>

    </div>

</div>

@endsection
