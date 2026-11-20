@extends('layouts.customer')

@section('title', $product->name)

@section('content')

    {{-- Breadcrumb --}}
    <section class="border-b border-sage/20 bg-white">

        <div class="mx-auto max-w-7xl px-5 py-5 md:px-8">

            <div class="flex flex-wrap items-center gap-2 text-sm text-dark/50">

                <a href="{{ route('home') }}"
                   class="transition hover:text-forest">
                    Home
                </a>

                <span>/</span>

                <a href="{{ route('catalog.index') }}"
                   class="transition hover:text-forest">
                    Katalog
                </a>

                <span>/</span>

                <span class="font-medium text-dark">
                    {{ $product->name }}
                </span>

            </div>

        </div>

    </section>


    {{-- PRODUCT DETAIL --}}
    <section class="bg-cream">

        <div class="mx-auto max-w-7xl px-5 py-12 md:px-8 md:py-16">

            <div class="grid gap-10 lg:grid-cols-2 lg:items-start">

                {{-- IMAGE --}}
                <div class="overflow-hidden rounded-3xl border border-sage/30 bg-white">

                    <div class="relative aspect-[4/3] overflow-hidden bg-light-sage">

                        @if (filled($product->image_url))

                            <img
                                src="{{ $product->image_url }}"
                                alt="{{ $product->name }}"
                                class="h-full w-full object-cover"
                            >

                        @else

                            <div class="flex h-full items-center justify-center">

                                <div class="text-center">

                                    <div class="text-7xl">
                                        ⛺
                                    </div>

                                    <p class="mt-4 text-xs font-semibold uppercase tracking-[0.25em] text-olive">
                                        Wayfarer Gear
                                    </p>

                                </div>

                            </div>

                        @endif

                    </div>

                </div>


                {{-- INFO --}}
                <div>

                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-olive">
                        {{ $product->category->name }}
                    </p>

                    <h1 class="mt-3 text-4xl font-bold leading-tight text-forest-dark md:text-5xl">
                        {{ $product->name }}
                    </h1>

                    <div class="mt-4 inline-flex rounded-full bg-light-sage px-4 py-2 text-sm font-semibold text-forest">
                        {{ $product->modeLabel() }}
                    </div>


                    {{-- Description --}}
                    <div class="mt-7">

                        <h2 class="text-sm font-bold uppercase tracking-wide text-forest">
                            Tentang produk
                        </h2>

                        <p class="mt-3 text-sm leading-7 text-dark/65">
                            {{ $product->description ?: 'Informasi produk akan tersedia di sini.' }}
                        </p>

                    </div>


                    {{-- Price --}}
                    <div class="mt-8 rounded-2xl border border-sage/30 bg-white p-6">

                        <h2 class="text-sm font-bold uppercase tracking-wide text-forest">
                            Harga
                        </h2>

                        <div class="mt-4 space-y-3">

                            @if ($product->canBeSold())

                                <div class="flex items-center justify-between gap-4">

                                    <span class="text-sm text-dark/60">
                                        Harga beli
                                    </span>

                                    <span class="text-lg font-bold text-forest">
                                        @rupiah($product->sale_price)
                                    </span>

                                </div>

                            @endif


                            @if ($product->canBeRented())

                                <div class="flex items-center justify-between gap-4">

                                    <span class="text-sm text-dark/60">
                                        Harga sewa
                                    </span>

                                    <span class="text-lg font-bold text-forest">
                                        @rupiah($product->rent_price_per_day)
                                        <span class="text-xs font-medium text-dark/50">
                                            /hari
                                        </span>
                                    </span>

                                </div>

                            @endif

                        </div>

                    </div>


                    {{-- Sale availability --}}
                    @if ($product->canBeSold())

                        <div class="mt-4 rounded-2xl border border-sage/30 bg-white p-5">

                            <div class="flex items-center justify-between gap-4">

                                <div>

                                    <p class="text-xs font-semibold uppercase tracking-wide text-olive">
                                        Stok pembelian
                                    </p>

                                    <p class="mt-1 font-semibold text-forest-dark">
                                        {{ $availableSale }} unit tersedia
                                    </p>

                                </div>

                                <span class="rounded-full bg-light-sage px-3 py-1 text-xs font-semibold text-forest">
                                    {{ $availableSale > 0 ? 'Tersedia' : 'Habis' }}
                                </span>

                            </div>

                        </div>

                    @endif


                    {{-- Rental --}}
                    @if ($product->canBeRented())

                        <div class="mt-4 rounded-2xl border border-sage/30 bg-white p-6">

                            <div>

                                <p class="text-xs font-semibold uppercase tracking-wide text-olive">
                                    Cek ketersediaan sewa
                                </p>

                                <h2 class="mt-2 text-lg font-bold text-forest-dark">
                                    Pilih tanggal perjalananmu
                                </h2>

                            </div>


                            @if (!empty($dateErrors))

                                <div class="mt-4 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">

                                    <ul class="space-y-1">

                                        @foreach ($dateErrors as $error)
                                            <li>
                                                {{ $error }}
                                            </li>
                                        @endforeach

                                    </ul>

                                </div>

                            @endif


                            <form method="GET"
                                  action="{{ route('catalog.show', ['product' => $product->slug]) }}"
                                  class="mt-5 space-y-4">

                                <div class="grid gap-4 sm:grid-cols-2">

                                    <div>

                                        <label for="rent_start"
                                               class="mb-2 block text-xs font-semibold text-dark/60">
                                            Mulai sewa
                                        </label>

                                        <input
                                            type="date"
                                            id="rent_start"
                                            name="rent_start"
                                            value="{{ $rentStart }}"
                                            min="{{ now()->format('Y-m-d') }}"
                                            class="w-full rounded-xl border border-sage/40 bg-white px-4 py-3 text-sm outline-none focus:border-forest focus:ring-2 focus:ring-sage/30"
                                        >

                                    </div>


                                    <div>

                                        <label for="rent_end"
                                               class="mb-2 block text-xs font-semibold text-dark/60">
                                            Selesai sewa
                                        </label>

                                        <input
                                            type="date"
                                            id="rent_end"
                                            name="rent_end"
                                            value="{{ $rentEnd }}"
                                            min="{{ now()->format('Y-m-d') }}"
                                            class="w-full rounded-xl border border-sage/40 bg-white px-4 py-3 text-sm outline-none focus:border-forest focus:ring-2 focus:ring-sage/30"
                                        >

                                    </div>

                                </div>


                                <button
                                    type="submit"
                                    class="w-full rounded-xl bg-forest px-5 py-3 text-sm font-semibold text-white transition hover:bg-forest-dark"
                                >
                                    Cek Ketersediaan
                                </button>

                            </form>


                            @if ($availableRent !== null)

                                <div class="mt-5 rounded-xl bg-light-sage p-4">

                                    <p class="text-xs font-semibold uppercase tracking-wide text-olive">
                                        Hasil pengecekan
                                    </p>

                                    <p class="mt-1 text-lg font-bold text-forest-dark">
                                        {{ $availableRent }} unit tersedia
                                    </p>

                                    <p class="mt-1 text-xs text-dark/55">
                                        Untuk periode yang kamu pilih.
                                    </p>

                                </div>

                            @endif

                        </div>

                    @endif


