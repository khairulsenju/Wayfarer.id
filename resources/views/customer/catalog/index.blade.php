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

    <h1 class="mb-4 text-2xl font-bold">Katalog Perlengkapan Outdoor</h1>

    <div class="mb-4 flex flex-wrap gap-2">
        @foreach ($tabs as $value => $label)
            @php
                $active = ($mode ?? '') === $value;
                $query = $value === '' ? $baseQuery : array_merge($baseQuery, ['mode' => $value]);
            @endphp
            <a href="{{ route('catalog.index', $query) }}"
               class="rounded-full border px-4 py-1 text-sm {{ $active ? 'border-green-600 bg-green-600 text-white' : 'bg-white hover:bg-gray-100' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    <form method="GET" action="{{ route('catalog.index') }}" class="mb-6 grid gap-3 rounded-lg border bg-white p-4 md:grid-cols-5">
        @if ($mode)
            <input type="hidden" name="mode" value="{{ $mode }}">
        @endif

        <input type="text" name="q" value="{{ $keyword }}" placeholder="Cari produk..."
               class="rounded border px-3 py-2 md:col-span-2">

        <select name="category" class="rounded border px-3 py-2">
            <option value="">Semua kategori</option>
            @foreach ($categories as $category)
                <option value="{{ $category->slug }}" @selected($categorySlug === $category->slug)>{{ $category->name }}</option>
            @endforeach
        </select>

        <select name="sort" class="rounded border px-3 py-2">
            <option value="latest" @selected($sort === 'latest')>Terbaru</option>
            <option value="name" @selected($sort === 'name')>Nama A-Z</option>
        </select>

        <div class="flex gap-2">
            <button type="submit" class="flex-1 rounded bg-green-600 px-4 py-2 text-white hover:bg-green-700">Filter</button>
            <a href="{{ route('catalog.index') }}" class="rounded border px-4 py-2 hover:bg-gray-100">Reset</a>
        </div>
    </form>

    @if ($products->isEmpty())
        <div class="rounded-lg border bg-white p-8 text-center text-gray-500">Produk tidak ditemukan.</div>
    @else
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($products as $product)
                <a href="{{ route('catalog.show', $product) }}" class="block overflow-hidden rounded-lg border bg-white hover:shadow">
                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="h-40 w-full object-cover">

                    <div class="space-y-1 p-3">
                        <div class="text-xs text-gray-500">{{ $product->category->name }}</div>
                        <div class="font-semibold">{{ $product->name }}</div>
                        <x-badge :status="$product->mode">{{ $product->modeLabel() }}</x-badge>

                        <div class="pt-1 text-sm">
                            @if ($product->canBeSold())
                                <div>Beli: <span class="font-medium">@rupiah($product->sale_price)</span></div>
                            @endif
                            @if ($product->canBeRented())
                                <div>Sewa: <span class="font-medium">@rupiah($product->rent_price_per_day)</span>/hari</div>
                            @endif
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $products->links() }}
        </div>
    @endif
@endsection