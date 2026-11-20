@extends('layouts.customer')

@section('title', 'Explore Gear')

@section('content')

{{-- HERO --}}
<section class="relative isolate overflow-hidden bg-[#E4EADF]">

    {{-- Background foto --}}
    <div
        class="absolute inset-0 bg-cover bg-center opacity-60"
        style="background-image: url('{{ asset('images/outdoor-explore.jpg') }}');"
    ></div>

    {{-- Overlay --}}
    <div class="absolute inset-0 bg-[#E4EADF]/40"></div>

    {{-- Konten --}}
    <div class="relative z-10 mx-auto max-w-7xl px-5 py-16 md:px-8 md:py-20">

        <div class="max-w-3xl">
            <p class="text-xs font-semibold uppercase tracking-[0.25em] text-olive">
                Explore Gear
            </p>

            <h1 class="mt-4 text-4xl font-bold leading-tight tracking-tight text-forest-dark md:text-6xl">
                Temukan gear yang
                <span class="text-olive">sesuai perjalananmu.</span>
            </h1>

            <p class="mt-6 max-w-2xl text-base leading-7 text-dark/65 md:text-lg">
                Belum tahu perlengkapan apa yang kamu butuhkan?
                Mulai dari kategori dan temukan berbagai gear outdoor
                yang tersedia di Wayfarer.
            </p>

            <div class="mt-8 flex flex-wrap gap-3">
                <a href="#kategori"
                   class="rounded-full bg-forest px-6 py-3 text-sm font-semibold text-white transition hover:bg-forest-dark">
                    Jelajahi Kategori
                </a>

                <a href="{{ route('catalog.index') }}"
                   class="rounded-full border border-forest px-6 py-3 text-sm font-semibold text-forest transition hover:bg-sage/20">
                    Lihat Semua Produk
                </a>
            </div>
        </div>

    </div>
</section>

{{-- KATEGORI --}}
<section id="kategori" class="bg-[#f3f8f1]">
    <div class="mx-auto max-w-7xl px-5 py-14 md:px-8">

        <div class="mb-8">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-olive">
                Browse by category
            </p>

            <h2 class="mt-2 text-3xl font-bold text-forest-dark md:text-4xl">
                Pilih berdasarkan kebutuhan
            </h2>

            <p class="mt-3 max-w-xl text-sm leading-6 text-dark/60">
                Temukan perlengkapan berdasarkan kategori yang paling
                sesuai dengan aktivitas outdoor-mu.
            </p>
        </div>

        @if ($categories->isEmpty())
            <div class="rounded-2xl border border-sage/30 bg-[#edf4ea] px-6 py-14 text-center">

                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-white/70 text-2xl">
                    ⛺
                </div>

                <h3 class="mt-4 font-semibold text-forest-dark">
                    Belum ada kategori
                </h3>

                <p class="mt-2 text-sm text-dark/55">
                    Kategori perlengkapan akan muncul di sini.
                </p>
            </div>
        @else
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">

                @foreach ($categories as $index => $category)
                    <a href="{{ route('catalog.index', ['category' => $category->slug]) }}"
                       class="group rounded-2xl border border-sage/30 bg-[#eef5eb] p-6 transition duration-200 hover:-translate-y-1 hover:border-forest/30 hover:bg-[#e5efe1] hover:shadow-md">

                        <div class="flex items-start justify-between">
                            <span class="flex h-10 w-10 items-center justify-center rounded-full bg-white/80 text-sm font-bold text-forest shadow-sm">
                                {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}
                            </span>

                            <span class="text-lg text-forest transition group-hover:translate-x-1">
                                →
                            </span>
                        </div>

                        <h3 class="mt-6 text-lg font-bold text-forest-dark">
                            {{ $category->name }}
                        </h3>

                        <p class="mt-2 text-sm leading-6 text-dark/55">
                            Lihat perlengkapan dalam kategori ini.
                        </p>
                    </a>
                @endforeach

            </div>
        @endif

    </div>
</section>

{{-- HOW IT WORKS --}}
<section class="bg-[#dde8d8]">
    <div class="mx-auto max-w-7xl px-5 py-14 md:px-8">

        <div class="max-w-2xl">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-olive">
                Start exploring
            </p>

            <h2 class="mt-2 text-3xl font-bold text-forest-dark">
                Mulai dari yang kamu butuhkan.
            </h2>
        </div>

        <div class="mt-8 grid gap-5 md:grid-cols-3">

            <div class="rounded-2xl bg-[#f7fbf5] p-6 shadow-sm">
                <span class="text-sm font-bold text-olive">01</span>

                <h3 class="mt-4 font-bold text-forest-dark">
                    Pilih kategori
                </h3>

                <p class="mt-2 text-sm leading-6 text-dark/55">
                    Tentukan jenis perlengkapan berdasarkan aktivitas
                    yang ingin kamu lakukan.
                </p>
            </div>

            <div class="rounded-2xl bg-[#f7fbf5] p-6 shadow-sm">
                <span class="text-sm font-bold text-olive">02</span>

                <h3 class="mt-4 font-bold text-forest-dark">
                    Temukan gear
                </h3>

                <p class="mt-2 text-sm leading-6 text-dark/55">
                    Lihat berbagai pilihan perlengkapan yang tersedia
                    di Wayfarer.
                </p>
            </div>

            <div class="rounded-2xl bg-[#f7fbf5] p-6 shadow-sm">
                <span class="text-sm font-bold text-olive">03</span>

                <h3 class="mt-4 font-bold text-forest-dark">
                    Lihat detail
                </h3>

                <p class="mt-2 text-sm leading-6 text-dark/55">
                    Buka detail produk untuk melihat informasi,
                    harga, dan ketersediaannya.
                </p>
            </div>

        </div>

    </div>
