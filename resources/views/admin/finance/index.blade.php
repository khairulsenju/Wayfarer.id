@extends('layouts.admin')

@section('title', 'Keuangan & Laporan')

@section('content')

<div class="space-y-6">

    {{-- HEADER --}}
    <div class="flex flex-wrap items-center justify-between gap-4">

        <div>
            <h1 class="text-2xl font-bold text-gray-800">
                Keuangan & Laporan
            </h1>

            <p class="mt-1 text-sm text-gray-500">
                Pantau pemasukan, pengeluaran, dan kondisi keuangan Wayfarer.
            </p>
        </div>

        <div class="rounded-lg bg-green-50 px-4 py-2 text-sm font-semibold text-green-800">
            Laporan Bulanan
        </div>

    </div>


    {{-- FILTER BULAN --}}
    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">

        <form method="GET"
              action="{{ route('admin.finance.index') }}"
              class="flex flex-wrap items-end gap-4">

            <div>
                <label class="mb-2 block text-sm font-medium text-gray-600">
                    Bulan
                </label>

                <select name="month"
                        class="w-48 rounded-lg border border-gray-300 px-3 py-2 text-sm">

                    @foreach ($monthNames as $number => $name)
                        <option value="{{ $number }}"
                            @selected($month == $number)>
                            {{ $name }}
                        </option>
                    @endforeach

                </select>
            </div>


            <div>
                <label class="mb-2 block text-sm font-medium text-gray-600">
                    Tahun
                </label>

                <input type="number"
                       name="year"
                       value="{{ $year }}"
                       min="2020"
                       max="2100"
                       class="w-32 rounded-lg border border-gray-300 px-3 py-2 text-sm">
            </div>


            <button type="submit"
                    class="rounded-lg bg-green-700 px-6 py-2.5 text-sm font-semibold text-white hover:bg-green-800">
                Tampilkan
            </button>

            <a href="{{ route('admin.finance.index') }}"
               class="rounded-lg border border-gray-300 px-5 py-2.5 text-sm text-gray-600 hover:bg-gray-50">
                Reset
            </a>

        </form>

    </div>


    {{-- RINGKASAN KEUANGAN --}}
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">

        {{-- TOTAL UANG MASUK --}}
        <div class="rounded-xl border border-green-100 bg-green-50 p-5">

            <p class="text-sm font-medium text-green-800">
                Total Pembayaran Diterima
            </p>

            <h2 class="mt-3 text-2xl font-bold text-green-900">
                Rp {{ number_format($cashReceived, 0, ',', '.') }}
            </h2>

            <p class="mt-2 text-xs text-green-700">
                Termasuk deposit penyewaan
            </p>

        </div>


        {{-- PENJUALAN --}}
        <div class="rounded-xl border border-blue-100 bg-blue-50 p-5">

            <p class="text-sm font-medium text-blue-800">
                Pendapatan Penjualan
            </p>

            <h2 class="mt-3 text-2xl font-bold text-blue-900">
                Rp {{ number_format($saleIncome, 0, ',', '.') }}
            </h2>

            <p class="mt-2 text-xs text-blue-700">
                Dari transaksi pembelian produk
            </p>

        </div>


        {{-- PENYEWAAN --}}
        <div class="rounded-xl border border-purple-100 bg-purple-50 p-5">

            <p class="text-sm font-medium text-purple-800">
                Pendapatan Penyewaan
            </p>

            <h2 class="mt-3 text-2xl font-bold text-purple-900">
                Rp {{ number_format($rentalIncome, 0, ',', '.') }}
            </h2>

            <p class="mt-2 text-xs text-purple-700">
                Dari transaksi penyewaan produk
            </p>

        </div>


        {{-- PENGELUARAN --}}
        <div class="rounded-xl border border-red-100 bg-red-50 p-5">

            <p class="text-sm font-medium text-red-800">
                Total Pengeluaran
            </p>

            <h2 class="mt-3 text-2xl font-bold text-red-900">
                Rp {{ number_format($expensesTotal, 0, ',', '.') }}
            </h2>

            <p class="mt-2 text-xs text-red-700">
                Pengeluaran yang dicatat admin
            </p>

        </div>


        {{-- SELISIH --}}
        <div class="rounded-xl border border-indigo-100 bg-indigo-50 p-5">

            <p class="text-sm font-medium text-indigo-800">
                Selisih Pendapatan - Pengeluaran
            </p>

            <h2 class="mt-3 text-2xl font-bold text-indigo-900">
                Rp {{ number_format($netBalance, 0, ',', '.') }}
            </h2>

            <p class="mt-2 text-xs text-indigo-700">
                Belum memperhitungkan biaya yang belum dicatat
            </p>

        </div>


        {{-- DEPOSIT --}}
        <div class="rounded-xl border border-amber-100 bg-amber-50 p-5">

            <p class="text-sm font-medium text-amber-800">
                Nilai Deposit Pesanan Lunas
            </p>

            <h2 class="mt-3 text-2xl font-bold text-amber-900">
                Rp {{ number_format($depositTotal, 0, ',', '.') }}
            </h2>

            <p class="mt-2 text-xs text-amber-700">
                Tidak dihitung sebagai pendapatan usaha
            </p>

        </div>

    </div>


    {{-- INFORMASI TAMBAHAN --}}
    <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-yellow-200 bg-yellow-50 p-4">

        <div>
            <p class="font-semibold text-yellow-900">
                Pembayaran Menunggu
            </p>

            <p class="mt-1 text-sm text-yellow-800">
                Pesanan yang belum dikonfirmasi pembayarannya pada periode ini.
            </p>
        </div>

        <div class="text-xl font-bold text-yellow-900">
            Rp {{ number_format($pendingTotal, 0, ',', '.') }}
        </div>

    </div>


    {{-- GRAFIK KEUANGAN --}}
    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">

        <div class="mb-6">
            <h2 class="text-lg font-bold text-gray-800">
                Grafik Keuangan
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Perbandingan pendapatan dan pengeluaran selama enam bulan.
            </p>
        </div>


        <div class="mb-5 flex flex-wrap gap-5 text-sm">

            <div class="flex items-center gap-2">
                <span class="h-3 w-3 rounded-full bg-green-600"></span>
                Pendapatan usaha
            </div>

            <div class="flex items-center gap-2">
                <span class="h-3 w-3 rounded-full bg-red-400"></span>
                Pengeluaran
            </div>

        </div>


        <div class="space-y-6">

            @foreach ($chartMonths as $item)

                <div>

                    <p class="mb-2 text-sm font-semibold text-gray-700">
                        {{ $item['label'] }}
                    </p>

                    {{-- PENDAPATAN --}}
                    <div class="mb-2 flex items-center gap-3">

                        <span class="w-20 shrink-0 text-xs text-gray-500">
                            Pendapatan
                        </span>

                        <div class="h-4 flex-1 overflow-hidden rounded-full bg-gray-100">

                            <div class="h-full rounded-full bg-green-600"
                                 style="width: {{ min(100, ($item['income'] / $maxChartValue) * 100) }}%">
                            </div>

                        </div>

                        <span class="w-32 shrink-0 text-right text-xs text-gray-600">
                            Rp {{ number_format($item['income'], 0, ',', '.') }}
                        </span>

                    </div>


                    {{-- PENGELUARAN --}}
                    <div class="flex items-center gap-3">

                        <span class="w-20 shrink-0 text-xs text-gray-500">
                            Pengeluaran
                        </span>

                        <div class="h-4 flex-1 overflow-hidden rounded-full bg-gray-100">

                            <div class="h-full rounded-full bg-red-400"
                                 style="width: {{ min(100, ($item['expense'] / $maxChartValue) * 100) }}%">
                            </div>

                        </div>

                        <span class="w-32 shrink-0 text-right text-xs text-gray-600">
                            Rp {{ number_format($item['expense'], 0, ',', '.') }}
                        </span>

                    </div>

                </div>

            @endforeach

        </div>

    </div>


    {{-- FORM PENGELUARAN --}}
    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">

        <div class="mb-5">
            <h2 class="text-lg font-bold text-gray-800">
                Catat Pengeluaran
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Tambahkan biaya operasional atau kebutuhan toko Wayfarer.
            </p>
        </div>


        <form method="POST"
              action="{{ route('admin.finance.expenses.store') }}"
              class="grid gap-4 md:grid-cols-2">

            @csrf

            <input type="hidden" name="filter_month" value="{{ $month }}">
            <input type="hidden" name="filter_year" value="{{ $year }}">


            {{-- KATEGORI --}}
            <div>

                <label class="mb-2 block text-sm font-medium text-gray-700">
                    Kategori Pengeluaran
                </label>

                <select name="category" required
                        class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm">

                    <option value="">Pilih kategori</option>
                    <option value="pembelian_stok" @selected(old('category') === 'pembelian_stok')>Pembelian Stok</option>
                    <option value="operasional" @selected(old('category') === 'operasional')>Operasional</option>
                    <option value="perawatan" @selected(old('category') === 'perawatan')>Perawatan Gear</option>
                    <option value="pengiriman" @selected(old('category') === 'pengiriman')>Pengiriman</option>
                    <option value="pengemasan" @selected(old('category') === 'pengemasan')>Pengemasan</option>
                    <option value="lainnya" @selected(old('category') === 'lainnya')>Lainnya</option>

                </select>

                @error('category')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror

            </div>


            {{-- NOMINAL --}}
            <div>

                <label class="mb-2 block text-sm font-medium text-gray-700">
                    Nominal Pengeluaran (Rp)
                </label>

                <input type="number"
                       name="amount"
                       min="1"
                       max="999999999999"
                       step="1"
                       value="{{ old('amount') }}"
                       placeholder="Contoh: 150000"
                       required
                       class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm">

                @error('amount')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror

            </div>


            {{-- TANGGAL --}}
            <div>

                <label class="mb-2 block text-sm font-medium text-gray-700">
                    Tanggal Pengeluaran
                </label>

                <input type="date"
                       name="expense_date"
                       value="{{ old('expense_date', now()->toDateString()) }}"
                       required
                       class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm">

                @error('expense_date')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror

            </div>


            {{-- KETERANGAN --}}
            <div>

                <label class="mb-2 block text-sm font-medium text-gray-700">
                    Keterangan
                </label>

                <input type="text"
                       name="description"
                       value="{{ old('description') }}"
                       placeholder="Contoh: Pembelian perlengkapan camping"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm">

                @error('description')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror

            </div>


            <div class="md:col-span-2">

                <button type="submit"
                        class="rounded-lg bg-green-700 px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-green-800">

                    + Simpan Pengeluaran

                </button>

            </div>

        </form>

    </div>


    {{-- RIWAYAT PENGELUARAN --}}
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

        <div class="border-b border-gray-200 p-5">

            <h2 class="text-lg font-bold text-gray-800">
                Riwayat Pengeluaran
            </h2>

        </div>


        <div class="overflow-x-auto">

            <table class="w-full text-left text-sm">

                <thead class="bg-gray-50 text-gray-600">

                    <tr>
                        <th class="px-5 py-3">Tanggal</th>
                        <th class="px-5 py-3">Kategori</th>
                        <th class="px-5 py-3">Keterangan</th>
                        <th class="px-5 py-3">Nominal</th>
                        <th class="px-5 py-3">Aksi</th>
                    </tr>

                </thead>


                <tbody class="divide-y divide-gray-100">

                    @forelse ($expenses as $expense)

                        <tr>

                            <td class="px-5 py-4">
                                {{ $expense->expense_date->format('d/m/Y') }}
                            </td>

                            <td class="px-5 py-4 font-medium text-gray-700">
                                {{ ucfirst(str_replace('_', ' ', $expense->category)) }}
                            </td>

                            <td class="px-5 py-4 text-gray-500">
                                {{ $expense->description ?? '-' }}
                            </td>

                            <td class="px-5 py-4 font-semibold text-red-600">
                                Rp {{ number_format($expense->amount, 0, ',', '.') }}
                            </td>

                            <td class="px-5 py-4">

                                <form method="POST"
                                      action="{{ route('admin.finance.expenses.destroy', $expense) }}"
                                      onsubmit="return confirm('Hapus catatan pengeluaran ini?')">

                                    @csrf
                                    @method('DELETE')

                                    <input type="hidden" name="filter_month" value="{{ $month }}">
                                    <input type="hidden" name="filter_year" value="{{ $year }}">

                                    <button type="submit"
                                            class="font-medium text-red-600 hover:text-red-800">
                                        Hapus
                                    </button>

                                </form>

                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="5" class="px-5 py-10 text-center text-gray-400">
                                Belum ada pengeluaran pada periode ini.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        <div class="p-5">
            {{ $expenses->links() }}
        </div>

    </div>


    {{-- RIWAYAT TRANSAKSI --}}
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

        <div class="border-b border-gray-200 p-5">

            <h2 class="text-lg font-bold text-gray-800">
                Riwayat Pemasukan
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Daftar pesanan yang pembayarannya berhasil.
            </p>

        </div>


        <div class="overflow-x-auto">

            <table class="w-full text-left text-sm">

                <thead class="bg-gray-50 text-gray-600">

                    <tr>
                        <th class="px-5 py-3">Kode Pesanan</th>
                        <th class="px-5 py-3">Tanggal Bayar</th>
                        <th class="px-5 py-3">Penerima</th>
                        <th class="px-5 py-3">Subtotal</th>
                        <th class="px-5 py-3">Deposit</th>
                        <th class="px-5 py-3">Total Diterima</th>
                    </tr>

                </thead>


                <tbody class="divide-y divide-gray-100">

                    @forelse ($orders as $order)

                        <tr>

                            <td class="px-5 py-4 font-medium text-green-700">
                                {{ $order->order_code }}
                            </td>

                            <td class="px-5 py-4">
                                {{ \Carbon\Carbon::parse($order->paid_at)->format('d/m/Y H:i') }}
                            </td>

                            <td class="px-5 py-4">
                                {{ $order->recipient_name }}
                            </td>

                            <td class="px-5 py-4">
                                Rp {{ number_format($order->subtotal, 0, ',', '.') }}
                            </td>

                            <td class="px-5 py-4">
                                Rp {{ number_format($order->deposit_total, 0, ',', '.') }}
                            </td>

                            <td class="px-5 py-4 font-semibold text-green-700">
                                Rp {{ number_format($order->grand_total, 0, ',', '.') }}
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center text-gray-400">
                                Belum ada transaksi lunas pada periode ini.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        <div class="p-5">
            {{ $orders->links() }}
        </div>

    </div>

</div>

@endsection
