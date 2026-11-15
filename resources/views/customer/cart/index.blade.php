@extends('layouts.customer')

@section('title', 'Keranjang')

@section('content')
    <h1 class="mb-4 text-2xl font-bold">Keranjang</h1>

    @if ($items->isEmpty())
        <div class="rounded-lg border bg-white p-8 text-center text-gray-500">
            Keranjang masih kosong.
            <div class="mt-3">
                <a href="{{ route('catalog.index') }}" class="text-green-700 hover:underline">Lihat katalog</a>
            </div>
        </div>
    @else
        @php
            $lines = collect($summary['lines'])->keyBy(fn ($line) => $line['cart_item']->id);
        @endphp

        <div class="overflow-x-auto rounded-lg border bg-white">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-100 text-left">
                    <tr>
                        <th class="px-3 py-2">Produk</th>
                        <th class="px-3 py-2">Tipe</th>
                        <th class="px-3 py-2">Jumlah dan Tanggal</th>
                        <th class="px-3 py-2 text-right">Harga</th>
                        <th class="px-3 py-2 text-right">Deposit</th>
                        <th class="px-3 py-2 text-right">Subtotal</th>
                        <th class="px-3 py-2"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($items as $item)
                        @php
                            $line = $lines[$item->id];
                            $issue = $issues[$item->id] ?? null;
                        @endphp
                        <tr class="border-t align-top">
                            <td class="px-3 py-3">
                                <a href="{{ route('catalog.show', $item->product) }}" class="font-medium hover:underline">{{ $item->product->name }}</a>
                                @if ($issue)
                                    <div class="mt-1 text-xs text-red-600">{{ $issue }}</div>
                                @endif
                            </td>

                            <td class="px-3 py-3">
                                <x-badge :status="$item->type">{{ $item->type === 'rent' ? 'Sewa' : 'Beli' }}</x-badge>
                            </td>

                            <td class="px-3 py-3">
                                <form method="POST" action="{{ route('cart.update', ['item' => $item->id]) }}" class="space-y-2">
                                    @csrf
                                    @method('PATCH')

                                    <div>
                                        <label class="text-xs text-gray-500">Jumlah</label>
                                        <input type="number" name="qty" value="{{ $item->qty }}" min="1" max="100"
                                               class="w-20 rounded border px-2 py-1">
                                    </div>

                                    @if ($item->type === 'rent')
                                        <div class="flex gap-2">
                                            <div>
                                                <label class="text-xs text-gray-500">Mulai</label>
                                                <input type="date" name="rent_start" value="{{ $item->rent_start->toDateString() }}"
                                                       class="rounded border px-2 py-1">
                                            </div>
                                            <div>
                                                <label class="text-xs text-gray-500">Selesai</label>
                                                <input type="date" name="rent_end" value="{{ $item->rent_end->toDateString() }}"
                                                       class="rounded border px-2 py-1">
                                            </div>
                                        </div>
                                        <div class="text-xs text-gray-500">Durasi: {{ $line['rent_days'] }} hari</div>
                                    @endif

                                    <button type="submit" class="rounded border px-3 py-1 text-xs hover:bg-gray-100">Simpan</button>
                                </form>
                            </td>

                            <td class="px-3 py-3 text-right">
                                @rupiah($line['unit_price'])
                                @if ($item->type === 'rent')
                                    <div class="text-xs text-gray-500">per hari</div>
                                @endif
                            </td>

                            <td class="px-3 py-3 text-right">
                                @if ($line['deposit'] > 0)
                                    @rupiah($line['deposit'])
                                @else
                                    -
                                @endif
                            </td>

                            <td class="px-3 py-3 text-right font-medium">@rupiah($line['subtotal'])</td>

                            <td class="px-3 py-3 text-right">
                                <form method="POST" action="{{ route('cart.destroy', ['item' => $item->id]) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6 grid gap-6 md:grid-cols-2">
            <div>
                <form method="POST" action="{{ route('cart.clear') }}" onsubmit="return confirm('Kosongkan seluruh keranjang?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="rounded border px-4 py-2 text-sm text-red-600 hover:bg-red-50">Kosongkan Keranjang</button>
                </form>
            </div>

            <div class="rounded-lg border bg-white p-4">
                <div class="flex justify-between py-1">
                    <span>Subtotal</span>
                    <span>@rupiah($summary['subtotal'])</span>
                </div>
                <div class="flex justify-between py-1">
                    <span>Total Deposit</span>
                    <span>@rupiah($summary['deposit_total'])</span>
                </div>
                <div class="flex justify-between border-t py-2 text-lg font-bold">
                    <span>Total Bayar</span>
                    <span>@rupiah($summary['grand_total'])</span>
                </div>

                @if ($hasIssues)
                    <div class="mb-2 text-sm text-red-600">Perbaiki item bermasalah sebelum melanjutkan.</div>
                    <span class="block cursor-not-allowed rounded bg-gray-300 py-2 text-center text-white">Lanjut ke Checkout</span>
                @else
                    <a href="{{ route('checkout.show') }}" class="block rounded bg-green-600 py-2 text-center text-white hover:bg-green-700">
                        Lanjut ke Checkout
                    </a>
                @endif
            </div>
        </div>
    @endif
@endsection