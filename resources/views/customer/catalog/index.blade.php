
@extends('layouts.customer')

@section('title', 'Katalog')

@section('content')

@php
    $tabs = [
        '' => 'Semua',
        'sale' => 'Jual',
        'rent' => 'Sewa',
        'both' => 'Jual & Sewa',
    ];

    $baseQuery = request()->except(['mode', 'page']);
@endphp


{{-- HEADER / HERO --}}
<section class="relative isolate overflow-hidden bg-[#E4EADF]">

    {{-- Background foto --}}
    <div
        class="absolute inset-0 bg-cover bg-center opacity-55"
        style="background-image: url('{{ asset('images/outdoor-catalog.jpg') }}');"
    ></div>

    {{-- Overlay --}}
    <div class="absolute inset-0 bg-[#E4EADF]/40"></div>

    {{-- Konten --}}
    <div class="relative z-10 mx-auto max-w-7xl px-5 py-14 md:px-8 md:py-20">

        <p class="text-xs font-semibold uppercase tracking-[0.25em] text-[#718553]">
            Outdoor Collection
        </p>

        <div class="mt-3 flex flex-col gap-5 md:flex-row md:items-end md:justify-between">

            <div class="max-w-2xl">

                <h1 class="text-4xl font-bold leading-tight tracking-tight text-[#183C2D] md:text-5xl">
                    Katalog perlengkapan outdoor.
                </h1>

                <p class="mt-4 text-base leading-7 text-[#45594A]/80">
                    Cari perlengkapan yang kamu butuhkan dengan lebih mudah.
                    Gunakan pencarian, kategori, dan filter untuk menemukan gear yang tepat.
                </p>

            </div>

            <a href="{{ route('explore') }}"
               class="shrink-0 text-sm font-semibold text-[#244B38] transition hover:text-[#718553]">
                Belum tahu mau cari apa? Explore Gear →
            </a>

        </div>

    </div>

</section>


{{-- FILTER --}}
<section class="border-y border-[#D0DCCB] bg-[#F3F8F1]">

    <div class="mx-auto max-w-7xl px-5 py-8 md:px-8">

        {{-- MODE TABS --}}
        <div class="flex flex-wrap gap-2">

            @foreach ($tabs as $value => $label)

                @php
                    $active = ($mode ?? '') === $value;

                    $query = $value === ''
                        ? $baseQuery
                        : array_merge($baseQuery, ['mode' => $value]);
                @endphp

                <a href="{{ route('catalog.index', $query) }}"
                   @class([
                       'rounded-full px-5 py-2.5 text-sm font-semibold transition',
                       'bg-[#244B38] text-white shadow-sm' => $active,
                       'border border-[#C4D2BF] bg-[#E4EADF] text-[#244B38] hover:border-[#244B38] hover:bg-[#DDE8D8]' => ! $active,
                   ])>
                    {{ $label }}
                </a>

            @endforeach

        </div>


        {{-- SEARCH & FILTER --}}
        <form method="GET"
              action="{{ route('catalog.index') }}"
              class="mt-6 rounded-2xl border border-[#C8D5C2] bg-[#DDE8D8] p-5 shadow-sm">

            @if ($mode)
                <input type="hidden" name="mode" value="{{ $mode }}">
            @endif

            <div class="grid gap-4 md:grid-cols-12">

                {{-- SEARCH --}}
                <div class="md:col-span-5">

                    <label for="q"
                           class="mb-2 block text-xs font-semibold uppercase tracking-wide text-[#45594A]/75">
                        Cari perlengkapan
                    </label>

                    <input
                        type="text"
                        id="q"
                        name="q"
                        value="{{ $keyword }}"
                        placeholder="Contoh: tenda, carrier, sleeping bag..."
                        class="w-full rounded-xl border border-[#C6D3C0] bg-[#F7F9F3] px-4 py-3 text-sm text-[#183C2D] outline-none transition placeholder:text-[#45594A]/40 focus:border-[#244B38] focus:ring-2 focus:ring-[#244B38]/15"
                    >

                </div>


                {{-- CATEGORY --}}
                <div class="md:col-span-3">

                    <label for="category"
                           class="mb-2 block text-xs font-semibold uppercase tracking-wide text-[#45594A]/75">
                        Kategori
                    </label>

                    <select
                        id="category"
                        name="category"
                        class="w-full rounded-xl border border-[#C6D3C0] bg-[#F7F9F3] px-4 py-3 text-sm text-[#183C2D] outline-none focus:border-[#244B38] focus:ring-2 focus:ring-[#244B38]/15"
                    >

                        <option value="">Semua kategori</option>

                        @foreach ($categories as $category)

                            <option
                                value="{{ $category->slug }}"
                                @selected($categorySlug === $category->slug)
                            >
                                {{ $category->name }}
                            </option>

                        @endforeach

                    </select>

                </div>


                {{-- SORT --}}
                <div class="md:col-span-2">

                    <label for="sort"
                           class="mb-2 block text-xs font-semibold uppercase tracking-wide text-[#45594A]/75">
                        Urutkan
                    </label>

                    <select
                        id="sort"
                        name="sort"
                        class="w-full rounded-xl border border-[#C6D3C0] bg-[#F7F9F3] px-4 py-3 text-sm text-[#183C2D] outline-none focus:border-[#244B38] focus:ring-2 focus:ring-[#244B38]/15"
                    >

                        <option value="latest" @selected($sort === 'latest')>
                            Terbaru
                        </option>

                        <option value="name" @selected($sort === 'name')>
                            Nama A-Z
                        </option>

                    </select>

                </div>


                {{-- BUTTON --}}
                <div class="flex items-end md:col-span-2">

                    <button
                        type="submit"
                        class="w-full rounded-xl bg-[#244B38] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#173B2C]"
                    >
                        Cari Produk
                    </button>

                </div>

            </div>


            {{-- RESET --}}
            <div class="mt-3 flex justify-end">

                <a href="{{ route('catalog.index') }}"
                   class="text-xs font-medium text-[#45594A]/60 transition hover:text-[#244B38]">
                    Reset filter
                </a>

            </div>

        </form>

    </div>

