
@extends('layouts.customer')

@section('title', 'Beranda')

@section('content')

<div>

    {{-- HERO --}}
    <section class="relative isolate overflow-hidden bg-[#E4EADF]">

        {{-- Background foto --}}
<div
    class="absolute inset-0 scale-105 bg-cover bg-center opacity-60"
    style="background-image: url('{{ asset('images/hero-home.jpg') }}');"
></div>

{{-- Overlay lebih transparan --}}
<div class="absolute inset-0 bg-gradient-to-r from-[#E4EADF]/65 via-[#E4EADF]/40 to-[#E4EADF]/25"></div>

        <div class="relative z-10 mx-auto grid max-w-7xl items-center gap-12 px-6 py-20 md:grid-cols-2 md:px-8 md:py-28">

            <div class="max-w-xl">

                <p class="mb-5 text-sm font-semibold uppercase tracking-[0.25em] text-[#687E55]">
                    Outdoor Collection
                </p>

                <h1 class="text-5xl font-extrabold leading-tight tracking-tight text-[#183C2D] md:text-6xl">
                    Gear untuk setiap
                    <span class="text-[#718553]">petualangan.</span>
                </h1>

                <p class="mt-6 max-w-lg text-base leading-8 text-[#45594A]/80 md:text-lg">
                    Temukan perlengkapan outdoor berkualitas untuk menemani setiap
                    perjalananmu. Beli atau sewa sesuai kebutuhan.
                </p>

                <div class="mt-8 flex flex-wrap gap-3">

                    <a href="{{ route('catalog.index') }}"
                       class="rounded-full bg-[#244B38] px-6 py-3 text-sm font-semibold text-white transition hover:bg-[#173B2C]">
                        Explore Gear →
                    </a>

                    <a href="#about"
                       class="rounded-full border border-[#244B38] bg-[#F3F8F1]/80 px-6 py-3 text-sm font-semibold text-[#244B38] transition hover:bg-[#DDE8D8]">
                        Tentang Kami
                    </a>

                </div>

                <div class="mt-8 grid grid-cols-3 gap-4 text-sm">

                    <div>
                        <p class="font-semibold text-[#183C2D]">Buy</p>
                        <p class="text-[#45594A]/70">Miliki gear pilihanmu</p>
                    </div>

                    <div>
                        <p class="font-semibold text-[#183C2D]">Rent</p>
                        <p class="text-[#45594A]/70">Sewa sesuai kebutuhan</p>
                    </div>

                    <div>
                        <p class="font-semibold text-[#183C2D]">Explore</p>
                        <p class="text-[#45594A]/70">Mulai petualanganmu</p>
                    </div>

                </div>

            </div>


            {{-- FOTO HERO --}}
            <div class="relative hidden justify-center md:flex">

                <div class="relative">

                    <img
                        src="{{ asset('images/hero-home.jpg') }}"
                        alt="Petualangan camping bersama Wayfarer"
                        class="h-[420px] w-[300px] rounded-[2rem] object-cover shadow-xl"
                    >

                    <div class="absolute -bottom-3 -left-3 rounded-2xl border border-white/50 bg-[#F3F8F1]/95 px-4 py-3 shadow-lg backdrop-blur">

                        <p class="text-[10px] uppercase tracking-[0.2em] text-[#45594A]/50">
                            Your next adventure
                        </p>

                        <p class="text-sm font-bold text-[#183C2D]">
                            Starts with Wayfarer.
                        </p>

                    </div>

                </div>

            </div>

        </div>

    </section>


    {{-- ABOUT WAYFARER --}}
    <section id="about" class="bg-[#F3F8F1]">

        <div class="mx-auto grid max-w-7xl items-center gap-12 px-6 py-20 md:grid-cols-2 md:px-8">

            <div class="overflow-hidden rounded-3xl shadow-md">

                <img
                    src="{{ asset('images/home-about.jpg') }}"
                    alt="Aktivitas petualangan outdoor"
                    loading="lazy"
                    class="h-[300px] w-full object-cover transition duration-500 hover:scale-105 md:h-[400px]"
                >

            </div>

            <div>

                <p class="text-xs font-semibold uppercase tracking-[0.25em] text-[#718553]">
                    About Wayfarer
                </p>

                <h2 class="mt-4 text-3xl font-bold leading-tight text-[#183C2D] md:text-4xl">
                    Perjalanan hebat dimulai dari persiapan yang tepat.
                </h2>

                <p class="mt-5 leading-8 text-[#45594A]/80">
                    Wayfarer merupakan platform jual beli dan sewa
                    perlengkapan outdoor yang membantu mempersiapkan
                    perjalanan menjadi lebih mudah, praktis, dan nyaman.
                </p>

                <p class="mt-4 leading-8 text-[#45594A]/80">
                    Kami percaya setiap orang berhak menikmati petualangan
                    tanpa harus khawatir tentang perlengkapan yang dibutuhkan.
                </p>

                <a href="{{ route('catalog.index') }}"
                   class="mt-7 inline-flex items-center font-semibold text-[#244B38] transition hover:text-[#718553]">
                    Temukan perlengkapanmu →
                </a>

            </div>

        </div>

    </section>


    {{-- WHY WAYFARER --}}
    <section class="bg-[#DDE8D8]">

        <div class="mx-auto max-w-7xl px-6 py-20 md:px-8">

            <div class="mb-12 max-w-xl">

                <p class="text-xs font-semibold uppercase tracking-[0.25em] text-[#718553]">
                    Why Wayfarer?
                </p>

                <h2 class="mt-3 text-3xl font-bold text-[#183C2D] md:text-4xl">
                    Semua kebutuhan petualangan dalam satu tempat.
                </h2>

            </div>

            <div class="grid gap-6 md:grid-cols-3">

                <div class="rounded-2xl border border-[#C7D5C0] bg-[#F3F8F1] p-7 shadow-sm transition hover:-translate-y-1 hover:shadow-md">

                    <span class="text-sm font-bold text-[#8A7953]">
                        01 / FLEXIBILITY
                    </span>

                    <h3 class="mt-4 text-xl font-bold text-[#183C2D]">
                        Beli atau Sewa
                    </h3>

                    <p class="mt-3 text-sm leading-7 text-[#45594A]/75">
                        Pilih untuk memiliki perlengkapan sendiri atau
                        menyewanya sesuai kebutuhan perjalanan.
                    </p>

                </div>


                <div class="rounded-2xl border border-[#C7D5C0] bg-[#F3F8F1] p-7 shadow-sm transition hover:-translate-y-1 hover:shadow-md">

                    <span class="text-sm font-bold text-[#8A7953]">
                        02 / SIMPLICITY
                    </span>

                    <h3 class="mt-4 text-xl font-bold text-[#183C2D]">
                        Mudah Dijelajahi
                    </h3>

                    <p class="mt-3 text-sm leading-7 text-[#45594A]/75">
                        Temukan berbagai perlengkapan melalui katalog
                        yang praktis dengan fitur pencarian dan kategori.
                    </p>

                </div>


                <div class="rounded-2xl border border-[#C7D5C0] bg-[#F3F8F1] p-7 shadow-sm transition hover:-translate-y-1 hover:shadow-md">

                    <span class="text-sm font-bold text-[#8A7953]">
                        03 / CONVENIENCE
                    </span>

                    <h3 class="mt-4 text-xl font-bold text-[#183C2D]">
                        Praktis dalam Satu Platform
                    </h3>

                    <p class="mt-3 text-sm leading-7 text-[#45594A]/75">
                        Jelajahi produk, lakukan pemesanan, dan pantau
                        transaksi dalam satu platform.
                    </p>

                </div>

            </div>

        </div>

    </section>


    {{-- FEATURED PRODUCTS --}}
    <section class="bg-[#EDF4EA]">

        <div class="mx-auto max-w-7xl px-6 py-20 md:px-8">

            <div class="mb-10 flex flex-wrap items-end justify-between gap-4">

                <div>

                    <p class="text-xs font-semibold uppercase tracking-[0.25em] text-[#718553]">
                        Featured Gear
                    </p>

                    <h2 class="mt-3 text-3xl font-bold text-[#183C2D]">
                        Pilihan untuk petualanganmu.
                    </h2>

                    <p class="mt-2 text-sm text-[#45594A]/65">
                        Temukan perlengkapan favorit dari Wayfarer.
                    </p>

                </div>

                <a href="{{ route('catalog.index') }}"
                   class="text-sm font-semibold text-[#244B38] transition hover:text-[#718553]">
                    Lihat semua produk →
                </a>

            </div>


            @if ($products->isEmpty())

                <div class="rounded-2xl border border-[#CAD8C5] bg-[#F3F8F1] p-10 text-center">

                    <p class="text-sm text-[#45594A]/70">
                        Belum ada produk yang tersedia.
                    </p>

                </div>

            @else

                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">

                    @foreach ($products as $product)

                        <a href="{{ route('catalog.show', $product) }}"
                           class="group overflow-hidden rounded-2xl border border-[#D2DDCB] bg-[#F7F8F2] transition duration-300 hover:-translate-y-1 hover:shadow-xl">

                            <div class="overflow-hidden bg-[#DDE8D8]">

                                @if (filled($product->image_url))

                                    <img
                                        src="{{ $product->image_url }}"
                                        alt="{{ $product->name }}"
                                        loading="lazy"
                                        class="h-56 w-full object-cover transition duration-500 group-hover:scale-105"
                                    >

                                @else

                                    <div class="flex h-56 items-center justify-center">
                                        <span class="text-4xl">⛺</span>
                                    </div>

                                @endif

                            </div>

                            <div class="p-5">

                                <p class="text-xs font-medium text-[#718553]">
                                    {{ $product->category?->name ?? 'Outdoor Gear' }}
                                </p>

                                <h3 class="mt-2 font-bold text-[#183C2D]">
                                    {{ $product->name }}
                                </h3>

                                <div class="mt-3">
                                    <x-badge :status="$product->mode">
                                        {{ $product->modeLabel() }}
                                    </x-badge>
                                </div>

                                <div class="mt-4 space-y-2 text-sm text-[#45594A]/80">

                                    @if ($product->canBeSold())

                                        <p>
                                            Beli:
                                            <span class="font-bold text-[#244B38]">
                                                @rupiah($product->sale_price)
                                            </span>
                                        </p>

                                    @endif

                                    @if ($product->canBeRented())

                                        <p>
                                            Sewa:
                                            <span class="font-bold text-[#244B38]">
                                                @rupiah($product->rent_price_per_day)
                                            </span>
                                            /hari
                                        </p>

                                    @endif

                                </div>

                                <p class="mt-5 border-t border-[#DCE5D7] pt-4 text-sm font-semibold text-[#244B38]">
                                    Lihat detail →
                                </p>

                            </div>

                        </a>

                    @endforeach

                </div>

            @endif

        </div>

    </section>


    {{-- FINAL CTA / ADVENTURE --}}
    <section class="relative isolate overflow-hidden bg-[#173B2C]">

        <div class="absolute inset-0 bg-cover bg-center opacity-35"
             style="background-image: url('{{ asset('images/wayfarer-dashboard.jpg') }}');">
        </div>

        <div class="absolute inset-0 bg-[#173B2C]/65"></div>

        <div class="relative mx-auto max-w-7xl px-6 py-24 text-center md:px-8">

            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-[#D6DDBD]">
                Ready to Explore?
            </p>

            <h2 class="mt-4 text-3xl font-bold text-white md:text-5xl">
                Your adventure starts here.
            </h2>

            <p class="mx-auto mt-5 max-w-xl text-sm leading-7 text-white/80">
                Temukan perlengkapan yang tepat dan bersiaplah
                menciptakan pengalaman baru bersama Wayfarer.
            </p>

            <a href="{{ route('catalog.index') }}"
               class="mt-8 inline-flex rounded-full bg-[#F3F8F1] px-8 py-3 text-sm font-bold text-[#244B38] transition hover:bg-[#DDE8D8]">
                Explore Gear →
            </a>

        </div>

    </section>

</div>

@endsection
