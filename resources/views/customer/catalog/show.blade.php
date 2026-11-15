@extends('layouts.customer')

@section('title', $product->name)

@section('content')
    <a href="{{ route('catalog.index') }}" class="mb-4 inline-block text-sm text-green-700 hover:underline">&larr; Kembali ke katalog</a>

    <div class="grid gap-8 md:grid-cols-2">
        <div>
            <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-full rounded-lg border object-cover">
        </div>

        <div class="space-y-4">
            <div>
                <div class="text-sm text-gray-500">{{ $product->category->name }}</div>
                <h1 class="text-2xl font-bold">{{ $product->name }}</h1>
                <x-badge :status="$product->mode">{{ $product->modeLabel() }}</x-badge>
            </div>

            @if ($product->canBeSold())
                <div class="rounded-lg border bg-white p-4">
                    <h2 class="mb-2 font-semibold">Beli</h2>
                    <div class="text-xl font-bold text-green-700">@rupiah($product->sale_price)</div>
                    <div class="mb-3 text-sm text-gray-600">Stok tersedia: {{ $availableSale }} unit</div>

                    @auth
                        @if (auth()->user()->isCustomer())
                            @if ($availableSale > 0)
                                <form method="POST" action="{{ route('cart.store') }}" class="flex items-end gap-3">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                    <input type="hidden" name="type" value="sale">

                                    <div>
                                        <label class="mb-1 block text-sm">Jumlah</label>
                                        <input type="number" name="qty" value="1" min="1" max="{{ $availableSale }}"
                                               class="w-24 rounded border px-3 py-2">
                                    </div>

                                    <button type="submit" class="rounded bg-green-600 px-4 py-2 text-white hover:bg-green-700">
                                        Tambah ke Keranjang (Beli)
                                    </button>
                                </form>
                            @else
                                <div class="text-sm font-medium text-red-600">Stok beli habis.</div>
                            @endif
                        @else
                            <div class="text-sm text-gray-500">Akun admin tidak dapat berbelanja.</div>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="text-sm text-green-700 hover:underline">Masuk untuk membeli</a>
                    @endauth
                </div>
            @endif

            @if ($product->canBeRented())
                <div class="rounded-lg border bg-white p-4">
                    <h2 class="mb-2 font-semibold">Sewa</h2>
                    <div class="text-xl font-bold text-orange-600">@rupiah($product->rent_price_per_day) <span class="text-sm font-normal text-gray-600">/ hari</span></div>
                    <div class="text-sm text-gray-600">Deposit: @rupiah($product->deposit) per unit (dikembalikan setelah barang diterima kembali)</div>
                    <div class="mb-3 text-sm text-gray-600">Total unit sewa: {{ $product->stock_rent }}</div>

                    <form method="GET" action="{{ route('catalog.show', $product) }}" class="mb-3 grid gap-3 sm:grid-cols-3 sm:items-end">
                        <div>
                            <label class="mb-1 block text-sm">Mulai sewa</label>
                            <input type="date" name="rent_start" min="{{ now()->toDateString() }}"
                                   value="{{ request('rent_start') }}" required class="w-full rounded border px-3 py-2">
                        </div>
                        <div>
                            <label class="mb-1 block text-sm">Selesai sewa</label>
                            <input type="date" name="rent_end" min="{{ now()->toDateString() }}"
                                   value="{{ request('rent_end') }}" required class="w-full rounded border px-3 py-2">
                        </div>
                        <button type="submit" class="rounded border border-orange-500 px-4 py-2 text-orange-600 hover:bg-orange-50">
                            Cek Ketersediaan
                        </button>
                    </form>

                    @if (count($dateErrors) > 0)
                        <div class="mb-3 rounded border border-red-300 bg-red-50 px-3 py-2 text-sm text-red-800">
                            <ul class="list-disc pl-5">
                                @foreach ($dateErrors as $message)
                                    <li>{{ $message }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if ($rentQuote !== null)
                        <div class="mb-3 rounded border bg-gray-50 p-3 text-sm">
                            <div>Periode: {{ $rentStart->format('d/m/Y') }} - {{ $rentEnd->format('d/m/Y') }} ({{ $rentQuote['rent_days'] }} hari)</div>
                            <div>Biaya sewa per unit: @rupiah($rentQuote['subtotal'])</div>
                            <div>Deposit per unit: @rupiah($rentQuote['deposit'])</div>
                            <div class="mt-1 font-medium {{ $availableRent > 0 ? 'text-green-700' : 'text-red-600' }}">
                                Tersedia pada tanggal ini: {{ $availableRent }} unit
                            </div>
                        </div>

                        @auth
                            @if (auth()->user()->isCustomer())
                                @if ($availableRent > 0)
                                    <form method="POST" action="{{ route('cart.store') }}" class="flex items-end gap-3">
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                                        <input type="hidden" name="type" value="rent">
                                        <input type="hidden" name="rent_start" value="{{ $rentStart->toDateString() }}">
                                        <input type="hidden" name="rent_end" value="{{ $rentEnd->toDateString() }}">

                                        <div>
                                            <label class="mb-1 block text-sm">Jumlah</label>
                                            <input type="number" name="qty" value="1" min="1" max="{{ $availableRent }}"
                                                   class="w-24 rounded border px-3 py-2">
                                        </div>

                                        <button type="submit" class="rounded bg-orange-500 px-4 py-2 text-white hover:bg-orange-600">
                                            Tambah ke Keranjang (Sewa)
                                        </button>
                                    </form>
                                @endif
                            @else
                                <div class="text-sm text-gray-500">Akun admin tidak dapat menyewa.</div>
                            @endif
                        @else
                            <a href="{{ route('login') }}" class="text-sm text-green-700 hover:underline">Masuk untuk menyewa</a>
                        @endauth
                    @else
                        <div class="text-sm text-gray-500">Pilih tanggal sewa lalu klik Cek Ketersediaan.</div>
                    @endif
                </div>
            @endif
        </div>
    </div>

    @if ($related->isNotEmpty())
        <h2 class="mb-3 mt-10 text-xl font-bold">Produk Terkait</h2>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($related as $item)
                <a href="{{ route('catalog.show', $item) }}" class="block overflow-hidden rounded-lg border bg-white hover:shadow">
                    <img src="{{ $item->image_url }}" alt="{{ $item->name }}" class="h-32 w-full object-cover">
                    <div class="space-y-1 p-3">
                        <div class="font-semibold">{{ $item->name }}</div>
                        <x-badge :status="$item->mode">{{ $item->modeLabel() }}</x-badge>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
@endsection