{{-- ADD TO CART --}}
<div class="mt-5 space-y-4">

    @guest
        <div class="rounded-2xl border border-sage/30 bg-white p-5">
            <h2 class="font-bold text-forest-dark">
                Tertarik dengan produk ini?
            </h2>
            <p class="mt-2 text-sm text-dark/60">
                Masuk atau daftar sebagai pelanggan untuk membeli
                maupun menyewa perlengkapan ini.
            </p>
            <div class="mt-4 flex gap-3">
                <a href="{{ route('login') }}"
                   class="rounded-xl bg-forest px-5 py-3 text-sm font-semibold text-white">
                    Masuk
                </a>
                <a href="{{ route('register') }}"
                   class="rounded-xl border border-forest px-5 py-3 text-sm font-semibold text-forest">
                    Daftar
                </a>
            </div>
        </div>
    @endguest

    @auth
        @if (auth()->user()->isCustomer())

            {{-- PEMBELIAN --}}
            @if ($product->canBeSold())
                <form method="POST" action="{{ route('cart.store') }}"
                      class="rounded-2xl border border-sage/30 bg-white p-5 space-y-3">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <input type="hidden" name="type" value="sale">

                    <h3 class="font-bold text-forest-dark">
                        Pembelian Produk
                    </h3>

                    <label class="block text-sm font-medium">
                        Jumlah pembelian
                    </label>
                    <input type="number" name="qty" value="1"
                           min="1" max="100" required
                           class="w-full rounded-xl border border-sage/40 px-4 py-3">

                    <button type="submit"
                            class="w-full rounded-xl bg-forest px-5 py-3 font-semibold text-white hover:bg-forest-dark">
                        + Tambah ke Keranjang
                    </button>
                </form>
            @endif

            {{-- PENYEWAAN --}}
            @if ($product->canBeRented())
                <form method="POST" action="{{ route('cart.store') }}"
                      class="rounded-2xl border border-sage/30 bg-white p-5 space-y-3">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <input type="hidden" name="type" value="rent">

                    <h3 class="font-bold text-forest-dark">
                        Penyewaan Produk
                    </h3>

                    <div class="grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-medium">
                                Mulai sewa
                            </label>
                            <input type="date" name="rent_start"
                                   value="{{ old('rent_start', $rentStart ?? '') }}"
                                   min="{{ now()->toDateString() }}" required
                                   class="w-full rounded-xl border border-sage/40 px-3 py-3">
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium">
                                Selesai sewa
                            </label>
                            <input type="date" name="rent_end"
                                   value="{{ old('rent_end', $rentEnd ?? '') }}"
                                   min="{{ now()->toDateString() }}" required
                                   class="w-full rounded-xl border border-sage/40 px-3 py-3">
                        </div>
                    </div>

                    <label class="block text-sm font-medium">
                        Jumlah penyewaan
                    </label>
                    <input type="number" name="qty" value="1"
                           min="1" max="100" required
                           class="w-full rounded-xl border border-sage/40 px-4 py-3">

                    <button type="submit"
                            class="w-full rounded-xl bg-forest px-5 py-3 font-semibold text-white hover:bg-forest-dark">
                        + Tambah ke Keranjang
                    </button>
                </form>
            @endif

        @else
            <p class="rounded-xl bg-light-sage p-4 text-sm">
                Silakan masuk sebagai pelanggan untuk melakukan transaksi.
            </p>
        @endif
    @endauth