</section>


{{-- PRODUCTS --}}
<section class="bg-[#DDE8D8]">

    <div class="mx-auto max-w-7xl px-5 py-12 md:px-8">

        @if ($products->isEmpty())

            {{-- EMPTY STATE --}}
            <div class="rounded-2xl border border-[#C8D5C2] bg-[#F3F8F1] px-6 py-16 text-center">

                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-[#E4EADF] text-2xl">
                    ⛺
                </div>

                <h2 class="mt-5 text-xl font-semibold text-[#183C2D]">
                    Produk tidak ditemukan
                </h2>

                <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-[#45594A]/70">
                    Coba gunakan kata kunci atau kategori yang berbeda
                    untuk menemukan perlengkapan yang kamu cari.
                </p>

                <a href="{{ route('catalog.index') }}"
                   class="mt-6 inline-flex rounded-full bg-[#244B38] px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-[#173B2C]">
                    Lihat Semua Produk
                </a>

            </div>

        @else

            {{-- COLLECTION HEADER --}}
            <div class="mb-7 flex items-end justify-between">

                <div>

                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[#718553]">
                        Collection
                    </p>

                    <h2 class="mt-1 text-2xl font-bold text-[#183C2D]">
                        Perlengkapan tersedia
                    </h2>

                </div>

                <span class="text-sm text-[#45594A]/60">
                    {{ $products->total() }} produk
                </span>

            </div>


            {{-- PRODUCT GRID --}}
            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">

                @foreach ($products as $product)

                    <a
                        href="{{ route('catalog.show', ['product' => $product->slug]) }}"
                        class="group overflow-hidden rounded-2xl border border-[#C8D5C2] bg-[#F3F8F1] transition duration-200 hover:-translate-y-1 hover:shadow-lg"
                    >

                        {{-- PRODUCT IMAGE --}}
                        <div class="relative h-52 overflow-hidden bg-[#D2DECF]">

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

                                        <div class="text-4xl">
                                            ⛺
                                        </div>

                                        <p class="mt-2 text-xs font-medium uppercase tracking-widest text-[#718553]">
                                            Wayfarer Gear
                                        </p>

                                    </div>

                                </div>

                            @endif


                            <span class="absolute left-3 top-3 rounded-full bg-[#F3F8F1]/95 px-3 py-1 text-xs font-semibold text-[#244B38] shadow-sm">
                                {{ $product->modeLabel() }}
                            </span>

                        </div>


                        {{-- PRODUCT CONTENT --}}
                        <div class="p-5">

                            <p class="text-xs font-medium uppercase tracking-wide text-[#718553]">
                                {{ $product->category?->name ?? 'Outdoor Gear' }}
                            </p>

                            <h3 class="mt-2 line-clamp-2 min-h-12 text-lg font-semibold text-[#183C2D]">
                                {{ $product->name }}
                            </h3>


                            {{-- PRICE --}}
                            <div class="mt-4 space-y-1 text-sm text-[#45594A]/75">

                                @if ($product->canBeSold())

                                    <p>
                                        Beli:
                                        <span class="font-semibold text-[#244B38]">
                                            @rupiah($product->sale_price)
                                        </span>
                                    </p>

                                @endif


                                @if ($product->canBeRented())

                                    <p>
                                        Sewa:
                                        <span class="font-semibold text-[#244B38]">
                                            @rupiah($product->rent_price_per_day)
                                        </span>
                                        /hari
                                    </p>

                                @endif

                            </div>


                            {{-- DETAIL --}}
                            <div class="mt-5 border-t border-[#D5E0D0] pt-4 text-sm font-semibold text-[#244B38]">

                                Lihat detail

                                <span class="ml-1 transition group-hover:ml-2">
                                    →
                                </span>

                            </div>

                        </div>

                    </a>

                @endforeach

            </div>


            {{-- PAGINATION --}}
            <div class="mt-10">
                {{ $products->links() }}
            </div>

        @endif

    </div>

</section>

@endsection