</section>

{{-- FEATURED --}}
<section class="bg-[#edf4ea]">
    <div class="mx-auto max-w-7xl px-5 py-14 md:px-8">

        <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-olive">
                    Featured gear
                </p>

                <h2 class="mt-2 text-3xl font-bold text-forest-dark">
                    Beberapa pilihan untukmu
                </h2>
            </div>

            <a href="{{ route('catalog.index') }}"
               class="text-sm font-semibold text-forest hover:text-olive">
                Lihat semua →
            </a>
        </div>

        @if ($products->isEmpty())
            <div class="rounded-2xl border border-sage/30 bg-[#f7fbf5] px-6 py-14 text-center shadow-sm">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-sage/20 text-xl">
                    ⛺
                </div>

                <h3 class="mt-4 font-semibold text-forest-dark">
                    Belum ada produk
                </h3>

                <p class="mt-2 text-sm text-dark/55">
                    Produk akan tampil ketika sudah tersedia.
                </p>
            </div>
        @else
            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">

                @foreach ($products as $product)
                    <a href="{{ route('catalog.show', ['product' => $product->slug]) }}"
                       class="group overflow-hidden rounded-2xl border border-sage/30 bg-[#f7fbf5] transition duration-200 hover:-translate-y-1 hover:shadow-lg">

                        <div class="relative h-52 overflow-hidden bg-light-sage">
                            @if (filled($product->image_url))
                                <img
                                    src="{{ $product->image_url }}"
                                    alt="{{ $product->name }}"
                                    loading="lazy"
                                    class="h-full w-full object-cover transition duration-300 group-hover:scale-105"
                                >
                            @else
                                <div class="flex h-full items-center justify-center">
                                    <div class="text-center">
                                        <div class="text-4xl">⛺</div>
                                        <p class="mt-2 text-xs font-medium uppercase tracking-widest text-olive">
                                            Wayfarer Gear
                                        </p>
                                    </div>
                                </div>
                            @endif

                            <span class="absolute left-4 top-4 rounded-full bg-white/90 px-3 py-1 text-xs font-semibold text-forest shadow-sm">
                                {{ $product->modeLabel() }}
                            </span>
                        </div>

                        <div class="p-5">
                            <p class="text-xs font-medium uppercase tracking-wide text-olive">
                                {{ $product->category->name }}
                            </p>

                            <h3 class="mt-2 text-lg font-bold text-forest-dark">
                                {{ $product->name }}
                            </h3>

                            <div class="mt-4 space-y-1 text-sm text-dark/65">
                                @if ($product->canBeSold())
                                    <p>
                                        Beli:
                                        <span class="font-semibold text-forest">
                                            @rupiah($product->sale_price)
                                        </span>
                                    </p>
                                @endif

                                @if ($product->canBeRented())
                                    <p>
                                        Sewa:
                                        <span class="font-semibold text-forest">
                                            @rupiah($product->rent_price_per_day)
                                        </span>
                                        /hari
                                    </p>
                                @endif
                            </div>

                            <div class="mt-5 border-t border-sage/20 pt-4 text-sm font-semibold text-forest">
                                Lihat detail
                                <span class="ml-1 transition group-hover:ml-2">→</span>
                            </div>
                        </div>

                    </a>
                @endforeach

            </div>
        @endif

    </div>
</section>

{{-- CTA --}}
<section class="relative isolate overflow-hidden bg-forest-dark">

    <div
        class="absolute inset-0 bg-cover bg-center opacity-25"
        style="background-image: url('{{ asset('images/wayfarer-dashboard.jpg') }}');"
    ></div>

    <div class="absolute inset-0 bg-forest-dark/75"></div>

    <div class="relative z-10 mx-auto max-w-4xl px-5 py-20 text-center">
        <p class="text-xs font-semibold uppercase tracking-[0.25em] text-sage">
            Ready to explore?
        </p>

        <h2 class="mt-3 text-3xl font-bold text-white md:text-4xl">
            Sudah tahu yang kamu cari?
        </h2>

        <p class="mx-auto mt-4 max-w-xl text-sm leading-6 text-white/75">
            Langsung gunakan katalog untuk mencari perlengkapan
            berdasarkan nama, kategori, atau jenis layanan.
        </p>

        <a href="{{ route('catalog.index') }}"
           class="mt-7 inline-flex rounded-full bg-white px-6 py-3 text-sm font-semibold text-forest transition hover:bg-light-sage">
            Buka Katalog
        </a>
    </div>

</section>

@endsection