</div>

                    {{-- Back --}}
                    <div class="mt-7 flex flex-wrap gap-3">

                        <a href="{{ route('catalog.index') }}"
                           class="rounded-full border border-forest px-6 py-3 text-sm font-semibold text-forest transition hover:bg-light-sage">
                            ← Kembali ke Katalog
                        </a>

                        <a href="{{ route('explore') }}"
                           class="rounded-full bg-forest px-6 py-3 text-sm font-semibold text-white transition hover:bg-forest-dark">
                            Explore Gear
                        </a>

                    </div>

                </div>

            </div>

        </div>

    </section>


    {{-- RELATED --}}
    @if ($related->isNotEmpty())

        <section class="bg-white">

            <div class="mx-auto max-w-7xl px-5 py-14 md:px-8">

                <div class="mb-8">

                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-olive">
                        You may also like
                    </p>

                    <h2 class="mt-2 text-3xl font-bold text-forest-dark">
                        Perlengkapan lainnya
                    </h2>

                </div>


                <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">

                    @foreach ($related as $item)

                        <a href="{{ route('catalog.show', ['product' => $item->slug]) }}"
                           class="group overflow-hidden rounded-2xl border border-sage/30 bg-white transition hover:-translate-y-1 hover:shadow-lg">

                            <div class="relative h-44 overflow-hidden bg-light-sage">

                                @if (filled($item->image_url))

                                    <img
                                        src="{{ $item->image_url }}"
                                        alt="{{ $item->name }}"
                                        class="h-full w-full object-cover transition duration-300 group-hover:scale-105"
                                    >

                                @else

                                    <div class="flex h-full items-center justify-center text-4xl">
                                        ⛺
                                    </div>

                                @endif

                            </div>


                            <div class="p-4">

                                <p class="text-xs uppercase tracking-wide text-olive">
                                    {{ $item->category->name ?? 'Outdoor Gear' }}
                                </p>

                                <h3 class="mt-2 font-semibold text-forest-dark">
                                    {{ $item->name }}
                                </h3>

                                <p class="mt-3 text-sm font-semibold text-forest">
                                    Lihat detail →
                                </p>

                            </div>

                        </a>

                    @endforeach

                </div>

            </div>

        </section>

    @endif

@endsection